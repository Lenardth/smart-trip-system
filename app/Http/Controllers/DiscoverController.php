<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\DestinationSearch;
use App\Models\MoodCategory;
use App\Services\PexelsService;
use App\Contracts\GeoapifyInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscoverController extends Controller
{
    public function __construct(
        private PexelsService     $pexels,
        private GeoapifyInterface $geoapify,
    ) {}

    public function index(Request $request)
    {
        return view('discover.index', [
            'countries'      => $this->geoapify->getCountries(),
            'moodCategories' => MoodCategory::active()->ordered()->get(),
            'heroImage'      => $this->pexels->getHeroImage('discover'),
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q'      => 'nullable|string|max:120',
            'search' => 'nullable|string|max:120',
            'region' => 'nullable|string|max:10',
            'mood'   => 'nullable|string|max:60',
        ]);

        $rawQuery   = trim((string) ($validated['q'] ?? $validated['search'] ?? ''));
        $regionCode = !empty($validated['region']) ? strtoupper($validated['region']) : null;
        $mood       = $validated['mood'] ?? null;

        if (!$rawQuery && !$regionCode && !$mood) {
            $destinations = Destination::active()
                ->ordered()
                ->limit(16)
                ->get()
                ->map(fn($d) => $this->format($d->toArray()))
                ->toArray();

            return response()->json(['destinations' => $destinations, 'source' => 'featured']);
        }

        $resolvedQuery = $this->resolveAlias($rawQuery);
        $searchTerms   = array_unique(array_filter([$rawQuery, $resolvedQuery]));
        $dbCount       = $this->dbQuery($searchTerms, $regionCode, $mood)->count();

        // Resolve country code from query if not already provided (e.g. user typed "Algeria")
        $resolvedCountryCode = $regionCode;
        if ($dbCount < 5) {
            $resolvedCountryCode = $this->fetchAndStoreFromApi($resolvedQuery ?: $rawQuery, $regionCode, $mood);
        }

        // Build final effective region code (original or resolved from country name)
        $effectiveRegion = $resolvedCountryCode ?? $regionCode;

        $destinations = $this->dbQuery($searchTerms, $effectiveRegion, $mood)
            ->orderBy('display_order')
            ->limit(24)
            ->get()
            ->map(fn($d) => $this->format($d->toArray()))
            ->toArray();

        $this->logSearch($request, $rawQuery, $resolvedQuery, $effectiveRegion, $mood, count($destinations));

        return response()->json([
            'destinations'   => $destinations,
            'count'          => count($destinations),
            'resolved_query' => $resolvedQuery !== $rawQuery ? $resolvedQuery : null,
            'source'         => 'database',
        ]);
    }

    public function show(Destination $destination)
    {
        $countryCode = $destination->country_code;
        $countryName = $destination->country ?: $destination->region ?: 'Global';
        $details     = $countryCode ? $this->geoapify->countryDetails($countryCode) : [];
        $tourism     = $this->geoapify->searchWikipediaSummary("Tourism in {$countryName}");

        if (empty($tourism['extract'])) {
            $tourism = $this->geoapify->searchWikipediaSummary($countryName);
        }

        $heroImage  = $this->pexels->getRandomPhoto($destination->name . ' ' . $countryName);
        $highlights = $this->extractHighlights($tourism['extract'] ?? $tourism['description'] ?? '', 4);

        return view('discover.show', compact('destination', 'details', 'tourism', 'highlights', 'heroImage'));
    }

    public function search(Request $request): JsonResponse
    {
        return $this->list($request);
    }

    /**
     * Get recent destination searches for the authenticated user
     */
    public function recentSearches(): JsonResponse
    {
        $searches = DestinationSearch::byUser(Auth::id())
            ->recent(20)
            ->get(['id', 'query', 'resolved_query', 'region_code', 'mood', 'results_count', 'created_at']);

        return response()->json(['searches' => $searches]);
    }

    private function resolveAlias(string $query): string
    {
        if (!$query) {
            return $query;
        }

        $aliases = config('discover.aliases', []);
        $lower   = strtolower(trim($query));

        if (isset($aliases[$lower])) {
            return $aliases[$lower];
        }

        foreach ($aliases as $alias => $canonical) {
            if (str_starts_with($alias, $lower) || str_starts_with($lower, $alias)) {
                return $canonical;
            }
        }

        return $query;
    }

    private function dbQuery(array $terms, ?string $regionCode, ?string $mood)
    {
        return Destination::active()
            ->when(!empty($terms), function ($query) use ($terms) {
                $query->where(function ($q) use ($terms) {
                    foreach ($terms as $term) {
                        // Prioritize exact country matches
                        $q->orWhere('country', 'like', "%{$term}%")
                          ->orWhere('name', 'like', "%{$term}%")
                          ->orWhere('region', 'like', "%{$term}%")
                          ->orWhere('description', 'like', "%{$term}%");
                    }
                });
            })
            ->when($regionCode, fn($q) => $q->where('country_code', $regionCode))
            ->when($mood,       fn($q) => $q->whereJsonContains('tags', $mood));
    }

    private function fetchAndStoreFromApi(string $query, ?string $countryCode, ?string $mood): ?string
    {
        try {
            // Resolve whether the query is a country name → get its ISO code
            // so we can search for cities *within* that country
            $resolvedCountryCode = $countryCode;
            $cityQuery           = $query;

            if ($query && !$countryCode) {
                $geo = Http::timeout(8)
                    ->withHeaders(['User-Agent' => 'SmartTripPlanner/1.0'])
                    ->get('https://nominatim.openstreetmap.org/search', [
                        'q'              => $query,
                        'format'         => 'json',
                        'limit'          => 1,
                        'addressdetails' => 1,
                        'featuretype'    => 'country',
                    ]);

                if ($geo->successful() && !empty($geo->json())) {
                    $hit  = $geo->json()[0];
                    $type = $hit['type'] ?? '';
                    // If Nominatim says this is a country-level result, switch to city search
                    if (in_array($type, ['country', 'administrative'], true) &&
                        isset($hit['address']['country_code'])) {
                        $resolvedCountryCode = strtoupper($hit['address']['country_code']);
                        $cityQuery           = 'city';   // search for cities inside the country
                    }
                }
            }

            $places = $this->geoapify->searchDestinations(
                $cityQuery ?: 'popular travel city',
                $resolvedCountryCode,
                $mood,
                30
            );

            $addedCount = 0;
            foreach ($places as $place) {
                $sourceId = $place['id'] ?? null;

                if (!$sourceId || empty($place['name']) || $place['name'] === 'Unknown place') {
                    continue;
                }

                $destination = Destination::firstOrNew([
                    'source'    => 'openstreetmap',
                    'source_id' => $sourceId,
                ]);

                // Skip if already fully populated
                if ($destination->exists && !empty($destination->name)) {
                    $addedCount++;
                    continue;
                }

                $imageQuery = trim(($place['name'] ?? '') . ' ' . ($place['country'] ?? '') . ' travel');
                $imageUrl   = $this->pexels->getRandomPhoto($imageQuery)
                    ?? $this->imageFallback($imageQuery);

                $destination->fill([
                    'name'         => $place['name'],
                    'country'      => $place['country'] ?? ($place['region'] ?? 'Unknown'),
                    'country_code' => $place['country_code'] ?? ($resolvedCountryCode ?? null),
                    'region'       => $place['region'] ?? $place['country'] ?? 'Global',
                    'description'  => $this->buildDescription($place),
                    'image_url'    => $destination->image_url ?: $imageUrl,
                    'price_from'   => 0,
                    'tags'         => $this->buildTags($place, $mood),
                    'lat'          => $place['lat'] ?? null,
                    'lng'          => $place['lon'] ?? null,
                    'raw_data'     => $place,
                    'is_active'    => true,
                ]);

                if (!$destination->display_order) {
                    $destination->display_order = 100 + $addedCount;
                }

                $destination->save();
                $addedCount++;

                if ($addedCount >= 20) {
                    break;
                }
            }

            Log::info('Discover API fetch completed', [
                'query'        => $query,
                'country_code' => $resolvedCountryCode,
                'added'        => $addedCount,
                'fetched'      => count($places),
            ]);

            return $resolvedCountryCode;
        } catch (\Throwable $e) {
            Log::warning('Discover API fetch failed', ['query' => $query, 'error' => $e->getMessage()]);
            return $countryCode;
        }
    }

    private function format(array $d): array
    {
        $name    = $d['name'] ?? '';
        $country = $d['country'] ?? '';

        return [
            'id'           => $d['id'],
            'name'         => $name,
            'country'      => $country,
            'country_code' => $d['country_code'] ?? null,
            'region'       => $d['region'] ?? $country,
            'description'  => $d['description'] ?? '',
            'image_url'    => $d['image_url'] ?: $this->imageFallback("{$name} {$country} travel"),
            'price_from'   => $d['price_from'] ?? 0,
            'tags'         => is_array($d['tags']) ? $d['tags'] : (json_decode($d['tags'] ?? '[]', true) ?? []),
            'lat'          => $d['lat'] ?? null,
            'lng'          => $d['lng'] ?? null,
            'is_featured'  => (bool) ($d['is_featured'] ?? false),
            'detail_url'   => route('discover.place.show', ['destination' => $d['id']]),
            'plan_url'     => route('plan-trip') . '?destination=' . urlencode($name),
        ];
    }

    private function buildDescription(array $place): string
    {
        if (!empty($place['description']) && strlen($place['description']) > 20) {
            return $place['description'];
        }

        $parts    = array_filter([$place['city'] ?? null, $place['region'] ?? null, $place['country'] ?? null]);
        $type     = ucwords(str_replace('_', ' ', $place['type'] ?? $place['category'] ?? ''));
        $location = implode(', ', $parts);

        return $type && $location
            ? "{$type} in {$location}."
            : ($location ?: 'A destination worth exploring.');
    }

    private function buildTags(array $place, ?string $mood): array
    {
        $tags    = $mood ? [$mood] : [];
        $typeMap = config('discover.type_tags', []);
        $type    = strtolower($place['type'] ?? $place['category'] ?? '');

        foreach ($typeMap as $keyword => $tag) {
            if (str_contains($type, $keyword)) {
                $tags[] = $tag;
                break;
            }
        }

        return array_values(array_unique(array_filter($tags)));
    }

    private function imageFallback(string $query): string
    {
        $base = config('services.image_fallback.base_url', 'https://placehold.co/800x600');

        return $base . '?' . urlencode($query);
    }

    private function logSearch(
        Request $request,
        string  $rawQuery,
        string  $resolvedQuery,
        ?string $regionCode,
        ?string $mood,
        int     $count
    ): void {
        if (!$rawQuery) {
            return;
        }

        try {
            // Check if a similar search was already logged today
            $alreadyLogged = \App\Models\DestinationSearch::alreadyLoggedToday(
                Auth::id(),
                $rawQuery,
                $regionCode,
                $mood
            );

            if (!$alreadyLogged) {
                \App\Models\DestinationSearch::create([
                    'user_id'        => Auth::id(),
                    'query'          => $rawQuery,
                    'resolved_query' => $resolvedQuery !== $rawQuery ? $resolvedQuery : null,
                    'region_code'    => $regionCode,
                    'mood'           => $mood,
                    'results_count'  => $count,
                    'ip_address'     => $request->ip(),
                    'source'         => 'web',
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('DestinationSearch log failed: ' . $e->getMessage());
        }
    }

    private function extractHighlights(string $text, int $max = 4): array
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', strip_tags($text));

        if (!is_array($sentences) || empty($sentences)) {
            return [];
        }

        return array_values(array_filter(array_slice($sentences, 0, $max)));
    }
}

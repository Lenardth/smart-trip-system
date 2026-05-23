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
use Illuminate\Support\Facades\Cache;
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

        // No filters — return featured destinations from DB only.
        if (!$rawQuery && !$regionCode && !$mood) {
            $destinations = Destination::active()
                ->ordered()
                ->limit(config('api.pagination.per_page', 48))
                ->get()
                ->map(fn($d) => $this->format($d->toArray()))
                ->pipe(fn($col) => $this->deduplicateByCountry($col->toArray()));

            return response()->json(['destinations' => $destinations, 'source' => 'featured']);
        }

        $resolvedQuery  = $this->resolveAlias($rawQuery);
        $searchTerms    = array_unique(array_filter([$rawQuery, $resolvedQuery]));
        $effectiveQuery = $resolvedQuery ?: $rawQuery;

        // Always fetch from the external API for any search so new places are
        // discovered and saved. Cache the resolved country code for 30 min per
        // unique (query, region, mood) combination to avoid hammering Nominatim.
        $cacheKey = 'discover_api_' . md5("{$effectiveQuery}|{$regionCode}|{$mood}");
        $resolvedCountryCode = Cache::remember(
            $cacheKey,
            now()->addMinutes(30),
            fn () => $this->fetchAndStoreFromApi($effectiveQuery, $regionCode, $mood)
        );

        $effectiveRegion = $resolvedCountryCode ?? $regionCode;

        $destinations = $this->dbQuery($searchTerms, $effectiveRegion, $mood)
            ->orderBy('display_order')
            ->limit(config('api.pagination.per_page', 60))
            ->get()
            ->map(fn($d) => $this->format($d->toArray()))
            ->pipe(fn($col) => $this->deduplicateByCountry($col->toArray()));

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
        // Nominatim-friendly search terms for each mood when no text query is given.
        // These are concrete, searchable keywords that return real geographical results.
        static $moodQueries = [
            'Beach'       => 'beach bay coast',
            'Cultural'    => 'museum historic city',
            'Nature'      => 'national park nature reserve',
            'Adventurous' => 'mountain peak hiking',
            'Romantic'    => 'viewpoint castle garden',
            'Foodie'      => 'food market',
            'Relaxed'     => 'resort spa garden',
            'Eco-Travel'  => 'nature reserve wildlife',
            'Photography' => 'viewpoint scenic landscape',
        ];

        try {
            // Resolve whether the query is a country name → get its ISO code
            // so we can search for cities *within* that country
            $resolvedCountryCode = $countryCode;
            $cityQuery           = $query;

            // Check whether the query is a known country name using the cached
            // countries list. Nominatim's featuretype=country is unreliable — it
            // misclassifies capital cities (Budapest, Singapore) as administrative
            // country boundaries, so we avoid that HTTP round-trip entirely.
            if ($query && !$countryCode) {
                $countries = $this->geoapify->getCountries();
                $matched   = collect($countries)->first(
                    fn ($c) => strtolower($c['name']) === strtolower($query)
                );
                if ($matched) {
                    $resolvedCountryCode = $matched['code'];
                    $cityQuery           = 'city';
                }
            }

            // For mood-only searches (empty query), use a mood-specific keyword so
            // Nominatim returns places that can actually be mood-filtered.
            if (!$cityQuery) {
                $cityQuery = ($mood && isset($moodQueries[$mood]))
                    ? $moodQueries[$mood]
                    : 'popular travel destination';
            }

            $destinationFetchLimit = config('api.limits.destination_api_fetch', 20);
            $places = $this->geoapify->searchDestinations(
                $cityQuery,
                $resolvedCountryCode,
                $mood,
                $destinationFetchLimit
            );

            $addedCount = 0;
            $fetchLimit = config('api.limits.destination_api_fetch', 20);
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

                // Use only the place name for Pexels so the query is always in
                // English — raw country names from Nominatim can be in local script.
                $imageQuery = trim(($place['name'] ?? 'travel destination') . ' travel');
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
                    $displayOrderOffset = config('api.limits.destination_display_order_offset', 100);
                    $destination->display_order = $displayOrderOffset + $addedCount;
                }

                $destination->save();
                $addedCount++;

                if ($addedCount >= $fetchLimit) {
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
            'image_url'    => $this->resolveImage($d['image_url'] ?? '', $name),
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
        $seed = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($query)));
        return "https://picsum.photos/seed/{$seed}/800/560";
    }

    private function resolveImage(string $stored, string $name): string
    {
        $isBad = empty($stored)
            || str_contains($stored, 'placehold.co')
            || str_contains($stored, 'source.unsplash.com');

        if ($isBad) {
            $img = $this->pexels->getRandomPhoto("{$name} travel");
            return $img ?? $this->imageFallback("{$name} travel");
        }

        return $stored;
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
            $alreadyLogged = DestinationSearch::alreadyLoggedToday(
                Auth::id(),
                $rawQuery,
                $regionCode,
                $mood
            );

            if (!$alreadyLogged) {
                DestinationSearch::create([
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

    private function deduplicateByCountry(array $destinations, int $limit = 16): array
    {
        $seen   = [];
        $result = [];

        foreach ($destinations as $d) {
            $key = strtolower(trim($d['country'] ?? $d['region'] ?? 'unknown'));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $result[]   = $d;
            if (count($result) >= $limit) {
                break;
            }
        }

        return $result;
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

<?php

namespace App\Services;

use App\Contracts\GeoapifyInterface;
use App\Models\Destination;
use App\Models\DestinationSearch;
use App\Models\MoodCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DiscoverService
{
    public function __construct(
        private PexelsService $pexels,
        private GeoapifyInterface $geoapify,
        private DestinationMatchScoringService $scoring,
    ) {}

    public function index()
    {
        return view('discover.index', [
            'countries' => $this->geoapify->getCountries(),
            'moodCategories' => $this->moodCategories(),
            'heroImage' => $this->pexels->getHeroImage('discover'),
        ]);
    }

    private function moodCategories(): Collection
    {
        $categories = MoodCategory::active()->ordered()->get();

        if ($categories->isNotEmpty()) {
            return $categories;
        }

        return collect(config('discover.mood_categories', []))
            ->filter(fn (array $category) => $category['is_active'] ?? true)
            ->sortBy([
                ['display_order', 'asc'],
                ['name', 'asc'],
            ])
            ->values()
            ->map(fn (array $category) => new MoodCategory($category));
    }

    public function list(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:120',
            'search' => 'nullable|string|max:120',
            'region' => 'nullable|string|max:10',
            'mood' => 'nullable|string|max:60',
            'budget' => 'nullable|string|max:50',
            'companion' => 'nullable|string|max:50',
        ]);

        $rawQuery = trim((string) ($validated['q'] ?? $validated['search'] ?? ''));
        $regionCode = ! empty($validated['region']) ? strtoupper($validated['region']) : null;
        $mood = $validated['mood'] ?? null;
        $preferences = $this->preferences($validated);
        $requestPayload = [
            'query' => $rawQuery,
            'region_code' => $regionCode,
            'mood' => $mood,
            'budget' => $preferences['budget'] ?? null,
            'companion' => $preferences['companion'] ?? null,
        ];
        $searchHash = $this->searchHash($requestPayload);

        // No filters — return featured destinations from DB only.
        if (! $rawQuery && ! $regionCode && ! $this->scoring->hasPreferences($preferences)) {
            $destinations = $this->scoring->rank($this->featuredDestinations(), $preferences);

            if (empty($destinations)) {
                $this->fetchAndStoreFromApi('popular travel destination', null, null);
                $destinations = $this->scoring->rank($this->featuredDestinations(), $preferences);
            }

            $this->logSearch($request, 'all', 'all', null, null, $destinations, $searchHash, false, $requestPayload);

            return response()->json(['destinations' => $destinations, 'source' => 'featured']);
        }

        $resolvedQuery = $this->resolveAlias($rawQuery);
        $queryCountryCode = $regionCode
            ?: $this->resolveCountryCodeFromQuery($resolvedQuery ?: $rawQuery)
            ?: $this->resolveCountryCodeFromDatabase($resolvedQuery ?: $rawQuery);

        $cached = DestinationSearch::where('search_hash', $searchHash)
            ->whereNotNull('response_payload')
            ->where('created_at', '>=', now()->subDay())
            ->latest()
            ->first();

        if ($cached) {
            $destinations = $this->filterFormattedDestinationsByCountry(
                $cached->response_payload['destinations'] ?? [],
                $queryCountryCode
            );
            $destinations = $this->scoring->rank($this->limitForDisplay($destinations), $preferences);

            if ($queryCountryCode && empty($destinations)) {
                $cached = null;
            }
        }

        if ($cached) {
            $this->logSearch(
                $request,
                $rawQuery ?: 'all',
                $cached->resolved_query ?: $rawQuery,
                $regionCode,
                $mood,
                $destinations,
                $searchHash,
                true,
                $requestPayload
            );

            return response()->json([
                'destinations' => $destinations,
                'count' => count($destinations),
                'resolved_query' => $cached->resolved_query,
                'source' => 'database-cache',
                'cached' => true,
            ]);
        }

        $searchTerms = $queryCountryCode
            ? []
            : array_unique(array_filter([$rawQuery, $resolvedQuery]));
        $effectiveQuery = $resolvedQuery ?: $rawQuery;
        $localResults = $this->scoring->rank(
            $this->queryDestinations($searchTerms, $queryCountryCode ?? $regionCode, $mood),
            $preferences
        );

        if (! empty($localResults)) {
            $this->logSearch(
                $request,
                $rawQuery ?: 'all',
                $resolvedQuery,
                $queryCountryCode ?? $regionCode,
                $mood,
                $localResults,
                $searchHash,
                false,
                $requestPayload
            );

            return response()->json([
                'destinations' => $localResults,
                'count' => count($localResults),
                'resolved_query' => $resolvedQuery !== $rawQuery ? $resolvedQuery : null,
                'source' => 'database-local',
                'cached' => false,
            ]);
        }

        // Always fetch from the external API for any search so new places are
        // discovered and saved. Cache the resolved country code for 30 min per
        // unique (query, region, mood) combination to avoid hammering Nominatim.
        $cacheKey = 'discover_api_'.md5("{$effectiveQuery}|{$regionCode}|{$mood}");
        $resolvedCountryCode = Cache::remember(
            $cacheKey,
            now()->addMinutes(30),
            fn () => $this->fetchAndStoreFromApi($effectiveQuery, $regionCode, $mood)
        );

        $effectiveRegion = $resolvedCountryCode ?? $queryCountryCode ?? $regionCode;

        $destinations = $this->scoring->rank(
            $this->queryDestinations($searchTerms, $effectiveRegion, $mood),
            $preferences
        );

        if (empty($destinations)) {
            $fallbackQuery = $effectiveRegion ? 'popular travel destination' : ($effectiveQuery ?: 'popular travel destination');
            $this->fetchAndStoreFromApi($fallbackQuery, $effectiveRegion, $mood);
            $destinations = $this->scoring->rank($this->queryDestinations([], $effectiveRegion, $mood), $preferences);
        }

        if (empty($destinations) && $mood) {
            $destinations = $this->scoring->rank($this->queryDestinations($searchTerms, $effectiveRegion, null), $preferences);
        }

        if (empty($destinations) && $effectiveRegion) {
            $this->ensureCountryFallbackDestination($effectiveRegion, $mood);
            $destinations = $this->scoring->rank($this->queryDestinations([], $effectiveRegion, $mood), $preferences);
        }

        $this->logSearch(
            $request,
            $rawQuery ?: 'all',
            $resolvedQuery,
            $effectiveRegion,
            $mood,
            $destinations,
            $searchHash,
            false,
            $requestPayload
        );

        return response()->json([
            'destinations' => $destinations,
            'count' => count($destinations),
            'resolved_query' => $resolvedQuery !== $rawQuery ? $resolvedQuery : null,
            'source' => 'database',
            'cached' => false,
        ]);
    }

    private function searchHash(array $payload): string
    {
        ksort($payload);

        return hash('sha256', json_encode($payload));
    }

    private function preferences(array $validated): array
    {
        return array_filter([
            'mood' => $validated['mood'] ?? null,
            'budget' => $validated['budget'] ?? null,
            'companion' => $validated['companion'] ?? null,
        ], fn ($value) => filled($value));
    }

    private function featuredDestinations(): array
    {
        return Destination::active()
            ->ordered()
            ->limit(config('api.pagination.per_page', 48))
            ->get()
            ->map(fn ($d) => $this->format($d->toArray()))
            ->pipe(fn ($col) => $this->deduplicateByCountry($col->toArray()));
    }

    private function queryDestinations(array $terms, ?string $regionCode, ?string $mood): array
    {
        $destinations = $this->dbQuery($terms, $regionCode, $mood)
            ->orderBy('display_order')
            ->limit(config('api.pagination.per_page', 60))
            ->get()
            ->map(fn ($d) => $this->format($d->toArray()))
            ->toArray();

        return $regionCode
            ? $this->limitForDisplay($destinations)
            : $this->deduplicateByCountry($destinations, $this->displayLimit());
    }

    public function show(Destination $destination)
    {
        $countryCode = $destination->country_code;
        $countryName = $destination->country ?: $destination->region ?: 'Global';
        $details = $countryCode ? $this->geoapify->countryDetails($countryCode) : [];
        $tourism = $this->geoapify->searchWikipediaSummary("Tourism in {$countryName}");

        if (empty($tourism['extract'])) {
            $tourism = $this->geoapify->searchWikipediaSummary($countryName);
        }

        $heroImage = $this->pexels->getRandomPhoto($destination->name.' '.$countryName);
        $highlights = $this->extractHighlights($tourism['extract'] ?? $tourism['description'] ?? '', 4);

        return view('discover.show', compact('destination', 'details', 'tourism', 'highlights', 'heroImage'));
    }

    public function search(Request $request): JsonResponse
    {
        return $this->list($request);
    }

    private function resolveAlias(string $query): string
    {
        if (! $query) {
            return $query;
        }

        $aliases = config('discover.aliases', []);
        $lower = strtolower(trim($query));

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

    private function resolveCountryCodeFromQuery(string $query): ?string
    {
        if (! $query) {
            return null;
        }

        $needle = strtolower(trim($query));

        try {
            $matched = collect($this->geoapify->getCountries())->first(
                fn ($country) => strtolower($country['name'] ?? '') === $needle
            );

            return $matched['code'] ?? null;
        } catch (\Throwable $e) {
            Log::warning('Country lookup failed', ['query' => $query, 'error' => $e->getMessage()]);

            return null;
        }
    }

    private function resolveCountryCodeFromDatabase(string $query): ?string
    {
        if (! $query) {
            return null;
        }

        $destination = Destination::active()
            ->whereNotNull('country_code')
            ->where('country', $query)
            ->first();

        return $destination ? strtoupper($destination->country_code) : null;
    }

    private function dbQuery(array $terms, ?string $regionCode, ?string $mood)
    {
        $countryName = $regionCode ? $this->countryNameFromCode($regionCode) : null;

        return Destination::active()
            ->when(! empty($terms), function ($query) use ($terms) {
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
            ->when($regionCode, function ($q) use ($regionCode, $countryName) {
                $q->where(function ($regionQuery) use ($regionCode, $countryName) {
                    $regionQuery->where('country_code', strtoupper($regionCode));

                    if ($countryName) {
                        $regionQuery->orWhere('country', $countryName);
                    }
                });
            })
            ->when($mood, fn ($q) => $q->whereJsonContains('tags', $mood));
    }

    private function countryNameFromCode(string $countryCode): ?string
    {
        $countryCode = strtoupper($countryCode);

        try {
            $matched = collect($this->geoapify->getCountries())->first(
                fn ($country) => strtoupper($country['code'] ?? '') === $countryCode
            );

            if (! empty($matched['name'])) {
                return $matched['name'];
            }
        } catch (\Throwable $e) {
            Log::warning('Country code lookup failed', ['code' => $countryCode, 'error' => $e->getMessage()]);
        }

        return config("discover.country_names.{$countryCode}");
    }

    private function filterFormattedDestinationsByCountry(array $destinations, ?string $countryCode): array
    {
        if (! $countryCode) {
            return $destinations;
        }

        $countryCode = strtoupper($countryCode);
        $countryName = $this->countryNameFromCode($countryCode);

        return array_values(array_filter($destinations, function (array $destination) use ($countryCode, $countryName) {
            if (strtoupper((string) ($destination['country_code'] ?? '')) === $countryCode) {
                return true;
            }

            return $countryName
                && strcasecmp((string) ($destination['country'] ?? ''), $countryName) === 0;
        }));
    }

    private function ensureCountryFallbackDestination(string $countryCode, ?string $mood): void
    {
        $countryCode = strtoupper($countryCode);
        $countryName = $this->countryNameFromCode($countryCode) ?: $countryCode;

        $destination = Destination::firstOrNew([
            'source' => 'country-fallback',
            'source_id' => $countryCode,
        ]);

        if ($destination->exists && $destination->is_active) {
            return;
        }

        $imageQuery = "{$countryName} travel";

        $destination->fill([
            'name' => $countryName,
            'country' => $countryName,
            'country_code' => $countryCode,
            'region' => $countryName,
            'description' => "Explore {$countryName} and discover places to match your travel style.",
            'image_url' => $destination->image_url ?: ($this->pexels->getRandomPhoto($imageQuery) ?? $this->imageFallback($imageQuery)),
            'price_from' => 0,
            'tags' => array_values(array_unique(array_filter([$mood, 'Cultural', 'Nature']))),
            'raw_data' => ['fallback' => true, 'country_code' => $countryCode],
            'is_active' => true,
        ]);

        if (! $destination->display_order) {
            $destination->display_order = config('api.limits.destination_display_order_offset', 100);
        }

        $destination->save();
    }

    private function fetchAndStoreFromApi(string $query, ?string $countryCode, ?string $mood): ?string
    {
        // Nominatim-friendly search terms for each mood when no text query is given.
        // These are concrete, searchable keywords that return real geographical results.
        static $moodQueries = [
            'Beach' => 'beach bay coast',
            'Cultural' => 'museum historic city',
            'Nature' => 'national park nature reserve',
            'Adventurous' => 'mountain peak hiking',
            'Romantic' => 'viewpoint castle garden',
            'Foodie' => 'food market',
            'Relaxed' => 'resort spa garden',
            'Eco-Travel' => 'nature reserve wildlife',
            'Photography' => 'viewpoint scenic landscape',
        ];

        try {
            // Resolve whether the query is a country name → get its ISO code
            // so we can search for cities *within* that country
            $resolvedCountryCode = $countryCode;
            $cityQuery = $query;

            // Check whether the query is a known country name using the cached
            // countries list. Nominatim's featuretype=country is unreliable — it
            // misclassifies capital cities (Budapest, Singapore) as administrative
            // country boundaries, so we avoid that HTTP round-trip entirely.
            if ($query && ! $countryCode) {
                $matchedCode = $this->resolveCountryCodeFromQuery($query);

                if ($matchedCode) {
                    $resolvedCountryCode = $matchedCode;
                    $cityQuery = 'city';
                }
            }

            // For mood-only searches (empty query), use a mood-specific keyword so
            // Nominatim returns places that can actually be mood-filtered.
            if (! $cityQuery) {
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
            foreach ($places as $place) {
                $sourceId = $place['id'] ?? null;

                if (! $sourceId || empty($place['name']) || $place['name'] === 'Unknown place') {
                    continue;
                }

                $destination = Destination::firstOrNew([
                    'source' => 'openstreetmap',
                    'source_id' => $sourceId,
                ]);

                // Skip if already fully populated
                if ($destination->exists && ! empty($destination->name)) {
                    $addedCount++;

                    continue;
                }

                // Use only the place name for Pexels so the query is always in
                // English — raw country names from Nominatim can be in local script.
                $imageQuery = trim(($place['name'] ?? 'travel destination').' travel');
                $imageUrl = $this->pexels->getRandomPhoto($imageQuery)
                    ?? $this->imageFallback($imageQuery);

                $destination->fill([
                    'name' => $place['name'],
                    'country' => $place['country'] ?? ($place['region'] ?? 'Unknown'),
                    'country_code' => $place['country_code'] ?? ($resolvedCountryCode ?? null),
                    'region' => $place['region'] ?? $place['country'] ?? 'Global',
                    'description' => $this->buildDescription($place),
                    'image_url' => $destination->image_url ?: $imageUrl,
                    'price_from' => 0,
                    'tags' => $this->buildTags($place, $mood),
                    'lat' => $place['lat'] ?? null,
                    'lng' => $place['lon'] ?? null,
                    'raw_data' => $place,
                    'is_active' => true,
                ]);

                if (! $destination->display_order) {
                    $displayOrderOffset = config('api.limits.destination_display_order_offset', 100);
                    $destination->display_order = $displayOrderOffset + $addedCount;
                }

                $destination->save();
                $addedCount++;

                if ($addedCount >= $destinationFetchLimit) {
                    break;
                }
            }

            Log::info('Discover API fetch completed', [
                'query' => $query,
                'country_code' => $resolvedCountryCode,
                'added' => $addedCount,
                'fetched' => count($places),
            ]);

            return $resolvedCountryCode;
        } catch (\Throwable $e) {
            Log::warning('Discover API fetch failed', ['query' => $query, 'error' => $e->getMessage()]);

            return $countryCode;
        }
    }

    private function format(array $d): array
    {
        $name = $d['name'] ?? '';
        $country = $d['country'] ?? '';

        return [
            'id' => $d['id'],
            'name' => $name,
            'country' => $country,
            'country_code' => $d['country_code'] ?? null,
            'region' => $d['region'] ?? $country,
            'description' => $d['description'] ?? '',
            'image_url' => $this->resolveImage($d['image_url'] ?? '', $name),
            'price_from' => $d['price_from'] ?? 0,
            'tags' => is_array($d['tags']) ? $d['tags'] : (json_decode($d['tags'] ?? '[]', true) ?? []),
            'lat' => $d['lat'] ?? null,
            'lng' => $d['lng'] ?? null,
            'is_featured' => (bool) ($d['is_featured'] ?? false),
            'detail_url' => route('discover.place.show', ['destination' => $d['id']]),
            'plan_url' => route('plan-trip').'?destination='.urlencode($name).'&country='.urlencode($country),
        ];
    }

    private function buildDescription(array $place): string
    {
        if (! empty($place['description']) && strlen($place['description']) > 20) {
            return $place['description'];
        }

        $parts = array_filter([$place['city'] ?? null, $place['region'] ?? null, $place['country'] ?? null]);
        $type = ucwords(str_replace('_', ' ', $place['type'] ?? $place['category'] ?? ''));
        $location = implode(', ', $parts);

        return $type && $location
            ? "{$type} in {$location}."
            : ($location ?: 'A destination worth exploring.');
    }

    private function buildTags(array $place, ?string $mood): array
    {
        $tags = $mood ? [$mood] : [];
        $typeMap = config('discover.type_tags', []);
        $type = strtolower($place['type'] ?? $place['category'] ?? '');

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
        string $rawQuery,
        string $resolvedQuery,
        ?string $regionCode,
        ?string $mood,
        array $destinations,
        string $searchHash,
        bool $cacheHit,
        array $requestPayload
    ): void {
        try {
            DestinationSearch::create([
                'user_id' => Auth::id(),
                'search_hash' => $searchHash,
                'request_payload' => $requestPayload,
                'response_payload' => ['destinations' => $destinations],
                'query' => $rawQuery,
                'resolved_query' => $resolvedQuery !== $rawQuery ? $resolvedQuery : null,
                'region_code' => $regionCode,
                'mood' => $mood,
                'results_count' => count($destinations),
                'cache_hit' => $cacheHit,
                'ip_address' => $request->ip(),
                'source' => 'web',
            ]);
        } catch (\Throwable $e) {
            Log::warning('DestinationSearch log failed: '.$e->getMessage());
        }
    }

    private function deduplicateByCountry(array $destinations, int $limit = 16): array
    {
        $seen = [];
        $result = [];

        foreach ($destinations as $d) {
            $key = strtolower(trim($d['country'] ?? $d['region'] ?? 'unknown'));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $result[] = $d;
            if (count($result) >= $limit) {
                break;
            }
        }

        return $result;
    }

    private function limitForDisplay(array $destinations): array
    {
        return array_slice($destinations, 0, $this->displayLimit());
    }

    private function displayLimit(): int
    {
        return (int) config('api.pagination.per_page', 16);
    }

    private function extractHighlights(string $text, int $max = 4): array
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', strip_tags($text));

        if (! is_array($sentences) || empty($sentences)) {
            return [];
        }

        return array_values(array_filter(array_slice($sentences, 0, $max)));
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Contracts\GeoapifyInterface;

class GeoapifyService implements GeoapifyInterface
{
    private array $overpassUrls = [
        'https://overpass.kumi.systems/api/interpreter',
        'https://maps.mail.ru/osm/tools/overpass/api/interpreter',
        'https://overpass-api.de/api/interpreter',
    ];

    private string $nominatimUrl = 'https://nominatim.openstreetmap.org';

    public function geocodeCity(string $city): ?array
    {
        $response = Http::timeout(10)
            ->withHeaders(['User-Agent' => 'SmartTripPlanner/1.0'])
            ->get("{$this->nominatimUrl}/search", [
                'q'              => $city,
                'format'         => 'json',
                'limit'          => 1,
                'addressdetails' => 1,
            ]);

        if (! $response->successful() || empty($response->json())) {
            return null;
        }

        $r   = $response->json()[0];
        $lat = (float) $r['lat'];
        $lon = (float) $r['lon'];

        $radius = 5000;
        if (isset($r['boundingbox'])) {
            [$minLat, $maxLat, $minLon, $maxLon] = $r['boundingbox'];
            $latKm    = abs($maxLat - $minLat) * 111;
            $lonKm    = abs($maxLon - $minLon) * 111 * cos(deg2rad($lat));
            $radiusKm = min(max($latKm, $lonKm) / 2, 50);
            $radius   = (int) ($radiusKm * 1000);
        }

        return ['lat' => $lat, 'lon' => $lon, 'radius' => $radius];
    }

    public function placesByCity(
        string $city,
        array $categories = [],
        int $limit = 100
    ): array {
        $geo = $this->geocodeCity($city);

        if (! $geo) {
            Log::warning('Overpass: geocode failed', ['city' => $city]);
            return [];
        }

        $lat    = $geo['lat'];
        $lon    = $geo['lon'];
        $radius = $geo['radius'];

        $query = "[out:json][timeout:20];(node[\"tourism\"~\"hotel|hostel|guest_house|motel|apartment|resort\"](around:{$radius},{$lat},{$lon});way[\"tourism\"~\"hotel|hostel|guest_house|motel|resort\"](around:{$radius},{$lat},{$lon}););out center {$limit};";

        foreach ($this->overpassUrls as $url) {
            try {
                $response = Http::timeout(25)
                    ->withHeaders(['User-Agent' => 'SmartTripPlanner/1.0'])
                    ->asForm()
                    ->post($url, ['data' => $query]);

                if ($response->successful()) {
                    $elements = $response->json()['elements'] ?? [];
                    return array_map(fn($el) => $this->mapElement($el, $city), $elements);
                }

                Log::warning('Overpass mirror failed', ['url' => $url, 'status' => $response->status()]);
            } catch (\Throwable $e) {
                Log::warning('Overpass mirror exception', ['url' => $url, 'error' => $e->getMessage()]);
            }
        }

        Log::error('All Overpass mirrors failed', ['city' => $city]);
        return [];
    }

    public function searchPlaces(string $query, ?string $countryCode = null, ?string $mood = null, int $limit = 50): array
    {
        $limit = min(max($limit, 1), 100);
        $params = [
            'q' => $query,
            'format' => 'json',
            'addressdetails' => 1,
            'limit' => $limit,
            'extratags' => 1,
            'namedetails' => 1,
        ];

        if ($countryCode) {
            $params['countrycodes'] = strtolower($countryCode);
        }

        try {
            $response = Http::timeout(12)
                ->withHeaders(['User-Agent' => 'SmartTripPlanner/1.0'])
                ->get("{$this->nominatimUrl}/search", $params);

            if (! $response->successful() || empty($response->json())) {
                Log::warning('Nominatim search failed', [
                    'query' => $query,
                    'country' => $countryCode,
                    'status' => $response->status(),
                ]);
                return [];
            }

            $places = array_map(fn(array $item) => $this->mapNominatimPlace($item), $response->json());

            if ($mood) {
                $places = $this->filterPlacesByMood($places, $mood);
            }

            return array_slice($places, 0, $limit);
        } catch (\Throwable $e) {
            Log::warning('Nominatim search exception', ['query' => $query, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Search for travel destinations (cities, tourist attractions, landmarks).
     * Returns clean destination data suitable for the Discover page.
     * Uses multiple Nominatim queries to find the best results.
     */
    public function searchDestinations(string $query, ?string $countryCode = null, ?string $mood = null, int $limit = 20): array
    {
        $limit = min(max($limit, 1), 50);

        // OSM class/type keywords that map well to each mood for Nominatim searches.
        $moodTypeMap = [
            'Cultural'    => ['museum', 'artwork', 'gallery', 'theatre', 'historic', 'monument', 'castle', 'ruins'],
            'Adventurous' => ['peak', 'camp_site', 'viewpoint', 'waterfall', 'national_park', 'nature_reserve'],
            'Relaxed'     => ['beach', 'spa', 'park', 'garden', 'resort', 'hot_spring'],
            'Romantic'    => ['viewpoint', 'castle', 'garden', 'beach', 'monument', 'historic'],
            'Foodie'      => ['restaurant', 'market', 'food_court', 'cafe', 'brewery', 'winery'],
            'Eco-Travel'  => ['nature_reserve', 'national_park', 'forest', 'wetland', 'protected_area'],
            'Beach'       => ['beach', 'bay', 'coast', 'island', 'lagoon'],
            'Nature'      => ['peak', 'forest', 'waterfall', 'lake', 'river', 'national_park', 'nature_reserve'],
            'Photography' => ['viewpoint', 'peak', 'waterfall', 'scenic', 'monument'],
        ];

        $results = [];

        // Strategy 1: Search for cities/places matching the query (skipped for empty query).
        if (!empty($query)) {
            $cityResults = $this->nominatimSearch($query, $countryCode, ['city', 'town', 'village', 'administrative'], $limit);
            $results = array_merge($results, $cityResults);
        }

        // Strategy 2: Mood-specific feature search.
        // For mood-only (empty query) we search each mood keyword individually so
        // Nominatim can match actual geographic features rather than a phrase.
        if ($mood && isset($moodTypeMap[$mood])) {
            $moodKeywords = $moodTypeMap[$mood];
            $featureTypes = ['tourism', 'natural', 'leisure', 'amenity', 'place'];

            if (empty($query)) {
                // Mood-only: run a targeted search per keyword (top 3) to maximise matches.
                foreach (array_slice($moodKeywords, 0, 3) as $keyword) {
                    $kwResults = $this->nominatimSearch($keyword, $countryCode, $featureTypes, (int) ceil($limit / 3));
                    $results   = array_merge($results, $kwResults);
                }
            } else {
                // Text + mood: search attractions matching the text query.
                $attractionResults = $this->nominatimSearch($query, $countryCode, $featureTypes, min($limit, 15));
                $results = array_merge($results, $attractionResults);
            }
        }

        // Strategy 3: Broad fallback if still empty and we have a text query.
        if (empty($results) && !empty($query)) {
            $broadResults = $this->nominatimSearch($query, $countryCode, [], $limit);
            $results = array_merge($results, $broadResults);
        }

        // Deduplicate by name+country.
        $seen   = [];
        $unique = [];
        foreach ($results as $place) {
            $key = strtolower(($place['name'] ?? '') . '|' . ($place['country'] ?? ''));
            if (!isset($seen[$key]) && !empty($place['name']) && $place['name'] !== 'Unknown place') {
                $seen[$key] = true;
                $unique[]   = $place;
            }
        }

        // Apply mood filter; keep all results if the filter would empty the list.
        if ($mood && !empty($unique)) {
            $moodFiltered = $this->filterPlacesByMood($unique, $mood);
            if (!empty($moodFiltered)) {
                $unique = $moodFiltered;
            }
        }

        return array_slice($unique, 0, $limit);
    }

    /**
     * Run a Nominatim search filtered by OSM classes/types.
     */
    private function nominatimSearch(string $query, ?string $countryCode, array $featureTypes, int $limit): array
    {
        if (empty($query)) {
            return [];
        }

        $params = [
            'q'              => $query,
            'format'         => 'json',
            'addressdetails' => 1,
            'limit'          => min($limit * 2, 50), // fetch extra, we'll filter
            'extratags'      => 1,
            'namedetails'    => 1,
        ];

        if ($countryCode) {
            $params['countrycodes'] = strtolower($countryCode);
        }

        try {
            $response = Http::timeout(12)
                ->withHeaders(['User-Agent' => 'SmartTripPlanner/1.0'])
                ->get("{$this->nominatimUrl}/search", $params);

            if (! $response->successful() || empty($response->json())) {
                return [];
            }

            $items = $response->json();

            // Filter to useful feature types if specified
            if (!empty($featureTypes)) {
                $items = array_filter($items, function (array $item) use ($featureTypes) {
                    $class = $item['class'] ?? '';
                    $type  = $item['type'] ?? '';
                    foreach ($featureTypes as $ft) {
                        if ($class === $ft || $type === $ft || str_contains($class, $ft) || str_contains($type, $ft)) {
                            return true;
                        }
                    }
                    return false;
                });
            }

            return array_values(array_map(
                fn(array $item) => $this->mapNominatimDestination($item),
                array_slice($items, 0, $limit)
            ));
        } catch (\Throwable $e) {
            Log::warning('Nominatim destination search failed', ['query' => $query, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Map a Nominatim result to a clean destination array.
     */
    private function mapNominatimDestination(array $item): array
    {
        $address = $item['address'] ?? [];
        $nameDetails = $item['namedetails'] ?? [];
        $extraTags = $item['extratags'] ?? [];

        // Prefer the proper name over display_name (which includes full address)
        $name = $nameDetails['name:en']
            ?? $nameDetails['name']
            ?? $address['city']
            ?? $address['town']
            ?? $address['village']
            ?? $address['county']
            ?? null;

        // If name is still the full display_name, extract just the first part
        if (!$name && isset($item['display_name'])) {
            $name = explode(',', $item['display_name'])[0];
        }

        $city    = $address['city'] ?? $address['town'] ?? $address['village'] ?? null;
        $region  = $address['state'] ?? $address['region'] ?? $address['county'] ?? null;
        $country = $address['country'] ?? null;
        $countryCode = isset($address['country_code']) ? strtoupper($address['country_code']) : null;

        // Build a meaningful description
        $type = $item['type'] ?? $item['class'] ?? 'place';
        $typeLabel = ucwords(str_replace('_', ' ', $type));
        $description = $extraTags['description'] ?? $extraTags['wikipedia'] ?? null;
        if (!$description) {
            $parts = array_filter([$typeLabel, $city, $country]);
            $description = implode(' · ', $parts);
        }

        // Build location label
        $locationParts = array_filter([$city ?: $region, $country]);
        $location = implode(', ', $locationParts) ?: ($country ?? 'Global');

        return [
            'id'           => 'osm_' . ($item['osm_type'] ?? 'n') . '_' . ($item['osm_id'] ?? uniqid()),
            'name'         => $name ?: 'Unknown place',
            'type'         => $type,
            'category'     => $item['class'] ?? $type,
            'city'         => $city,
            'region'       => $region ?? $country,
            'country'      => $country,
            'country_code' => $countryCode,
            'location'     => $location,
            'address'      => $item['display_name'] ?? null,
            'description'  => $description,
            'lat'          => isset($item['lat']) ? (float) $item['lat'] : null,
            'lon'          => isset($item['lon']) ? (float) $item['lon'] : null,
            'source'       => 'openstreetmap',
            'wikipedia'    => $extraTags['wikipedia'] ?? null,
            'website'      => $extraTags['website'] ?? null,
            'population'   => $extraTags['population'] ?? null,
        ];
    }

    public function getCountries(): array
    {
        return Cache::remember('geoapify_countries', now()->addDay(), function () {
            try {
                $response = Http::timeout(10)
                    ->get('https://restcountries.com/v3.1/all?fields=name,cca2');

                if (! $response->successful() || empty($response->json())) {
                    return [];
                }

                return collect($response->json())
                    ->map(fn(array $country) => [
                        'name' => $country['name']['common'] ?? $country['name']['official'] ?? '',
                        'code' => $country['cca2'] ?? '',
                    ])
                    ->filter(fn(array $country) => ! empty($country['name']) && ! empty($country['code']))
                    ->sortBy('name')
                    ->values()
                    ->all();
            } catch (\Throwable $e) {
                Log::warning('RestCountries API exception', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    public function countryDetails(string $countryCode): array
    {
        try {
            $response = Http::timeout(10)
                ->get("https://restcountries.com/v3.1/alpha/{$countryCode}");

            if (! $response->successful() || empty($response->json())) {
                return [];
            }

            $country = $response->json()[0] ?? $response->json();

            return [
                'name' => $country['name']['common'] ?? $country['name']['official'] ?? null,
                'capital' => is_array($country['capital']) ? implode(', ', $country['capital']) : ($country['capital'] ?? null),
                'region' => $country['region'] ?? null,
                'subregion' => $country['subregion'] ?? null,
                'population' => $country['population'] ?? null,
                'currency' => $this->formatCurrencies($country['currencies'] ?? []),
                'languages' => $this->formatLanguages($country['languages'] ?? []),
                'flag' => $country['flags']['svg'] ?? $country['flags']['png'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::warning('RestCountries country details failed', ['code' => $countryCode, 'error' => $e->getMessage()]);
            return [];
        }
    }

    public function searchWikipediaSummary(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'SmartTripPlanner/1.0'])
                ->get("https://en.wikipedia.org/api/rest_v1/page/summary/" . rawurlencode($query));

            if (! $response->successful()) {
                return [];
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::warning('Wikipedia summary fetch failed', ['query' => $query, 'error' => $e->getMessage()]);
            return [];
        }
    }

    private function formatCurrencies(array $currencies): string
    {
        return implode(', ', array_map(fn($currency) => ($currency['name'] ?? '') . ' (' . ($currency['symbol'] ?? '') . ')', $currencies));
    }

    private function formatLanguages(array $languages): string
    {
        return implode(', ', array_values($languages));
    }

    private function mapNominatimPlace(array $item): array
    {
        $address = $item['address'] ?? [];
        $city = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['hamlet'] ?? null;
        $region = $address['state'] ?? $address['region'] ?? $address['county'] ?? null;
        $country = $address['country'] ?? null;

        $fqName = $item['namedetails']['name'] ?? $address['attraction'] ?? $address['building'] ?? $item['display_name'] ?? null;

        return [
            'id' => 'nominatim_' . ($item['osm_type'] ?? 'place') . '_' . ($item['osm_id'] ?? uniqid()),
            'name' => $fqName ?: 'Unknown place',
            'type' => $item['type'] ?? $item['class'] ?? 'place',
            'category' => $item['class'] ?? $item['type'] ?? 'place',
            'city' => $city,
            'region' => $region,
            'country' => $country,
            'country_code' => isset($address['country_code']) ? strtoupper($address['country_code']) : null,
            'address' => $item['display_name'] ?? null,
            'description' => $item['type'] ? ucwords(str_replace('_', ' ', $item['type'])) : null,
            'lat' => isset($item['lat']) ? (float) $item['lat'] : null,
            'lon' => isset($item['lon']) ? (float) $item['lon'] : null,
            'image_url' => $this->imageUrlForPlace($item),
            'source' => 'openstreetmap',
        ];
    }

    private function filterPlacesByMood(array $places, string $mood): array
    {
        $mood = ucwords(strtolower($mood));
        $moodMap = [
            'Cultural' => ['historic', 'tourism', 'arts_centre', 'museum', 'theatre', 'gallery', 'library'],
            'Adventurous' => ['natural', 'tourism', 'peak', 'trailhead', 'waterfall', 'camp_site', 'mountain'],
            'Relaxed' => ['leisure', 'park', 'garden', 'spa', 'beach', 'resort'],
            'Romantic' => ['park', 'garden', 'viewpoint', 'hotel', 'castle', 'historic', 'monument'],
            'Foodie' => ['amenity', 'restaurant', 'cafe', 'market', 'food_court', 'brewery'],
            'Eco-Travel' => ['natural', 'protected_area', 'park', 'nature_reserve'],
            'Beach' => ['beach', 'coast', 'bay', 'lagoon', 'island'],
            'Nature' => ['natural', 'forest', 'waterway', 'river', 'lake', 'mountain'],
        ];

        $filters = $moodMap[$mood] ?? [];
        if (empty($filters)) {
            return $places;
        }

        return array_values(array_filter($places, function (array $place) use ($filters, $mood) {
            $type = strtolower($place['type'] ?? '');
            $category = strtolower($place['category'] ?? '');
            $address = strtolower($place['address'] ?? '');

            if (in_array($type, $filters, true) || in_array($category, $filters, true)) {
                return true;
            }

            foreach ($filters as $filter) {
                if (str_contains($address, $filter) || str_contains($address, strtolower($mood))) {
                    return true;
                }
            }

            return false;
        }));
    }

    private function imageUrlForPlace(array $item): string
    {
        $term = $item['display_name'] ?? $item['type'] ?? 'travel destination';
        $term = preg_replace('/[^a-z0-9\s]/i', ' ', $term);
        return 'https://source.unsplash.com/800x600/?' . urlencode($term);
    }

    private function mapElement(array $el, string $city): array
    {
        $tags    = $el['tags'] ?? [];
        $elLat   = $el['lat'] ?? $el['center']['lat'] ?? null;
        $elLon   = $el['lon'] ?? $el['center']['lon'] ?? null;
        $tourism = $tags['tourism'] ?? 'hotel';

        return [
            'properties' => [
                'place_id'   => 'osm_' . $el['type'] . '_' . $el['id'],
                'name'       => $tags['name'] ?? $tags['brand'] ?? null,
                'city'       => $tags['addr:city'] ?? $city,
                'country'    => $tags['addr:country'] ?? '',
                'categories' => [$tourism],
                'stars'      => isset($tags['stars']) ? (int) $tags['stars'] : null,
                'website'    => $tags['website'] ?? null,
                'phone'      => $tags['phone'] ?? null,
            ],
            'geometry' => [
                'coordinates' => [$elLon, $elLat],
            ],
        ];
    }
}

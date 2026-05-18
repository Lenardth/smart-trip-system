<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\MoodCategory;
use App\Services\PexelsService;
use App\Contracts\GeoapifyInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DiscoverController extends Controller
{
    /**
     * Common city nicknames / aliases → canonical search term.
     * Nominatim understands the canonical name; we resolve before querying.
     */
    private const ALIASES = [
        // South Africa
        'jozi'          => 'Johannesburg',
        'joburg'        => 'Johannesburg',
        'jhb'           => 'Johannesburg',
        'pta'           => 'Pretoria',
        'cpt'           => 'Cape Town',
        'dbn'           => 'Durban',
        'the mother city'=> 'Cape Town',
        // USA
        'nyc'           => 'New York City',
        'new york'      => 'New York City',
        'the big apple' => 'New York City',
        'la'            => 'Los Angeles',
        'l.a.'          => 'Los Angeles',
        'chi-town'      => 'Chicago',
        'the windy city'=> 'Chicago',
        'sf'            => 'San Francisco',
        'sin city'      => 'Las Vegas',
        'vegas'         => 'Las Vegas',
        'nola'          => 'New Orleans',
        'dc'            => 'Washington DC',
        'philly'        => 'Philadelphia',
        // UK
        'london'        => 'London',
        'brum'          => 'Birmingham',
        'manc'          => 'Manchester',
        'edinburgh'     => 'Edinburgh',
        // Europe
        'paris'         => 'Paris',
        'rome'          => 'Rome',
        'the eternal city' => 'Rome',
        'barcelona'     => 'Barcelona',
        'barca'         => 'Barcelona',
        'amsterdam'     => 'Amsterdam',
        'dam'           => 'Amsterdam',
        'berlin'        => 'Berlin',
        'vienna'        => 'Vienna',
        'prague'        => 'Prague',
        'lisbon'        => 'Lisbon',
        'athens'        => 'Athens',
        // Asia
        'bkk'           => 'Bangkok',
        'bangkok'       => 'Bangkok',
        'hk'            => 'Hong Kong',
        'sg'            => 'Singapore',
        'kl'            => 'Kuala Lumpur',
        'tokyo'         => 'Tokyo',
        'osaka'         => 'Osaka',
        'beijing'       => 'Beijing',
        'shanghai'      => 'Shanghai',
        'mumbai'        => 'Mumbai',
        'bombay'        => 'Mumbai',
        'delhi'         => 'New Delhi',
        'calcutta'      => 'Kolkata',
        'dubai'         => 'Dubai',
        // Africa
        'nairobi'       => 'Nairobi',
        'accra'         => 'Accra',
        'lagos'         => 'Lagos',
        'cairo'         => 'Cairo',
        'marrakech'     => 'Marrakech',
        'marrakesh'     => 'Marrakech',
        'casablanca'    => 'Casablanca',
        'addis'         => 'Addis Ababa',
        // Australia
        'sydney'        => 'Sydney',
        'melbs'         => 'Melbourne',
        'melbourne'     => 'Melbourne',
        'brisbane'      => 'Brisbane',
        // Americas
        'rio'           => 'Rio de Janeiro',
        'buenos aires'  => 'Buenos Aires',
        'ba'            => 'Buenos Aires',
        'bogota'        => 'Bogotá',
        'cdmx'          => 'Mexico City',
        'mexico city'   => 'Mexico City',
        'toronto'       => 'Toronto',
        'van'           => 'Vancouver',
        'vancouver'     => 'Vancouver',
        'montreal'      => 'Montreal',
    ];

    public function __construct(
        private PexelsService     $pexels,
        private GeoapifyInterface $geoapify,
    ) {}

    // ── Page ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        return view('discover.index', [
            'countries'      => $this->geoapify->getCountries(),
            'moodCategories' => MoodCategory::active()->ordered()->get(),
            'heroImage'      => $this->pexels->getHeroImage('discover'),
        ]);
    }

    // ── JSON list endpoint (mirrors /api/accommodations) ─────────────────────

    public function list(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q'      => 'nullable|string|max:120',
            'search' => 'nullable|string|max:120',
            'region' => 'nullable|string|max:10',
            'mood'   => 'nullable|string|max:60',
        ]);

        $rawQuery   = trim((string) ($validated['q'] ?? $validated['search'] ?? ''));
        $regionCode = $validated['region'] ? strtoupper($validated['region']) : null;
        $mood       = $validated['mood'] ?? null;

        // No filters → return featured/cached destinations
        if (!$rawQuery && !$regionCode && !$mood) {
            $destinations = Destination::active()
                ->ordered()
                ->limit(16)
                ->get()
                ->map(fn($d) => $this->format($d->toArray()))
                ->toArray();

            return response()->json(['destinations' => $destinations, 'source' => 'featured']);
        }

        // Resolve nickname → canonical city name for API + DB search
        $resolvedQuery = $this->resolveAlias($rawQuery);

        // Build all search terms: original + resolved (if different)
        $searchTerms = array_unique(array_filter([$rawQuery, $resolvedQuery]));

        // Check DB with all terms
        $dbCount = $this->dbQuery($searchTerms, $regionCode, $mood)->count();

        if ($dbCount < 5) {
            // Fetch from live API using the resolved (canonical) name
            $this->fetchAndStoreFromApi($resolvedQuery ?: $rawQuery, $regionCode, $mood);
        }

        // Re-query DB with all terms after potential API fetch
        $destinations = $this->dbQuery($searchTerms, $regionCode, $mood)
            ->orderBy('display_order')
            ->limit(24)
            ->get()
            ->map(fn($d) => $this->format($d->toArray()))
            ->toArray();

        $this->logSearch($request, $rawQuery, $resolvedQuery, $regionCode, $mood, count($destinations));

        return response()->json([
            'destinations'   => $destinations,
            'count'          => count($destinations),
            'resolved_query' => $resolvedQuery !== $rawQuery ? $resolvedQuery : null,
            'source'         => 'database',
        ]);
    }

    // ── Destination detail page ───────────────────────────────────────────────

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

    // ── Legacy alias ──────────────────────────────────────────────────────────

    public function search(Request $request): JsonResponse
    {
        return $this->list($request);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Resolve a nickname/alias to its canonical city name.
     * Falls back to the original query if no alias found.
     */
    private function resolveAlias(string $query): string
    {
        if (!$query) {
            return $query;
        }

        $lower = strtolower(trim($query));

        // Direct alias lookup
        if (isset(self::ALIASES[$lower])) {
            return self::ALIASES[$lower];
        }

        // Partial match — if the query is a prefix of an alias key
        foreach (self::ALIASES as $alias => $canonical) {
            if (str_starts_with($alias, $lower) || str_starts_with($lower, $alias)) {
                return $canonical;
            }
        }

        return $query;
    }

    /**
     * Build a DB query that searches across multiple terms.
     */
    private function dbQuery(array $terms, ?string $regionCode, ?string $mood)
    {
        return Destination::active()
            ->when(!empty($terms), function ($query) use ($terms) {
                $query->where(function ($q) use ($terms) {
                    foreach ($terms as $term) {
                        $q->orWhere('name', 'like', "%{$term}%")
                          ->orWhere('country', 'like', "%{$term}%")
                          ->orWhere('region', 'like', "%{$term}%")
                          ->orWhere('description', 'like', "%{$term}%");
                    }
                });
            })
            ->when($regionCode, fn($q) => $q->where('country_code', $regionCode))
            ->when($mood, fn($q) => $q->whereJsonContains('tags', $mood));
    }

    /**
     * Fetch destinations from live API and store in DB.
     * Uses the canonical/resolved query for best API results.
     */
    private function fetchAndStoreFromApi(string $query, ?string $countryCode, ?string $mood): void
    {
        try {
            $places = $this->geoapify->searchDestinations(
                $query ?: 'popular travel city',
                $countryCode,
                $mood,
                20
            );

            foreach ($places as $place) {
                $sourceId = $place['id'] ?? null;
                if (!$sourceId || empty($place['name']) || $place['name'] === 'Unknown place') {
                    continue;
                }

                $destination = Destination::firstOrNew([
                    'source'    => 'openstreetmap',
                    'source_id' => $sourceId,
                ]);

                $imageQuery = trim(($place['name'] ?? '') . ' ' . ($place['country'] ?? '') . ' travel');
                $imageUrl   = $this->pexels->getRandomPhoto($imageQuery)
                    ?? "https://source.unsplash.com/800x600/?" . urlencode($imageQuery);

                $destination->fill([
                    'name'         => $place['name'],
                    'country'      => $place['country'] ?? ($place['region'] ?? 'Unknown'),
                    'country_code' => $place['country_code'] ?? null,
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
                    $destination->display_order = 100;
                }

                $destination->save();
            }
        } catch (\Throwable $e) {
            Log::warning('Discover API fetch failed', ['query' => $query, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Format a destination row for the JSON response.
     */
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
            'image_url'    => $d['image_url'] ?: "https://source.unsplash.com/800x600/?" . urlencode("{$name} {$country} travel"),
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

        return $type && $location ? "{$type} in {$location}." : ($location ?: 'A destination worth exploring.');
    }

    private function buildTags(array $place, ?string $mood): array
    {
        $tags = $mood ? [$mood] : [];

        $typeTagMap = [
            'museum'         => 'Cultural',
            'gallery'        => 'Cultural',
            'historic'       => 'Cultural',
            'castle'         => 'Cultural',
            'monument'       => 'Cultural',
            'beach'          => 'Beach',
            'bay'            => 'Beach',
            'island'         => 'Beach',
            'peak'           => 'Adventurous',
            'waterfall'      => 'Nature',
            'national_park'  => 'Nature',
            'nature_reserve' => 'Eco-Travel',
            'forest'         => 'Nature',
            'park'           => 'Relaxed',
            'garden'         => 'Relaxed',
            'spa'            => 'Relaxed',
            'restaurant'     => 'Foodie',
            'market'         => 'Foodie',
            'viewpoint'      => 'Romantic',
            'city'           => 'Cultural',
            'town'           => 'Cultural',
        ];

        $type = strtolower($place['type'] ?? $place['category'] ?? '');
        foreach ($typeTagMap as $keyword => $tag) {
            if (str_contains($type, $keyword)) {
                $tags[] = $tag;
                break;
            }
        }

        return array_values(array_unique(array_filter($tags)));
    }

    private function logSearch(Request $request, string $rawQuery, string $resolvedQuery, ?string $regionCode, ?string $mood, int $count): void
    {
        if (!$rawQuery) {
            return;
        }

        try {
            \App\Models\AccommodationSearch::firstOrCreate(
                [
                    'user_id'     => Auth::id(),
                    'query'       => $rawQuery,
                    'style'       => 'discover:' . ($mood ?? 'any'),
                    'budget_tier' => $regionCode,
                ],
                [
                    'results_count' => $count,
                    'ip_address'    => $request->ip(),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('DiscoverSearch log failed: ' . $e->getMessage());
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

<?php

namespace App\Services;

use App\Models\Accommodation;
use App\Models\AccommodationSearch;
use App\Models\ApiResponse;
use App\Contracts\GeoapifyInterface;
use App\Contracts\AccommodationPricingInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AccommodationCatalogService
{
    public function __construct(
        private GeoapifyInterface $geoapify,
        private AccommodationPricingInterface $pricingService
    ) {}

    public function index(): \Illuminate\View\View
    {
        return view('accommodations.index');
    }

    public function list(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q'           => 'nullable|string|max:120',
            'search'      => 'nullable|string|max:120',
            'style'       => 'nullable|string|max:40',
            'budget'      => 'nullable|string|max:20',
            'budget_tier' => 'nullable|string|max:20',
        ]);

        $q          = trim((string) ($validated['q'] ?? $validated['search'] ?? ''));
        $style      = $validated['style'] ?? null;
        $budgetTier = $validated['budget_tier'] ?? $validated['budget'] ?? null;

        if ($budgetTier === 'any') {
            $budgetTier = null;
        }

        $requestPayload = [
            'query'       => $q,
            'style'       => $style,
            'budget_tier' => $budgetTier,
        ];
        $searchHash = $this->searchHash($requestPayload);

        $cached = AccommodationSearch::where('search_hash', $searchHash)
            ->whereNotNull('response_payload')
            ->where('created_at', '>=', now()->subDay())
            ->latest()
            ->first();

        if ($cached) {
            $results = $cached->response_payload['accommodations'] ?? [];

            if (! empty($results)) {
                $this->logSearch($request, $q, $style, $budgetTier, $results, $searchHash, true, $requestPayload);

                return response()->json(['accommodations' => $results, 'cached' => true]);
            }
        }

        if (!$q || strlen($q) < 2) {
            $results = $this->formattedAccommodations(null, $style, $budgetTier);

            if (empty($results)) {
                $results = $this->formattedAccommodations();
            }

            $this->logSearch($request, $q, $style, $budgetTier, $results, $searchHash, false, $requestPayload);

            return response()->json(['accommodations' => $results, 'cached' => false]);
        }

        $location = trim($q);
        $isKnownCountry = Accommodation::active()
            ->where('country', 'like', "%{$location}%")
            ->exists();

        if (! $isKnownCountry && Accommodation::active()->byLocation($location)->count() < 5) {
            $this->fetchAndStoreFromApi($location);
        }

        $results = $this->formattedAccommodations($location, $style, $budgetTier);

        if (empty($results)) {
            $results = $this->formattedAccommodations($location);
        }

        if (empty($results)) {
            $results = $this->formattedAccommodations(null, $style, $budgetTier);
        }

        if (empty($results)) {
            $results = $this->formattedAccommodations();
        }

        $this->logSearch($request, $q, $style, $budgetTier, $results, $searchHash, false, $requestPayload);

        return response()->json(['accommodations' => $results, 'cached' => false]);
    }

    public function searches(): JsonResponse
    {
        $searches = AccommodationSearch::where('user_id', Auth::id())
            ->latest()
            ->limit(20)
            ->get(['id', 'query', 'style', 'budget_tier', 'results_count', 'created_at']);

        return response()->json(['searches' => $searches]);
    }

    private function fetchAndStoreFromApi(string $city): void
    {
        try {
            $places = $this->geoapify->placesByCity($city, [], 100);

            foreach ($places as $feature) {
                $props      = $feature['properties'] ?? [];
                $geom       = $feature['geometry']['coordinates'] ?? null;
                $name       = $props['name'] ?? null;
                $geoapifyId = $props['place_id'] ?? null;

                if (!$name || !$geoapifyId) {
                    continue;
                }

                if (Accommodation::where('geoapify_id', $geoapifyId)->exists()) {
                    continue;
                }

                $resolvedCity  = $props['city'] ?? $city;
                $resolvedStyle = $this->resolveStyle($props['categories'] ?? []);
                $tier          = $this->resolveBudgetTier($resolvedStyle);
                $pricing       = $this->pricingService->getPrice($resolvedCity, $resolvedStyle, $tier);

                Accommodation::create([
                    'geoapify_id'  => $geoapifyId,
                    'name'         => $name,
                    'city'         => $resolvedCity,
                    'country'      => $props['country'] ?? '',
                    'style'        => $resolvedStyle,
                    'budget_tier'  => $tier,
                    'nightly_rate' => $pricing['price'],
                    'rating'       => $props['stars'] ?? null,
                    'lat'          => is_array($geom) && isset($geom[1]) ? (float) $geom[1] : null,
                    'lng'          => is_array($geom) && isset($geom[0]) ? (float) $geom[0] : null,
                    'is_active'    => true,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Accommodation API fetch failed', ['error' => $e->getMessage(), 'city' => $city]);
        }
    }

    private function formattedAccommodations(
        ?string $location = null,
        ?string $style = null,
        ?string $budgetTier = null
    ): array {
        return Accommodation::active()
            ->when($location, fn($q) => $q->byLocation($location))
            ->when($style, fn($q) => $q->byStyle($style))
            ->when($budgetTier, fn($q) => $q->byBudget($budgetTier))
            ->limit(20)
            ->get()
            ->map(fn($a) => $this->format($a->toArray()))
            ->toArray();
    }

    private function format(array $a): array
    {
        $name  = $a['name'] ?? '';
        $city  = $a['city'] ?? '';
        $image = $a['image_url'] ?? $this->fetchImage($name, $city);

        return [
            'id'           => $a['id'],
            'name'         => $name,
            'city'         => $city,
            'country'      => $a['country'] ?? '',
            'style'        => $a['style'] ?? 'hotel',
            'budget_tier'  => $a['budget_tier'] ?? 'mid',
            'nightly_rate' => $a['nightly_rate'] ?? 0,
            'rating'       => $a['rating'] ?? 0,
            'review_count' => $a['review_count'] ?? null,
            'lat'          => $a['lat'] ?? null,
            'lng'          => $a['lng'] ?? null,
            'amenities'    => $a['amenities'] ?? $this->defaultAmenities($a['style'] ?? 'hotel'),
            'description'  => $a['description'] ?? '',
            'image_url'    => $image,
            'booking_url'  => 'https://www.booking.com/search.html?ss=' . urlencode($name . ' ' . $city),
        ];
    }

    private function fetchImage(string $name, string $city): string
    {
        $key = config('services.pexels.api_key');

        if ($key) {
            $pexels = ApiResponse::remember('pexels', 'accommodation_image', [
                'city' => $city,
            ], now()->addDay(), function () use ($key, $city) {
                try {
                    $response = Http::timeout(5)
                        ->withHeaders(['Authorization' => $key])
                        ->get(config('services.pexels.search_endpoint'), [
                            'query'       => "{$city} hotel",
                            'per_page'    => 5,
                            'orientation' => 'landscape',
                        ]);

                    return $response->successful() ? $response->json() : null;
                } catch (\Throwable $e) {
                    Log::warning('Pexels image fetch failed', ['error' => $e->getMessage()]);
                    return null;
                }
            });

            $photos = $pexels['photos'] ?? [];
            if (!empty($photos)) {
                $photo = $photos[array_rand($photos)];
                return $photo['src']['large'] ?? $photo['src']['original'];
            }
        }

        $wiki = ApiResponse::remember('wikipedia', 'accommodation_city_summary', [
            'city' => $city,
        ], now()->addDays(30), function () use ($city) {
            try {
                $response = Http::timeout(5)
                    ->get('https://en.wikipedia.org/api/rest_v1/page/summary/' . urlencode($city));

                return $response->successful() ? $response->json() : null;
            } catch (\Throwable $e) {
                Log::warning('Wikipedia image fetch failed', ['error' => $e->getMessage()]);
                return null;
            }
        });

        if (!empty($wiki['originalimage']['source'])) {
            return $wiki['originalimage']['source'];
        }

        if (!empty($wiki['thumbnail']['source'])) {
            return $wiki['thumbnail']['source'];
        }

        return config('api.image_fallback.base_url') . '?' . urlencode("{$city} hotel");
    }

    private function defaultAmenities(string $style): array
    {
        $map = config('accommodation.amenities', []);

        return $map[$style] ?? $map['default'] ?? [];
    }

    private function resolveStyle(array $categories): string
    {
        $map = config('accommodation.category_style_map', []);

        foreach ($categories as $cat) {
            foreach ($map as $fragment => $style) {
                if (str_contains((string) $cat, $fragment)) {
                    return $style;
                }
            }
        }

        return config('accommodation.default_style', 'hotel');
    }

    private function resolveBudgetTier(string $style): string
    {
        $map = config('accommodation.style_budget_tier', []);

        return $map[$style] ?? config('accommodation.default_budget_tier', 'mid');
    }

    public function news(Request $request): JsonResponse
    {
        $validated = $request->validate(['q' => 'required|string|max:120']);
        $query     = trim($validated['q']);

        try {
            $articles = $this->fetchNewsArticles($query);

            if (empty($articles)) {
                $articles = $this->fallbackNewsArticles($query);
            }

            return response()->json([
                'articles' => $articles,
                'query'    => $query,
            ]);
        } catch (\Throwable $e) {
            Log::warning('GNews fetch failed', ['error' => $e->getMessage()]);

            return response()->json([
                'articles' => $this->fallbackNewsArticles($query),
                'query'    => $query,
            ]);
        }
    }

    private function fetchNewsArticles(string $query, string $cacheEndpoint = 'accommodation_news', int $max = 6): array
    {
        $providers = [
            'gnews' => config('services.gnews.api_key'),
            'newsapi' => config('services.newsapi.key'),
        ];

        foreach ($providers as $provider => $apiKey) {
            if (!$apiKey) {
                continue;
            }

            $payload = ApiResponse::remember($provider, $cacheEndpoint, [
                'query' => $query,
                'lang'  => 'en',
                'max'   => $max,
            ], now()->addHours(6), function () use ($provider, $query, $apiKey, $max) {
                return $provider === 'newsapi'
                    ? $this->requestNewsApiArticles($query, $apiKey, $max)
                    : $this->requestGNewsArticles($query, $apiKey, $max);
            });

            $articles = $this->normalizeNewsArticles($payload['articles'] ?? []);

            if (!empty($articles)) {
                return $articles;
            }
        }

        return [];
    }

    private function requestGNewsArticles(string $query, string $apiKey, int $max): array
    {
        $response = Http::timeout((int) config('services.gnews.timeout', 8))
            ->get(config('services.gnews.search_endpoint'), [
                'q'     => $query,
                'lang'  => 'en',
                'max'   => $max,
                'token' => $apiKey,
            ]);

        return $response->successful() ? $response->json() : ['articles' => []];
    }

    private function requestNewsApiArticles(string $query, string $apiKey, int $max): array
    {
        $response = Http::timeout((int) config('services.newsapi.timeout', 8))
            ->get(config('services.newsapi.everything_endpoint'), [
                'q'        => $query,
                'language' => 'en',
                'pageSize' => $max,
                'sortBy'   => 'publishedAt',
                'apiKey'   => $apiKey,
            ]);

        return $response->successful() ? $response->json() : ['articles' => []];
    }

    private function normalizeNewsArticles(array $articles): array
    {
        return collect($articles)
            ->map(fn ($a) => [
                'title'       => trim((string) ($a['title'] ?? '')),
                'description' => $this->cleanNewsText($a['description'] ?? ''),
                'url'         => $this->safeNewsUrl($a['url'] ?? null),
                'publishedAt' => $a['publishedAt'] ?? null,
                'source'      => ['name' => $a['source']['name'] ?? 'Travel News'],
            ])
            ->filter(fn ($a) => $a['title'] !== '' && $a['title'] !== '[Removed]')
            ->values()
            ->take(6)
            ->all();
    }

    private function fallbackNewsArticles(string $query): array
    {
        $topic = preg_replace('/\s+/', ' ', trim($query)) ?: 'travel';
        $publishedAt = now()->toIso8601String();

        return [
            [
                'title'       => "Travel updates for {$topic}",
                'description' => 'Live news could not be reached right now. Open the source link to check current local travel headlines before booking.',
                'url'         => 'https://news.google.com/search?q=' . urlencode($topic),
                'publishedAt' => $publishedAt,
                'source'      => ['name' => 'Google News'],
            ],
            [
                'title'       => "Visitor information for {$topic}",
                'description' => 'Check transport, accommodation, local events, and safety notices close to your travel dates.',
                'url'         => 'https://www.google.com/search?q=' . urlencode($topic . ' official tourism'),
                'publishedAt' => $publishedAt,
                'source'      => ['name' => 'Smart Trip'],
            ],
        ];
    }

    public function travelWarning(Request $request): JsonResponse
    {
        $validated = $request->validate(['q' => 'required|string|max:120']);
        $city      = trim($validated['q']);

        if (!config('services.gnews.api_key') && !config('services.newsapi.key')) {
            return response()->json(['warnings' => []]);
        }

        try {
            $articles = $this->fetchNewsArticles("{$city} travel safety warning", 'travel_warning', 4);

            $safetyKeywords = ['warning', 'alert', 'unrest', 'protest', 'danger', 'safety', 'advisory', 'risk', 'attack', 'strike'];

            $warnings = collect($articles)
                ->filter(function ($a) use ($safetyKeywords) {
                    $text = strtolower(($a['title'] ?? '') . ' ' . ($a['description'] ?? ''));
                    foreach ($safetyKeywords as $kw) {
                        if (str_contains($text, $kw)) return true;
                    }
                    return false;
                })
                ->map(fn ($a) => [
                    'title'       => $a['title']       ?? '',
                    'description' => $a['description'] ?? '',
                    'url'         => $a['url']          ?? '#',
                    'source'      => ['name' => $a['source']['name'] ?? ''],
                ])
                ->values()
                ->all();

            return response()->json(['warnings' => $warnings]);
        } catch (\Throwable $e) {
            Log::warning('Travel warning fetch failed', ['error' => $e->getMessage()]);
            return response()->json(['warnings' => []]);
        }
    }

    private function cleanNewsText(mixed $value): string
    {
        $text = trim((string) $value);

        return $text === '[Removed]' ? '' : $text;
    }

    private function safeNewsUrl(?string $url): string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return '#';
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $url : '#';
    }

    private function searchHash(array $payload): string
    {
        ksort($payload);

        return hash('sha256', json_encode($payload));
    }

    private function logSearch(
        Request $request,
        string $q,
        ?string $style,
        ?string $budgetTier,
        array $results,
        string $searchHash,
        bool $cacheHit,
        array $requestPayload
    ): void
    {
        try {
            AccommodationSearch::create([
                'user_id'          => Auth::id(),
                'search_hash'      => $searchHash,
                'request_payload'  => $requestPayload,
                'response_payload' => ['accommodations' => $results],
                'query'            => $q ?: 'all',
                'style'            => $style,
                'budget_tier'      => $budgetTier,
                'results_count'    => count($results),
                'cache_hit'        => $cacheHit,
                'ip_address'       => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('AccommodationSearch log failed: ' . $e->getMessage());
        }
    }
}

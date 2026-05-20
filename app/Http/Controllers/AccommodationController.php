<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\AccommodationSearch;
use App\Contracts\GeoapifyInterface;
use App\Contracts\AccommodationPricingInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AccommodationController extends Controller
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

        if (!$q || strlen($q) < 2) {
            $results = Accommodation::active()
                ->when($style,      fn($q) => $q->byStyle($style))
                ->when($budgetTier, fn($q) => $q->byBudget($budgetTier))
                ->limit(20)
                ->get()
                ->map(fn($a) => $this->format($a->toArray()))
                ->toArray();

            return response()->json(['accommodations' => $results]);
        }

        $city = trim($q);

        if (Accommodation::active()->byCity($city)->count() < 5) {
            $this->fetchAndStoreFromApi($city);
        }

        $results = Accommodation::active()
            ->byCity($city)
            ->when($style,      fn($q) => $q->byStyle($style))
            ->when($budgetTier, fn($q) => $q->byBudget($budgetTier))
            ->get()
            ->map(fn($a) => $this->format($a->toArray()))
            ->toArray();

        $this->logSearch($request, $q, $style, $budgetTier, count($results));

        return response()->json(['accommodations' => $results]);
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
            try {
                $response = Http::timeout(5)
                    ->withHeaders(['Authorization' => $key])
                    ->get('https://api.pexels.com/v1/search', [
                        'query'       => "{$city} hotel",
                        'per_page'    => 5,
                        'orientation' => 'landscape',
                    ]);

                if ($response->successful()) {
                    $photos = $response->json()['photos'] ?? [];
                    if (!empty($photos)) {
                        $photo = $photos[array_rand($photos)];
                        return $photo['src']['large'] ?? $photo['src']['original'];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Pexels image fetch failed', ['error' => $e->getMessage()]);
            }
        }

        try {
            $response = Http::timeout(5)
                ->get('https://en.wikipedia.org/api/rest_v1/page/summary/' . urlencode($city));

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['originalimage']['source'])) return $data['originalimage']['source'];
                if (!empty($data['thumbnail']['source']))     return $data['thumbnail']['source'];
            }
        } catch (\Throwable $e) {
            Log::warning('Wikipedia image fetch failed', ['error' => $e->getMessage()]);
        }

        return config('services.image_fallback.base_url', 'https://placehold.co/800x600') . '?' . urlencode("{$city} hotel");
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

    private function logSearch(Request $request, string $q, ?string $style, ?string $budgetTier, int $count): void
    {
        try {
            $alreadyLogged = AccommodationSearch::where('user_id', Auth::id())
                ->where('query', $q)
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if (!$alreadyLogged) {
                AccommodationSearch::create([
                    'user_id'       => Auth::id(),
                    'query'         => $q,
                    'style'         => $style,
                    'budget_tier'   => $budgetTier,
                    'results_count' => $count,
                    'ip_address'    => $request->ip(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('AccommodationSearch log failed: ' . $e->getMessage());
        }
    }
}

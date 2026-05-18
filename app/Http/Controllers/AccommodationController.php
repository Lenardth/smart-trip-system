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
                ->when($style,      fn($query) => $query->byStyle($style))
                ->when($budgetTier, fn($query) => $query->byBudget($budgetTier))
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
            ->when($style,      fn($query) => $query->byStyle($style))
            ->when($budgetTier, fn($query) => $query->byBudget($budgetTier))
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

                if (!$name || !$geoapifyId) continue;

                $exists = Accommodation::where('geoapify_id', $geoapifyId)->exists();
                if ($exists) continue;

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
                    'rating'       => $props['stars'] ?? (rand(35, 50) / 10),
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

        return "https://source.unsplash.com/800x600/?" . urlencode("{$city} hotel");
    }

    private function defaultAmenities(string $style): array
    {
        return match ($style) {
            'hostel'      => ['Free WiFi', 'Shared Kitchen', 'Lockers', 'Common Room'],
            'guest_house' => ['Free WiFi', 'Breakfast', 'Private Bathroom', 'Garden'],
            'boutique'    => ['Free WiFi', 'Breakfast', 'Concierge', 'Bar'],
            'resort'      => ['Pool', 'Spa', 'Restaurant', 'Beach Access', 'Free WiFi'],
            'villa'       => ['Private Pool', 'Kitchen', 'Free WiFi', 'Garden', 'Parking'],
            'apartment'   => ['Kitchen', 'Free WiFi', 'Washing Machine', 'Self Check-in'],
            default       => ['Free WiFi', 'Air Conditioning', 'Reception 24/7', 'Parking'],
        };
    }

    private function resolveStyle(array $categories): string
    {
        foreach ($categories as $cat) {
            if (str_contains($cat, 'hostel'))      return 'hostel';
            if (str_contains($cat, 'apartment'))   return 'apartment';
            if (str_contains($cat, 'guest_house')) return 'guest_house';
            if (str_contains($cat, 'motel'))       return 'motel';
            if (str_contains($cat, 'resort'))      return 'resort';
        }
        return 'hotel';
    }

    private function resolveBudgetTier(string $style): string
    {
        return match ($style) {
            'hostel', 'guest_house', 'motel' => 'budget',
            'resort'                         => 'premium',
            default                          => 'mid',
        };
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

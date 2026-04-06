<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\AccommodationSearch;
use App\Services\GeoapifyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AccommodationController extends Controller
{
    public function __construct(private GeoapifyService $geoapify) {}

    public function index(): \Illuminate\View\View
    {
        return view('accommodations.index');
    }

    public function list(Request $request): JsonResponse
    {
        $q          = trim((string) ($request->input('q') ?? $request->input('search', '')));
        $style      = $request->input('style');
        $budgetTier = $request->input('budget_tier');

        // No query — return existing DB accommodations
        if (!$q || strlen($q) < 2) {
            $dbResults = Accommodation::active()
                ->when($style,      fn($query) => $query->byStyle($style))
                ->when($budgetTier, fn($query) => $query->byBudget($budgetTier))
                ->get()
                ->map(fn($a) => $this->formatAccommodation($a->toArray()))
                ->toArray();

            return response()->json(['accommodations' => $dbResults]);
        }

        // Has query — fetch from Geoapify and persist to DB
        $places = $this->geoapify->placesByCity($q, [], 100);

        if (empty($places)) {
            Log::warning('AccommodationController: no places returned', ['q' => $q]);
        }

        foreach ($places as $feature) {
            $props      = $feature['properties'] ?? [];
            $geom       = $feature['geometry']['coordinates'] ?? null;
            $name       = $props['name'] ?? null;
            $geoapifyId = $props['place_id'] ?? null;

            if (!$name || !$geoapifyId) continue;

            $city          = $props['city'] ?? $q;
            $country       = $props['country'] ?? '';
            $lat           = is_array($geom) && isset($geom[1]) ? (float) $geom[1] : null;
            $lng           = is_array($geom) && isset($geom[0]) ? (float) $geom[0] : null;
            $resolvedStyle = $this->resolveStyle($props['categories'] ?? []);
            $stars         = $props['stars'] ?? null;

            Accommodation::updateOrCreate(
                ['geoapify_id' => $geoapifyId],
                [
                    'name'         => $name,
                    'city'         => $city,
                    'country'      => $country,
                    'style'        => $resolvedStyle,
                    'budget_tier'  => $this->resolveBudgetTier($resolvedStyle),
                    'nightly_rate' => $this->estimateNightlyRate($resolvedStyle),
                    'rating'       => $stars ?? rand(35, 50) / 10,
                    'lat'          => $lat,
                    'lng'          => $lng,
                    'image_url'    => 'https://picsum.photos/seed/' . urlencode($name) . '/400/280',
                    'is_active'    => true,
                ]
            );
        }

        // Return from DB so data is always consistent
        $results = Accommodation::active()
            ->byCity($q)
            ->when($style,      fn($query) => $query->byStyle($style))
            ->when($budgetTier, fn($query) => $query->byBudget($budgetTier))
            ->get()
            ->map(fn($a) => $this->formatAccommodation($a->toArray()))
            ->toArray();

        AccommodationSearch::create([
            'user_id'       => Auth::id(),
            'query'         => $q,
            'style'         => $style,
            'budget_tier'   => $budgetTier,
            'results_count' => count($results),
            'ip_address'    => $request->ip(),
        ]);

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

    public function debug(Request $request): JsonResponse
    {
        abort_unless(app()->isLocal(), 403);

        $q = trim($request->input('q', 'Paris'));

        return response()->json([
            'query'   => $q,
            'geocode' => $this->geoapify->geocodeCity($q),
        ]);
    }

    private function formatAccommodation(array $a): array
    {
        return [
            'id'           => $a['id'],
            'name'         => $a['name'],
            'city'         => $a['city'] ?? '',
            'country'      => $a['country'] ?? '',
            'style'        => $a['style'] ?? 'hotel',
            'budget_tier'  => $a['budget_tier'] ?? 'mid',
            'nightly_rate' => $a['nightly_rate'] ?? 0,
            'rating'       => $a['rating'] ?? 0,
            'lat'          => $a['lat'] ?? null,
            'lng'          => $a['lng'] ?? null,
            'amenities'    => $a['amenities'] ?? [],
            'description'  => $a['description'] ?? '',
            'image_url'    => $a['image_url'] ?? 'https://picsum.photos/seed/' . urlencode($a['name']) . '/400/280',
        ];
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
            'hostel'      => 'backpacker',
            'guest_house' => 'budget',
            'motel'       => 'budget',
            'apartment'   => 'mid',
            'hotel'       => 'mid',
            'resort'      => 'premium',
            default       => 'mid',
        };
    }

    private function estimateNightlyRate(string $style): int
    {
        return match ($style) {
            'hostel'      => rand(15, 40),
            'guest_house' => rand(40, 80),
            'motel'       => rand(50, 90),
            'apartment'   => rand(70, 150),
            'hotel'       => rand(80, 250),
            'resort'      => rand(200, 600),
            default       => rand(80, 200),
        };
    }
}
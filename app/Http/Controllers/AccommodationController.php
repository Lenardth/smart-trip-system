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

        if (!$q || strlen($q) < 2) {
            $dbResults = Accommodation::active()
                ->when($style,      fn($query) => $query->byStyle($style))
                ->when($budgetTier, fn($query) => $query->byBudget($budgetTier))
                ->limit(20)
                ->get()
                ->map(fn($a) => $this->formatAccommodation($a->toArray()))
                ->toArray();

            // If no results, seed some popular destinations
            if (empty($dbResults)) {
                $this->seedPopularAccommodations();
                $dbResults = Accommodation::active()
                    ->limit(20)
                    ->get()
                    ->map(fn($a) => $this->formatAccommodation($a->toArray()))
                    ->toArray();
            }

            return response()->json(['accommodations' => $dbResults]);
        }

        // Normalize city name (handle nicknames like "jozi" -> "Johannesburg")
        $normalizedCity = $this->normalizeCityName($q);

        // Check existing accommodations first
        $existingCount = Accommodation::active()->byCity($normalizedCity)->count();

        // If we have no results for this city, seed popular destinations first
        if ($existingCount === 0) {
            $this->seedPopularAccommodations();
            $existingCount = Accommodation::active()->byCity($normalizedCity)->count();
        }

        // Only fetch from API if we still have fewer than 5 results
        if ($existingCount < 5) {
            try {
                $places = $this->geoapify->placesByCity($normalizedCity, [], 100);

                if (!empty($places)) {
                    foreach ($places as $feature) {
                        $props      = $feature['properties'] ?? [];
                        $geom       = $feature['geometry']['coordinates'] ?? null;
                        $name       = $props['name'] ?? null;
                        $geoapifyId = $props['place_id'] ?? null;

                        if (!$name || !$geoapifyId) continue;

                        $city          = $props['city'] ?? $normalizedCity;
                        $country       = $props['country'] ?? '';
                        $lat           = is_array($geom) && isset($geom[1]) ? (float) $geom[1] : null;
                        $lng           = is_array($geom) && isset($geom[0]) ? (float) $geom[0] : null;
                        $resolvedStyle = $this->resolveStyle($props['categories'] ?? []);
                        $stars         = $props['stars'] ?? null;

                        // Check if accommodation already exists
                        $existing = Accommodation::where('geoapify_id', $geoapifyId)
                            ->orWhere(function($query) use ($name, $city) {
                                $query->where('name', 'ILIKE', $name)
                                      ->where('city', 'ILIKE', $city);
                            })
                            ->first();

                        // Only insert if not already in DB
                        if (!$existing) {
                            Accommodation::create([
                                'geoapify_id'  => $geoapifyId,
                                'name'         => $name,
                                'city'         => $city,
                                'country'      => $country,
                                'style'        => $resolvedStyle,
                                'budget_tier'  => $this->resolveBudgetTier($resolvedStyle),
                                'nightly_rate' => $this->estimateNightlyRate($resolvedStyle),
                                'rating'       => $stars ?? rand(35, 50) / 10,
                                'lat'          => $lat,
                                'lng'          => $lng,
                                'image_url'    => null, // Will be fetched from Pexels/Unsplash when displayed
                                'is_active'    => true,
                            ]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('AccommodationController: API fetch failed', ['error' => $e->getMessage(), 'city' => $normalizedCity]);
            }
        }

        $results = Accommodation::active()
            ->byCity($normalizedCity)
            ->when($style,      fn($query) => $query->byStyle($style))
            ->when($budgetTier, fn($query) => $query->byBudget($budgetTier))
            ->get()
            ->map(fn($a) => $this->formatAccommodation($a->toArray()))
            ->toArray();

        // Log search only once per unique city per user per day
        try {
            $today = now()->toDateString();
            $alreadyLogged = AccommodationSearch::where('user_id', Auth::id())
                ->where('query', $q)
                ->whereDate('created_at', $today)
                ->exists();

            if (!$alreadyLogged) {
                AccommodationSearch::create([
                    'user_id'       => Auth::id(),
                    'query'         => $q,
                    'style'         => $style,
                    'budget_tier'   => $budgetTier,
                    'results_count' => count($results),
                    'ip_address'    => $request->ip(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('AccommodationSearch log failed: ' . $e->getMessage());
        }

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

    private function formatAccommodation(array $a): array
    {
        $name = $a['name'] ?? '';
        $city = $a['city'] ?? '';

        // Generate a Booking.com search link for the property
        $bookingQuery = urlencode($name . ' ' . $city);
        $bookingUrl   = "https://www.booking.com/search.html?ss=" . $bookingQuery;

        // Use real review count if available, otherwise estimate based on rating
        $rating = $a['rating'] ?? 0;
        $reviewCount = $a['review_count'] ?? $this->estimateReviewCount($rating);
        
        // Determine deal badge based on actual data
        $dealBadge = $this->getDealBadge($a['budget_tier'] ?? 'mid', $a['nightly_rate'] ?? 0);

        // Use real image from Pexels API or property image if available
        $imageUrl = $a['image_url'] ?? $this->getPropertyImage($name, $city);

        return [
            'id'           => $a['id'],
            'name'         => $name,
            'city'         => $city,
            'country'      => $a['country'] ?? '',
            'style'        => $a['style'] ?? 'hotel',
            'budget_tier'  => $a['budget_tier'] ?? 'mid',
            'nightly_rate' => $a['nightly_rate'] ?? 0,
            'rating'       => $rating,
            'review_count' => $reviewCount,
            'lat'          => $a['lat'] ?? null,
            'lng'          => $a['lng'] ?? null,
            'amenities'    => $a['amenities'] ?? $this->getDefaultAmenities($a['style'] ?? 'hotel'),
            'description'  => $a['description'] ?? '',
            'image_url'    => $imageUrl,
            'booking_url'  => $bookingUrl,
            'deal_badge'   => $dealBadge,
        ];
    }

    private function estimateReviewCount(float $rating): int
    {
        // Higher rated properties typically have more reviews
        if ($rating >= 4.5) return rand(500, 2500);
        if ($rating >= 4.0) return rand(200, 800);
        if ($rating >= 3.5) return rand(100, 400);
        return rand(50, 200);
    }

    private function getPropertyImage(string $name, string $city): string
    {
        $pexelsKey = config('services.pexels.api_key') ?? env('PEXELS_API_KEY');
        
        if (!$pexelsKey) {
            // Fallback to Unsplash if no Pexels key
            $query = urlencode($city . ' hotel');
            return "https://source.unsplash.com/800x600/?{$query}";
        }

        try {
            // Search for hotel images in the city
            $query = urlencode($city . ' hotel luxury');
            $response = Http::timeout(5)
                ->withHeaders(['Authorization' => $pexelsKey])
                ->get('https://api.pexels.com/v1/search', [
                    'query' => $query,
                    'per_page' => 15,
                    'orientation' => 'landscape',
                ]);

            if ($response->successful()) {
                $photos = $response->json()['photos'] ?? [];
                if (!empty($photos)) {
                    // Pick a random photo from results
                    $photo = $photos[array_rand($photos)];
                    return $photo['src']['large'] ?? $photo['src']['original'];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Pexels API failed', ['error' => $e->getMessage()]);
        }

        // Final fallback to Unsplash
        $query = urlencode($city . ' hotel');
        return "https://source.unsplash.com/800x600/?{$query}";
    }

    private function getDealBadge(string $tier, float $rate): ?string
    {
        if ($tier === 'backpacker' || $tier === 'budget') return 'Great Value';
        if ($rate > 0 && $rate < 60)  return 'Best Price';
        if ($rate > 200)              return 'Luxury Pick';
        return rand(0, 2) === 0 ? 'Popular Choice' : null;
    }

    private function getDefaultAmenities(string $style): array
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

    private function seedPopularAccommodations(): void
    {
        // Only seed if completely empty - prefer real API data
        $count = Accommodation::count();
        if ($count > 0) {
            return; // Don't seed if we already have data
        }

        $popularDestinations = [
            ['name' => 'Bali Beach Resort', 'city' => 'Bali', 'country' => 'Indonesia', 'style' => 'resort', 'lat' => -8.4095, 'lng' => 115.1889],
            ['name' => 'Lisbon Central Hotel', 'city' => 'Lisbon', 'country' => 'Portugal', 'style' => 'hotel', 'lat' => 38.7223, 'lng' => -9.1393],
            ['name' => 'Tokyo Capsule Inn', 'city' => 'Tokyo', 'country' => 'Japan', 'style' => 'hostel', 'lat' => 35.6762, 'lng' => 139.6503],
            ['name' => 'Cape Town Boutique', 'city' => 'Cape Town', 'country' => 'South Africa', 'style' => 'boutique', 'lat' => -33.9249, 'lng' => 18.4241],
            ['name' => 'Marrakech Riad', 'city' => 'Marrakech', 'country' => 'Morocco', 'style' => 'guest_house', 'lat' => 31.6295, 'lng' => -7.9811],
            ['name' => 'Santorini Sunset Villa', 'city' => 'Santorini', 'country' => 'Greece', 'style' => 'villa', 'lat' => 36.3932, 'lng' => 25.4615],
            ['name' => 'Bangkok Hostel', 'city' => 'Bangkok', 'country' => 'Thailand', 'style' => 'hostel', 'lat' => 13.7563, 'lng' => 100.5018],
            ['name' => 'New York Plaza Hotel', 'city' => 'New York', 'country' => 'USA', 'style' => 'hotel', 'lat' => 40.7128, 'lng' => -74.0060],
            ['name' => 'Dubai Luxury Resort', 'city' => 'Dubai', 'country' => 'UAE', 'style' => 'resort', 'lat' => 25.2048, 'lng' => 55.2708],
            ['name' => 'Amsterdam Canal House', 'city' => 'Amsterdam', 'country' => 'Netherlands', 'style' => 'apartment', 'lat' => 52.3676, 'lng' => 4.9041],
            ['name' => 'Johannesburg City Lodge', 'city' => 'Johannesburg', 'country' => 'South Africa', 'style' => 'hotel', 'lat' => -26.2041, 'lng' => 28.0473],
            ['name' => 'Sandton Sun Hotel', 'city' => 'Johannesburg', 'country' => 'South Africa', 'style' => 'hotel', 'lat' => -26.1076, 'lng' => 28.0567],
            ['name' => 'Rosebank Boutique', 'city' => 'Johannesburg', 'country' => 'South Africa', 'style' => 'boutique', 'lat' => -26.1467, 'lng' => 28.0406],
            ['name' => 'Maboneng Lofts', 'city' => 'Johannesburg', 'country' => 'South Africa', 'style' => 'apartment', 'lat' => -26.2023, 'lng' => 28.0594],
            ['name' => 'Melrose Arch Hotel', 'city' => 'Johannesburg', 'country' => 'South Africa', 'style' => 'hotel', 'lat' => -26.1333, 'lng' => 28.0667],
            ['name' => 'Paris Eiffel Hotel', 'city' => 'Paris', 'country' => 'France', 'style' => 'hotel', 'lat' => 48.8566, 'lng' => 2.3522],
            ['name' => 'London Bridge Inn', 'city' => 'London', 'country' => 'UK', 'style' => 'hotel', 'lat' => 51.5074, 'lng' => -0.1278],
            ['name' => 'Rome Colosseum Suites', 'city' => 'Rome', 'country' => 'Italy', 'style' => 'apartment', 'lat' => 41.9028, 'lng' => 12.4964],
            ['name' => 'Barcelona Beach Hostel', 'city' => 'Barcelona', 'country' => 'Spain', 'style' => 'hostel', 'lat' => 41.3851, 'lng' => 2.1734],
            ['name' => 'Sydney Harbour Hotel', 'city' => 'Sydney', 'country' => 'Australia', 'style' => 'hotel', 'lat' => -33.8688, 'lng' => 151.2093],
        ];

        foreach ($popularDestinations as $dest) {
            Accommodation::create([
                'geoapify_id'  => 'seed_' . md5($dest['name'] . $dest['city']),
                'name'         => $dest['name'],
                'city'         => $dest['city'],
                'country'      => $dest['country'],
                'style'        => $dest['style'],
                'budget_tier'  => $this->resolveBudgetTier($dest['style']),
                'nightly_rate' => $this->estimateNightlyRate($dest['style']),
                'rating'       => rand(40, 50) / 10,
                'lat'          => $dest['lat'],
                'lng'          => $dest['lng'],
                'image_url'    => null, // Will be fetched from Pexels/Unsplash when displayed
                'is_active'    => true,
            ]);
        }
    }

    private function normalizeCityName(string $city): string
    {
        // Handle common city nicknames and variations
        $nicknames = [
            'jozi' => 'Johannesburg',
            'joburg' => 'Johannesburg',
            'jhb' => 'Johannesburg',
            'nyc' => 'New York',
            'la' => 'Los Angeles',
            'sf' => 'San Francisco',
            'vegas' => 'Las Vegas',
            'philly' => 'Philadelphia',
            'chi-town' => 'Chicago',
            'the big apple' => 'New York',
            'the city of angels' => 'Los Angeles',
            'sin city' => 'Las Vegas',
        ];

        $normalized = strtolower(trim($city));
        return $nicknames[$normalized] ?? $city;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LandingController extends Controller
{
    private static array $DESTINATIONS = [
        ['name' => 'Lisbon',       'country' => 'Portugal',      'region' => 'europe',        'mood' => 'cultural',    'category' => 'general',      'price_from' => 89,  'description' => 'Historic neighbourhoods, river views, and Atlantic breezes.', 'badge' => 'Editor Pick'],
        ['name' => 'Kyoto',        'country' => 'Japan',         'region' => 'east_asia',     'mood' => 'cultural',    'category' => 'historical',   'price_from' => 120, 'description' => 'Temples, gardens, and timeless traditions.', 'badge' => null],
        ['name' => 'Cape Town',    'country' => 'South Africa',  'region' => 'africa',        'mood' => 'adventurous', 'category' => 'mountain',     'price_from' => 95,  'description' => 'Coastline, mountains, and vibrant culture.', 'badge' => 'Adventure'],
        ['name' => 'Barcelona',    'country' => 'Spain',         'region' => 'europe',        'mood' => 'foodie',      'category' => 'food_culture', 'price_from' => 102, 'description' => 'Architecture, markets, and Mediterranean flavour.', 'badge' => null],
        ['name' => 'Reykjavik',    'country' => 'Iceland',       'region' => 'europe',        'mood' => 'nature',      'category' => 'general',      'price_from' => 140, 'description' => 'Northern lights, hot springs, and dramatic landscapes.', 'badge' => 'Nature'],
        ['name' => 'Mexico City',  'country' => 'Mexico',        'region' => 'north_america', 'mood' => 'foodie',      'category' => 'food_culture', 'price_from' => 78,  'description' => 'World-class cuisine and buzzing neighbourhoods.', 'badge' => null],
        ['name' => 'Bali',         'country' => 'Indonesia',     'region' => 'southeast_asia','mood' => 'relaxed',     'category' => 'beach',        'price_from' => 85,  'description' => 'Rice terraces, temples, and tropical beaches.', 'badge' => 'Popular'],
        ['name' => 'Marrakech',    'country' => 'Morocco',       'region' => 'africa',        'mood' => 'cultural',    'category' => 'historical',   'price_from' => 72,  'description' => 'Spice markets, riads, and Saharan sunsets.', 'badge' => null],
        ['name' => 'Santorini',    'country' => 'Greece',        'region' => 'europe',        'mood' => 'relaxed',     'category' => 'beach',        'price_from' => 130, 'description' => 'White-washed cliffs, blue domes, and Aegean sunsets.', 'badge' => 'Romantic'],
        ['name' => 'New York',     'country' => 'USA',           'region' => 'north_america', 'mood' => 'urban',       'category' => 'general',      'price_from' => 150, 'description' => 'The city that never sleeps — culture, food, and energy.', 'badge' => null],
        ['name' => 'Bangkok',      'country' => 'Thailand',      'region' => 'southeast_asia','mood' => 'foodie',      'category' => 'food_culture', 'price_from' => 65,  'description' => 'Street food, temples, and neon-lit nights.', 'badge' => 'Budget Pick'],
        ['name' => 'Patagonia',    'country' => 'Argentina',     'region' => 'south_america', 'mood' => 'adventurous', 'category' => 'mountain',     'price_from' => 110, 'description' => 'Glaciers, peaks, and untouched wilderness.', 'badge' => 'Adventure'],
    ];

    public function index()
    {
        return view('landing.index');
    }

    public function destinations(): JsonResponse
    {
        $pexelsKey = config('services.pexels.api_key');

        $destinations = array_map(function (array $dest) use ($pexelsKey) {
            $dest['id']        = crc32($dest['name'] . $dest['country']);
            $dest['image_url'] = $this->fetchPexelsImage($dest['name'], $dest['country'], $pexelsKey);
            return $dest;
        }, self::$DESTINATIONS);

        return response()->json($destinations);
    }

    private function fetchPexelsImage(string $city, string $country, ?string $apiKey): string
    {
        if (!$apiKey) {
            return $this->unsplashFallback($city);
        }

        $cacheKey = 'pexels_dest_' . md5($city . $country);

        return Cache::remember($cacheKey, 86400, function () use ($city, $country, $apiKey) {
            try {
                $response = Http::timeout(6)
                    ->withHeaders(['Authorization' => $apiKey])
                    ->get('https://api.pexels.com/v1/search', [
                        'query'       => "{$city} {$country} travel landmark",
                        'per_page'    => 5,
                        'orientation' => 'landscape',
                    ]);

                if ($response->successful()) {
                    $photos = $response->json()['photos'] ?? [];
                    if (!empty($photos)) {
                        $photo = $photos[array_rand($photos)];
                        return $photo['src']['large2x'] ?? $photo['src']['large'] ?? $photo['src']['original'];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Pexels image fetch failed for {$city}: " . $e->getMessage());
            }

            return $this->unsplashFallback($city);
        });
    }

    private function unsplashFallback(string $city): string
    {
        return 'https://source.unsplash.com/800x600/?' . urlencode($city . ' travel');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LandingController extends Controller
{
    public function index()
    {
        return view('landing.index');
    }

    public function destinations(): JsonResponse
    {
        $pexelsKey    = config('services.pexels.api_key');
        $destinations = config('destinations', []);

        $destinations = array_map(function (array $dest) use ($pexelsKey) {
            $dest['id']        = crc32($dest['name'] . $dest['country']);
            $dest['image_url'] = $this->fetchPexelsImage($dest['name'], $dest['country'], $pexelsKey);
            return $dest;
        }, $destinations);

        return response()->json($destinations);
    }

    private function fetchPexelsImage(string $city, string $country, ?string $apiKey): string
    {
        if (!$apiKey) {
            return $this->imageFallback($city);
        }

        $cacheKey = 'pexels_dest_' . md5($city . $country);
        $cacheTtl = config('api.cache.pexels_ttl', 86400);
        $timeout  = config('api.timeouts.pexels', 6);

        return Cache::remember($cacheKey, $cacheTtl, function () use ($city, $country, $apiKey, $timeout) {
            try {
                $response = Http::timeout($timeout)
                    ->withHeaders(['Authorization' => $apiKey])
                    ->get(config('services.pexels.search_endpoint'), [
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

            return $this->imageFallback($city);
        });
    }

    private function imageFallback(string $city): string
    {
        $base = config('api.image_fallback.base_url', 'https://placehold.co/800x600');

        return $base . '?' . urlencode($city . ' travel');
    }
}

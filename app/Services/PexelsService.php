<?php

namespace App\Services;

use App\Models\ApiResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PexelsService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.pexels.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.pexels.api_key');
    }

    public function searchPhotos(string $query, int $perPage = 15, string $orientation = 'landscape'): ?array
    {
        if (!$this->apiKey) {
            return null;
        }

        $cacheKey = 'pexels_' . md5("{$query}_{$perPage}_{$orientation}");

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($query, $perPage, $orientation) {
            return ApiResponse::remember('pexels', 'search', [
                'query'       => $query,
                'per_page'    => $perPage,
                'orientation' => $orientation,
            ], now()->addDay(), function () use ($query, $perPage, $orientation) {
                try {
                    $timeout  = config('services.pexels.timeout', 6);
                    $response = Http::timeout($timeout)
                        ->withHeaders(['Authorization' => $this->apiKey])
                        ->get("{$this->baseUrl}/search", [
                            'query'       => $query,
                            'per_page'    => $perPage,
                            'orientation' => $orientation,
                        ]);

                    if ($response->successful()) {
                        return $response->json();
                    }

                    Log::warning('Pexels search failed', [
                        'query'  => $query,
                        'status' => $response->status(),
                    ]);
                    return null;
                } catch (\Exception $e) {
                    Log::warning('Pexels exception', ['query' => $query, 'error' => $e->getMessage()]);
                    return null;
                }
            });
        });
    }

    public function getRandomPhoto(string $query, string $orientation = 'landscape'): ?string
    {
        $results = $this->searchPhotos($query, 15, $orientation);

        if (!$results || empty($results['photos'])) {
            return null;
        }

        $photo = $results['photos'][array_rand($results['photos'])];

        return $photo['src']['large2x'] ?? $photo['src']['large'] ?? $photo['src']['original'] ?? null;
    }

    private const HERO_FALLBACKS = [
        'home'           => 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=1920&q=80',
        'discover'       => 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=1920&q=80',
        'plan-trip'      => 'https://images.unsplash.com/photo-1503220317375-aaad61436b1b?w=1920&q=80',
        'flights'        => 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1920&q=80',
        'accommodations' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1920&q=80',
        'bookings'       => 'https://images.unsplash.com/photo-1507608616759-54f48f0af0ee?w=1920&q=80',
        'dashboard'      => 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=1920&q=80',
    ];

    public function getHeroImage(string $page): string
    {
        $queries = [
            'home'           => 'travel world adventure explore',
            'discover'       => 'travel adventure world explore',
            'plan-trip'      => 'travel planning map journey',
            'flights'        => 'airplane flying sky clouds',
            'accommodations' => 'luxury hotel resort vacation',
            'bookings'       => 'travel booking passport tickets',
            'dashboard'      => 'travel destination beautiful landscape',
        ];

        $query    = $queries[$page] ?? 'travel adventure';
        $fallback = self::HERO_FALLBACKS[$page] ?? self::HERO_FALLBACKS['home'];

        return $this->getRandomPhoto($query) ?? $fallback;
    }

    protected function getFallbackImage(string $query): string
    {
        $seed = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($query)));
        return "https://picsum.photos/seed/{$seed}/800/600";
    }

}

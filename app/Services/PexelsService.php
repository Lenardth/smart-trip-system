<?php

namespace App\Services;

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

    /**
     * Search for photos by query
     *
     * @param string $query
     * @param int $perPage
     * @param string $orientation
     * @return array|null
     */
    public function searchPhotos(string $query, int $perPage = 15, string $orientation = 'landscape')
    {
        if (!$this->apiKey) {
            Log::warning('Pexels API key not configured');
            return null;
        }

        $cacheKey = "pexels_search_{$query}_{$perPage}_{$orientation}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($query, $perPage, $orientation) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => $this->apiKey,
                ])
                ->get("{$this->baseUrl}/search", [
                    'query' => $query,
                    'per_page' => $perPage,
                    'orientation' => $orientation,
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('Pexels API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('Pexels API exception', [
                    'message' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Get a random photo from search results
     *
     * @param string $query
     * @param string $orientation
     * @return string|null
     */
    public function getRandomPhoto(string $query, string $orientation = 'landscape')
    {
        $results = $this->searchPhotos($query, 15, $orientation);

        if (!$results || empty($results['photos'])) {
            return $this->getFallbackImage($query);
        }

        $randomPhoto = $results['photos'][array_rand($results['photos'])];
        
        // Return the large size URL
        return $randomPhoto['src']['large'] ?? $randomPhoto['src']['original'] ?? null;
    }

    /**
     * Get hero image for a specific page
     *
     * @param string $page
     * @return string
     */
    public function getHeroImage(string $page)
    {
        $queries = [
            'discover' => 'travel adventure world explore',
            'plan-trip' => 'travel planning map journey',
            'flights' => 'airplane flying sky clouds',
            'accommodations' => 'luxury hotel resort vacation',
            'bookings' => 'travel booking passport tickets',
            'dashboard' => 'travel destination beautiful landscape',
            'home' => 'travel world adventure explore',
        ];

        $query = $queries[$page] ?? 'travel adventure';
        
        return $this->getRandomPhoto($query) ?? $this->getFallbackImage($query);
    }

    /**
     * Get curated photos
     *
     * @param int $perPage
     * @return array|null
     */
    public function getCuratedPhotos(int $perPage = 15)
    {
        if (!$this->apiKey) {
            return null;
        }

        $cacheKey = "pexels_curated_{$perPage}";

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($perPage) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => $this->apiKey,
                ])
                ->get("{$this->baseUrl}/curated", [
                    'per_page' => $perPage,
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return null;
            } catch (\Exception $e) {
                Log::error('Pexels curated API exception', [
                    'message' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Get fallback image URL (Unsplash as backup)
     *
     * @param string $query
     * @return string
     */
    protected function getFallbackImage(string $query)
    {
        $keywords = str_replace(' ', ',', $query);
        return "https://source.unsplash.com/1920x1080/?{$keywords}";
    }

    /**
     * Clear cache for a specific query
     *
     * @param string $query
     * @return void
     */
    public function clearCache(string $query = null)
    {
        if ($query) {
            Cache::forget("pexels_search_{$query}_15_landscape");
        } else {
            Cache::flush();
        }
    }
}

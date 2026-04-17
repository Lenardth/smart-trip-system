<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DestinationEnrichmentService
{
    /**
     * Get enriched destination data from external APIs
     */
    public function getDestinationData(string $destinationName, string $country): array
    {
        $cacheKey = 'destination_data_' . md5($destinationName . $country);
        
        return Cache::remember($cacheKey, 3600, function () use ($destinationName, $country) {
            return [
                'fun_facts' => $this->getFunFacts($destinationName, $country),
                'activities' => $this->getActivities($destinationName, $country),
                'food' => $this->getLocalFood($destinationName, $country),
                'culture' => $this->getCultureInfo($destinationName, $country),
            ];
        });
    }

    /**
     * Search for destinations not in database using external API
     */
    public function searchDestinations(string $query): array
    {
        // Use a free API like REST Countries or Wikipedia API
        try {
            // Example: Search using REST Countries API
            $response = Http::timeout(5)->get('https://restcountries.com/v3.1/name/' . urlencode($query));
            
            if ($response->successful()) {
                $countries = $response->json();
                return array_map(function ($country) {
                    return [
                        'name' => $country['name']['common'] ?? '',
                        'capital' => $country['capital'][0] ?? '',
                        'region' => strtolower($country['region'] ?? 'general'),
                        'population' => $country['population'] ?? 0,
                        'flag' => $country['flags']['png'] ?? '',
                        'languages' => implode(', ', array_values($country['languages'] ?? [])),
                        'currencies' => implode(', ', array_keys($country['currencies'] ?? [])),
                    ];
                }, array_slice($countries, 0, 10));
            }
        } catch (\Exception $e) {
            \Log::error('Destination search failed: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get fun facts about a destination
     */
    private function getFunFacts(string $destination, string $country): array
    {
        // Use Wikipedia API or similar
        try {
            $query = $destination . ', ' . $country;
            $response = Http::timeout(5)->get('https://en.wikipedia.org/api/rest_v1/page/summary/' . urlencode($query));
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'summary' => $data['extract'] ?? '',
                    'thumbnail' => $data['thumbnail']['source'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Fun facts fetch failed: ' . $e->getMessage());
        }

        return [
            'summary' => 'Discover the unique charm and beauty of ' . $destination . '.',
            'thumbnail' => null,
        ];
    }

    /**
     * Get popular activities
     */
    private function getActivities(string $destination, string $country): array
    {
        // This would ideally use TripAdvisor API or similar
        // For now, return generic activities based on destination type
        return [
            ['name' => 'City Tours', 'icon' => 'fa-walking', 'description' => 'Explore the city on foot'],
            ['name' => 'Local Cuisine', 'icon' => 'fa-utensils', 'description' => 'Taste authentic local dishes'],
            ['name' => 'Cultural Sites', 'icon' => 'fa-landmark', 'description' => 'Visit historical landmarks'],
            ['name' => 'Photography', 'icon' => 'fa-camera', 'description' => 'Capture stunning views'],
        ];
    }

    /**
     * Get local food information
     */
    private function getLocalFood(string $destination, string $country): array
    {
        // This would use a food/recipe API
        return [
            'popular_dishes' => [
                'Traditional local cuisine',
                'Street food specialties',
                'Regional delicacies',
            ],
            'dining_tips' => 'Try local restaurants and street vendors for authentic flavors.',
        ];
    }

    /**
     * Get culture and people information
     */
    private function getCultureInfo(string $destination, string $country): array
    {
        return [
            'language' => 'Local language spoken',
            'customs' => 'Respect local customs and traditions',
            'best_time' => 'Year-round destination',
            'tips' => [
                'Learn a few local phrases',
                'Dress appropriately for cultural sites',
                'Be respectful of local traditions',
            ],
        ];
    }

    /**
     * Get destination by name (search in DB or create from API)
     */
    public function findOrCreateDestination(string $name, string $country): ?array
    {
        // First, try to find in database
        $destination = \App\Models\Destination::where('name', 'ILIKE', '%' . $name . '%')
            ->where('country', 'ILIKE', '%' . $country . '%')
            ->first();

        if ($destination) {
            return $destination->toArray();
        }

        // If not found, fetch from API
        $apiData = $this->searchDestinations($name);
        
        if (!empty($apiData)) {
            // Return the first match as a virtual destination
            return [
                'id' => 'api_' . md5($name . $country),
                'name' => $name,
                'country' => $country,
                'description' => 'Discover this amazing destination',
                'is_api_result' => true,
                'api_data' => $apiData[0] ?? [],
            ];
        }

        return null;
    }
}

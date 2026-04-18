<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DestinationEnrichmentService
{
    private $geoapifyKey;

    public function __construct()
    {
        $this->geoapifyKey = config('services.geoapify.key');
    }

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
                'attractions' => $this->getAttractions($destinationName, $country),
            ];
        });
    }

    /**
     * Get attractions using Geoapify
     */
    public function getAttractions(string $city, string $country): array
    {
        if (!$this->geoapifyKey) {
            return [];
        }

        try {
            // First, geocode the city to get coordinates
            $geocodeResponse = Http::timeout(10)->get('https://api.geoapify.com/v1/geocode/search', [
                'text' => $city . ', ' . $country,
                'apiKey' => $this->geoapifyKey,
                'limit' => 1
            ]);

            if (!$geocodeResponse->successful()) {
                \Log::warning('Geoapify geocode failed for: ' . $city);
                return [];
            }

            $geocodeData = $geocodeResponse->json();
            $features = $geocodeData['features'] ?? [];
            
            if (empty($features)) {
                return [];
            }

            $coordinates = $features[0]['geometry']['coordinates'] ?? null;
            if (!$coordinates) {
                return [];
            }

            $lon = $coordinates[0];
            $lat = $coordinates[1];

            // Get places of interest around the city
            $placesResponse = Http::timeout(10)->get('https://api.geoapify.com/v2/places', [
                'categories' => 'tourism.attraction,tourism.sights,entertainment.museum,entertainment.culture,natural,heritage,leisure.park',
                'filter' => 'circle:' . $lon . ',' . $lat . ',10000', // 10km radius
                'limit' => 20,
                'apiKey' => $this->geoapifyKey
            ]);

            if (!$placesResponse->successful()) {
                return [];
            }

            $placesData = $placesResponse->json();
            $places = $placesData['features'] ?? [];

            $attractions = [];
            foreach (array_slice($places, 0, 8) as $place) {
                $props = $place['properties'] ?? [];
                $name = $props['name'] ?? $props['address_line1'] ?? 'Attraction';
                
                // Determine icon based on categories
                $categories = $props['categories'] ?? [];
                $icon = $this->getIconForCategory($categories);
                
                $attractions[] = [
                    'name' => $name,
                    'icon' => $icon,
                    'category' => $this->getCategoryLabel($categories),
                    'address' => $props['address_line2'] ?? '',
                    'lat' => $place['geometry']['coordinates'][1] ?? null,
                    'lon' => $place['geometry']['coordinates'][0] ?? null,
                ];
            }

            return $attractions;

        } catch (\Exception $e) {
            \Log::error('Geoapify attractions error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get icon for category
     */
    private function getIconForCategory(array $categories): string
    {
        foreach ($categories as $cat) {
            if (str_contains($cat, 'museum')) return 'fa-landmark';
            if (str_contains($cat, 'park')) return 'fa-tree';
            if (str_contains($cat, 'beach')) return 'fa-umbrella-beach';
            if (str_contains($cat, 'mountain')) return 'fa-mountain';
            if (str_contains($cat, 'culture')) return 'fa-theater-masks';
            if (str_contains($cat, 'entertainment')) return 'fa-ticket-alt';
            if (str_contains($cat, 'heritage')) return 'fa-monument';
            if (str_contains($cat, 'natural')) return 'fa-leaf';
        }
        return 'fa-map-marker-alt';
    }

    /**
     * Get category label
     */
    private function getCategoryLabel(array $categories): string
    {
        if (empty($categories)) return 'Attraction';
        
        $cat = $categories[0];
        $parts = explode('.', $cat);
        return ucfirst(end($parts));
    }

    /**
     * Search for destinations not in database using Geoapify
     */
    public function searchDestinations(string $query): array
    {
        if (!$this->geoapifyKey) {
            return $this->searchCountries($query);
        }

        try {
            // Use Geoapify to search for cities and places
            $response = Http::timeout(10)->get('https://api.geoapify.com/v1/geocode/search', [
                'text' => $query,
                'type' => 'city',
                'apiKey' => $this->geoapifyKey,
                'limit' => 10
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $features = $data['features'] ?? [];
                
                $results = [];
                foreach ($features as $feature) {
                    $props = $feature['properties'] ?? [];
                    
                    $results[] = [
                        'name' => $props['city'] ?? $props['name'] ?? '',
                        'capital' => $props['city'] ?? '',
                        'country' => $props['country'] ?? '',
                        'region' => strtolower($props['region'] ?? 'general'),
                        'population' => $props['population'] ?? 0,
                        'flag' => '', // Geoapify doesn't provide flags
                        'languages' => '',
                        'currencies' => '',
                        'lat' => $props['lat'] ?? null,
                        'lon' => $props['lon'] ?? null,
                    ];
                }
                
                return $results;
            }
        } catch (\Exception $e) {
            \Log::error('Geoapify search failed: ' . $e->getMessage());
        }

        // Fallback to REST Countries
        return $this->searchCountries($query);
    }

    /**
     * Search countries using REST Countries API
     */
    private function searchCountries(string $query): array
    {
        try {
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
            \Log::error('REST Countries search failed: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get fun facts about a destination
     */
    private function getFunFacts(string $destination, string $country): array
    {
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
     * Get popular activities using Geoapify
     */
    private function getActivities(string $destination, string $country): array
    {
        if (!$this->geoapifyKey) {
            return $this->getDefaultActivities();
        }

        try {
            // Geocode the destination
            $geocodeResponse = Http::timeout(10)->get('https://api.geoapify.com/v1/geocode/search', [
                'text' => $destination . ', ' . $country,
                'apiKey' => $this->geoapifyKey,
                'limit' => 1
            ]);

            if (!$geocodeResponse->successful()) {
                return $this->getDefaultActivities();
            }

            $geocodeData = $geocodeResponse->json();
            $features = $geocodeData['features'] ?? [];
            
            if (empty($features)) {
                return $this->getDefaultActivities();
            }

            $coordinates = $features[0]['geometry']['coordinates'] ?? null;
            if (!$coordinates) {
                return $this->getDefaultActivities();
            }

            $lon = $coordinates[0];
            $lat = $coordinates[1];

            // Get activity places
            $placesResponse = Http::timeout(10)->get('https://api.geoapify.com/v2/places', [
                'categories' => 'activity,sport,entertainment,leisure',
                'filter' => 'circle:' . $lon . ',' . $lat . ',15000',
                'limit' => 10,
                'apiKey' => $this->geoapifyKey
            ]);

            if (!$placesResponse->successful()) {
                return $this->getDefaultActivities();
            }

            $placesData = $placesResponse->json();
            $places = $placesData['features'] ?? [];

            if (empty($places)) {
                return $this->getDefaultActivities();
            }

            $activities = [];
            foreach (array_slice($places, 0, 6) as $place) {
                $props = $place['properties'] ?? [];
                $categories = $props['categories'] ?? [];
                
                $activities[] = [
                    'name' => $props['name'] ?? 'Activity',
                    'icon' => $this->getIconForCategory($categories),
                    'description' => $this->getActivityDescription($categories),
                ];
            }

            return !empty($activities) ? $activities : $this->getDefaultActivities();

        } catch (\Exception $e) {
            \Log::error('Activities fetch failed: ' . $e->getMessage());
            return $this->getDefaultActivities();
        }
    }

    /**
     * Get activity description from categories
     */
    private function getActivityDescription(array $categories): string
    {
        if (empty($categories)) return 'Explore this activity';
        
        $descriptions = [
            'sport' => 'Enjoy sports and outdoor activities',
            'entertainment' => 'Experience local entertainment',
            'leisure' => 'Relax and enjoy leisure time',
            'activity' => 'Participate in local activities',
        ];

        foreach ($categories as $cat) {
            foreach ($descriptions as $key => $desc) {
                if (str_contains($cat, $key)) {
                    return $desc;
                }
            }
        }

        return 'Discover local experiences';
    }

    /**
     * Get default activities fallback
     */
    private function getDefaultActivities(): array
    {
        return [
            ['name' => 'City Tours', 'icon' => 'fa-walking', 'description' => 'Explore the city on foot with guided tours'],
            ['name' => 'Local Cuisine', 'icon' => 'fa-utensils', 'description' => 'Taste authentic local dishes and street food'],
            ['name' => 'Cultural Sites', 'icon' => 'fa-landmark', 'description' => 'Visit historical landmarks and museums'],
            ['name' => 'Photography', 'icon' => 'fa-camera', 'description' => 'Capture stunning views and memorable moments'],
        ];
    }

    /**
     * Get local food information
     */
    private function getLocalFood(string $destination, string $country): array
    {
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
        $destination = \App\Models\Destination::where('name', 'ILIKE', '%' . $name . '%')
            ->where('country', 'ILIKE', '%' . $country . '%')
            ->first();

        if ($destination) {
            return $destination->toArray();
        }

        $apiData = $this->searchDestinations($name);
        
        if (!empty($apiData)) {
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

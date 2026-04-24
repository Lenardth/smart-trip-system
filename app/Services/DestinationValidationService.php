<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Validates AI destination suggestions using multiple free APIs
 * Prevents hallucinations by cross-referencing real data
 */
class DestinationValidationService
{
    /**
     * Validate destination exists and get real data
     */
    public function validateDestination(string $destination, string $country): array
    {
        $cacheKey = 'dest_validation_' . md5($destination . $country);
        
        return Cache::remember($cacheKey, 3600, function () use ($destination, $country) {
            $validation = [
                'exists' => false,
                'real_name' => $destination,
                'real_country' => $country,
                'coordinates' => null,
                'population' => null,
                'timezone' => null,
                'currency' => null,
                'languages' => [],
                'confidence' => 0,
                'sources' => []
            ];
            
            // 1. Validate with REST Countries API (Free, no key needed)
            $countryData = $this->validateCountry($country);
            if ($countryData) {
                $validation['exists'] = true;
                $validation['real_country'] = $countryData['name'];
                $validation['currency'] = $countryData['currency'];
                $validation['languages'] = $countryData['languages'];
                $validation['confidence'] += 30;
                $validation['sources'][] = 'REST Countries API';
            }
            
            // 2. Validate with Geoapify (if key available)
            $geoData = $this->validateWithGeoapify($destination, $country);
            if ($geoData) {
                $validation['exists'] = true;
                $validation['real_name'] = $geoData['name'];
                $validation['coordinates'] = $geoData['coordinates'];
                $validation['population'] = $geoData['population'];
                $validation['confidence'] += 40;
                $validation['sources'][] = 'Geoapify';
            }
            
            // 3. Validate with OpenStreetMap Nominatim (Free, no key needed)
            $osmData = $this->validateWithOSM($destination, $country);
            if ($osmData) {
                $validation['exists'] = true;
                if (!$validation['coordinates']) {
                    $validation['coordinates'] = $osmData['coordinates'];
                }
                $validation['confidence'] += 30;
                $validation['sources'][] = 'OpenStreetMap';
            }
            
            return $validation;
        });
    }
    
    /**
     * Get real weather data for destination
     */
    public function getWeatherData(string $destination, string $country): ?array
    {
        try {
            // Use Open-Meteo (Free, no API key needed)
            $validation = $this->validateDestination($destination, $country);
            
            if (!$validation['coordinates']) {
                return null;
            }
            
            [$lat, $lon] = $validation['coordinates'];
            
            $response = Http::timeout(5)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $lat,
                'longitude' => $lon,
                'current_weather' => true,
                'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum',
                'timezone' => 'auto'
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'current_temp' => $data['current_weather']['temperature'] ?? null,
                    'avg_high' => isset($data['daily']['temperature_2m_max']) 
                        ? round(array_sum($data['daily']['temperature_2m_max']) / count($data['daily']['temperature_2m_max']), 1)
                        : null,
                    'avg_low' => isset($data['daily']['temperature_2m_min'])
                        ? round(array_sum($data['daily']['temperature_2m_min']) / count($data['daily']['temperature_2m_min']), 1)
                        : null,
                    'precipitation' => isset($data['daily']['precipitation_sum'])
                        ? round(array_sum($data['daily']['precipitation_sum']) / count($data['daily']['precipitation_sum']), 1)
                        : null,
                    'source' => 'Open-Meteo'
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Weather data fetch failed: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Get cost of living data from Numbeo-like free sources
     */
    public function getCostOfLivingData(string $destination, string $country): ?array
    {
        // Use Teleport API (Free, no key needed) for cost of living
        try {
            $cacheKey = 'cost_living_' . md5($destination);
            
            return Cache::remember($cacheKey, 86400, function () use ($destination) {
                // Search for city
                $searchResponse = Http::timeout(5)->get('https://api.teleport.org/api/cities/', [
                    'search' => $destination,
                    'limit' => 1
                ]);
                
                if (!$searchResponse->successful()) {
                    return null;
                }
                
                $cities = $searchResponse->json()['_embedded']['city:search-results'] ?? [];
                if (empty($cities)) {
                    return null;
                }
                
                $cityUrl = $cities[0]['_links']['city:item']['href'] ?? null;
                if (!$cityUrl) {
                    return null;
                }
                
                // Get urban area link
                $cityResponse = Http::timeout(5)->get($cityUrl);
                if (!$cityResponse->successful()) {
                    return null;
                }
                
                $urbanAreaUrl = $cityResponse->json()['_links']['city:urban_area']['href'] ?? null;
                if (!$urbanAreaUrl) {
                    return null;
                }
                
                // Get cost of living scores
                $scoresResponse = Http::timeout(5)->get($urbanAreaUrl . 'scores/');
                if (!$scoresResponse->successful()) {
                    return null;
                }
                
                $scores = $scoresResponse->json();
                $categories = $scores['categories'] ?? [];
                
                $costData = [
                    'overall_score' => $scores['teleport_city_score'] ?? null,
                    'housing_cost' => null,
                    'cost_of_living' => null,
                    'source' => 'Teleport API'
                ];
                
                foreach ($categories as $category) {
                    if ($category['name'] === 'Housing') {
                        $costData['housing_cost'] = $category['score_out_of_10'];
                    }
                    if ($category['name'] === 'Cost of Living') {
                        $costData['cost_of_living'] = $category['score_out_of_10'];
                    }
                }
                
                return $costData;
            });
        } catch (\Exception $e) {
            Log::warning('Cost of living data fetch failed: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Get safety and travel advisory data
     */
    public function getSafetyData(string $country): ?array
    {
        try {
            // Use Travel Advisory API (Free, no key needed)
            $cacheKey = 'safety_' . md5($country);
            
            return Cache::remember($cacheKey, 86400, function () use ($country) {
                $response = Http::timeout(5)->get('https://www.travel-advisory.info/api');
                
                if (!$response->successful()) {
                    return null;
                }
                
                $data = $response->json();
                $countries = $data['data'] ?? [];
                
                // Find matching country
                $countryCode = $this->getCountryCode($country);
                if (!$countryCode || !isset($countries[$countryCode])) {
                    return null;
                }
                
                $advisory = $countries[$countryCode]['advisory'];
                
                return [
                    'score' => $advisory['score'] ?? null, // 1-5 scale
                    'message' => $advisory['message'] ?? null,
                    'updated' => $advisory['updated'] ?? null,
                    'source' => 'Travel Advisory API'
                ];
            });
        } catch (\Exception $e) {
            Log::warning('Safety data fetch failed: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Validate country with REST Countries API
     */
    private function validateCountry(string $country): ?array
    {
        try {
            $response = Http::timeout(5)->get('https://restcountries.com/v3.1/name/' . urlencode($country), [
                'fullText' => 'false'
            ]);
            
            if (!$response->successful()) {
                return null;
            }
            
            $countries = $response->json();
            if (empty($countries)) {
                return null;
            }
            
            $countryData = $countries[0];
            
            return [
                'name' => $countryData['name']['common'] ?? $country,
                'currency' => isset($countryData['currencies']) 
                    ? array_key_first($countryData['currencies'])
                    : null,
                'languages' => isset($countryData['languages'])
                    ? array_values($countryData['languages'])
                    : [],
                'capital' => $countryData['capital'][0] ?? null,
                'region' => $countryData['region'] ?? null,
                'population' => $countryData['population'] ?? null
            ];
        } catch (\Exception $e) {
            Log::warning('REST Countries validation failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Validate with Geoapify
     */
    private function validateWithGeoapify(string $destination, string $country): ?array
    {
        $apiKey = config('services.geoapify.key');
        if (!$apiKey) {
            return null;
        }
        
        try {
            $response = Http::timeout(5)->get('https://api.geoapify.com/v1/geocode/search', [
                'text' => $destination . ', ' . $country,
                'apiKey' => $apiKey,
                'limit' => 1
            ]);
            
            if (!$response->successful()) {
                return null;
            }
            
            $data = $response->json();
            $features = $data['features'] ?? [];
            
            if (empty($features)) {
                return null;
            }
            
            $feature = $features[0];
            $props = $feature['properties'] ?? [];
            $coords = $feature['geometry']['coordinates'] ?? null;
            
            return [
                'name' => $props['city'] ?? $props['name'] ?? $destination,
                'coordinates' => $coords ? [$coords[1], $coords[0]] : null, // [lat, lon]
                'population' => $props['population'] ?? null
            ];
        } catch (\Exception $e) {
            Log::warning('Geoapify validation failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Validate with OpenStreetMap Nominatim
     */
    private function validateWithOSM(string $destination, string $country): ?array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'SmartBooking/1.0'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $destination . ', ' . $country,
                    'format' => 'json',
                    'limit' => 1
                ]);
            
            if (!$response->successful()) {
                return null;
            }
            
            $results = $response->json();
            if (empty($results)) {
                return null;
            }
            
            $result = $results[0];
            
            return [
                'name' => $result['display_name'] ?? $destination,
                'coordinates' => [
                    (float) $result['lat'],
                    (float) $result['lon']
                ]
            ];
        } catch (\Exception $e) {
            Log::warning('OSM validation failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get ISO country code
     */
    private function getCountryCode(string $country): ?string
    {
        $codes = [
            'afghanistan' => 'AF', 'albania' => 'AL', 'algeria' => 'DZ', 'argentina' => 'AR',
            'australia' => 'AU', 'austria' => 'AT', 'bangladesh' => 'BD', 'belgium' => 'BE',
            'bolivia' => 'BO', 'brazil' => 'BR', 'bulgaria' => 'BG', 'cambodia' => 'KH',
            'canada' => 'CA', 'chile' => 'CL', 'china' => 'CN', 'colombia' => 'CO',
            'costa rica' => 'CR', 'croatia' => 'HR', 'cuba' => 'CU', 'czech republic' => 'CZ',
            'denmark' => 'DK', 'ecuador' => 'EC', 'egypt' => 'EG', 'ethiopia' => 'ET',
            'finland' => 'FI', 'france' => 'FR', 'germany' => 'DE', 'greece' => 'GR',
            'guatemala' => 'GT', 'hungary' => 'HU', 'iceland' => 'IS', 'india' => 'IN',
            'indonesia' => 'ID', 'iran' => 'IR', 'iraq' => 'IQ', 'ireland' => 'IE',
            'israel' => 'IL', 'italy' => 'IT', 'jamaica' => 'JM', 'japan' => 'JP',
            'jordan' => 'JO', 'kenya' => 'KE', 'south korea' => 'KR', 'laos' => 'LA',
            'malaysia' => 'MY', 'mexico' => 'MX', 'morocco' => 'MA', 'myanmar' => 'MM',
            'nepal' => 'NP', 'netherlands' => 'NL', 'new zealand' => 'NZ', 'norway' => 'NO',
            'pakistan' => 'PK', 'panama' => 'PA', 'peru' => 'PE', 'philippines' => 'PH',
            'poland' => 'PL', 'portugal' => 'PT', 'romania' => 'RO', 'russia' => 'RU',
            'saudi arabia' => 'SA', 'serbia' => 'RS', 'singapore' => 'SG', 'south africa' => 'ZA',
            'spain' => 'ES', 'sri lanka' => 'LK', 'sweden' => 'SE', 'switzerland' => 'CH',
            'syria' => 'SY', 'taiwan' => 'TW', 'tanzania' => 'TZ', 'thailand' => 'TH',
            'tunisia' => 'TN', 'turkey' => 'TR', 'uganda' => 'UG', 'ukraine' => 'UA',
            'united arab emirates' => 'AE', 'united kingdom' => 'GB', 'united states' => 'US',
            'uruguay' => 'UY', 'venezuela' => 'VE', 'vietnam' => 'VN', 'yemen' => 'YE',
        ];
        
        $countryLower = strtolower($country);
        return $codes[$countryLower] ?? null;
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationService
{
    /**
     * Get user's location from IP address (privacy-friendly, no personal data stored)
     */
    public function getLocationFromIP(string $ipAddress): ?array
    {
        try {
            // Use ipapi.co for IP geolocation (free tier: 1000 requests/day)
            $response = Http::timeout(3)->get("https://ipapi.co/{$ipAddress}/json/");
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'city' => $data['city'] ?? null,
                    'country' => $data['country_name'] ?? null,
                    'country_code' => $data['country_code'] ?? null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'timezone' => $data['timezone'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Location detection failed: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Find nearest airport to coordinates
     */
    public function findNearestAirport(float $latitude, float $longitude): ?array
    {
        try {
            // Use a simple distance calculation to find nearest major airport
            $airports = $this->getMajorAirports();
            
            $nearest = null;
            $minDistance = PHP_FLOAT_MAX;
            
            foreach ($airports as $airport) {
                $distance = $this->calculateDistance(
                    $latitude,
                    $longitude,
                    $airport['lat'],
                    $airport['lng']
                );
                
                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $nearest = $airport;
                }
            }
            
            return $nearest;
        } catch (\Exception $e) {
            Log::warning('Airport detection failed: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    /**
     * Get list of major international airports
     */
    private function getMajorAirports(): array
    {
        return [
            // North America
            ['code' => 'JFK', 'name' => 'New York JFK', 'city' => 'New York', 'country' => 'USA', 'lat' => 40.6413, 'lng' => -73.7781],
            ['code' => 'LAX', 'name' => 'Los Angeles', 'city' => 'Los Angeles', 'country' => 'USA', 'lat' => 33.9416, 'lng' => -118.4085],
            ['code' => 'ORD', 'name' => 'Chicago O\'Hare', 'city' => 'Chicago', 'country' => 'USA', 'lat' => 41.9742, 'lng' => -87.9073],
            ['code' => 'MIA', 'name' => 'Miami', 'city' => 'Miami', 'country' => 'USA', 'lat' => 25.7959, 'lng' => -80.2870],
            ['code' => 'YYZ', 'name' => 'Toronto Pearson', 'city' => 'Toronto', 'country' => 'Canada', 'lat' => 43.6777, 'lng' => -79.6248],
            ['code' => 'YVR', 'name' => 'Vancouver', 'city' => 'Vancouver', 'country' => 'Canada', 'lat' => 49.1967, 'lng' => -123.1815],
            
            // Europe
            ['code' => 'LHR', 'name' => 'London Heathrow', 'city' => 'London', 'country' => 'UK', 'lat' => 51.4700, 'lng' => -0.4543],
            ['code' => 'CDG', 'name' => 'Paris Charles de Gaulle', 'city' => 'Paris', 'country' => 'France', 'lat' => 49.0097, 'lng' => 2.5479],
            ['code' => 'FRA', 'name' => 'Frankfurt', 'city' => 'Frankfurt', 'country' => 'Germany', 'lat' => 50.0379, 'lng' => 8.5622],
            ['code' => 'AMS', 'name' => 'Amsterdam Schiphol', 'city' => 'Amsterdam', 'country' => 'Netherlands', 'lat' => 52.3105, 'lng' => 4.7683],
            ['code' => 'MAD', 'name' => 'Madrid Barajas', 'city' => 'Madrid', 'country' => 'Spain', 'lat' => 40.4983, 'lng' => -3.5676],
            ['code' => 'FCO', 'name' => 'Rome Fiumicino', 'city' => 'Rome', 'country' => 'Italy', 'lat' => 41.8003, 'lng' => 12.2389],
            
            // Middle East
            ['code' => 'DXB', 'name' => 'Dubai', 'city' => 'Dubai', 'country' => 'UAE', 'lat' => 25.2532, 'lng' => 55.3657],
            ['code' => 'DOH', 'name' => 'Doha Hamad', 'city' => 'Doha', 'country' => 'Qatar', 'lat' => 25.2731, 'lng' => 51.6080],
            ['code' => 'IST', 'name' => 'Istanbul', 'city' => 'Istanbul', 'country' => 'Turkey', 'lat' => 41.2753, 'lng' => 28.7519],
            
            // Asia
            ['code' => 'HKG', 'name' => 'Hong Kong', 'city' => 'Hong Kong', 'country' => 'Hong Kong', 'lat' => 22.3080, 'lng' => 113.9185],
            ['code' => 'SIN', 'name' => 'Singapore Changi', 'city' => 'Singapore', 'country' => 'Singapore', 'lat' => 1.3644, 'lng' => 103.9915],
            ['code' => 'NRT', 'name' => 'Tokyo Narita', 'city' => 'Tokyo', 'country' => 'Japan', 'lat' => 35.7720, 'lng' => 140.3929],
            ['code' => 'ICN', 'name' => 'Seoul Incheon', 'city' => 'Seoul', 'country' => 'South Korea', 'lat' => 37.4602, 'lng' => 126.4407],
            ['code' => 'BKK', 'name' => 'Bangkok Suvarnabhumi', 'city' => 'Bangkok', 'country' => 'Thailand', 'lat' => 13.6900, 'lng' => 100.7501],
            ['code' => 'DEL', 'name' => 'Delhi', 'city' => 'Delhi', 'country' => 'India', 'lat' => 28.5562, 'lng' => 77.1000],
            
            // Oceania
            ['code' => 'SYD', 'name' => 'Sydney', 'city' => 'Sydney', 'country' => 'Australia', 'lat' => -33.9399, 'lng' => 151.1753],
            ['code' => 'MEL', 'name' => 'Melbourne', 'city' => 'Melbourne', 'country' => 'Australia', 'lat' => -37.6690, 'lng' => 144.8410],
            ['code' => 'AKL', 'name' => 'Auckland', 'city' => 'Auckland', 'country' => 'New Zealand', 'lat' => -37.0082, 'lng' => 174.7850],
            
            // South America
            ['code' => 'GRU', 'name' => 'São Paulo', 'city' => 'São Paulo', 'country' => 'Brazil', 'lat' => -23.4356, 'lng' => -46.4731],
            ['code' => 'EZE', 'name' => 'Buenos Aires', 'city' => 'Buenos Aires', 'country' => 'Argentina', 'lat' => -34.8222, 'lng' => -58.5358],
            
            // Africa
            ['code' => 'JNB', 'name' => 'Johannesburg', 'city' => 'Johannesburg', 'country' => 'South Africa', 'lat' => -26.1392, 'lng' => 28.2460],
            ['code' => 'CAI', 'name' => 'Cairo', 'city' => 'Cairo', 'country' => 'Egypt', 'lat' => 30.1219, 'lng' => 31.4056],
        ];
    }

    /**
     * Get all major airports (for dropdown)
     */
    public function getAllAirports(): array
    {
        return $this->getMajorAirports();
    }
}

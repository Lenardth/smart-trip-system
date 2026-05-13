<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Flight Pricing Service
 * 
 * Fetches real-time flight prices from external APIs
 * Falls back to intelligent estimation when API is unavailable
 */
use App\Contracts\FlightPricingInterface;

class FlightPricingService implements FlightPricingInterface
{
    private ?string $rapidApiKey;
    private string $rapidApiHost = 'sky-scrapper.p.rapidapi.com';

    public function __construct()
    {
        $this->rapidApiKey = config('services.skyscanner.key') ?? env('SKYSCANNER_RAPIDAPI_KEY');
    }

    /**
     * Get flight price for a route
     * 
     * @param string $from IATA code
     * @param string $to IATA code
     * @param string $duration Flight duration (e.g., "2h 30m")
     * @param string $travelClass
     * @return array ['price' => int, 'source' => 'api'|'estimated']
     */
    public function getPrice(string $from, string $to, string $duration, string $travelClass = 'ECONOMY'): array
    {
        // Try to get from cache first (cache for 1 hour)
        $cacheKey = "flight_price_{$from}_{$to}_{$travelClass}";
        
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        // Try external API if configured
        if ($this->rapidApiKey) {
            try {
                $apiPrice = $this->fetchFromApi($from, $to, $travelClass);
                if ($apiPrice) {
                    $result = ['price' => $apiPrice, 'source' => 'api'];
                    Cache::put($cacheKey, $result, 3600); // 1 hour
                    return $result;
                }
            } catch (\Throwable $e) {
                Log::warning('Flight pricing API failed', [
                    'error' => $e->getMessage(),
                    'route' => "{$from}-{$to}"
                ]);
            }
        }

        // Fallback to intelligent estimation
        $estimatedPrice = $this->estimatePrice($from, $to, $duration, $travelClass);
        $result = ['price' => $estimatedPrice, 'source' => 'estimated'];
        
        Cache::put($cacheKey, $result, 1800); // 30 minutes for estimates
        
        return $result;
    }

    /**
     * Fetch price from external API (Skyscanner via RapidAPI)
     */
    private function fetchFromApi(string $from, string $to, string $travelClass): ?int
    {
        // Note: This is a placeholder for actual API integration
        // You would need to implement the specific API endpoint calls here
        return null;
    }

    /**
     * Intelligent price estimation based on route characteristics
     */
    private function estimatePrice(string $from, string $to, string $duration, string $travelClass): int
    {
        // Parse duration to minutes
        $minutes = $this->parseDuration($duration);

        // Base price calculation factors:
        // 1. Distance/duration (primary factor)
        // 2. Route popularity (major hubs vs regional)
        // 3. Regional pricing differences
        
        $baseRate = $this->getBaseRate($from, $to);
        $base = max(49, (int)($minutes * $baseRate));

        // Apply regional adjustments
        $base = $this->applyRegionalAdjustment($base, $from, $to);

        // Class multipliers (industry standard)
        $multiplier = match (strtoupper($travelClass)) {
            'PREMIUM_ECONOMY' => 1.8,
            'BUSINESS'        => 3.5,
            'FIRST'           => 6.0,
            default           => 1.0, // ECONOMY
        };

        return (int)round($base * $multiplier);
    }

    /**
     * Get base rate per minute based on route type
     */
    private function getBaseRate(string $from, string $to): float
    {
        $majorHubs = ['JFK', 'LAX', 'LHR', 'CDG', 'DXB', 'SIN', 'HKG', 'NRT', 'FRA', 'AMS'];
        
        $fromMajor = in_array(strtoupper($from), $majorHubs);
        $toMajor = in_array(strtoupper($to), $majorHubs);

        // Major hub to major hub: more competition, lower rates
        if ($fromMajor && $toMajor) {
            return 0.10;
        }

        // One major hub: moderate rates
        if ($fromMajor || $toMajor) {
            return 0.12;
        }

        // Regional routes: higher rates due to less competition
        return 0.15;
    }

    /**
     * Apply regional pricing adjustments
     */
    private function applyRegionalAdjustment(int $base, string $from, string $to): int
    {
        // African routes tend to be more expensive
        $africanAirports = ['JNB', 'CPT', 'NBO', 'CAI', 'LOS', 'ACC', 'ADD', 'DAR'];
        
        $fromAfrica = in_array(strtoupper($from), $africanAirports);
        $toAfrica = in_array(strtoupper($to), $africanAirports);

        if ($fromAfrica || $toAfrica) {
            return (int)($base * 1.15);
        }

        // Middle East routes
        $middleEastAirports = ['DXB', 'AUH', 'DOH', 'RUH', 'JED'];
        
        $fromME = in_array(strtoupper($from), $middleEastAirports);
        $toME = in_array(strtoupper($to), $middleEastAirports);

        if ($fromME && $toME) {
            return (int)($base * 0.9); // Competitive regional market
        }

        return $base;
    }

    /**
     * Parse duration string to minutes
     */
    private function parseDuration(string $duration): int
    {
        $minutes = 120; // default 2h
        
        if (preg_match('/(\d+)h\s*(\d+)?m?/', $duration, $m)) {
            $minutes = ((int)$m[1]) * 60 + (int)($m[2] ?? 0);
        }

        return $minutes;
    }

    /**
     * Get popular route deals with API-driven or estimated prices
     */
    public function getPopularRouteDeals(): array
    {
        $routes = [
            ['from' => 'JNB', 'to' => 'CPT', 'duration' => '2h 00m', 'airline' => 'FlySafair', 'tag' => 'Domestic Deal', 'icon' => 'fa-plane'],
            ['from' => 'DXB', 'to' => 'BKK', 'duration' => '6h 30m', 'airline' => 'Emirates', 'tag' => 'Popular Route', 'icon' => 'fa-star'],
            ['from' => 'LHR', 'to' => 'LIS', 'duration' => '2h 30m', 'airline' => 'TAP Air', 'tag' => 'Hot Deal', 'icon' => 'fa-fire'],
            ['from' => 'JFK', 'to' => 'CUN', 'duration' => '4h 15m', 'airline' => 'JetBlue', 'tag' => 'Beach Escape', 'icon' => 'fa-umbrella-beach'],
            ['from' => 'SIN', 'to' => 'DPS', 'duration' => '2h 30m', 'airline' => 'Scoot', 'tag' => 'Weekend Getaway', 'icon' => 'fa-leaf'],
            ['from' => 'CDG', 'to' => 'BCN', 'duration' => '1h 55m', 'airline' => 'Vueling', 'tag' => 'Flash Sale', 'icon' => 'fa-bolt'],
            ['from' => 'SYD', 'to' => 'MEL', 'duration' => '1h 25m', 'airline' => 'Jetstar', 'tag' => 'Domestic Deal', 'icon' => 'fa-plane'],
            ['from' => 'NBO', 'to' => 'ZNZ', 'duration' => '1h 45m', 'airline' => 'Kenya Airways', 'tag' => 'Island Escape', 'icon' => 'fa-sun'],
        ];

        return array_map(function($route) {
            $pricing = $this->getPrice($route['from'], $route['to'], $route['duration']);
            return array_merge($route, ['price' => $pricing['price']]);
        }, $routes);
    }

    /**
     * Get popular routes with pricing
     */
    public function getPopularRoutes(): array
    {
        $routes = [
            ['from' => 'JFK', 'to' => 'LHR', 'duration' => '7h 30m', 'direct' => true],
            ['from' => 'CDG', 'to' => 'NRT', 'duration' => '12h 45m', 'direct' => false],
            ['from' => 'DXB', 'to' => 'JFK', 'duration' => '14h 20m', 'direct' => true],
            ['from' => 'LAX', 'to' => 'SYD', 'duration' => '15h 10m', 'direct' => true],
            ['from' => 'SIN', 'to' => 'DPS', 'duration' => '2h 30m', 'direct' => true],
            ['from' => 'LHR', 'to' => 'DXB', 'duration' => '7h 00m', 'direct' => true],
        ];

        return array_map(function($route) {
            $pricing = $this->getPrice($route['from'], $route['to'], $route['duration']);
            return array_merge($route, ['price' => $pricing['price']]);
        }, $routes);
    }
}

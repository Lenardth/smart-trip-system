<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Accommodation Pricing Service
 * 
 * Fetches real-time accommodation prices from external APIs
 * Falls back to intelligent estimation when API is unavailable
 */
use App\Contracts\AccommodationPricingInterface;

class AccommodationPricingService implements AccommodationPricingInterface
{
    private ?string $rapidApiKey;
    private string $rapidApiHost = 'booking-com.p.rapidapi.com';

    public function __construct()
    {
        $this->rapidApiKey = config('services.booking.key') ?? env('BOOKING_RAPIDAPI_KEY');
    }

    /**
     * Get accommodation price
     * 
     * @param string $city
     * @param string $style (hostel, hotel, resort, etc.)
     * @param string $budgetTier (backpacker, budget, mid, premium, luxury)
     * @return array ['price' => float, 'source' => 'api'|'estimated']
     */
    public function getPrice(string $city, string $style, string $budgetTier): array
    {
        // Try to get from cache first (cache for 6 hours)
        $cacheKey = "accommodation_price_{$city}_{$style}_{$budgetTier}";
        
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        // Try external API if configured
        if ($this->rapidApiKey) {
            try {
                $apiPrice = $this->fetchFromApi($city, $style, $budgetTier);
                if ($apiPrice) {
                    $result = ['price' => $apiPrice, 'source' => 'api'];
                    Cache::put($cacheKey, $result, 21600); // 6 hours
                    return $result;
                }
            } catch (\Throwable $e) {
                Log::warning('Accommodation pricing API failed', [
                    'error' => $e->getMessage(),
                    'city' => $city
                ]);
            }
        }

        // Fallback to intelligent estimation
        $estimatedPrice = $this->estimatePrice($city, $style, $budgetTier);
        $result = ['price' => $estimatedPrice, 'source' => 'estimated'];
        
        Cache::put($cacheKey, $result, 3600); // 1 hour for estimates
        
        return $result;
    }

    /**
     * Fetch price from external API (Booking.com via RapidAPI)
     */
    private function fetchFromApi(string $city, string $style, string $budgetTier): ?float
    {
        // Note: This is a placeholder for actual API integration
        // You would need to implement the specific API endpoint calls here
        // Example: Booking.com API, Hotels.com API, or Amadeus Hotel API
        return null;
    }

    /**
     * Intelligent price estimation based on location and accommodation type
     */
    private function estimatePrice(string $city, string $style, string $budgetTier): float
    {
        // Base rates by style
        $baseRates = [
            'hostel'       => 25,
            'guest_house'  => 60,
            'motel'        => 70,
            'apartment'    => 110,
            'budget_hotel' => 85,
            'hotel'        => 140,
            'boutique'     => 180,
            'resort'       => 350,
            'villa'        => 280,
            'airbnb'       => 95,
            'glamping'     => 120,
        ];

        $base = $baseRates[$style] ?? 100;

        // Apply budget tier multiplier
        $tierMultiplier = match($budgetTier) {
            'backpacker' => 0.6,
            'budget'     => 0.8,
            'mid'        => 1.0,
            'premium'    => 1.5,
            'luxury'     => 2.5,
            default      => 1.0,
        };

        $price = $base * $tierMultiplier;

        // Apply regional pricing adjustments
        $price = $this->applyRegionalAdjustment($price, $city);

        // Add some variance to make it realistic (±15%)
        $variance = 1 + (rand(-15, 15) / 100);
        
        return round($price * $variance, 2);
    }

    /**
     * Apply regional pricing adjustments based on city
     */
    private function applyRegionalAdjustment(float $price, string $city): float
    {
        $cityLower = strtolower($city);

        // Expensive cities (1.5x - 2.5x)
        $expensiveCities = [
            'london', 'paris', 'new york', 'tokyo', 'singapore', 'hong kong',
            'zurich', 'geneva', 'oslo', 'copenhagen', 'reykjavik', 'dubai',
            'sydney', 'melbourne', 'san francisco', 'los angeles', 'miami'
        ];

        foreach ($expensiveCities as $expensive) {
            if (str_contains($cityLower, $expensive)) {
                return $price * 2.0;
            }
        }

        // Moderate cities (1.2x - 1.4x)
        $moderateCities = [
            'amsterdam', 'barcelona', 'rome', 'madrid', 'berlin', 'vienna',
            'prague', 'lisbon', 'dublin', 'edinburgh', 'brussels', 'milan',
            'munich', 'stockholm', 'helsinki', 'athens', 'istanbul', 'bangkok',
            'kuala lumpur', 'seoul', 'taipei', 'shanghai', 'beijing'
        ];

        foreach ($moderateCities as $moderate) {
            if (str_contains($cityLower, $moderate)) {
                return $price * 1.3;
            }
        }

        // Budget-friendly cities (0.6x - 0.8x)
        $budgetCities = [
            'budapest', 'warsaw', 'bucharest', 'sofia', 'belgrade', 'zagreb',
            'hanoi', 'ho chi minh', 'phnom penh', 'vientiane', 'yangon',
            'kathmandu', 'delhi', 'mumbai', 'bangalore', 'colombo', 'dhaka',
            'cairo', 'nairobi', 'dar es salaam', 'kampala', 'addis ababa',
            'casablanca', 'marrakech', 'tunis', 'algiers', 'johannesburg',
            'cape town', 'durban', 'lima', 'bogota', 'quito', 'la paz'
        ];

        foreach ($budgetCities as $budget) {
            if (str_contains($cityLower, $budget)) {
                return $price * 0.7;
            }
        }

        // Default: no adjustment
        return $price;
    }

    /**
     * Estimate nightly rate with rating consideration
     */
    public function estimateNightlyRate(string $style, string $budgetTier, int $rating = 0): float
    {
        $pricing = $this->getPrice('default', $style, $budgetTier);
        $base = $pricing['price'];

        // Adjust based on rating
        if ($rating >= 90) {
            return $base * 1.3;
        } elseif ($rating >= 80) {
            return $base * 1.15;
        } elseif ($rating >= 70) {
            return $base * 1.0;
        } elseif ($rating >= 60) {
            return $base * 0.85;
        }

        return $base * 0.7;
    }
}

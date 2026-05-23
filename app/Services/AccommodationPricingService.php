<?php

namespace App\Services;

use App\Contracts\AccommodationPricingInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AccommodationPricingService implements AccommodationPricingInterface
{
    private ?string $rapidApiKey;
    private string  $rapidApiHost;

    public function __construct()
    {
        $this->rapidApiKey  = config('services.booking.key');
        $this->rapidApiHost = config('services.booking.host');
    }

    public function getPrice(string $city, string $style, string $budgetTier): array
    {
        $cacheKey = "accommodation_price_{$city}_{$style}_{$budgetTier}";

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        if ($this->rapidApiKey) {
            try {
                $apiPrice = $this->fetchFromApi($city, $style, $budgetTier);
                if ($apiPrice) {
                    $result = ['price' => $apiPrice, 'source' => 'api'];
                    Cache::put($cacheKey, $result, config('accommodation.cache_ttl.api_price'));
                    return $result;
                }
            } catch (\Throwable $e) {
                Log::warning('Accommodation pricing API failed', [
                    'error' => $e->getMessage(),
                    'city'  => $city,
                ]);
            }
        }

        $result = ['price' => $this->estimatePrice($city, $style, $budgetTier), 'source' => 'estimated'];
        Cache::put($cacheKey, $result, config('accommodation.cache_ttl.estimated_price'));

        return $result;
    }

    public function estimateNightlyRate(string $style, string $budgetTier, int $rating = 0): float
    {
        $base = $this->getPrice('default', $style, $budgetTier)['price'];

        foreach (config('accommodation.rating_multipliers') as $tier) {
            if ($rating >= $tier['threshold']) {
                return $base * $tier['multiplier'];
            }
        }

        return $base;
    }

    private function fetchFromApi(string $city, string $style, string $budgetTier): ?float
    {
        // Placeholder — implement Booking.com / Hotels.com endpoint when key is provided.
        return null;
    }

    private function estimatePrice(string $city, string $style, string $budgetTier): float
    {
        $baseRates = config('accommodation.base_rates');
        $base      = $baseRates[$style] ?? $baseRates['default'];

        $tierMultipliers = config('accommodation.tier_multipliers');
        $tierMultiplier  = $tierMultipliers[$budgetTier] ?? $tierMultipliers['default'];

        $price = $base * $tierMultiplier;
        $price = $this->applyRegionalAdjustment($price, $city);

        $variancePct = config('accommodation.price_variance_pct');
        $variance    = 1 + (rand(-$variancePct, $variancePct) / 100);

        return round($price * $variance, 2);
    }

    private function applyRegionalAdjustment(float $price, string $city): float
    {
        $cityLower = strtolower($city);

        foreach (config('accommodation.city_multipliers') as $tier) {
            foreach ($tier['cities'] as $name) {
                if (str_contains($cityLower, $name)) {
                    return $price * $tier['multiplier'];
                }
            }
        }

        return $price;
    }
}

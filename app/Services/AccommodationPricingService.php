<?php

namespace App\Services;

use App\Contracts\AccommodationPricingInterface;
use Illuminate\Support\Facades\Cache;

class AccommodationPricingService implements AccommodationPricingInterface
{
    public function getPrice(string $city, string $style, string $budgetTier): array
    {
        $cacheKey = "accommodation_price_{$city}_{$style}_{$budgetTier}";

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $result = ['price' => $this->estimatePrice($city, $style, $budgetTier), 'source' => 'estimated'];
        Cache::put($cacheKey, $result, config('accommodation.cache_ttl.estimated_price'));

        return $result;
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

<?php

namespace App\Services;

use App\Contracts\FlightPricingInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlightPricingService implements FlightPricingInterface
{
    private ?string $rapidApiKey;
    private string  $rapidApiHost;

    public function __construct()
    {
        $this->rapidApiKey  = config('services.skyscanner.key');
        $this->rapidApiHost = config('services.skyscanner.host');
    }

    public function getPrice(string $from, string $to, string $duration, string $travelClass = 'ECONOMY'): array
    {
        $cacheKey = "flight_price_{$from}_{$to}_{$travelClass}";

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        if ($this->rapidApiKey) {
            try {
                $apiPrice = $this->fetchFromApi($from, $to, $travelClass);
                if ($apiPrice) {
                    $result = ['price' => $apiPrice, 'source' => 'api'];
                    Cache::put($cacheKey, $result, config('flights.cache_ttl.api_price'));
                    return $result;
                }
            } catch (\Throwable $e) {
                Log::warning('Flight pricing API failed', [
                    'error' => $e->getMessage(),
                    'route' => "{$from}-{$to}",
                ]);
            }
        }

        $result = ['price' => $this->estimatePrice($from, $to, $duration, $travelClass), 'source' => 'estimated'];
        Cache::put($cacheKey, $result, config('flights.cache_ttl.estimated_price'));

        return $result;
    }

    private function fetchFromApi(string $from, string $to, string $travelClass): ?int
    {
        // Placeholder — implement Skyscanner endpoint when key is provided.
        return null;
    }

    private function estimatePrice(string $from, string $to, string $duration, string $travelClass): int
    {
        $minutes    = $this->parseDuration($duration);
        $baseRate   = $this->getBaseRate($from, $to);
        $minPrice   = config('flights.min_price');
        $base       = max($minPrice, (int) ($minutes * $baseRate));
        $base       = $this->applyRegionalAdjustment($base, $from, $to);
        $multiplier = config('flights.class_multipliers.' . strtoupper($travelClass),
                            config('flights.class_multipliers.ECONOMY'));

        return (int) round($base * $multiplier);
    }

    private function getBaseRate(string $from, string $to): float
    {
        $hubs      = config('flights.major_hubs');
        $fromMajor = in_array(strtoupper($from), $hubs);
        $toMajor   = in_array(strtoupper($to),   $hubs);

        if ($fromMajor && $toMajor) return config('flights.base_rates.hub_to_hub');
        if ($fromMajor || $toMajor) return config('flights.base_rates.one_hub');

        return config('flights.base_rates.regional');
    }

    private function applyRegionalAdjustment(int $base, string $from, string $to): int
    {
        $from = strtoupper($from);
        $to   = strtoupper($to);

        if (in_array($from, config('flights.african_airports')) ||
            in_array($to,   config('flights.african_airports'))) {
            return (int) ($base * config('flights.regional_adjustments.africa'));
        }

        $me = config('flights.middle_east_airports');
        if (in_array($from, $me) && in_array($to, $me)) {
            return (int) ($base * config('flights.regional_adjustments.middle_east'));
        }

        return $base;
    }

    private function parseDuration(string $duration): int
    {
        if (preg_match('/(\d+)h\s*(\d+)?m?/', $duration, $m)) {
            return ((int) $m[1]) * 60 + (int) ($m[2] ?? 0);
        }

        return config('flights.default_duration_minutes');
    }
}

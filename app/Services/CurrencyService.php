<?php

namespace App\Services;

use App\Contracts\CurrencyServiceInterface;
use App\Models\ApiResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService implements CurrencyServiceInterface
{
    public function getRates(string $base = 'USD'): array
    {
        $ttl = config('currencies.cache_ttl');

        return Cache::remember("exchange_rates_{$base}", $ttl, function () use ($base, $ttl) {
            $stored = ApiResponse::remember('currency', 'rates', ['base' => $base], now()->addSeconds($ttl), function () use ($base) {
                try {
                    $url      = config('currencies.api_url') . "/{$base}";
                    $response = Http::timeout(10)->get($url);

                    if ($response->successful() && ($response->json()['result'] ?? '') === 'success') {
                        return [
                            'base'       => $base,
                            'rates'      => $response->json()['rates'] ?? [],
                            'updated_at' => $response->json()['time_last_update_utc'] ?? now()->toISOString(),
                        ];
                    }
                } catch (\Throwable $e) {
                    Log::warning('CurrencyService: failed to fetch rates', ['error' => $e->getMessage()]);
                }

                return null;
            });

            if ($stored) {
                return $stored;
            }

            return [
                'base'       => 'USD',
                'rates'      => config('currencies.fallback_rates'),
                'updated_at' => 'Fallback rates',
            ];
        });
    }

    public function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) return $amount;

        $rates = $this->getRates('USD')['rates'];
        $inUsd = $from === 'USD' ? $amount : $amount / ($rates[$from] ?? 1);

        return $inUsd * ($rates[$to] ?? 1);
    }

    public function getSymbol(string $currency): string
    {
        return config("currencies.supported.{$currency}.symbol", $currency);
    }

    public function format(float $amount, string $currency): string
    {
        $decimals = in_array($currency, config('currencies.zero_decimal')) ? 0 : 2;

        return $this->getSymbol($currency) . number_format($amount, $decimals);
    }

    public function getSupportedCurrencies(): array
    {
        return config('currencies.supported');
    }

    public function isSupported(string $currency): bool
    {
        return config("currencies.supported." . strtoupper($currency)) !== null;
    }
}

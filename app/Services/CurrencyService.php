<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    // Free tier: https://open.er-api.com — no key needed, 1,500 req/month
    private const BASE_URL = 'https://open.er-api.com/v6/latest';
    private const CACHE_TTL = 3600; // 1 hour

    public static array $SUPPORTED = [
        'USD' => ['name' => 'US Dollar',          'symbol' => '$',   'flag' => 'us'],
        'EUR' => ['name' => 'Euro',                'symbol' => '€',   'flag' => 'eu'],
        'GBP' => ['name' => 'British Pound',       'symbol' => '£',   'flag' => 'gb'],
        'ZAR' => ['name' => 'South African Rand',  'symbol' => 'R',   'flag' => 'za'],
        'AED' => ['name' => 'UAE Dirham',          'symbol' => 'د.إ', 'flag' => 'ae'],
        'JPY' => ['name' => 'Japanese Yen',        'symbol' => '¥',   'flag' => 'jp'],
        'AUD' => ['name' => 'Australian Dollar',   'symbol' => 'A$',  'flag' => 'au'],
        'CAD' => ['name' => 'Canadian Dollar',     'symbol' => 'C$',  'flag' => 'ca'],
        'CHF' => ['name' => 'Swiss Franc',         'symbol' => 'Fr',  'flag' => 'ch'],
        'CNY' => ['name' => 'Chinese Yuan',        'symbol' => '¥',   'flag' => 'cn'],
        'INR' => ['name' => 'Indian Rupee',        'symbol' => '₹',   'flag' => 'in'],
        'BRL' => ['name' => 'Brazilian Real',      'symbol' => 'R$',  'flag' => 'br'],
        'MXN' => ['name' => 'Mexican Peso',        'symbol' => '$',   'flag' => 'mx'],
        'SGD' => ['name' => 'Singapore Dollar',    'symbol' => 'S$',  'flag' => 'sg'],
        'THB' => ['name' => 'Thai Baht',           'symbol' => '฿',   'flag' => 'th'],
        'KES' => ['name' => 'Kenyan Shilling',     'symbol' => 'KSh', 'flag' => 'ke'],
        'NGN' => ['name' => 'Nigerian Naira',      'symbol' => '₦',   'flag' => 'ng'],
        'EGP' => ['name' => 'Egyptian Pound',      'symbol' => 'E£',  'flag' => 'eg'],
        'IDR' => ['name' => 'Indonesian Rupiah',   'symbol' => 'Rp',  'flag' => 'id'],
        'MYR' => ['name' => 'Malaysian Ringgit',   'symbol' => 'RM',  'flag' => 'my'],
        'NZD' => ['name' => 'New Zealand Dollar',  'symbol' => 'NZ$', 'flag' => 'nz'],
        'SEK' => ['name' => 'Swedish Krona',       'symbol' => 'kr',  'flag' => 'se'],
        'NOK' => ['name' => 'Norwegian Krone',     'symbol' => 'kr',  'flag' => 'no'],
        'DKK' => ['name' => 'Danish Krone',        'symbol' => 'kr',  'flag' => 'dk'],
        'TRY' => ['name' => 'Turkish Lira',        'symbol' => '₺',   'flag' => 'tr'],
    ];

    public function getRates(string $base = 'USD'): array
    {
        $cacheKey = "exchange_rates_{$base}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($base) {
            try {
                $response = Http::timeout(10)->get(self::BASE_URL . "/{$base}");

                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['result'] ?? '') === 'success') {
                        return [
                            'base'       => $base,
                            'rates'      => $data['rates'] ?? [],
                            'updated_at' => $data['time_last_update_utc'] ?? now()->toISOString(),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('CurrencyService: failed to fetch rates', ['error' => $e->getMessage()]);
            }

            // Fallback static rates (approximate, updated periodically)
            return [
                'base'       => 'USD',
                'rates'      => $this->fallbackRates(),
                'updated_at' => 'Fallback rates',
            ];
        });
    }

    public function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) return $amount;

        $rates = $this->getRates('USD');
        $rateMap = $rates['rates'];

        // Convert to USD first, then to target
        $inUsd = $from === 'USD' ? $amount : $amount / ($rateMap[$from] ?? 1);
        return $inUsd * ($rateMap[$to] ?? 1);
    }

    public function getSymbol(string $currency): string
    {
        return self::$SUPPORTED[$currency]['symbol'] ?? $currency;
    }

    public function format(float $amount, string $currency): string
    {
        $symbol = $this->getSymbol($currency);
        $decimals = in_array($currency, ['JPY', 'IDR', 'KES', 'NGN']) ? 0 : 2;
        return $symbol . number_format($amount, $decimals);
    }

    private function fallbackRates(): array
    {
        return [
            'USD' => 1.0,    'EUR' => 0.92,  'GBP' => 0.79,  'ZAR' => 18.5,
            'AED' => 3.67,   'JPY' => 149.5, 'AUD' => 1.53,  'CAD' => 1.36,
            'CHF' => 0.89,   'CNY' => 7.24,  'INR' => 83.1,  'BRL' => 4.97,
            'MXN' => 17.2,   'SGD' => 1.34,  'THB' => 35.1,  'KES' => 129.0,
            'NGN' => 1550.0, 'EGP' => 30.9,  'IDR' => 15600, 'MYR' => 4.72,
            'NZD' => 1.63,   'SEK' => 10.4,  'NOK' => 10.6,  'DKK' => 6.89,
            'TRY' => 32.1,
        ];
    }
}

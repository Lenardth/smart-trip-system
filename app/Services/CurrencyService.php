<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Contracts\CurrencyServiceInterface;

class CurrencyService implements CurrencyServiceInterface
{
    private const BASE_URL  = 'https://open.er-api.com/v6/latest';
    private const CACHE_TTL = 3600;

    public static array $SUPPORTED = [
        'USD' => ['name' => 'US Dollar',         'symbol' => '$'],
        'EUR' => ['name' => 'Euro',               'symbol' => '€'],
        'GBP' => ['name' => 'British Pound',      'symbol' => '£'],
        'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R'],
        'AED' => ['name' => 'UAE Dirham',         'symbol' => 'د.إ'],
        'JPY' => ['name' => 'Japanese Yen',       'symbol' => '¥'],
        'AUD' => ['name' => 'Australian Dollar',  'symbol' => 'A$'],
        'CAD' => ['name' => 'Canadian Dollar',    'symbol' => 'C$'],
        'CHF' => ['name' => 'Swiss Franc',        'symbol' => 'Fr'],
        'CNY' => ['name' => 'Chinese Yuan',       'symbol' => '¥'],
        'INR' => ['name' => 'Indian Rupee',       'symbol' => '₹'],
        'BRL' => ['name' => 'Brazilian Real',     'symbol' => 'R$'],
        'MXN' => ['name' => 'Mexican Peso',       'symbol' => '$'],
        'SGD' => ['name' => 'Singapore Dollar',   'symbol' => 'S$'],
        'THB' => ['name' => 'Thai Baht',          'symbol' => '฿'],
        'KES' => ['name' => 'Kenyan Shilling',    'symbol' => 'KSh'],
        'NGN' => ['name' => 'Nigerian Naira',     'symbol' => '₦'],
        'EGP' => ['name' => 'Egyptian Pound',     'symbol' => 'E£'],
        'IDR' => ['name' => 'Indonesian Rupiah',  'symbol' => 'Rp'],
        'MYR' => ['name' => 'Malaysian Ringgit',  'symbol' => 'RM'],
        'NZD' => ['name' => 'New Zealand Dollar', 'symbol' => 'NZ$'],
        'SEK' => ['name' => 'Swedish Krona',      'symbol' => 'kr'],
        'NOK' => ['name' => 'Norwegian Krone',    'symbol' => 'kr'],
        'DKK' => ['name' => 'Danish Krone',       'symbol' => 'kr'],
        'TRY' => ['name' => 'Turkish Lira',       'symbol' => '₺'],
        'HUF' => ['name' => 'Hungarian Forint',   'symbol' => 'Ft'],
        'PLN' => ['name' => 'Polish Zloty',       'symbol' => 'zł'],
        'CZK' => ['name' => 'Czech Koruna',       'symbol' => 'Kč'],
    ];

    public function getRates(string $base = 'USD'): array
    {
        return Cache::remember("exchange_rates_{$base}", self::CACHE_TTL, function () use ($base) {
            try {
                $response = Http::timeout(10)->get(self::BASE_URL . "/{$base}");
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

            return ['base' => 'USD', 'rates' => $this->fallbackRates(), 'updated_at' => 'Fallback rates'];
        });
    }

    public function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) return $amount;
        $rates  = $this->getRates('USD')['rates'];
        $inUsd  = $from === 'USD' ? $amount : $amount / ($rates[$from] ?? 1);
        return $inUsd * ($rates[$to] ?? 1);
    }

    public function getSymbol(string $currency): string
    {
        return self::$SUPPORTED[$currency]['symbol'] ?? $currency;
    }

    public function format(float $amount, string $currency): string
    {
        $decimals = in_array($currency, ['JPY', 'IDR', 'KES', 'NGN']) ? 0 : 2;
        return $this->getSymbol($currency) . number_format($amount, $decimals);
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
            'TRY' => 32.1,   'HUF' => 356.0, 'PLN' => 4.02,  'CZK' => 23.1,
        ];
    }
}

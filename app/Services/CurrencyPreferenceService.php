<?php

namespace App\Services;

use App\Contracts\CurrencyServiceInterface;

class CurrencyPreferenceService
{
    public function __construct(private readonly CurrencyServiceInterface $currency) {}

    public function rates(string $base): array
    {
        $base = strtoupper($base);
        $rates = $this->currency->getRates($base);

        return [
            'success'    => true,
            'base'       => $rates['base'],
            'rates'      => $rates['rates'],
            'updated_at' => $rates['updated_at'],
            'currencies' => $this->currency->getSupportedCurrencies(),
        ];
    }

    public function setCurrency(string $currency): array
    {
        $currency = strtoupper($currency);

        if (!$this->currency->isSupported($currency)) {
            return ['success' => false, 'status' => 422, 'message' => 'Unsupported currency.'];
        }

        session(['preferred_currency' => $currency]);
        $supportedCurrencies = $this->currency->getSupportedCurrencies();

        return [
            'success'  => true,
            'currency' => $currency,
            'symbol'   => $this->currency->getSymbol($currency),
            'name'     => $supportedCurrencies[$currency]['name'] ?? $currency,
        ];
    }

    public function convert(float $amount, string $from, string $to): array
    {
        $from = strtoupper($from);
        $to = strtoupper($to);
        $converted = $this->currency->convert($amount, $from, $to);

        return [
            'success'   => true,
            'amount'    => $converted,
            'formatted' => $this->currency->format($converted, $to),
            'symbol'    => $this->currency->getSymbol($to),
            'currency'  => $to,
        ];
    }
}

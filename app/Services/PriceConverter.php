<?php

namespace App\Services;

class PriceConverter
{
    public function __construct(private CurrencyService $currency) {}

    /**
     * Convert a USD price to the user's preferred currency
     */
    public function convert(float $amountUsd): float
    {
        $targetCurrency = session('preferred_currency', 'USD');
        
        if ($targetCurrency === 'USD') {
            return $amountUsd;
        }

        return $this->currency->convert($amountUsd, 'USD', $targetCurrency);
    }

    /**
     * Format a USD price in the user's preferred currency
     */
    public function format(float $amountUsd): string
    {
        $targetCurrency = session('preferred_currency', 'USD');
        $converted = $this->convert($amountUsd);
        
        return $this->currency->format($converted, $targetCurrency);
    }

    /**
     * Get the user's preferred currency code
     */
    public function getPreferredCurrency(): string
    {
        return session('preferred_currency', 'USD');
    }

    /**
     * Get the symbol for the user's preferred currency
     */
    public function getSymbol(): string
    {
        $currency = $this->getPreferredCurrency();
        return $this->currency->getSymbol($currency);
    }

    /**
     * Convert price fields in an array or object
     */
    public function convertPriceFields(array|object $data, array $priceFields = ['price', 'price_from', 'cost', 'amount', 'budget']): array
    {
        $array = is_object($data) ? (array) $data : $data;
        
        foreach ($priceFields as $field) {
            if (isset($array[$field]) && is_numeric($array[$field])) {
                $array[$field] = $this->convert((float) $array[$field]);
            }
        }
        
        return $array;
    }
}

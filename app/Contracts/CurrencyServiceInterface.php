<?php

namespace App\Contracts;

interface CurrencyServiceInterface
{
    public function getRates(string $base = 'USD'): array;

    public function convert(float $amount, string $from, string $to): float;

    public function getSymbol(string $currency): string;

    public function format(float $amount, string $currency): string;

    public function getSupportedCurrencies(): array;

    public function isSupported(string $currency): bool;
}

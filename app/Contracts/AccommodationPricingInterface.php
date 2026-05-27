<?php

namespace App\Contracts;

interface AccommodationPricingInterface
{
    public function getPrice(string $city, string $style, string $budgetTier): array;
}

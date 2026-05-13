<?php

namespace App\Contracts;

interface AccommodationPricingInterface
{
    public function getPrice(string $city, string $style, string $budgetTier): array;

    public function estimateNightlyRate(string $style, string $budgetTier, int $rating = 0): float;
}

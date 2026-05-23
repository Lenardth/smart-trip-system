<?php

namespace App\Http\Concerns;

use App\Services\AccommodationNormalizationService;

trait NormalisesAccommodation
{
    /**
     * Normalise accommodation data from various sources
     */
    protected function normaliseAccommodation(?array $data): ?array
    {
        return $data ? AccommodationNormalizationService::normalize($data) : null;
    }

    /**
     * Extract style from categories
     */
    protected function extractStyleFromCategories(array $categories): string
    {
        return AccommodationNormalizationService::extractStyleFromCategories($categories);
    }

    /**
     * Determine budget tier from style and price
     */
    protected function determineBudgetTier(string $style, float $price): string
    {
        return AccommodationNormalizationService::determineBudgetTier($style, $price);
    }
}
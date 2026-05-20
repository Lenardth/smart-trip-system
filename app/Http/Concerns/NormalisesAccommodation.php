<?php

namespace App\Http\Concerns;

trait NormalisesAccommodation
{
    /**
     * Normalise accommodation data from various sources
     */
    protected function normaliseAccommodation(array $data): array
    {
        return [
            'name'         => $data['name'] ?? 'Unknown Accommodation',
            'city'         => $data['city'] ?? $data['location'] ?? 'Unknown',
            'country'      => $data['country'] ?? '',
            'style'        => $data['style'] ?? $data['type'] ?? 'hotel',
            'budget_tier'  => $data['budget_tier'] ?? $data['price_tier'] ?? 'mid',
            'nightly_rate' => (float) ($data['nightly_rate'] ?? $data['price'] ?? 0),
            'rating'       => (float) ($data['rating'] ?? $data['stars'] ?? 0),
            'review_count' => (int) ($data['review_count'] ?? $data['reviews'] ?? 0),
            'lat'          => isset($data['lat']) ? (float) $data['lat'] : null,
            'lng'          => isset($data['lng']) ? (float) $data['lng'] : null,
            'amenities'    => $data['amenities'] ?? $data['features'] ?? [],
            'description'  => $data['description'] ?? '',
            'image_url'    => $data['image_url'] ?? $data['image'] ?? '',
            'booking_url'  => $data['booking_url'] ?? $data['url'] ?? '',
        ];
    }

    /**
     * Extract style from categories
     */
    protected function extractStyleFromCategories(array $categories): string
    {
        $styleMap = [
            'hotel' => ['hotel', 'motel', 'inn', 'lodge'],
            'apartment' => ['apartment', 'flat', 'condo', 'suite'],
            'hostel' => ['hostel', 'backpacker'],
            'villa' => ['villa', 'house', 'cottage', 'cabin'],
            'resort' => ['resort', 'spa', 'wellness'],
            'camping' => ['camping', 'glamping', 'tent'],
        ];

        foreach ($categories as $category) {
            $categoryLower = strtolower($category);
            foreach ($styleMap as $style => $keywords) {
                foreach ($keywords as $keyword) {
                    if (str_contains($categoryLower, $keyword)) {
                        return $style;
                    }
                }
            }
        }

        return 'hotel';
    }

    /**
     * Determine budget tier from style and price
     */
    protected function determineBudgetTier(string $style, float $price): string
    {
        $tiers = [
            'budget' => ['hostel', 'camping'],
            'mid' => ['hotel', 'apartment'],
            'luxury' => ['villa', 'resort'],
        ];

        // First try style-based tier
        foreach ($tiers as $tier => $styles) {
            if (in_array($style, $styles)) {
                return $tier;
            }
        }

        // Fallback to price-based tier
        if ($price < 50) return 'budget';
        if ($price < 150) return 'mid';
        return 'luxury';
    }
}
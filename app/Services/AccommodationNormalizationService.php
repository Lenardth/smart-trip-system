<?php

namespace App\Services;

class AccommodationNormalizationService
{
    private const STYLE_KEYWORDS = [
        'hotel' => ['hotel', 'motel', 'inn', 'lodge'],
        'apartment' => ['apartment', 'flat', 'condo', 'suite'],
        'hostel' => ['hostel', 'backpacker'],
        'villa' => ['villa', 'house', 'cottage', 'cabin'],
        'resort' => ['resort', 'spa', 'wellness'],
        'camping' => ['camping', 'glamping', 'tent'],
    ];

    private const BUDGET_TIER_MAP = [
        'budget' => ['hostel', 'camping'],
        'mid' => ['hotel', 'apartment'],
        'luxury' => ['villa', 'resort'],
    ];

    public static function normalize(?array $data): array
    {
        if (!$data) {
            $data = [];
        }

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

    public static function extractStyleFromCategories(array $categories): string
    {
        foreach ($categories as $category) {
            $categoryLower = strtolower($category);
            foreach (self::STYLE_KEYWORDS as $style => $keywords) {
                foreach ($keywords as $keyword) {
                    if (str_contains($categoryLower, $keyword)) {
                        return $style;
                    }
                }
            }
        }

        return 'hotel';
    }

    public static function determineBudgetTier(string $style, float $price): string
    {
        foreach (self::BUDGET_TIER_MAP as $tier => $styles) {
            if (in_array($style, $styles)) {
                return $tier;
            }
        }

        if ($price < 50) {
            return 'budget';
        }
        if ($price < 150) {
            return 'mid';
        }
        return 'luxury';
    }
}

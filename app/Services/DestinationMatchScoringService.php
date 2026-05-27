<?php

namespace App\Services;

use Illuminate\Support\Str;

class DestinationMatchScoringService
{
    public const WEIGHTS = [
        'mood' => 0.4,
        'budget' => 0.3,
        'companion' => 0.2,
        'rating' => 0.1,
    ];

    public function rank(array $destinations, array $preferences): array
    {
        if (! $this->hasPreferences($preferences)) {
            return $destinations;
        }

        return collect($destinations)
            ->map(function (array $destination) use ($preferences) {
                $destination['match_score'] = $this->score($destination, $preferences);

                return $destination;
            })
            ->sortByDesc('match_score')
            ->values()
            ->all();
    }

    public function score(array $destination, array $preferences): int
    {
        $score = (self::WEIGHTS['mood'] * $this->moodScore($destination, $preferences['mood'] ?? null))
            + (self::WEIGHTS['budget'] * $this->budgetScore($destination, $preferences['budget'] ?? null))
            + (self::WEIGHTS['companion'] * $this->companionScore($destination, $preferences['companion'] ?? null))
            + (self::WEIGHTS['rating'] * $this->ratingScore($destination));

        return (int) round($score * 100);
    }

    public function hasPreferences(array $preferences): bool
    {
        return filled($preferences['mood'] ?? null)
            || filled($preferences['budget'] ?? null)
            || filled($preferences['companion'] ?? null);
    }

    private function moodScore(array $destination, ?string $mood): float
    {
        if (! $mood) {
            return 1.0;
        }

        $needle = $this->normalise($mood);
        $haystack = collect([
            $destination['mood'] ?? null,
            $destination['category'] ?? null,
            ...($destination['tags'] ?? []),
        ])
            ->filter()
            ->map(fn (string $value) => $this->normalise($value));

        if ($haystack->contains($needle)) {
            return 1.0;
        }

        return $haystack->contains(fn (string $value) => str_contains($value, $needle) || str_contains($needle, $value))
            ? 0.7
            : 0.35;
    }

    private function budgetScore(array $destination, ?string $budget): float
    {
        if (! $budget) {
            return 1.0;
        }

        if ($this->normalise($destination['budget'] ?? $destination['budget_tier'] ?? '') === $this->normalise($budget)) {
            return 1.0;
        }

        $price = $this->destinationPrice($destination);

        if ($price === null) {
            return 1.0;
        }

        $ranges = [
            'backpacker' => [0, 500],
            'budget' => [500, 1500],
            'mid' => [1500, 4000],
            'premium' => [4000, 8000],
            'luxury' => [8000, INF],
        ];

        [$min, $max] = $ranges[$budget] ?? $ranges['mid'];

        if ($price >= $min && $price <= $max) {
            return 1.0;
        }

        $nearest = $price < $min ? $min : $max;
        $distance = abs($price - $nearest);
        $spread = is_finite($max) ? max(1, $max - $min) : max(1, $min);

        return max(0.25, 1 - ($distance / $spread));
    }

    private function companionScore(array $destination, ?string $companion): float
    {
        if (! $companion) {
            return 1.0;
        }

        if ($this->normalise($destination['companion'] ?? '') === $this->normalise($companion)) {
            return 1.0;
        }

        $tags = collect($destination['tags'] ?? [])
            ->map(fn (string $value) => $this->normalise($value))
            ->all();

        $mood = $this->normalise($destination['mood'] ?? '');
        $companionMatches = [
            'solo' => ['adventurous', 'cultural', 'nature', 'photography', 'wellness'],
            'couple' => ['romantic', 'relaxed', 'beach', 'foodie', 'wellness'],
            'family_young' => ['relaxed', 'beach', 'nature', 'cultural'],
            'family_teens' => ['adventurous', 'nature', 'cultural', 'beach'],
            'friends_small' => ['adventurous', 'foodie', 'nightlife', 'beach'],
            'friends_large' => ['nightlife', 'beach', 'adventurous', 'foodie'],
            'business' => ['cultural', 'city_break', 'foodie'],
        ];

        $matches = $companionMatches[$companion] ?? [];

        return collect([$mood, ...$tags])->intersect($matches)->isNotEmpty() ? 1.0 : 0.6;
    }

    private function ratingScore(array $destination): float
    {
        if (isset($destination['rating'])) {
            return min(1.0, max(0.0, ((float) $destination['rating']) / 5));
        }

        if (! empty($destination['is_editors_choice'])) {
            return 1.0;
        }

        return ! empty($destination['is_featured']) ? 0.85 : 0.65;
    }

    private function destinationPrice(array $destination): ?float
    {
        if (! empty($destination['price_from'])) {
            return (float) $destination['price_from'];
        }

        if (! empty($destination['cost_min_usd']) && ! empty($destination['cost_max_usd'])) {
            return (((float) $destination['cost_min_usd']) + ((float) $destination['cost_max_usd'])) / 2;
        }

        return null;
    }

    private function normalise(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->replace(['-', ' '], '_')
            ->squish()
            ->toString();
    }
}

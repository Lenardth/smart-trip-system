<?php

namespace App\Services;

class TripLabelService
{
    private static array $defaultDurationLabels = [
        'weekend'   => 'Long Weekend',
        'week'      => 'One Week',
        'two_weeks' => 'Two Weeks',
        'month'     => 'One Month+',
        'flexible'  => 'Flexible',
    ];

    private static array $defaultBudgetLabels = [
        'backpacker' => 'Backpacker',
        'budget'     => 'Budget',
        'mid'        => 'Mid-Range',
        'premium'    => 'Premium',
        'luxury'     => 'Luxury',
    ];

    public static function getDurationLabel(?string $duration): string
    {
        $configLabels = [];
        if (function_exists('config')) {
            try {
                $configLabels = config('trips.duration_labels', []);
                if (!is_array($configLabels)) $configLabels = [];
            } catch (\Throwable) {
                $configLabels = [];
            }
        }

        $labels = array_merge(self::$defaultDurationLabels, $configLabels ?: []);

        if (!$duration) {
            return '-';
        }

        return $labels[$duration] ?? $duration;
    }

    public static function getBudgetLabel(?string $budget): string
    {
        $configLabels = [];
        if (function_exists('config')) {
            try {
                $configLabels = config('trips.budget_labels', []);
                if (!is_array($configLabels)) $configLabels = [];
            } catch (\Throwable) {
                $configLabels = [];
            }
        }

        $labels = array_merge(self::$defaultBudgetLabels, $configLabels ?: []);

        if (!$budget) {
            return '-';
        }

        return $labels[$budget] ?? $budget;
    }
}

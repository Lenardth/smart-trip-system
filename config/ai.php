<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Suggestion Retries
    |--------------------------------------------------------------------------
    */
    'max_retries' => 3,

    /*
    |--------------------------------------------------------------------------
    | Cost Validation
    |--------------------------------------------------------------------------
    */
    'cost_validation' => [
        'deviation_threshold' => 0.5,        // 50% tolerance
        'min_range_spread'    => 200,        // Minimum USD difference between min/max
        'default_range_multiplier' => 1.5,   // Factor for default max vs min
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Daily Rates (USD)
    |--------------------------------------------------------------------------
    | Used when pricing data unavailable
    */
    'default_daily_rates' => [
        'accommodation' => 75,
        'food'          => 75,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Calculation Weights
    |--------------------------------------------------------------------------
    | How much to weight AI suggestions vs internal calculations
    */
    'cost_weights' => [
        'ai'       => 0.6,
        'internal' => 0.4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Best Months (when unavailable)
    |--------------------------------------------------------------------------
    */
    'default_best_months' => [
        'warm'   => ['November', 'December', 'January', 'February'],
        'cool'   => ['June', 'July', 'August', 'September'],
        'spring' => ['April', 'May', 'September', 'October'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Temperature Thresholds (Celsius)
    |--------------------------------------------------------------------------
    */
    'temp_thresholds' => [
        'warm' => 25,
        'cool' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Prompt Configuration
    |--------------------------------------------------------------------------
    */
    'prompt' => [
        'destination_count' => 5,
        'max_activities'    => 6,
    ],
];

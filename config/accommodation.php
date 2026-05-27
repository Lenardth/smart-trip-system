<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nightly base rates by accommodation style (USD)
    |--------------------------------------------------------------------------
    */
    'base_rates' => [
        'hostel'       => 25,
        'guest_house'  => 60,
        'motel'        => 70,
        'apartment'    => 110,
        'budget_hotel' => 85,
        'hotel'        => 140,
        'boutique'     => 180,
        'resort'       => 350,
        'villa'        => 280,
        'airbnb'       => 95,
        'glamping'     => 120,
        'default'      => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Budget-tier price multipliers
    |--------------------------------------------------------------------------
    */
    'tier_multipliers' => [
        'backpacker' => 0.6,
        'budget'     => 0.8,
        'mid'        => 1.0,
        'premium'    => 1.5,
        'luxury'     => 2.5,
        'default'    => 1.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | City-tier price multipliers
    |--------------------------------------------------------------------------
    | Cities not listed here receive a 1.0 multiplier (no adjustment).
    */
    'city_multipliers' => [

        'expensive' => [
            'multiplier' => 2.0,
            'cities'     => [
                'london', 'paris', 'new york', 'tokyo', 'singapore', 'hong kong',
                'zurich', 'geneva', 'oslo', 'copenhagen', 'reykjavik', 'dubai',
                'sydney', 'melbourne', 'san francisco', 'los angeles', 'miami',
            ],
        ],

        'moderate' => [
            'multiplier' => 1.3,
            'cities'     => [
                'amsterdam', 'barcelona', 'rome', 'madrid', 'berlin', 'vienna',
                'prague', 'lisbon', 'dublin', 'edinburgh', 'brussels', 'milan',
                'munich', 'stockholm', 'helsinki', 'athens', 'istanbul', 'bangkok',
                'kuala lumpur', 'seoul', 'taipei', 'shanghai', 'beijing',
            ],
        ],

        'budget' => [
            'multiplier' => 0.7,
            'cities'     => [
                'budapest', 'warsaw', 'bucharest', 'sofia', 'belgrade', 'zagreb',
                'hanoi', 'ho chi minh', 'phnom penh', 'vientiane', 'yangon',
                'kathmandu', 'delhi', 'mumbai', 'bangalore', 'colombo', 'dhaka',
                'cairo', 'nairobi', 'dar es salaam', 'kampala', 'addis ababa',
                'casablanca', 'marrakech', 'tunis', 'algiers', 'johannesburg',
                'cape town', 'durban', 'lima', 'bogota', 'quito', 'la paz',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Randomised price variance percentage (±N%)
    |--------------------------------------------------------------------------
    | Applied to estimated prices to make results feel realistic.
    | Set to 0 to disable variance.
    */
    'price_variance_pct' => 15,

    /*
    |--------------------------------------------------------------------------
    | Cache TTLs (seconds)
    |--------------------------------------------------------------------------
    */
    'cache_ttl' => [
        'estimated_price' => 3600,    // 1 hour  — estimates
    ],

];

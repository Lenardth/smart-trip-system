<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Accommodation Normalisation
    |--------------------------------------------------------------------------
    | Maps legacy / shorthand form values to canonical stored values.
    */
    'accommodation_aliases' => [
        'hotel' => 'budget_hotel',
        'bnb'   => 'boutique',
    ],

    'accommodation_labels' => [
        'budget_hotel' => 'Budget Hotel',
        'boutique'     => 'Boutique / B&B',
        'hostel'       => 'Hostel',
        'resort'       => 'Resort',
        'villa'        => 'Villa / Private Rental',
        'apartment'    => 'Apartment',
        'guest_house'  => 'Guest House',
        'motel'        => 'Motel',
    ],

    /*
    |--------------------------------------------------------------------------
    | Duration → Days
    |--------------------------------------------------------------------------
    */
    'duration_days' => [
        'weekend'   => 4,
        'week'      => 7,
        'two_weeks' => 14,
        'month'     => 30,
    ],

    'default_duration_days' => 7,

    /*
    |--------------------------------------------------------------------------
    | Human-Readable Labels (used by Trip model accessors and views)
    |--------------------------------------------------------------------------
    */
    'duration_labels' => [
        'weekend'   => 'Long Weekend',
        'week'      => 'One Week',
        'two_weeks' => 'Two Weeks',
        'month'     => 'One Month+',
        'flexible'  => 'Flexible',
    ],

    'budget_labels' => [
        'backpacker' => 'Backpacker',
        'budget'     => 'Budget',
        'mid'        => 'Mid-Range',
        'premium'    => 'Premium',
        'luxury'     => 'Luxury',
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Prompt Labels
    |--------------------------------------------------------------------------
    | Used when building natural-language prompts for the AI suggestion engine.
    | Budget labels use a %s placeholder for the currency code.
    */
    'budget_prompt_labels' => [
        'backpacker' => 'backpacker (under 500 %s total)',
        'budget'     => 'budget-friendly (500–1,500 %s)',
        'mid'        => 'mid-range (1,500–4,000 %s)',
        'premium'    => 'premium (4,000–8,000 %s)',
        'luxury'     => 'luxury (8,000+ %s)',
    ],

    'companion_prompt_labels' => [
        'solo'          => 'solo traveller',
        'couple'        => 'couple',
        'family_young'  => 'family with young children',
        'family_teens'  => 'family with teenagers',
        'friends_small' => 'small group of friends (2–4)',
        'friends_large' => 'large group of friends (5+)',
        'business'      => 'business traveller',
    ],

    'duration_prompt_labels' => [
        'weekend'   => 'a long weekend (3–4 days)',
        'week'      => 'one week (7 days)',
        'two_weeks' => 'two weeks (10–14 days)',
        'month'     => 'one month or longer',
        'flexible'  => 'a flexible open-ended trip',
    ],

];

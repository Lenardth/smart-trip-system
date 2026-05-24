<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Travel-class price multipliers
    |--------------------------------------------------------------------------
    | Applied on top of the economy base price.
    */
    'class_multipliers' => [
        'ECONOMY'         => 1.0,
        'PREMIUM_ECONOMY' => 1.8,
        'BUSINESS'        => 3.5,
        'FIRST'           => 6.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Base rate per flight-minute (USD) by route type
    |--------------------------------------------------------------------------
    */
    'base_rates' => [
        'hub_to_hub'  => 0.10,   // Both endpoints are major hubs
        'one_hub'     => 0.12,   // One endpoint is a major hub
        'regional'    => 0.15,   // Neither endpoint is a major hub
    ],

    /*
    |--------------------------------------------------------------------------
    | Regional price adjustment multipliers
    |--------------------------------------------------------------------------
    */
    'regional_adjustments' => [
        'africa'       => 1.15,  // Less competition → higher fares
        'middle_east'  => 0.90,  // Competitive regional market
    ],

    /*
    |--------------------------------------------------------------------------
    | Minimum estimated price (USD)
    |--------------------------------------------------------------------------
    */
    'min_price' => 49,

    /*
    |--------------------------------------------------------------------------
    | Default flight duration when none can be parsed (minutes)
    |--------------------------------------------------------------------------
    */
    'default_duration_minutes' => 120,

    /*
    |--------------------------------------------------------------------------
    | AeroDataBox request delay between 12-hour window calls (milliseconds)
    |--------------------------------------------------------------------------
    */
    'request_delay_ms' => 500,

    /*
    |--------------------------------------------------------------------------
    | Flight Search Validation
    |--------------------------------------------------------------------------
    */
    'min_departure_days_ahead' => 2,
    'max_adults'               => 9,

    /*
    |--------------------------------------------------------------------------
    | API Timeouts (seconds)
    |--------------------------------------------------------------------------
    */
    'api_timeouts' => [
        'search'  => 30,
        'airports' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTLs (seconds)
    |--------------------------------------------------------------------------
    */
    'cache_ttl' => [
        'api_price'       => 3600,   // 1 hour — live prices
        'estimated_price' => 1800,   // 30 min — estimates
    ],

    /*
    |--------------------------------------------------------------------------
    | Major hub IATA codes
    |--------------------------------------------------------------------------
    | Used to determine base pricing rate (hub-to-hub routes are cheaper).
    */
    'major_hubs' => ['JFK', 'LAX', 'LHR', 'CDG', 'DXB', 'SIN', 'HKG', 'NRT', 'FRA', 'AMS'],

    /*
    |--------------------------------------------------------------------------
    | African airport IATA codes (regional surcharge applies)
    |--------------------------------------------------------------------------
    */
    'african_airports' => ['JNB', 'CPT', 'NBO', 'CAI', 'LOS', 'ACC', 'ADD', 'DAR'],

    /*
    |--------------------------------------------------------------------------
    | Middle-East airport IATA codes (competitive discount applies)
    |--------------------------------------------------------------------------
    */
    'middle_east_airports' => ['DXB', 'AUH', 'DOH', 'RUH', 'JED'],

];

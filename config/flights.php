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

    /*
    |--------------------------------------------------------------------------
    | Popular route deals shown on the flights landing page
    |--------------------------------------------------------------------------
    */
    'popular_deals' => [
        ['from' => 'JNB', 'to' => 'CPT', 'duration' => '2h 00m', 'airline' => 'FlySafair',    'tag' => 'Domestic Deal',    'icon' => 'fa-plane'],
        ['from' => 'DXB', 'to' => 'BKK', 'duration' => '6h 30m', 'airline' => 'Emirates',     'tag' => 'Popular Route',    'icon' => 'fa-star'],
        ['from' => 'LHR', 'to' => 'LIS', 'duration' => '2h 30m', 'airline' => 'TAP Air',      'tag' => 'Hot Deal',         'icon' => 'fa-fire'],
        ['from' => 'JFK', 'to' => 'CUN', 'duration' => '4h 15m', 'airline' => 'JetBlue',      'tag' => 'Beach Escape',     'icon' => 'fa-umbrella-beach'],
        ['from' => 'SIN', 'to' => 'DPS', 'duration' => '2h 30m', 'airline' => 'Scoot',        'tag' => 'Weekend Getaway',  'icon' => 'fa-leaf'],
        ['from' => 'CDG', 'to' => 'BCN', 'duration' => '1h 55m', 'airline' => 'Vueling',      'tag' => 'Flash Sale',       'icon' => 'fa-bolt'],
        ['from' => 'SYD', 'to' => 'MEL', 'duration' => '1h 25m', 'airline' => 'Jetstar',      'tag' => 'Domestic Deal',    'icon' => 'fa-plane'],
        ['from' => 'NBO', 'to' => 'ZNZ', 'duration' => '1h 45m', 'airline' => 'Kenya Airways','tag' => 'Island Escape',    'icon' => 'fa-sun'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Popular routes shown on the flights landing page
    |--------------------------------------------------------------------------
    */
    'popular_routes' => [
        ['from' => 'JFK', 'to' => 'LHR', 'duration' => '7h 30m',  'direct' => true],
        ['from' => 'CDG', 'to' => 'NRT', 'duration' => '12h 45m', 'direct' => false],
        ['from' => 'DXB', 'to' => 'JFK', 'duration' => '14h 20m', 'direct' => true],
        ['from' => 'LAX', 'to' => 'SYD', 'duration' => '15h 10m', 'direct' => true],
        ['from' => 'SIN', 'to' => 'DPS', 'duration' => '2h 30m',  'direct' => true],
        ['from' => 'LHR', 'to' => 'DXB', 'duration' => '7h 00m',  'direct' => true],
    ],

];

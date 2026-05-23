<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Exchange-rate API
    |--------------------------------------------------------------------------
    */
    'api_url'   => env('EXCHANGE_RATE_API_URL', 'https://open.er-api.com/v6/latest'),
    'cache_ttl' => 3600, // 1 hour

    /*
    |--------------------------------------------------------------------------
    | Supported currencies
    |--------------------------------------------------------------------------
    | Each entry: 'CODE' => ['name' => '...', 'symbol' => '...']
    */
    'supported' => [
        'USD' => ['name' => 'US Dollar',           'symbol' => '$'],
        'EUR' => ['name' => 'Euro',                 'symbol' => '€'],
        'GBP' => ['name' => 'British Pound',        'symbol' => '£'],
        'ZAR' => ['name' => 'South African Rand',   'symbol' => 'R'],
        'AED' => ['name' => 'UAE Dirham',           'symbol' => 'د.إ'],
        'JPY' => ['name' => 'Japanese Yen',         'symbol' => '¥'],
        'AUD' => ['name' => 'Australian Dollar',    'symbol' => 'A$'],
        'CAD' => ['name' => 'Canadian Dollar',      'symbol' => 'C$'],
        'CHF' => ['name' => 'Swiss Franc',          'symbol' => 'Fr'],
        'CNY' => ['name' => 'Chinese Yuan',         'symbol' => '¥'],
        'INR' => ['name' => 'Indian Rupee',         'symbol' => '₹'],
        'BRL' => ['name' => 'Brazilian Real',       'symbol' => 'R$'],
        'MXN' => ['name' => 'Mexican Peso',         'symbol' => '$'],
        'SGD' => ['name' => 'Singapore Dollar',     'symbol' => 'S$'],
        'THB' => ['name' => 'Thai Baht',            'symbol' => '฿'],
        'KES' => ['name' => 'Kenyan Shilling',      'symbol' => 'KSh'],
        'NGN' => ['name' => 'Nigerian Naira',       'symbol' => '₦'],
        'EGP' => ['name' => 'Egyptian Pound',       'symbol' => 'E£'],
        'IDR' => ['name' => 'Indonesian Rupiah',    'symbol' => 'Rp'],
        'MYR' => ['name' => 'Malaysian Ringgit',    'symbol' => 'RM'],
        'NZD' => ['name' => 'New Zealand Dollar',   'symbol' => 'NZ$'],
        'SEK' => ['name' => 'Swedish Krona',        'symbol' => 'kr'],
        'NOK' => ['name' => 'Norwegian Krone',      'symbol' => 'kr'],
        'DKK' => ['name' => 'Danish Krone',         'symbol' => 'kr'],
        'TRY' => ['name' => 'Turkish Lira',         'symbol' => '₺'],
        'HUF' => ['name' => 'Hungarian Forint',     'symbol' => 'Ft'],
        'PLN' => ['name' => 'Polish Zloty',         'symbol' => 'zł'],
        'CZK' => ['name' => 'Czech Koruna',         'symbol' => 'Kč'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Currencies that display with zero decimal places
    |--------------------------------------------------------------------------
    */
    'zero_decimal' => ['JPY', 'IDR', 'KES', 'NGN'],

    /*
    |--------------------------------------------------------------------------
    | Fallback rates used when the exchange-rate API is unavailable
    |--------------------------------------------------------------------------
    | Base: USD. Update periodically or fetch fresh values in production.
    */
    'fallback_rates' => [
        'USD' => 1.0,      'EUR' => 0.92,   'GBP' => 0.79,   'ZAR' => 18.5,
        'AED' => 3.67,     'JPY' => 149.5,  'AUD' => 1.53,   'CAD' => 1.36,
        'CHF' => 0.89,     'CNY' => 7.24,   'INR' => 83.1,   'BRL' => 4.97,
        'MXN' => 17.2,     'SGD' => 1.34,   'THB' => 35.1,   'KES' => 129.0,
        'NGN' => 1550.0,   'EGP' => 30.9,   'IDR' => 15600,  'MYR' => 4.72,
        'NZD' => 1.63,     'SEK' => 10.4,   'NOK' => 10.6,   'DKK' => 6.89,
        'TRY' => 32.1,     'HUF' => 356.0,  'PLN' => 4.02,   'CZK' => 23.1,
    ],

];

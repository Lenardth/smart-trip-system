<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pricing Configuration
    |--------------------------------------------------------------------------
    |
    | Configure pricing, fees, and commission rates for the booking system.
    |
    */

    'service_fee_rate' => env('SERVICE_FEE_RATE', 0.05), // 5%
    
    'agency_commission' => env('AGENCY_COMMISSION', 0.10), // 10%
    
    'tax_rate' => env('TAX_RATE', 0.05), // 5% VAT/GST
    
    'currency' => [
        'default' => env('DEFAULT_CURRENCY', 'USD'),
        'symbol' => env('DEFAULT_CURRENCY_SYMBOL', '$'),
    ],

];

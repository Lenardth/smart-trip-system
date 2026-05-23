<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Service fee applied to non-premium users
    |--------------------------------------------------------------------------
    | Expressed as a decimal fraction (0.05 = 5%).
    */
    'service_fee_rate' => env('PRICING_SERVICE_FEE_RATE', 0.05),

    /*
    |--------------------------------------------------------------------------
    | Agency commission deducted from booking subtotal
    |--------------------------------------------------------------------------
    | Expressed as a decimal fraction (0.10 = 10%).
    */
    'agency_commission' => env('PRICING_AGENCY_COMMISSION', 0.10),

];

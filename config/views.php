<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hero Image Composer Views
    |--------------------------------------------------------------------------
    | Views that automatically receive a $heroImage variable injected by
    | App\View\Composers\HeroImageComposer via AppServiceProvider::boot().
    */
    'hero_image_views' => [
        'discover.index',
        'plan-trip.index',
        'flights.index',
        'accommodations.index',
        'bookings.index',
        'dashboard.index',
        'landing.index',
    ],

];

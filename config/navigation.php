<?php

return [

    'dashboard_sidebar' => [
        [
            'label' => 'Travel',
            'items' => [
                ['href' => '/', 'icon' => 'fa-home', 'label' => 'Home'],
                ['route' => 'dashboard', 'icon' => 'fa-tachometer-alt', 'label' => 'Dashboard', 'dashboard_tab' => 'overview'],
                ['route' => 'discover', 'icon' => 'fa-compass', 'label' => 'Discover'],
                ['route' => 'plan-trip', 'icon' => 'fa-route', 'label' => 'Plan Trip'],
                ['route' => 'flights.index', 'icon' => 'fa-plane', 'label' => 'Flights'],
                ['route' => 'accommodations.index', 'icon' => 'fa-hotel', 'label' => 'Stays'],
                ['route' => 'bookings.index', 'icon' => 'fa-ticket-alt', 'label' => 'Bookings', 'badge' => 'bookingsCount'],
            ],
        ],
        [
            'label' => 'Agency',
            'items' => [
                ['route' => 'agency.flights.index', 'icon' => 'fa-plane-departure', 'label' => 'Flight Listings', 'user_type' => 'agency'],
                ['route' => 'agency.bookings.index', 'icon' => 'fa-inbox', 'label' => 'Incoming Bookings', 'user_type' => 'agency'],
            ],
        ],
        [
            'label' => 'Account',
            'items' => [
                ['route' => 'dashboard', 'params' => ['tab' => 'settings'], 'icon' => 'fa-cog', 'label' => 'Settings', 'dashboard_tab' => 'settings'],
            ],
        ],
    ],

];

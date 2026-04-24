<?php

return [

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'geoapify' => [
        'key' => env('GEOAPIFY_KEY'),
    ],

    'unsplash' => [
        'access_key' => env('UNSPLASH_ACCESS_KEY'),
    ],

    'pexels' => [
        'api_key' => env('PEXELS_API_KEY'),
    ],

    'gnews' => [
        'key' => env('GNEWS_API_KEY'),
    ],

    'newsapi' => [
        'key' => env('NEWSAPI_KEY'),
    ],

    'aviationstack' => [
        'key' => env('AVIATIONSTACK_KEY'),
    ],

    'groq' => [
        'key' => env('GROQ_API_KEY'),
    ],

    'skyscanner' => [
        'key' => env('SKYSCANNER_RAPIDAPI_KEY'),
    ],

    'booking' => [
        'key' => env('BOOKING_RAPIDAPI_KEY'),
    ],

];

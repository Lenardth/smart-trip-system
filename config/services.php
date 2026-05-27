<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'groq' => [
        'api_key'    => env('GROQ_API_KEY'),
        'url'        => env('GROQ_URL', 'https://api.groq.com/openai/v1/chat/completions'),
        'model'      => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        'max_tokens' => env('GROQ_MAX_TOKENS', 2048),
    ],

    'aerodatabox' => [
        'key'      => env('AERODATABOX_KEY'),
        'host'     => env('AERODATABOX_HOST', 'aerodatabox.p.rapidapi.com'),
        'base_url' => env('AERODATABOX_BASE_URL', 'https://aerodatabox.p.rapidapi.com'),
    ],

    'geoapify' => [
        'key' => env('GEOAPIFY_KEY'),
    ],

    'gnews' => [
        'api_key' => env('GNEWS_API_KEY', env('GNEWS_KEY')),
        'search_endpoint' => env('GNEWS_SEARCH_ENDPOINT', 'https://gnews.io/api/v4/search'),
        'timeout' => env('GNEWS_TIMEOUT', 8),
    ],

    'newsapi' => [
        'key' => env('NEWSAPI_KEY', env('NEWS_API_KEY')),
        'everything_endpoint' => env('NEWSAPI_EVERYTHING_ENDPOINT', 'https://newsapi.org/v2/everything'),
        'timeout' => env('NEWSAPI_TIMEOUT', 8),
    ],

    'pexels' => [
        'api_key' => env('PEXELS_API_KEY'),
        'search_endpoint' => 'https://api.pexels.com/v1/search',
        'timeout' => env('PEXELS_TIMEOUT', 6),
    ],

];

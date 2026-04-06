<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class NetworkServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Force cURL to use IPv4 and system DNS
        if (function_exists('curl_version')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
            curl_close($ch);
        }
    }
}
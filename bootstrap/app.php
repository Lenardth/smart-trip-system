<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust specific proxies or configure based on environment
        $trustedProxies = env('TRUSTED_PROXIES', null);
        
        if ($trustedProxies === '*') {
            // Only trust all proxies in development
            $middleware->trustProxies(
                at: '*',
                headers: Request::HEADER_X_FORWARDED_FOR |
                         Request::HEADER_X_FORWARDED_HOST |
                         Request::HEADER_X_FORWARDED_PORT |
                         Request::HEADER_X_FORWARDED_PROTO |
                         Request::HEADER_X_FORWARDED_AWS_ELB
            );
        } elseif ($trustedProxies) {
            // Trust specific proxy IPs in production
            $middleware->trustProxies(
                at: explode(',', $trustedProxies),
                headers: Request::HEADER_X_FORWARDED_FOR |
                         Request::HEADER_X_FORWARDED_HOST |
                         Request::HEADER_X_FORWARDED_PORT |
                         Request::HEADER_X_FORWARDED_PROTO |
                         Request::HEADER_X_FORWARDED_AWS_ELB
            );
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
    })->create();

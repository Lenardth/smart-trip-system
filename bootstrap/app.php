<?php

declare(strict_types=1);

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
        $trustedProxies = env('TRUSTED_PROXIES', null);
        $forwardedHeaders = Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO |
            Request::HEADER_X_FORWARDED_AWS_ELB;

        if ($trustedProxies === '*') {
            $middleware->trustProxies(at: '*', headers: $forwardedHeaders);
        } elseif ($trustedProxies) {
            $middleware->trustProxies(
                at: array_map('trim', explode(',', $trustedProxies)),
                headers: $forwardedHeaders
            );
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();

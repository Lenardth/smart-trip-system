<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        channels: __DIR__.'/../routes/channels.php',
        then: function () {
            // Setup routes (for initial deployment only)
            if (file_exists(__DIR__.'/../routes/setup.php')) {
                Route::middleware('web')->group(__DIR__.'/../routes/setup.php');
            }
        },
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (important for Ngrok)
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
                     Request::HEADER_X_FORWARDED_HOST |
                     Request::HEADER_X_FORWARDED_PORT |
                     Request::HEADER_X_FORWARDED_PROTO |
                     Request::HEADER_X_FORWARDED_AWS_ELB
        );

        // Exclude setup routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            '/setup/*',
        ]);

        // Register custom middleware aliases
        $middleware->alias([
            'check.traveler' => \App\Http\Middleware\CheckTraveler::class,
            'persist.country' => \App\Http\Middleware\PersistCountrySelection::class,
        ]);
        
        // Apply country persistence to web routes
        $middleware->web(append: [
            \App\Http\Middleware\PersistCountrySelection::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

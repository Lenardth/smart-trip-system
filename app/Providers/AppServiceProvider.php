<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS when running on production or when APP_URL contains https
        if ($this->app->environment('production') ||
            (config('app.url') && strpos(config('app.url'), 'https') !== false)) {
            URL::forceScheme('https');
        }
    }
}
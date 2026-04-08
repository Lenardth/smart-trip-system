<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    
    public function register(): void
    {
        
    }

    
    public function boot(): void
    {
        
        if ($this->app->environment('production') ||
            (config('app.url') && strpos(config('app.url'), 'https') !== false)) {
            URL::forceScheme('https');
        }
    }
}
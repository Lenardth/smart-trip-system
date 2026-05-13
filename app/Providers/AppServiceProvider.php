<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;

use App\Contracts\AccommodationPricingInterface;
use App\Contracts\FlightPricingInterface;
use App\Contracts\FlightSearchInterface;
use App\Contracts\GeoapifyInterface;
use App\Contracts\PricingServiceInterface;

use App\Services\AccommodationPricingService;
use App\Services\AviationstackService;
use App\Services\FlightPricingService;
use App\Services\GeoapifyService;
use App\Services\PricingService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PricingServiceInterface::class,       PricingService::class);
        $this->app->bind(AccommodationPricingInterface::class, AccommodationPricingService::class);
        $this->app->bind(FlightPricingInterface::class,        FlightPricingService::class);
        $this->app->bind(FlightSearchInterface::class,         AviationstackService::class);
        $this->app->bind(GeoapifyInterface::class,             GeoapifyService::class);
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Vite::useManifestFilename('.vite/manifest.json');
    }
}

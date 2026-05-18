<?php

namespace App\View\Composers;

use App\Services\PexelsService;
use Illuminate\View\View;

class HeroImageComposer
{
    protected $pexelsService;

    public function __construct(PexelsService $pexelsService)
    {
        $this->pexelsService = $pexelsService;
    }

    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        // Get the current route name
        $routeName = request()->route() ? request()->route()->getName() : '';
        
        // Map route names to page identifiers
        $pageMap = [
            'discover' => 'discover',
            'plan-trip' => 'plan-trip',
            'flights.index' => 'flights',
            'accommodations.index' => 'accommodations',
            'bookings.index' => 'bookings',
            'dashboard' => 'dashboard',
            'home' => 'home',
        ];

        $page = $pageMap[$routeName] ?? 'home';
        
        // Only inject if not already set
        if (!$view->offsetExists('heroImage')) {
            $heroImage = $this->pexelsService->getHeroImage($page);
            $view->with('heroImage', $heroImage);
        }
    }
}

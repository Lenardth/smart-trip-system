<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Services\PexelsService;
use Illuminate\View\View;

final class HeroImageComposer
{
    private const DEFAULT_PAGE = 'home';

    private const ROUTE_PAGE_MAP = [
        'discover' => 'discover',
        'plan-trip' => 'plan-trip',
        'flights.index' => 'flights',
        'accommodations.index' => 'accommodations',
        'bookings.index' => 'bookings',
        'dashboard' => 'dashboard',
        'home' => self::DEFAULT_PAGE,
    ];

    public function __construct(private readonly PexelsService $pexelsService) {}

    public function compose(View $view): void
    {
        if ($view->offsetExists('heroImage')) {
            return;
        }

        $view->with('heroImage', $this->pexelsService->getHeroImage($this->pageKey()));
    }

    private function pageKey(): string
    {
        $routeName = request()->route()?->getName();

        return self::ROUTE_PAGE_MAP[$routeName] ?? self::DEFAULT_PAGE;
    }
}

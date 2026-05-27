<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class AuthenticatedNavigationComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'activeBookingsCount' => $this->activeBookingsCount(),
            'dashboardTab' => request()->routeIs('dashboard') ? request('tab', 'overview') : null,
            'sidebarSections' => $this->sidebarSections(),
        ]);
    }

    private function activeBookingsCount(): int
    {
        if (!Auth::check()) {
            return 0;
        }

        return Booking::where('user_id', Auth::id())
            ->whereIn('status', [
                config('booking.statuses.confirmed'),
                config('booking.statuses.pending'),
            ])
            ->count();
    }

    private function sidebarSections(): array
    {
        return collect(config('navigation.dashboard_sidebar', []))
            ->map(function (array $section) {
                $section['items'] = collect($section['items'] ?? [])
                    ->map(fn (array $item) => [
                        ...$item,
                        'href' => isset($item['route'])
                            ? route($item['route'], $item['params'] ?? [])
                            : ($item['href'] ?? '#'),
                    ])
                    ->all();

                return $section;
            })
            ->all();
    }
}

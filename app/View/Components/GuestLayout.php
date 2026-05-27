<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

final class GuestLayout extends Component
{
    /**
     * Render the layout used by guest-facing authentication screens.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}

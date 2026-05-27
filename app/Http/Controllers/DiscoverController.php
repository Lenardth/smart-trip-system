<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Services\DiscoverService;
use Illuminate\Contracts\View\View;

class DiscoverController extends Controller
{
    public function __construct(private readonly DiscoverService $discover) {}

    public function index(): View
    {
        return $this->discover->index();
    }

    public function show(Destination $destination): View
    {
        return $this->discover->show($destination);
    }
}

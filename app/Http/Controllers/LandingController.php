<?php

namespace App\Http\Controllers;

use App\Services\LandingDestinationService;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __construct(private readonly LandingDestinationService $landing) {}

    public function index(): View
    {
        return $this->landing->index();
    }
}

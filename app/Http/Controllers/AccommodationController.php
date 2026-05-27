<?php

namespace App\Http\Controllers;

use App\Services\AccommodationCatalogService;
use Illuminate\View\View;

class AccommodationController extends Controller
{
    public function __construct(private readonly AccommodationCatalogService $accommodations) {}

    public function index(): View
    {
        return $this->accommodations->index();
    }
}

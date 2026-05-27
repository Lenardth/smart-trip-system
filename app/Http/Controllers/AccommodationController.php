<?php

namespace App\Http\Controllers;

use App\Services\AccommodationCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccommodationController extends Controller
{
    public function __construct(private readonly AccommodationCatalogService $accommodations) {}

    public function index(): \Illuminate\View\View
    {
        return $this->accommodations->index();
    }

    public function list(Request $request): JsonResponse
    {
        return $this->accommodations->list($request);
    }

    public function searches(): JsonResponse
    {
        return $this->accommodations->searches();
    }

    public function news(Request $request): JsonResponse
    {
        return $this->accommodations->news($request);
    }

    public function travelWarning(Request $request): JsonResponse
    {
        return $this->accommodations->travelWarning($request);
    }
}

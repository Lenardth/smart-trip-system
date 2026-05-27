<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LandingDestinationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function __construct(private readonly LandingDestinationService $landing) {}

    public function destinations(Request $request): JsonResponse
    {
        return $this->landing->destinations($request);
    }
}

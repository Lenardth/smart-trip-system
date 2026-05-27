<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LandingDestinationService;
use Illuminate\Http\JsonResponse;

class LandingController extends Controller
{
    public function __construct(private readonly LandingDestinationService $landing) {}

    public function destinations(): JsonResponse
    {
        return $this->landing->destinations();
    }
}

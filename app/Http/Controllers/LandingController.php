<?php

namespace App\Http\Controllers;

use App\Services\LandingDestinationService;
use Illuminate\Http\JsonResponse;

class LandingController extends Controller
{
    public function __construct(private readonly LandingDestinationService $landing) {}

    public function index()
    {
        return $this->landing->index();
    }

    public function destinations(): JsonResponse
    {
        return $this->landing->destinations();
    }
}

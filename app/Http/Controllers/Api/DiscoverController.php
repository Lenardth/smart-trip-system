<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DiscoverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    public function __construct(private readonly DiscoverService $discover) {}

    public function list(Request $request): JsonResponse
    {
        return $this->discover->list($request);
    }

    public function search(Request $request): JsonResponse
    {
        return $this->discover->search($request);
    }
}

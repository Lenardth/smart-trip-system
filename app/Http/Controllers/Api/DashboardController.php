<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function recentActivity(): JsonResponse
    {
        return response()->json(['activities' => $this->dashboard->recentActivity(Auth::id())]);
    }

    public function statistics(): JsonResponse
    {
        return response()->json($this->dashboard->statistics(Auth::id()));
    }
}

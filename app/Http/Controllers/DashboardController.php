<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function index()
    {
        return view('dashboard.index', [
            'user' => Auth::user(),
            ...$this->dashboard->overviewData(Auth::id()),
        ]);
    }

    public function recentActivity(): JsonResponse
    {
        return response()->json(['activities' => $this->dashboard->recentActivity(Auth::id())]);
    }

    public function statistics(): JsonResponse
    {
        return response()->json($this->dashboard->statistics(Auth::id()));
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function index(): View
    {
        return view('dashboard.index', [
            'user' => Auth::user(),
            ...$this->dashboard->overviewData(Auth::id()),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Support\Facades\Auth;

class TripController extends Controller
{
    public function index()
    {
        return response()->json(
            Trip::where('user_id', Auth::id())
                ->latest()
                ->get()
        );
    }

    public function upcoming()
    {
        return response()->json(
            Trip::where('user_id', Auth::id())
                ->whereDate('start_date', '>=', now())
                ->orderBy('start_date')
                ->get()
        );
    }
}

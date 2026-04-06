<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TripMood;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TripMoodController extends Controller
{
    public function index(): JsonResponse
    {
        $moods = Cache::remember('trip_moods_list', 60, function () {
            return TripMood::orderByDesc('use_count')
                ->orderByDesc('created_at')
                ->select(['id', 'label', 'use_count'])
                ->limit(100)
                ->get();
        });

        return response()->json(['moods' => $moods]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'label' => ['required', 'string', 'min:3', 'max:80'],
        ]);

        $mood = TripMood::findOrCreateByLabel(
            $request->input('label'),
            Auth::id()
        );

        Cache::forget('trip_moods_list');

        return response()->json(['mood' => $mood->only(['id', 'label', 'use_count'])], 201);
    }

    public function use(TripMood $mood): JsonResponse
    {
        $mood->increment('use_count');
        Cache::forget('trip_moods_list');

        return response()->json(['id' => $mood->id, 'use_count' => $mood->use_count]);
    }
}
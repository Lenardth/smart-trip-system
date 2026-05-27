<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TripMood;
use App\Services\TripMoodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripMoodController extends Controller
{
    public function __construct(private readonly TripMoodService $moods) {}

    public function index(): JsonResponse
    {
        return response()->json(['moods' => $this->moods->list()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'min:3', 'max:80'],
        ]);

        return response()->json([
            'mood' => $this->moods->create($validated['label'], Auth::id()),
        ], 201);
    }

    public function use(TripMood $mood): JsonResponse
    {
        return response()->json($this->moods->recordUse($mood));
    }
}

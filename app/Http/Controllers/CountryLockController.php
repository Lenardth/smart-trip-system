<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryLockController extends Controller
{
    /**
     * Get the current locked country from session
     */
    public function get(): JsonResponse
    {
        return response()->json([
            'locked' => session()->has('locked_country'),
            'country' => session('locked_country'),
            'destination' => session('locked_destination'),
            'locked_at' => session('country_locked_at'),
        ]);
    }

    /**
     * Lock a country selection
     */
    public function lock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country' => 'required|string|max:255',
            'destination' => 'nullable|string|max:255',
        ]);

        session([
            'locked_country' => $validated['country'],
            'locked_destination' => $validated['destination'] ?? '',
            'country_locked_at' => now()->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Country locked successfully',
            'country' => $validated['country'],
            'destination' => $validated['destination'] ?? '',
        ]);
    }

    /**
     * Unlock/clear the country selection
     */
    public function unlock(): JsonResponse
    {
        session()->forget(['locked_country', 'locked_destination', 'country_locked_at']);

        return response()->json([
            'success' => true,
            'message' => 'Country unlocked successfully',
        ]);
    }
}

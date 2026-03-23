<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TripController extends Controller
{
    public function index(): JsonResponse
    {
        $trips = Trip::where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json(['trips' => $trips]);
    }

    public function upcoming(): JsonResponse
    {
        $trips = Trip::where('user_id', Auth::id())
            ->where('status', 'planned')
            ->latest()
            ->get();

        return response()->json(['trips' => $trips]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'destination'    => 'required|string|max:255',
            'country'        => 'nullable|string|max:255',
            'mood'           => 'nullable|string|max:100',
            'budget'         => 'nullable|string|max:100',
            'duration'       => 'nullable|string|max:100',
            'companion'      => 'nullable|string|max:100',
            'region'         => 'nullable|string|max:100',
            'accommodation'  => 'nullable|string|max:100',
            'origin'         => 'nullable|string|max:255',
            'month'          => 'nullable|string|max:50',
            'estimated_cost' => 'nullable|numeric|min:0',
        ]);
        $data['accommodation'] = $this->normaliseAccommodation($data['accommodation'] ?? null);

        $exists = Trip::where('user_id', Auth::id())
            ->where('destination', $data['destination'])
            ->where('budget',      $data['budget']   ?? null)
            ->where('duration',    $data['duration'] ?? null)
            ->where('status',      'planned')
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This trip is already saved to your dashboard.',
            ], 409);
        }

        $days = match ($data['duration'] ?? 'week') {
            'weekend'   => 4,
            'week'      => 7,
            'two_weeks' => 14,
            'month'     => 30,
            default     => 7,
        };

        $trip = Trip::create([
            ...$data,
            'user_id'    => Auth::id(),
            'status'     => 'planned',
            'start_date' => now()->toDateString(),
            'end_date'   => now()->addDays($days)->toDateString(),
            'title'      => $data['destination'] . ($data['country'] ? ', ' . $data['country'] : ''),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Trip saved to your dashboard.',
            'trip'    => $trip,
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        Trip::where('user_id', Auth::id())
            ->where('id', $id)
            ->delete();

        return response()->json(['success' => true]);
    }

    private function normaliseAccommodation(?string $value): ?string
    {
        if (!$value) return null;

        return match ($value) {
            'hotel' => 'budget_hotel',
            'bnb' => 'boutique',
            default => $value,
        };
    }
}

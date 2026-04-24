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
            'destination'        => 'required|string|max:255',
            'country'            => 'nullable|string|max:255',
            'mood'               => 'nullable|string|max:100',
            'feeling_note'       => 'nullable|string|max:500',
            'budget'             => 'nullable|string|max:100',
            'duration'           => 'nullable|string|max:100',
            'companion'          => 'nullable|string|max:100',
            'region'             => 'nullable|string|max:100',
            'accommodation'      => 'nullable|string|max:100',
            'origin'             => 'nullable|string|max:255',
            'month'              => 'nullable|string|max:50',
            'estimated_cost'     => 'nullable|numeric|min:0',
            'description'        => 'nullable|string|max:2000',
            // Pricing breakdown
            'flight_cost'        => 'nullable|numeric|min:0',
            'accommodation_cost' => 'nullable|numeric|min:0',
            'activities_cost'    => 'nullable|numeric|min:0',
            'food_cost'          => 'nullable|numeric|min:0',
            'transport_cost'     => 'nullable|numeric|min:0',
            'cost_breakdown'     => 'nullable|array',
            // Itinerary
            'daily_itinerary'    => 'nullable|array',
            'activities'         => 'nullable|array',
            'cities_to_visit'    => 'nullable|array',
            // AI metadata
            'travel_tip'         => 'nullable|string|max:1000',
            'visa_info'          => 'nullable|string|max:1000',
            'flight_info'        => 'nullable|string|max:500',
            'best_time_to_visit' => 'nullable|string|max:255',
            'is_good_right_now'  => 'nullable|boolean',
            // Validation data
            'validation_data'    => 'nullable|array',
            'weather_data'       => 'nullable|array',
            'safety_data'        => 'nullable|array',
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

    public function update(Request $request, int $id): JsonResponse
    {
        $trip = Trip::where('user_id', Auth::id())->findOrFail($id);

        $data = $request->validate([
            'destination'        => 'sometimes|string|max:255',
            'country'            => 'nullable|string|max:255',
            'mood'               => 'nullable|string|max:100',
            'feeling_note'       => 'nullable|string|max:500',
            'budget'             => 'nullable|string|max:100',
            'duration'           => 'nullable|string|max:100',
            'companion'          => 'nullable|string|max:100',
            'region'             => 'nullable|string|max:100',
            'accommodation'      => 'nullable|string|max:100',
            'origin'             => 'nullable|string|max:255',
            'month'              => 'nullable|string|max:50',
            'estimated_cost'     => 'nullable|numeric|min:0',
            'status'             => 'nullable|in:planned,ongoing,completed,cancelled',
            'notes'              => 'nullable|string|max:2000',
            'start_date'         => 'nullable|date',
            'end_date'           => 'nullable|date|after_or_equal:start_date',
            'description'        => 'nullable|string|max:2000',
            // Editable pricing
            'flight_cost'        => 'nullable|numeric|min:0',
            'accommodation_cost' => 'nullable|numeric|min:0',
            'activities_cost'    => 'nullable|numeric|min:0',
            'food_cost'          => 'nullable|numeric|min:0',
            'transport_cost'     => 'nullable|numeric|min:0',
            'cost_breakdown'     => 'nullable|array',
            // Editable itinerary
            'daily_itinerary'    => 'nullable|array',
            'activities'         => 'nullable|array',
            'cities_to_visit'    => 'nullable|array',
            // AI metadata
            'travel_tip'         => 'nullable|string|max:1000',
            'visa_info'          => 'nullable|string|max:1000',
            'flight_info'        => 'nullable|string|max:500',
            'best_time_to_visit' => 'nullable|string|max:255',
        ]);

        if (isset($data['accommodation'])) {
            $data['accommodation'] = $this->normaliseAccommodation($data['accommodation']);
        }

        // Recalculate estimated_cost if individual costs are updated
        if (isset($data['flight_cost']) || isset($data['accommodation_cost']) || 
            isset($data['activities_cost']) || isset($data['food_cost']) || isset($data['transport_cost'])) {
            $data['estimated_cost'] = 
                ($data['flight_cost'] ?? $trip->flight_cost ?? 0) +
                ($data['accommodation_cost'] ?? $trip->accommodation_cost ?? 0) +
                ($data['activities_cost'] ?? $trip->activities_cost ?? 0) +
                ($data['food_cost'] ?? $trip->food_cost ?? 0) +
                ($data['transport_cost'] ?? $trip->transport_cost ?? 0);
        }

        $trip->update($data);

        return response()->json(['success' => true, 'trip' => $trip->fresh()]);
    }
    
    /**
     * Get a single trip for editing
     */
    public function show(int $id): JsonResponse
    {
        $trip = Trip::where('user_id', Auth::id())->findOrFail($id);
        
        return response()->json(['trip' => $trip]);
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
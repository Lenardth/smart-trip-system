<?php

namespace App\Http\Controllers;

use App\Services\TripService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripController extends Controller
{
    public function __construct(private readonly TripService $trips) {}

    public function index(): JsonResponse
    {
        return response()->json(['trips' => $this->trips->allForUser(Auth::id())]);
    }

    public function upcoming(): JsonResponse
    {
        return response()->json(['trips' => $this->trips->upcomingForUser(Auth::id())]);
    }

    public function store(Request $request): JsonResponse
    {
        $result = $this->trips->createForUser($request->validate($this->storeRules()), Auth::id());
        $status = $result['status'];
        unset($result['status']);

        return response()->json($result, $status);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->trips->deleteForUser($id, Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Trip deleted successfully.',
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'trip' => $this->trips->updateForUser($request->validate($this->updateRules()), $id, Auth::id()),
        ]);
    }

    private function storeRules(): array
    {
        return [
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
            'flight_cost'        => 'nullable|numeric|min:0',
            'accommodation_cost' => 'nullable|numeric|min:0',
            'activities_cost'    => 'nullable|numeric|min:0',
            'food_cost'          => 'nullable|numeric|min:0',
            'transport_cost'     => 'nullable|numeric|min:0',
            'cost_breakdown'     => 'nullable|array',
            'daily_itinerary'    => 'nullable|array',
            'activities'         => 'nullable|array',
            'cities_to_visit'    => 'nullable|array',
            'travel_tip'         => 'nullable|string|max:1000',
            'visa_info'          => 'nullable|string|max:1000',
            'flight_info'        => 'nullable|string|max:500',
            'best_time_to_visit' => 'nullable|string|max:255',
            'is_good_right_now'  => 'nullable|boolean',
            'validation_data'    => 'nullable|array',
            'weather_data'       => 'nullable|array',
            'safety_data'        => 'nullable|array',
        ];
    }

    private function updateRules(): array
    {
        return [
            ...$this->storeRules(),
            'destination' => 'sometimes|string|max:255',
            'status'     => 'nullable|in:planned,ongoing,completed,cancelled',
            'notes'      => 'nullable|string|max:2000',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ];
    }
}

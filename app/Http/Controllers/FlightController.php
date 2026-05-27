<?php

namespace App\Http\Controllers;

use App\Services\FlightSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class FlightController extends Controller
{
    public function __construct(
        private readonly FlightSearchService $flights
    ) {}

    public function index()
    {
        return view('flights.index');
    }

    public function search(Request $request): JsonResponse
    {
        $minDaysAhead = config('flights.min_departure_days_ahead', 2);
        $maxAdults    = config('flights.max_adults', 9);

        $validated = $request->validate([
            'from'           => ['required', 'string', 'max:100'],
            'to'             => ['required', 'string', 'max:100'],
            'departure_date' => ['required', 'date_format:Y-m-d', 'after:' . now()->addDays($minDaysAhead - 1)->format('Y-m-d')],
            'return_date'    => ['nullable', 'date_format:Y-m-d', 'after_or_equal:departure_date'],
            'adults'         => ['nullable', 'integer', 'min:1', 'max:' . $maxAdults],
            'travel_class'   => ['nullable', 'string', Rule::in(config('booking.travel_classes'))],
        ]);

        try {
            $result = $this->flights->search($validated, $request->ip());
            $status = $result['status'] ?? 200;
            unset($result['status']);

            return response()->json($result, $status);
        } catch (\Throwable $e) {
            Log::error('Flight search failed', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Flight search failed. Please try again.',
                'flights' => [],
            ], 500);
        }
    }

    public function airports(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'keyword' => ['required', 'string', 'min:1', 'max:100'],
        ]);

        try {
            return response()->json([
                'success' => true,
                'results' => $this->flights->airports($validated['keyword']),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'results' => [],
            ], 500);
        }
    }
}

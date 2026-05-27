<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SearchAirportsRequest;
use App\Http\Requests\Api\SearchFlightsRequest;
use App\Services\FlightSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class FlightController extends Controller
{
    public function __construct(private readonly FlightSearchService $flights) {}

    public function search(SearchFlightsRequest $request): JsonResponse
    {
        try {
            $result = $this->flights->search($request->validated(), $request->ip());
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

    public function airports(SearchAirportsRequest $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'results' => $this->flights->airports($request->validated('keyword')),
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

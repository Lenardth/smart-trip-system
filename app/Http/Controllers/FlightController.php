<?php

namespace App\Http\Controllers;

use App\Services\AviationstackService;
use App\Services\FlightPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FlightController extends Controller
{
    public function __construct(
        private readonly AviationstackService $aviationstack,
        private readonly FlightPricingService $flightPricing
    ) {
    }

    public function index()
    {
        // Get popular deals with real-time pricing
        $deals = $this->flightPricing->getPopularRouteDeals();
        $popularRoutes = $this->flightPricing->getPopularRoutes();
        
        return view('flights.index', compact('deals', 'popularRoutes'));
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from'           => ['required', 'string', 'max:100'],
            'to'             => ['required', 'string', 'max:100'],
            'departure_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'return_date'    => ['nullable', 'date_format:Y-m-d', 'after_or_equal:departure_date'],
            'adults'         => ['nullable', 'integer', 'min:1', 'max:9'],
            'travel_class'   => ['nullable', 'string'],
        ]);

        try {
            Log::info('Flight search request', $validated);

            $fromCode = $this->aviationstack->resolveIataCode($validated['from']);
            $toCode   = $this->aviationstack->resolveIataCode($validated['to']);

            if (!$fromCode || !$toCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not find airport for "' . (!$fromCode ? $validated['from'] : $validated['to']) . '". Try using the city name (e.g., London, Dubai) or IATA code (e.g., LHR, DXB).',
                    'flights' => [],
                ], 422);
            }

            usleep(500000);
            $flights = $this->aviationstack->searchFlights(
                $fromCode,
                $toCode,
                $validated['departure_date'],
                (int) ($validated['adults'] ?? 1),
                strtoupper($validated['travel_class'] ?? 'ECONOMY'),
                $validated['return_date'] ?? null
            );

            return response()->json([
                'success'   => true,
                'from_code' => $fromCode,
                'to_code'   => $toCode,
                'flights'   => $flights,
                'count'     => count($flights),
                'message'   => empty($flights)
                    ? 'No flights found for this route on the selected date. Try different dates or destinations.'
                    : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Flight search failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
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
            $results = $this->aviationstack->searchAirports($validated['keyword']);

            return response()->json([
                'success' => true,
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'results' => [],
            ], 500);
        }
    }
}
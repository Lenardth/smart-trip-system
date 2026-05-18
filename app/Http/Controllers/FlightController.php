<?php

namespace App\Http\Controllers;

use App\Contracts\FlightSearchInterface;
use App\Contracts\FlightPricingInterface;
use App\Models\FlightSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FlightController extends Controller
{
    public function __construct(
        private readonly FlightSearchInterface $aviationstack,
        private readonly FlightPricingInterface $flightPricing
    ) {
    }

    public function index()
    {
        $deals = array_map(function (array $deal) {
            $deal['from_city'] = $this->resolveCityLabel($deal['from']);
            $deal['to_city']   = $this->resolveCityLabel($deal['to']);

            return $deal;
        }, $this->flightPricing->getPopularRouteDeals());

        $popularRoutes = array_map(function (array $route) {
            $route['from_city'] = $this->resolveCityLabel($route['from']);
            $route['to_city']   = $this->resolveCityLabel($route['to']);

            return $route;
        }, $this->flightPricing->getPopularRoutes());

        return view('flights.index', compact('deals', 'popularRoutes'));
    }

    private function resolveCityLabel(string $code): string
    {
        $airports = $this->aviationstack->searchAirports($code);

        return collect($airports)->first()['city'] ?? $code;
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

            $flights = $this->aviationstack->searchFlights(
                $fromCode,
                $toCode,
                $validated['departure_date'],
                (int) ($validated['adults'] ?? 1),
                strtoupper($validated['travel_class'] ?? 'ECONOMY'),
                $validated['return_date'] ?? null
            );

            $this->logSearch($request, $validated, $fromCode, $toCode, count($flights));

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
            Log::error('Flight search failed', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Flight search failed. Please try again.',
                'flights' => [],
            ], 500);
        }
    }

    private function logSearch(Request $request, array $validated, ?string $fromCode, ?string $toCode, int $count): void
    {
        try {
            FlightSearch::create([
                'user_id'        => Auth::id(),
                'from_query'     => $validated['from'],
                'to_query'       => $validated['to'],
                'from_code'      => $fromCode,
                'to_code'        => $toCode,
                'departure_date' => $validated['departure_date'],
                'return_date'    => $validated['return_date'] ?? null,
                'adults'         => (int) ($validated['adults'] ?? 1),
                'travel_class'   => strtoupper($validated['travel_class'] ?? 'ECONOMY'),
                'results_count'  => $count,
                'ip_address'     => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('FlightSearch log failed: ' . $e->getMessage());
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
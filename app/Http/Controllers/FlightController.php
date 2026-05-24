<?php

namespace App\Http\Controllers;

use App\Contracts\FlightSearchInterface;
use App\Models\FlightSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FlightController extends Controller
{
    public function __construct(
        private readonly FlightSearchInterface $aviationstack
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
                strtoupper($validated['travel_class'] ?? config('booking.default_travel_class')),
                $validated['return_date'] ?? null
            );

            $this->logSearch($request, $validated, $fromCode, $toCode, count($flights));

            $message = null;
            if (empty($flights)) {
                $message = "No direct flights found from {$validated['from']} to {$validated['to']} on {$validated['departure_date']}. "
                    . "Try a different date, or search via a connecting hub (e.g. Frankfurt, London, Dubai).";
            }

            return response()->json([
                'success'   => true,
                'from_code' => $fromCode,
                'to_code'   => $toCode,
                'flights'   => $flights,
                'count'     => count($flights),
                'message'   => $message,
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

    private function resolveCityLabel(string $code): string
    {
        $airports = $this->aviationstack->searchAirports($code);
        return collect($airports)->first()['city'] ?? $code;
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
                'travel_class'   => strtoupper($validated['travel_class'] ?? config('booking.default_travel_class')),
                'results_count'  => $count,
                'ip_address'     => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('FlightSearch log failed: ' . $e->getMessage());
        }
    }
}

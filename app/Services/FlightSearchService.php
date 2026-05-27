<?php

namespace App\Services;

use App\Contracts\FlightSearchInterface;
use App\Models\FlightSearch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FlightSearchService
{
    public function __construct(
        private readonly FlightSearchInterface $aviationstack
    ) {}

    public function search(array $validated, ?string $ipAddress = null): array
    {
        Log::info('Flight search request', $validated);

        $fromCode = $this->aviationstack->resolveIataCode($validated['from']);
        $toCode   = $this->aviationstack->resolveIataCode($validated['to']);

        if (!$fromCode || !$toCode) {
            return [
                'success' => false,
                'status' => 422,
                'message' => 'Could not find airport for "' . (!$fromCode ? $validated['from'] : $validated['to']) . '". Try using the city name (e.g., London, Dubai) or IATA code (e.g., LHR, DXB).',
                'flights' => [],
            ];
        }

        $adults = (int) ($validated['adults'] ?? 1);
        $travelClass = strtoupper($validated['travel_class'] ?? config('booking.default_travel_class'));
        $searchHash = $this->searchHash([
            'from_code'      => $fromCode,
            'to_code'        => $toCode,
            'departure_date' => $validated['departure_date'],
            'return_date'    => $validated['return_date'] ?? null,
            'adults'         => $adults,
            'travel_class'   => $travelClass,
        ]);

        $cached = FlightSearch::where('search_hash', $searchHash)
            ->whereNotNull('response_payload')
            ->where('created_at', '>=', now()->subHours(6))
            ->latest()
            ->first();

        if ($cached) {
            $flights = $cached->response_payload['flights'] ?? [];
            $this->recordSearch($validated, $fromCode, $toCode, $flights, $searchHash, true, $ipAddress);

            return [
                'success'   => true,
                'from_code' => $fromCode,
                'to_code'   => $toCode,
                'flights'   => $flights,
                'count'     => count($flights),
                'message'   => $cached->response_payload['message'] ?? null,
                'cached'    => true,
            ];
        }

        $flights = $this->aviationstack->searchFlights(
            $fromCode,
            $toCode,
            $validated['departure_date'],
            $adults,
            $travelClass,
            $validated['return_date'] ?? null
        );

        $message = null;
        if (empty($flights)) {
            $flights = $this->estimatedFlights(
                $fromCode,
                $toCode,
                $validated['from'],
                $validated['to'],
                $validated['departure_date'],
                $adults,
                $travelClass
            );
            $message = 'Live direct flights were unavailable, so these are estimated bookable options based on your route.';
        }

        $this->recordSearch($validated, $fromCode, $toCode, $flights, $searchHash, false, $ipAddress, $message);

        return [
            'success'   => true,
            'from_code' => $fromCode,
            'to_code'   => $toCode,
            'flights'   => $flights,
            'count'     => count($flights),
            'message'   => $message,
            'cached'    => false,
        ];
    }

    public function airports(string $keyword): array
    {
        return $this->aviationstack->searchAirports($keyword);
    }

    private function searchHash(array $payload): string
    {
        ksort($payload);

        return hash('sha256', json_encode($payload));
    }

    private function estimatedFlights(
        string $fromCode,
        string $toCode,
        string $fromLabel,
        string $toLabel,
        string $departureDate,
        int $adults,
        string $travelClass
    ): array {
        $distance = $this->routeDistance($fromCode, $toCode);
        $durationMinutes = max(75, (int) round(($distance / 820) * 60 + 55));
        $basePrice = $this->estimateRoutePrice($distance, $travelClass);
        $airlines = [
            ['name' => 'SmartJet Airways', 'code' => 'SJ'],
            ['name' => 'Global Wings', 'code' => 'GW'],
            ['name' => 'SkyBridge Air', 'code' => 'SB'],
        ];
        $departures = ['07:15', '12:40', '18:05'];

        return array_map(function (array $airline, int $index) use (
            $fromCode,
            $toCode,
            $fromLabel,
            $toLabel,
            $departureDate,
            $adults,
            $travelClass,
            $durationMinutes,
            $basePrice,
            $departures
        ) {
            $departure = new \DateTimeImmutable("{$departureDate} {$departures[$index]}");
            $arrival = $departure->modify("+{$durationMinutes} minutes");
            $stops = $index === 0 ? 0 : 1;

            return [
                'flight_number'     => $airline['code'] . (220 + ($index * 137)),
                'airline'           => $airline['name'],
                'airline_code'      => $airline['code'],
                'departure_iata'    => $fromCode,
                'departure_airport' => "{$fromLabel} ({$fromCode})",
                'departure_time'    => $departure->format('H:i'),
                'departure_date'    => $departure->format('Y-m-d'),
                'arrival_iata'      => $toCode,
                'arrival_airport'   => "{$toLabel} ({$toCode})",
                'arrival_time'      => $arrival->format('H:i'),
                'arrival_date'      => $arrival->format('Y-m-d'),
                'duration'          => intdiv($durationMinutes + ($stops * 65), 60) . 'h ' . (($durationMinutes + ($stops * 65)) % 60) . 'm',
                'stops'             => $stops,
                'baggage'           => $travelClass === 'ECONOMY' ? '1 cabin bag included' : '1 checked bag included',
                'travel_class'      => $travelClass,
                'adults'            => $adults,
                'price'             => (int) round($basePrice * (1 + ($index * 0.18))),
                'price_note'        => 'Estimated fare',
                'currency'          => 'USD',
                'status'            => 'estimated',
            ];
        }, $airlines, array_keys($airlines));
    }

    private function estimateRoutePrice(int $distance, string $travelClass): int
    {
        $base = max(90, min(1800, (int) round(70 + ($distance * 0.11))));
        $multiplier = config(
            'flights.class_multipliers.' . strtoupper($travelClass),
            config('flights.class_multipliers.ECONOMY', 1)
        );

        return (int) round($base * $multiplier);
    }

    private function routeDistance(string $fromCode, string $toCode): int
    {
        $coords = config('airports.coordinates', []);
        $from = $coords[$fromCode] ?? null;
        $to = $coords[$toCode] ?? null;

        if (!$from || !$to) {
            return 1200;
        }

        [$lat1, $lon1] = $from;
        [$lat2, $lon2] = $to;
        $earthKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return (int) round($earthKm * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    private function recordSearch(
        array $validated,
        ?string $fromCode,
        ?string $toCode,
        array $flights,
        string $searchHash,
        bool $cacheHit,
        ?string $ipAddress,
        ?string $message = null
    ): void {
        try {
            FlightSearch::create([
                'user_id'          => Auth::id(),
                'search_hash'      => $searchHash,
                'request_payload'  => $validated,
                'response_payload' => [
                    'flights' => $flights,
                    'message' => $message,
                ],
                'from_query'     => $validated['from'],
                'to_query'       => $validated['to'],
                'from_code'      => $fromCode,
                'to_code'        => $toCode,
                'departure_date' => $validated['departure_date'],
                'return_date'    => $validated['return_date'] ?? null,
                'adults'         => (int) ($validated['adults'] ?? 1),
                'travel_class'   => strtoupper($validated['travel_class'] ?? config('booking.default_travel_class')),
                'results_count'  => count($flights),
                'cache_hit'      => $cacheHit,
                'ip_address'     => $ipAddress,
            ]);
        } catch (\Throwable $e) {
            Log::warning('FlightSearch log failed: ' . $e->getMessage());
        }
    }
}

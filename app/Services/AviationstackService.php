<?php

namespace App\Services;

use App\Contracts\FlightSearchInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AviationstackService implements FlightSearchInterface
{
    private string $host;
    private string $baseUrl;
    private ?string $key;

    public function __construct()
    {
        $this->host    = config('services.aerodatabox.host');
        $this->baseUrl = config('services.aerodatabox.base_url');
        $this->key     = config('services.aerodatabox.key');
    }

    public function resolveIataCode(string $input): ?string
    {
        $input = trim($input);

        if (preg_match('/^[A-Za-z]{3}$/', $input)) {
            return strtoupper($input);
        }

        $lower = strtolower($input);
        $lower = preg_replace('/\s*(international|airport|intl|city|centre|center)\s*/', ' ', $lower);
        $lower = preg_replace('/\s+/', ' ', trim($lower));

        $map = config('airports.iata_map');

        if (isset($map[$lower])) {
            return $map[$lower];
        }

        foreach ($map as $city => $iata) {
            if (str_contains($lower, $city) || str_contains($city, $lower)) {
                return $iata;
            }
        }

        $words = explode(' ', $lower);
        foreach ($words as $word) {
            if (strlen($word) < 3) continue;
            foreach ($map as $city => $iata) {
                if (str_starts_with($city, $word) || str_starts_with($word, $city)) {
                    return $iata;
                }
            }
        }

        $bestScore = 0;
        $bestIata  = null;
        foreach ($map as $city => $iata) {
            similar_text($lower, $city, $percent);
            if ($percent > $bestScore) {
                $bestScore = $percent;
                $bestIata  = $iata;
            }
        }

        $threshold = config('airports.fuzzy_threshold');

        return $bestScore >= $threshold ? $bestIata : null;
    }

    public function searchFlights(
        string  $from,
        string  $to,
        string  $departureDate,
        int     $adults = 1,
        string  $travelClass = 'ECONOMY',
        ?string $returnDate = null
    ): array {
        if (!$this->key) {
            Log::debug('AeroDataBox API key is not configured; using estimated flight fallback.');
            return [];
        }

        $windows = [
            ["{$departureDate}T00:00", "{$departureDate}T11:59"],
            ["{$departureDate}T12:00", "{$departureDate}T23:59"],
        ];

        $allDepartures = [];
        $delayUs       = config('flights.request_delay_ms') * 1000;
        $limit         = config('airports.search_limit');

        foreach ($windows as [$fromLocal, $toLocal]) {
            try {
                $response = Http::withHeaders([
                    'x-rapidapi-host' => $this->host,
                    'x-rapidapi-key'  => $this->key,
                ])->get("{$this->baseUrl}/flights/airports/iata/{$from}/{$fromLocal}/{$toLocal}", [
                    'withLeg'        => 'true',
                    'direction'      => 'Departure',
                    'withCancelled'  => 'false',
                    'withCodeshared' => 'true',
                    'limit'          => $limit,
                ]);

                if ($response->successful()) {
                    $allDepartures = array_merge(
                        $allDepartures,
                        $response->json()['departures'] ?? []
                    );
                } else {
                    Log::warning('AeroDataBox window failed', [
                        'window' => "{$fromLocal}/{$toLocal}",
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('AeroDataBox window exception', ['error' => $e->getMessage()]);
            }

            usleep($delayUs);
        }

        if (empty($allDepartures)) {
            return [];
        }

        $direct = array_filter(
            $allDepartures,
            fn($f) => strtoupper($f['arrival']['airport']['iata'] ?? '') === strtoupper($to)
        );

        if (empty($direct)) {
            Log::info('No direct flights found', [
                'from' => $from,
                'to'   => $to,
                'date' => $departureDate,
            ]);
            return [];
        }

        return array_map(
            fn($f) => $this->normalizeFlight($f, $departureDate, $adults, $travelClass),
            array_values($direct)
        );
    }

    public function searchAirports(string $keyword): array
    {
        $lower  = strtolower(trim($keyword));
        $result = [];

        foreach (config('airports.iata_map') as $city => $iata) {
            if (str_contains($city, $lower) || str_contains($lower, $city)) {
                $result[] = [
                    'iata_code' => $iata,
                    'name'      => ucwords($city) . ' Airport',
                    'city'      => ucwords($city),
                    'country'   => null,
                ];
            }
        }

        return $result;
    }

    private function normalizeFlight(array $f, string $departureDate, int $adults, string $travelClass): array
    {
        $dep = $f['departure'] ?? [];
        $arr = $f['arrival']   ?? [];

        $depRaw = $dep['scheduledTime']['local'] ?? $dep['scheduledTime']['utc'] ?? null;
        $arrRaw = $arr['scheduledTime']['local'] ?? $arr['scheduledTime']['utc'] ?? null;

        $duration       = ($depRaw && $arrRaw) ? $this->calcDuration($depRaw, $arrRaw) : null;
        $estimatedPrice = $this->estimatePrice($duration, $travelClass);

        return [
            'flight_number'     => $f['number']                          ?? 'N/A',
            'airline'           => $f['airline']['name']                 ?? 'Unknown',
            'airline_code'      => $f['airline']['iata']                 ?? null,
            'departure_iata'    => $dep['airport']['iata']               ?? null,
            'departure_airport' => $dep['airport']['name']               ?? ($dep['airport']['iata'] ?? null),
            'departure_time'    => $depRaw ? $this->extractTime($depRaw) : null,
            'departure_date'    => $depRaw ? $this->extractDate($depRaw) : $departureDate,
            'arrival_iata'      => $arr['airport']['iata']               ?? null,
            'arrival_airport'   => $arr['airport']['name']               ?? ($arr['airport']['iata'] ?? null),
            'arrival_time'      => $arrRaw ? $this->extractTime($arrRaw) : null,
            'arrival_date'      => $arrRaw ? $this->extractDate($arrRaw) : null,
            'duration'          => $duration,
            'stops'             => 0,
            'baggage'           => '1 bag included',
            'travel_class'      => $travelClass,
            'adults'            => $adults,
            'price'             => $estimatedPrice,
            'price_note'        => 'Estimated fare',
            'currency'          => 'USD',
            'status'            => $f['status'] ?? null,
        ];
    }

    private function estimatePrice(?string $duration, string $travelClass): int
    {
        $minutes = config('flights.default_duration_minutes');

        if ($duration && preg_match('/(\d+)h\s*(\d+)?m?/', $duration, $m)) {
            $minutes = ((int) $m[1]) * 60 + (int) ($m[2] ?? 0);
        }

        $baseRate = match (true) {
            $minutes <= 90  => 0.20,
            $minutes <= 180 => 0.25,
            $minutes <= 360 => 0.30,
            $minutes <= 600 => 0.35,
            default         => 0.40,
        };

        $minPrice   = config('flights.min_price');
        $base       = max($minPrice, (int) ($minutes * $baseRate));
        $multiplier = config(
            'flights.class_multipliers.' . strtoupper($travelClass),
            config('flights.class_multipliers.ECONOMY')
        );

        return (int) round($base * $multiplier);
    }

    private function extractTime(string $datetime): string
    {
        return preg_match('/(\d{2}:\d{2})/', $datetime, $m) ? $m[1] : '--:--';
    }

    private function extractDate(string $datetime): string
    {
        return preg_match('/(\d{4}-\d{2}-\d{2})/', $datetime, $m) ? $m[1] : '';
    }

    private function calcDuration(string $dep, string $arr): string
    {
        try {
            $d = (new \DateTime(str_replace(' ', 'T', $dep)))->diff(
                  new \DateTime(str_replace(' ', 'T', $arr))
            );
            return $d->h . 'h ' . $d->i . 'm';
        } catch (\Exception) {
            return '--h --m';
        }
    }
}

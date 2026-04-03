<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AviationstackService
{
    private string $host    = 'aerodatabox.p.rapidapi.com';
    private string $baseUrl = 'https://aerodatabox.p.rapidapi.com';
    private string $key;

    private array $airportMap = [
        // Cities → IATA
        'budapest'      => 'BUD', 'london'        => 'LHR', 'paris'         => 'CDG',
        'new york'      => 'JFK', 'los angeles'   => 'LAX', 'dubai'         => 'DXB',
        'amsterdam'     => 'AMS', 'frankfurt'     => 'FRA', 'istanbul'      => 'IST',
        'rome'          => 'FCO', 'madrid'        => 'MAD', 'barcelona'     => 'BCN',
        'vienna'        => 'VIE', 'zurich'        => 'ZRH', 'brussels'      => 'BRU',
        'munich'        => 'MUC', 'berlin'        => 'BER', 'prague'        => 'PRG',
        'warsaw'        => 'WAW', 'athens'        => 'ATH', 'lisbon'        => 'LIS',
        'dublin'        => 'DUB', 'copenhagen'    => 'CPH', 'stockholm'     => 'ARN',
        'oslo'          => 'OSL', 'helsinki'      => 'HEL', 'singapore'     => 'SIN',
        'tokyo'         => 'NRT', 'sydney'        => 'SYD', 'hong kong'     => 'HKG',
        'beijing'       => 'PEK', 'shanghai'      => 'PVG', 'seoul'         => 'ICN',
        'bangkok'       => 'BKK', 'mumbai'        => 'BOM', 'delhi'         => 'DEL',
        'johannesburg'  => 'JNB', 'cape town'     => 'CPT', 'nairobi'       => 'NBO',
        'cairo'         => 'CAI', 'toronto'       => 'YYZ', 'montreal'      => 'YUL',
        'chicago'       => 'ORD', 'miami'         => 'MIA', 'san francisco'  => 'SFO',
        'dallas'        => 'DFW', 'houston'       => 'IAH', 'atlanta'       => 'ATL',
        'mexico city'   => 'MEX', 'sao paulo'     => 'GRU', 'buenos aires'  => 'EZE',
        'doha'          => 'DOH', 'abu dhabi'     => 'AUH', 'riyadh'        => 'RUH',
        'kuala lumpur'  => 'KUL', 'jakarta'       => 'CGK', 'manila'        => 'MNL',
    ];

    public function __construct()
    {
        $this->key = config('services.aviationstack.key');
    }

    public function resolveIataCode(string $input): ?string
    {
        $input = trim($input);

        if (preg_match('/^[A-Za-z]{3}$/', $input)) {
            return strtoupper($input);
        }

        $lower = strtolower($input);

        if (isset($this->airportMap[$lower])) {
            return $this->airportMap[$lower];
        }

        foreach ($this->airportMap as $city => $iata) {
            if (str_contains($lower, $city) || str_contains($city, $lower)) {
                return $iata;
            }
        }

        return null;
    }

    public function searchFlights(
        string  $from,
        string  $to,
        string  $departureDate,
        int     $adults = 1,
        string  $travelClass = 'ECONOMY',
        ?string $returnDate = null
    ): array {
        $fromLocal = "{$departureDate}T00:00";
        $toLocal   = "{$departureDate}T23:59";

        $response = Http::withHeaders([
            'x-rapidapi-host' => $this->host,
            'x-rapidapi-key'  => $this->key,
        ])->get("{$this->baseUrl}/flights/airports/iata/{$from}/{$fromLocal}/{$toLocal}", [
            'withLeg'        => 'true',
            'direction'      => 'Departure',
            'withCancelled'  => 'false',
            'withCodeshared' => 'true',
            'limit'          => 50,
        ]);

        if (!$response->successful()) {
            Log::error('AeroDataBox searchFlights failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Unable to connect to flight API: ' . $response->body());
        }

        $data       = $response->json();
        $departures = $data['departures'] ?? [];

        $filtered = array_filter($departures, fn($f) =>
            strtoupper($f['arrival']['airport']['iata'] ?? '') === strtoupper($to)
        );

        return array_values(array_map(
            fn($f) => $this->normalizeFlight($f, $departureDate, $adults, $travelClass),
            $filtered
        ));
    }

    public function searchAirports(string $keyword): array
    {
        $lower  = strtolower(trim($keyword));
        $result = [];

        foreach ($this->airportMap as $city => $iata) {
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

        return [
            'flight_number'    => $f['number']                        ?? 'N/A',
            'airline'          => $f['airline']['name']               ?? 'Unknown',
            'airline_code'     => $f['airline']['iata']               ?? null,
            'departure_iata'   => $dep['airport']['iata']             ?? null,
            'departure_airport'=> $dep['airport']['name']             ?? ($dep['airport']['iata'] ?? null),
            'departure_time'   => $depRaw ? $this->extractTime($depRaw) : null,
            'departure_date'   => $depRaw ? $this->extractDate($depRaw) : $departureDate,
            'arrival_iata'     => $arr['airport']['iata']             ?? null,
            'arrival_airport'  => $arr['airport']['name']             ?? ($arr['airport']['iata'] ?? null),
            'arrival_time'     => $arrRaw ? $this->extractTime($arrRaw) : null,
            'arrival_date'     => $arrRaw ? $this->extractDate($arrRaw) : null,
            'duration'         => ($depRaw && $arrRaw) ? $this->calcDuration($depRaw, $arrRaw) : null,
            'stops'            => 0,
            'baggage'          => '1 bag included',
            'travel_class'     => $travelClass,
            'adults'           => $adults,
            'price'            => null,
            'currency'         => null,
            'status'           => $f['status'] ?? null,
        ];
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

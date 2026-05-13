<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Contracts\FlightSearchInterface;

class AviationstackService implements FlightSearchInterface
{
    private string $host    = 'aerodatabox.p.rapidapi.com';
    private string $baseUrl = 'https://aerodatabox.p.rapidapi.com';
    private ?string $key;

    private array $airportMap = [
        
        'budapest'        => 'BUD', 'london'          => 'LHR', 'paris'           => 'CDG',
        'amsterdam'       => 'AMS', 'frankfurt'       => 'FRA', 'istanbul'        => 'IST',
        'rome'            => 'FCO', 'madrid'          => 'MAD', 'barcelona'       => 'BCN',
        'vienna'          => 'VIE', 'zurich'          => 'ZRH', 'brussels'        => 'BRU',
        'munich'          => 'MUC', 'berlin'          => 'BER', 'prague'          => 'PRG',
        'warsaw'          => 'WAW', 'athens'          => 'ATH', 'lisbon'          => 'LIS',
        'dublin'          => 'DUB', 'copenhagen'      => 'CPH', 'stockholm'       => 'ARN',
        'oslo'            => 'OSL', 'helsinki'        => 'HEL', 'milan'           => 'MXP',
        'venice'          => 'VCE', 'florence'        => 'FLR', 'naples'          => 'NAP',
        'nice'            => 'NCE', 'lyon'            => 'LYS', 'marseille'       => 'MRS',
        'porto'           => 'OPO', 'seville'         => 'SVQ', 'valencia'        => 'VLC',
        'bilbao'          => 'BIO', 'malaga'          => 'AGP', 'palma'           => 'PMI',
        'edinburgh'       => 'EDI', 'manchester'      => 'MAN', 'birmingham'      => 'BHX',
        'glasgow'         => 'GLA', 'bristol'         => 'BRS', 'gatwick'         => 'LGW',
        'heathrow'        => 'LHR', 'stansted'        => 'STN', 'luton'           => 'LTN',
        'amsterdam schiphol' => 'AMS', 'charles de gaulle' => 'CDG',
        'bucharest'       => 'OTP', 'sofia'           => 'SOF', 'zagreb'          => 'ZAG',
        'belgrade'        => 'BEG', 'sarajevo'        => 'SJJ', 'skopje'          => 'SKP',
        'tirana'          => 'TIA', 'podgorica'       => 'TGD', 'pristina'        => 'PRN',
        'riga'            => 'RIX', 'tallinn'         => 'TLL', 'vilnius'         => 'VNO',
        'minsk'           => 'MSQ', 'kyiv'            => 'KBP', 'kiev'            => 'KBP',
        'lviv'            => 'LWO', 'odessa'          => 'ODS', 'tbilisi'         => 'TBS',
        'yerevan'         => 'EVN', 'baku'            => 'GYD', 'almaty'          => 'ALA',
        'nur-sultan'      => 'NQZ', 'astana'          => 'NQZ', 'tashkent'        => 'TAS',
        'reykjavik'       => 'KEF', 'valletta'        => 'MLA', 'nicosia'         => 'LCA',
        'larnaca'         => 'LCA', 'paphos'          => 'PFO', 'thessaloniki'    => 'SKG',
        'heraklion'       => 'HER', 'rhodes'          => 'RHO', 'corfu'           => 'CFU',
        'split'           => 'SPU', 'dubrovnik'       => 'DBV', 'zadar'           => 'ZAD',
        'bratislava'      => 'BTS', 'krakow'          => 'KRK', 'gdansk'          => 'GDN',
        'wroclaw'         => 'WRO', 'poznan'          => 'POZ', 'katowice'        => 'KTW',
        'budapest'        => 'BUD', 'debrecen'        => 'DEB',
        
        'dubai'           => 'DXB', 'abu dhabi'       => 'AUH', 'doha'            => 'DOH',
        'riyadh'          => 'RUH', 'jeddah'          => 'JED', 'muscat'          => 'MCT',
        'kuwait'          => 'KWI', 'kuwait city'     => 'KWI', 'bahrain'         => 'BAH',
        'amman'           => 'AMM', 'beirut'          => 'BEY', 'tel aviv'        => 'TLV',
        'jerusalem'       => 'TLV', 'tehran'          => 'IKA', 'baghdad'         => 'BGW',
        'erbil'           => 'EBL', 'sharjah'         => 'SHJ', 'ras al khaimah'  => 'RKT',
        
        'singapore'       => 'SIN', 'tokyo'           => 'NRT', 'osaka'           => 'KIX',
        'sydney'          => 'SYD', 'hong kong'       => 'HKG', 'beijing'         => 'PEK',
        'shanghai'        => 'PVG', 'seoul'           => 'ICN', 'bangkok'         => 'BKK',
        'mumbai'          => 'BOM', 'delhi'           => 'DEL', 'kuala lumpur'    => 'KUL',
        'jakarta'         => 'CGK', 'manila'          => 'MNL', 'taipei'          => 'TPE',
        'guangzhou'       => 'CAN', 'shenzhen'        => 'SZX', 'chengdu'         => 'CTU',
        'chongqing'       => 'CKG', 'xian'            => 'XIY', 'hangzhou'        => 'HGH',
        'nanjing'         => 'NKG', 'wuhan'           => 'WUH', 'kunming'         => 'KMG',
        'sanya'           => 'SYX', 'haikou'          => 'HAK', 'urumqi'          => 'URC',
        'nagoya'          => 'NGO', 'sapporo'         => 'CTS', 'fukuoka'         => 'FUK',
        'busan'           => 'PUS', 'jeju'            => 'CJU', 'hanoi'           => 'HAN',
        'ho chi minh'     => 'SGN', 'saigon'          => 'SGN', 'da nang'         => 'DAD',
        'phnom penh'      => 'PNH', 'siem reap'       => 'REP', 'vientiane'       => 'VTE',
        'yangon'          => 'RGN', 'colombo'         => 'CMB', 'dhaka'           => 'DAC',
        'kathmandu'       => 'KTM', 'karachi'         => 'KHI', 'lahore'          => 'LHE',
        'islamabad'       => 'ISB', 'chennai'         => 'MAA', 'bangalore'       => 'BLR',
        'bengaluru'       => 'BLR', 'hyderabad'       => 'HYD', 'kolkata'         => 'CCU',
        'ahmedabad'       => 'AMD', 'pune'            => 'PNQ', 'kochi'           => 'COK',
        'goa'             => 'GOI', 'ulaanbaatar'     => 'ULN', 'male'            => 'MLE',
        'maldives'        => 'MLE', 'phuket'          => 'HKT', 'chiang mai'      => 'CNX',
        'bali'            => 'DPS', 'denpasar'        => 'DPS', 'surabaya'        => 'SUB',
        'medan'           => 'KNO', 'makassar'        => 'UPG', 'cebu'            => 'CEB',
        'macau'           => 'MFM', 'langkawi'        => 'LGK', 'penang'          => 'PEN',
        'kota kinabalu'   => 'BKI', 'kuching'         => 'KCH',
        
        'johannesburg'    => 'JNB', 'cape town'       => 'CPT', 'nairobi'         => 'NBO',
        'cairo'           => 'CAI', 'casablanca'      => 'CMN', 'lagos'           => 'LOS',
        'accra'           => 'ACC', 'addis ababa'     => 'ADD', 'dar es salaam'   => 'DAR',
        'kampala'         => 'EBB', 'kigali'          => 'KGL', 'lusaka'          => 'LUN',
        'harare'          => 'HRE', 'maputo'          => 'MPM', 'antananarivo'    => 'TNR',
        'dakar'           => 'DSS', 'abidjan'         => 'ABJ', 'douala'          => 'DLA',
        'tunis'           => 'TUN', 'algiers'         => 'ALG', 'tripoli'         => 'TIP',
        'khartoum'        => 'KRT', 'mogadishu'       => 'MGQ', 'djibouti'        => 'JIB',
        'libreville'      => 'LBV', 'brazzaville'     => 'BZV', 'kinshasa'        => 'FIH',
        'luanda'          => 'LAD', 'windhoek'        => 'WDH', 'gaborone'        => 'GBE',
        'durban'          => 'DUR', 'port elizabeth'  => 'PLZ', 'bloemfontein'    => 'BFN',
        'mauritius'       => 'MRU', 'reunion'         => 'RUN', 'seychelles'      => 'SEZ',
        
        'new york'        => 'JFK', 'los angeles'     => 'LAX', 'chicago'         => 'ORD',
        'miami'           => 'MIA', 'san francisco'   => 'SFO', 'dallas'          => 'DFW',
        'houston'         => 'IAH', 'atlanta'         => 'ATL', 'toronto'         => 'YYZ',
        'montreal'        => 'YUL', 'vancouver'       => 'YVR', 'calgary'         => 'YYC',
        'ottawa'          => 'YOW', 'mexico city'     => 'MEX', 'sao paulo'       => 'GRU',
        'buenos aires'    => 'EZE', 'bogota'          => 'BOG', 'lima'            => 'LIM',
        'santiago'        => 'SCL', 'rio de janeiro'  => 'GIG', 'brasilia'        => 'BSB',
        'caracas'         => 'CCS', 'quito'           => 'UIO', 'guayaquil'       => 'GYE',
        'la paz'          => 'LPB', 'asuncion'        => 'ASU', 'montevideo'      => 'MVD',
        'havana'          => 'HAV', 'panama city'     => 'PTY', 'san jose'        => 'SJO',
        'guatemala city'  => 'GUA', 'tegucigalpa'     => 'TGU', 'managua'         => 'MGA',
        'san salvador'    => 'SAL', 'santo domingo'   => 'SDQ', 'port au prince'  => 'PAP',
        'kingston'        => 'KIN', 'nassau'          => 'NAS', 'bridgetown'      => 'BGI',
        'boston'          => 'BOS', 'washington'      => 'IAD', 'seattle'         => 'SEA',
        'denver'          => 'DEN', 'phoenix'         => 'PHX', 'las vegas'       => 'LAS',
        'orlando'         => 'MCO', 'tampa'           => 'TPA', 'charlotte'       => 'CLT',
        'detroit'         => 'DTW', 'minneapolis'     => 'MSP', 'portland'        => 'PDX',
        'salt lake city'  => 'SLC', 'san diego'       => 'SAN', 'new orleans'     => 'MSY',
        'memphis'         => 'MEM', 'nashville'       => 'BNA', 'kansas city'     => 'MCI',
        'st louis'        => 'STL', 'pittsburgh'      => 'PIT', 'cleveland'       => 'CLE',
        'cincinnati'      => 'CVG', 'indianapolis'    => 'IND', 'columbus'        => 'CMH',
        'raleigh'         => 'RDU', 'richmond'        => 'RIC', 'baltimore'       => 'BWI',
        'philadelphia'    => 'PHL', 'newark'          => 'EWR', 'jfk'             => 'JFK',
        'lax'             => 'LAX', 'lhr'             => 'LHR', 'cdg'             => 'CDG',
        
        'melbourne'       => 'MEL', 'brisbane'        => 'BNE', 'perth'           => 'PER',
        'auckland'        => 'AKL', 'wellington'      => 'WLG', 'christchurch'    => 'CHC',
        'adelaide'        => 'ADL', 'gold coast'      => 'OOL', 'cairns'          => 'CNS',
        'darwin'          => 'DRW', 'hobart'          => 'HBA', 'nadi'            => 'NAN',
        'suva'            => 'SUV', 'port moresby'    => 'POM', 'honiara'         => 'HIR',
        'noumea'          => 'NOU', 'papeete'         => 'PPT', 'apia'            => 'APW',
    ];

    public function __construct()
    {
        $this->key = config('services.aviationstack.key');
    }

    public function resolveIataCode(string $input): ?string
    {
        $input = trim($input);

        // Direct IATA code
        if (preg_match('/^[A-Za-z]{3}$/', $input)) {
            return strtoupper($input);
        }

        $lower = strtolower($input);
        $lower = preg_replace('/\s*(international|airport|intl|city|centre|center)\s*/', ' ', $lower);
        $lower = preg_replace('/\s+/', ' ', trim($lower));

        // Exact match
        if (isset($this->airportMap[$lower])) {
            return $this->airportMap[$lower];
        }

        // Substring match
        foreach ($this->airportMap as $city => $iata) {
            if (str_contains($lower, $city) || str_contains($city, $lower)) {
                return $iata;
            }
        }

        // Word prefix match
        $words = explode(' ', $lower);
        foreach ($words as $word) {
            if (strlen($word) < 3) continue;
            foreach ($this->airportMap as $city => $iata) {
                if (str_starts_with($city, $word) || str_starts_with($word, $city)) {
                    return $iata;
                }
            }
        }

        // Fuzzy match — handles typos like "Johanessburg" → "johannesburg"
        $bestScore = 0;
        $bestIata  = null;
        foreach ($this->airportMap as $city => $iata) {
            similar_text($lower, $city, $percent);
            if ($percent > $bestScore) {
                $bestScore = $percent;
                $bestIata  = $iata;
            }
        }

        // Only accept if similarity is above 70%
        return $bestScore >= 70 ? $bestIata : null;
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
            throw new \RuntimeException('Flight API key is not configured. Add AVIATIONSTACK_KEY to your .env file.');
        }

        // AeroDataBox requires max 12-hour windows — run two 12h searches and merge
        $windows = [
            ["{$departureDate}T00:00", "{$departureDate}T11:59"],
            ["{$departureDate}T12:00", "{$departureDate}T23:59"],
        ];

        $allDepartures = [];

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
                    'limit'          => 50,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $allDepartures = array_merge($allDepartures, $data['departures'] ?? []);
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

            // Small delay between requests to avoid rate limiting
            usleep(500000);
        }

        if (empty($allDepartures)) {
            throw new \RuntimeException('No flight data returned. The route may not exist or the API limit has been reached.');
        }

        $filtered = array_filter($allDepartures, fn($f) =>
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

        $duration = ($depRaw && $arrRaw) ? $this->calcDuration($depRaw, $arrRaw) : null;

        // Estimate price based on flight duration (AeroDataBox doesn't provide prices)
        $estimatedPrice = $this->estimatePrice($duration, $travelClass);

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
            'duration'         => $duration,
            'stops'            => 0,
            'baggage'          => '1 bag included',
            'travel_class'     => $travelClass,
            'adults'           => $adults,
            'price'            => $estimatedPrice,
            'price_note'       => 'Estimated fare',
            'currency'         => 'USD',
            'status'           => $f['status'] ?? null,
        ];
    }

    private function estimatePrice(string|null $duration, string $travelClass): int
    {
        // Parse duration to minutes
        $minutes = 120; // default 2h
        if ($duration && preg_match('/(\d+)h\s*(\d+)?m?/', $duration, $m)) {
            $minutes = ((int)$m[1]) * 60 + (int)($m[2] ?? 0);
        }

        // Base price: ~$0.12 per minute for economy (realistic airline pricing)
        $base = max(49, (int)($minutes * 0.12));

        // Class multipliers (industry standard)
        $multiplier = match (strtoupper($travelClass)) {
            'PREMIUM_ECONOMY' => 1.8,
            'BUSINESS'        => 3.5,
            'FIRST'           => 6.0,
            default           => 1.0, // ECONOMY
        };

        return (int)round($base * $multiplier);
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
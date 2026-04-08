<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AviationstackService
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

        
        if (preg_match('/^[A-Za-z]{3}$/', $input)) {
            return strtoupper($input);
        }

        
        $lower = strtolower($input);
        $lower = preg_replace('/\s*(international|airport|intl|city|centre|center)\s*/', ' ', $lower);
        $lower = preg_replace('/\s+/', ' ', trim($lower));

        
        if (isset($this->airportMap[$lower])) {
            return $this->airportMap[$lower];
        }

        
        foreach ($this->airportMap as $city => $iata) {
            if (str_contains($lower, $city) || str_contains($city, $lower)) {
                return $iata;
            }
        }

        
        $words = explode(' ', $lower);
        foreach ($words as $word) {
            if (strlen($word) < 3) continue;
            foreach ($this->airportMap as $city => $iata) {
                if (str_starts_with($city, $word) || str_starts_with($word, $city)) {
                    return $iata;
                }
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

        if (!$this->key) {
            throw new \RuntimeException('Flight API key is not configured. Add AVIATIONSTACK_KEY to your .env file.');
        }

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
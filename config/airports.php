<?php

return [

    /*
    |--------------------------------------------------------------------------
    | City → IATA Code Map
    |--------------------------------------------------------------------------
    | Used by AviationstackService to resolve free-text city names to IATA
    | airport codes. Keys are lowercase city/airport names.
    */
    'iata_map' => [

        // ── Europe ───────────────────────────────────────────────────────────
        'budapest'           => 'BUD', 'debrecen'           => 'DEB',
        'london'             => 'LHR', 'heathrow'           => 'LHR',
        'gatwick'            => 'LGW', 'stansted'           => 'STN',
        'luton'              => 'LTN', 'edinburgh'          => 'EDI',
        'manchester'         => 'MAN', 'birmingham'         => 'BHX',
        'glasgow'            => 'GLA', 'bristol'            => 'BRS',
        'paris'              => 'CDG', 'charles de gaulle'  => 'CDG',
        'nice'               => 'NCE', 'lyon'               => 'LYS',
        'marseille'          => 'MRS', 'amsterdam'          => 'AMS',
        'amsterdam schiphol' => 'AMS', 'frankfurt'          => 'FRA',
        'munich'             => 'MUC', 'berlin'             => 'BER',
        'istanbul'           => 'IST', 'rome'               => 'FCO',
        'milan'              => 'MXP', 'venice'             => 'VCE',
        'florence'           => 'FLR', 'naples'             => 'NAP',
        'madrid'             => 'MAD', 'barcelona'          => 'BCN',
        'seville'            => 'SVQ', 'valencia'           => 'VLC',
        'bilbao'             => 'BIO', 'malaga'             => 'AGP',
        'palma'              => 'PMI', 'vienna'             => 'VIE',
        'zurich'             => 'ZRH', 'brussels'           => 'BRU',
        'prague'             => 'PRG', 'warsaw'             => 'WAW',
        'athens'             => 'ATH', 'lisbon'             => 'LIS',
        'porto'              => 'OPO', 'dublin'             => 'DUB',
        'copenhagen'         => 'CPH', 'stockholm'          => 'ARN',
        'oslo'               => 'OSL', 'helsinki'           => 'HEL',
        'reykjavik'          => 'KEF', 'valletta'           => 'MLA',
        'larnaca'            => 'LCA', 'nicosia'            => 'LCA',
        'paphos'             => 'PFO', 'bucharest'          => 'OTP',
        'sofia'              => 'SOF', 'zagreb'             => 'ZAG',
        'belgrade'           => 'BEG', 'sarajevo'           => 'SJJ',
        'skopje'             => 'SKP', 'tirana'             => 'TIA',
        'podgorica'          => 'TGD', 'pristina'           => 'PRN',
        'riga'               => 'RIX', 'tallinn'            => 'TLL',
        'vilnius'            => 'VNO', 'minsk'              => 'MSQ',
        'kyiv'               => 'KBP', 'kiev'               => 'KBP',
        'lviv'               => 'LWO', 'odessa'             => 'ODS',
        'bratislava'         => 'BTS', 'krakow'             => 'KRK',
        'gdansk'             => 'GDN', 'wroclaw'            => 'WRO',
        'poznan'             => 'POZ', 'katowice'           => 'KTW',
        'thessaloniki'       => 'SKG', 'heraklion'          => 'HER',
        'rhodes'             => 'RHO', 'corfu'              => 'CFU',
        'split'              => 'SPU', 'dubrovnik'          => 'DBV',
        'zadar'              => 'ZAD',

        // ── Middle East ───────────────────────────────────────────────────────
        'dubai'              => 'DXB', 'abu dhabi'          => 'AUH',
        'doha'               => 'DOH', 'riyadh'             => 'RUH',
        'jeddah'             => 'JED', 'muscat'             => 'MCT',
        'kuwait city'        => 'KWI', 'kuwait'             => 'KWI',
        'bahrain'            => 'BAH', 'amman'              => 'AMM',
        'beirut'             => 'BEY', 'tel aviv'           => 'TLV',
        'jerusalem'          => 'TLV', 'tehran'             => 'IKA',
        'baghdad'            => 'BGW', 'erbil'              => 'EBL',
        'sharjah'            => 'SHJ', 'ras al khaimah'     => 'RKT',
        'tbilisi'            => 'TBS', 'yerevan'            => 'EVN',
        'baku'               => 'GYD', 'almaty'             => 'ALA',
        'nur-sultan'         => 'NQZ', 'astana'             => 'NQZ',
        'tashkent'           => 'TAS',

        // ── Asia-Pacific ──────────────────────────────────────────────────────
        'singapore'          => 'SIN', 'tokyo'              => 'NRT',
        'osaka'              => 'KIX', 'nagoya'             => 'NGO',
        'sapporo'            => 'CTS', 'fukuoka'            => 'FUK',
        'hong kong'          => 'HKG', 'macau'              => 'MFM',
        'beijing'            => 'PEK', 'shanghai'           => 'PVG',
        'guangzhou'          => 'CAN', 'shenzhen'           => 'SZX',
        'chengdu'            => 'CTU', 'chongqing'          => 'CKG',
        'xian'               => 'XIY', 'hangzhou'           => 'HGH',
        'nanjing'            => 'NKG', 'wuhan'              => 'WUH',
        'kunming'            => 'KMG', 'sanya'              => 'SYX',
        'haikou'             => 'HAK', 'urumqi'             => 'URC',
        'seoul'              => 'ICN', 'busan'              => 'PUS',
        'jeju'               => 'CJU', 'taipei'             => 'TPE',
        'bangkok'            => 'BKK', 'phuket'             => 'HKT',
        'chiang mai'         => 'CNX', 'hanoi'              => 'HAN',
        'ho chi minh'        => 'SGN', 'saigon'             => 'SGN',
        'da nang'            => 'DAD', 'phnom penh'         => 'PNH',
        'siem reap'          => 'REP', 'vientiane'          => 'VTE',
        'yangon'             => 'RGN', 'kuala lumpur'       => 'KUL',
        'penang'             => 'PEN', 'langkawi'           => 'LGK',
        'kota kinabalu'      => 'BKI', 'kuching'            => 'KCH',
        'jakarta'            => 'CGK', 'bali'               => 'DPS',
        'denpasar'           => 'DPS', 'surabaya'           => 'SUB',
        'medan'              => 'KNO', 'makassar'           => 'UPG',
        'manila'             => 'MNL', 'cebu'               => 'CEB',
        'mumbai'             => 'BOM', 'delhi'              => 'DEL',
        'bangalore'          => 'BLR', 'bengaluru'          => 'BLR',
        'hyderabad'          => 'HYD', 'kolkata'            => 'CCU',
        'ahmedabad'          => 'AMD', 'pune'               => 'PNQ',
        'chennai'            => 'MAA', 'kochi'              => 'COK',
        'goa'                => 'GOI', 'dhaka'              => 'DAC',
        'karachi'            => 'KHI', 'lahore'             => 'LHE',
        'islamabad'          => 'ISB', 'colombo'            => 'CMB',
        'kathmandu'          => 'KTM', 'male'               => 'MLE',
        'maldives'           => 'MLE', 'ulaanbaatar'        => 'ULN',
        'sydney'             => 'SYD', 'melbourne'          => 'MEL',
        'brisbane'           => 'BNE', 'perth'              => 'PER',
        'adelaide'           => 'ADL', 'gold coast'         => 'OOL',
        'cairns'             => 'CNS', 'darwin'             => 'DRW',
        'hobart'             => 'HBA', 'auckland'           => 'AKL',
        'wellington'         => 'WLG', 'christchurch'       => 'CHC',
        'nadi'               => 'NAN', 'suva'               => 'SUV',
        'port moresby'       => 'POM', 'honiara'            => 'HIR',
        'noumea'             => 'NOU', 'papeete'            => 'PPT',
        'apia'               => 'APW',

        // ── Africa ────────────────────────────────────────────────────────────
        'johannesburg'       => 'JNB', 'cape town'          => 'CPT',
        'durban'             => 'DUR', 'port elizabeth'     => 'PLZ',
        'bloemfontein'       => 'BFN', 'nairobi'            => 'NBO',
        'cairo'              => 'CAI', 'casablanca'         => 'CMN',
        'lagos'              => 'LOS', 'accra'              => 'ACC',
        'addis ababa'        => 'ADD', 'dar es salaam'      => 'DAR',
        'kampala'            => 'EBB', 'kigali'             => 'KGL',
        'lusaka'             => 'LUN', 'harare'             => 'HRE',
        'maputo'             => 'MPM', 'antananarivo'       => 'TNR',
        'dakar'              => 'DSS', 'abidjan'            => 'ABJ',
        'douala'             => 'DLA', 'tunis'              => 'TUN',
        'algiers'            => 'ALG', 'tripoli'            => 'TIP',
        'khartoum'           => 'KRT', 'mogadishu'          => 'MGQ',
        'djibouti'           => 'JIB', 'libreville'         => 'LBV',
        'brazzaville'        => 'BZV', 'kinshasa'           => 'FIH',
        'luanda'             => 'LAD', 'windhoek'           => 'WDH',
        'gaborone'           => 'GBE', 'mauritius'          => 'MRU',
        'reunion'            => 'RUN', 'seychelles'         => 'SEZ',

        // ── Americas ──────────────────────────────────────────────────────────
        'new york'           => 'JFK', 'jfk'                => 'JFK',
        'newark'             => 'EWR', 'los angeles'        => 'LAX',
        'lax'                => 'LAX', 'chicago'            => 'ORD',
        'miami'              => 'MIA', 'san francisco'      => 'SFO',
        'dallas'             => 'DFW', 'houston'            => 'IAH',
        'atlanta'            => 'ATL', 'boston'             => 'BOS',
        'washington'         => 'IAD', 'seattle'            => 'SEA',
        'denver'             => 'DEN', 'phoenix'            => 'PHX',
        'las vegas'          => 'LAS', 'orlando'            => 'MCO',
        'tampa'              => 'TPA', 'charlotte'          => 'CLT',
        'detroit'            => 'DTW', 'minneapolis'        => 'MSP',
        'portland'           => 'PDX', 'salt lake city'     => 'SLC',
        'san diego'          => 'SAN', 'new orleans'        => 'MSY',
        'memphis'            => 'MEM', 'nashville'          => 'BNA',
        'kansas city'        => 'MCI', 'st louis'           => 'STL',
        'pittsburgh'         => 'PIT', 'cleveland'          => 'CLE',
        'cincinnati'         => 'CVG', 'indianapolis'       => 'IND',
        'columbus'           => 'CMH', 'raleigh'            => 'RDU',
        'richmond'           => 'RIC', 'baltimore'          => 'BWI',
        'philadelphia'       => 'PHL', 'toronto'            => 'YYZ',
        'montreal'           => 'YUL', 'vancouver'          => 'YVR',
        'calgary'            => 'YYC', 'ottawa'             => 'YOW',
        'mexico city'        => 'MEX', 'sao paulo'          => 'GRU',
        'buenos aires'       => 'EZE', 'bogota'             => 'BOG',
        'lima'               => 'LIM', 'santiago'           => 'SCL',
        'rio de janeiro'     => 'GIG', 'brasilia'           => 'BSB',
        'caracas'            => 'CCS', 'quito'              => 'UIO',
        'guayaquil'          => 'GYE', 'la paz'             => 'LPB',
        'asuncion'           => 'ASU', 'montevideo'         => 'MVD',
        'havana'             => 'HAV', 'panama city'        => 'PTY',
        'san jose'           => 'SJO', 'guatemala city'     => 'GUA',
        'tegucigalpa'        => 'TGU', 'managua'            => 'MGA',
        'san salvador'       => 'SAL', 'santo domingo'      => 'SDQ',
        'port au prince'     => 'PAP', 'kingston'           => 'KIN',
        'nassau'             => 'NAS', 'bridgetown'         => 'BGI',
    ],

    /*
    |--------------------------------------------------------------------------
    | Country → Primary Airport Code Map
    |--------------------------------------------------------------------------
    | Used when destination sync passes a country instead of a specific city.
    */
    'country_iata_map' => [
        'australia'            => 'SYD',
        'france'               => 'CDG',
        'indonesia'            => 'DPS',
        'italy'                => 'FCO',
        'japan'                => 'NRT',
        'portugal'             => 'LIS',
        'singapore'            => 'SIN',
        'south africa'         => 'JNB',
        'spain'                => 'MAD',
        'thailand'             => 'BKK',
        'uae'                  => 'DXB',
        'uk'                   => 'LHR',
        'united arab emirates' => 'DXB',
        'united kingdom'       => 'LHR',
        'usa'                  => 'JFK',
        'united states'        => 'JFK',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fuzzy-match minimum similarity threshold (0–100)
    |--------------------------------------------------------------------------
    */
    'fuzzy_threshold' => 70,

    /*
    |--------------------------------------------------------------------------
    | Max results returned from searchAirports()
    |--------------------------------------------------------------------------
    */
    'search_limit' => 50,

    /*
    |--------------------------------------------------------------------------
    | Airport coordinates for estimated fallback itineraries
    |--------------------------------------------------------------------------
    | Only used when the live flight provider has no direct results.
    */
    'coordinates' => [
        'BUD' => [47.4369, 19.2556],
        'LHR' => [51.4700, -0.4543],
        'LGW' => [51.1537, -0.1821],
        'CDG' => [49.0097, 2.5479],
        'AMS' => [52.3105, 4.7683],
        'FRA' => [50.0379, 8.5622],
        'IST' => [41.2753, 28.7519],
        'FCO' => [41.8003, 12.2389],
        'MAD' => [40.4983, -3.5676],
        'BCN' => [41.2974, 2.0833],
        'VIE' => [48.1103, 16.5697],
        'DXB' => [25.2532, 55.3657],
        'DOH' => [25.2731, 51.6081],
        'SIN' => [1.3644, 103.9915],
        'NRT' => [35.7720, 140.3929],
        'BKK' => [13.6900, 100.7501],
        'JNB' => [-26.1337, 28.2420],
        'CPT' => [-33.9715, 18.6021],
        'JFK' => [40.6413, -73.7781],
        'LAX' => [33.9416, -118.4085],
        'MIA' => [25.7959, -80.2870],
        'SFO' => [37.6213, -122.3790],
        'YYZ' => [43.6777, -79.6248],
        'SYD' => [-33.9399, 151.1753],
        'MEL' => [-37.6690, 144.8410],
    ],

];

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Itinerary — Smart Booking</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            background: #fff;
            color: #2c2c2c;
            font-size: 13px;
            line-height: 1.6;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }

        .cover {
            background: #3b1f2b;
            color: #f5e6d3;
            padding: 40px 50px 36px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .cover-left h1 {
            font-size: 28px;
            font-weight: normal;
            letter-spacing: 1px;
            color: #c9a96e;
            margin-bottom: 6px;
        }
        .cover-left p { font-size: 13px; color: #d4c4b0; }
        .cover-right { text-align: right; font-size: 12px; color: #d4c4b0; }
        .cover-right .trip-id { font-size: 16px; color: #c9a96e; font-weight: bold; margin-bottom: 4px; }

        .gold-bar { height: 4px; background: #c9a96e; }

        .summary-strip {
            background: #fff8f2;
            border-bottom: 1px solid #e2d5c7;
            padding: 22px 50px;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
        }
        .sum-item .label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.8px; color: #6b5b4f; margin-bottom: 4px; }
        .sum-item .value { font-size: 15px; color: #3b1f2b; font-weight: bold; }

        .body-wrap { padding: 36px 50px; }

        .section-heading {
            font-size: 15px;
            color: #3b1f2b;
            font-weight: normal;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #c9a96e;
            padding-bottom: 8px;
            margin-bottom: 20px;
            margin-top: 32px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-heading:first-child { margin-top: 0; }
        .section-heading i {
            color: #c9a96e;
            font-size: 14px;
            width: 18px;
            text-align: center;
        }

        .day-card {
            display: flex;
            gap: 20px;
            padding: 16px 0;
            border-bottom: 1px solid #e2d5c7;
        }
        .day-card:last-child { border-bottom: none; }
        .day-num {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #3b1f2b;
            color: #c9a96e;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            flex-shrink: 0;
            line-height: 1;
            text-align: center;
            padding: 0;
        }
        .day-content h4 { color: #3b1f2b; font-size: 14px; margin-bottom: 4px; }
        .day-content p  { color: #6b5b4f; font-size: 12px; line-height: 1.6; }
        .day-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(201,169,110,0.2);
            color: #3b1f2b;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 10px;
            margin-top: 6px;
        }
        .day-badge i { color: #c9a96e; font-size: 10px; }

        .info-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .info-table td { padding: 8px 12px; border-bottom: 1px solid #e2d5c7; }
        .info-table td:first-child { color: #6b5b4f; width: 40%; font-weight: bold; }
        .info-table td:last-child  { color: #2c2c2c; }

        .tips-box {
            background: #fff8f2;
            border: 1px solid #e2d5c7;
            border-left: 4px solid #c9a96e;
            border-radius: 4px;
            padding: 16px 20px;
            margin-top: 8px;
        }
        .tips-box p {
            color: #6b5b4f;
            font-size: 12px;
            line-height: 1.7;
            margin-bottom: 6px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .tips-box p i {
            color: #c9a96e;
            font-size: 12px;
            margin-top: 3px;
            flex-shrink: 0;
            width: 14px;
            text-align: center;
        }

        .pdf-footer {
            background: #3b1f2b;
            color: #d4c4b0;
            padding: 18px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            margin-top: 40px;
        }
        .pdf-footer .brand { color: #c9a96e; font-weight: bold; font-size: 13px; }

        @media print {
            body { font-size: 11px; }
            .cover          { padding: 28px 36px; }
            .summary-strip  { padding: 16px 36px; }
            .body-wrap      { padding: 24px 36px; }
            .pdf-footer     { padding: 14px 36px; }
            .no-print       { display: none !important; }
            .day-card       { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

<div class="cover">
    <div class="cover-left">
        <h1> Smart Booking</h1>
        <p>Personalised Travel Itinerary</p>
        <p style="margin-top:14px;font-size:15px;color:#f5e6d3;">{{ $data['destination'] ?? 'Your Destination' }}</p>
    </div>
    <div class="cover-right">
        <div class="trip-id">{{ $data['itineraryId'] ?? ('TRIP-' . strtoupper(substr(md5(now()), 0, 8))) }}</div>
        <div>Generated: {{ now()->format('d M Y') }}</div>
        <div style="margin-top:8px;">
            Prepared for: <strong style="color:#f5e6d3;">{{ Auth::user()->name ?? 'Traveller' }}</strong>
        </div>
    </div>
</div>
<div class="gold-bar"></div>

<div class="summary-strip">
    <div class="sum-item">
        <div class="label">Destination</div>
        <div class="value">{{ ucfirst($data['destination'] ?? '—') }}</div>
    </div>
    <div class="sum-item">
        <div class="label">Departure</div>
        <div class="value">{{ isset($data['departureDate']) ? \Carbon\Carbon::parse($data['departureDate'])->format('d M Y') : '—' }}</div>
    </div>
    <div class="sum-item">
        <div class="label">Return</div>
        <div class="value">{{ isset($data['returnDate']) ? \Carbon\Carbon::parse($data['returnDate'])->format('d M Y') : '—' }}</div>
    </div>
    <div class="sum-item">
        <div class="label">Travellers</div>
        <div class="value">{{ $data['travelers'] ?? 1 }} person{{ ($data['travelers'] ?? 1) > 1 ? 's' : '' }}</div>
    </div>
    <div class="sum-item">
        <div class="label">Budget</div>
        <div class="value">${{ number_format($data['budget'] ?? 0) }}</div>
    </div>
</div>

<div class="body-wrap">

    <h2 class="section-heading"><i class="fas fa-clipboard-list"></i> Trip Overview</h2>
    <table class="info-table">
        <tr><td>Travel Style</td><td>{{ ucfirst($data['mood'] ?? 'Mixed') }}</td></tr>
        <tr><td>Travel Companions</td><td>{{ ucfirst($data['companion'] ?? 'Solo') }}</td></tr>
        <tr>
            <td>Duration</td>
            <td>
                @if(isset($data['departureDate']) && isset($data['returnDate']))
                    {{ \Carbon\Carbon::parse($data['departureDate'])->diffInDays(\Carbon\Carbon::parse($data['returnDate'])) }} days
                @else
                    —
                @endif
            </td>
        </tr>
        <tr><td>Budget Per Person</td><td>${{ number_format($data['budget'] ?? 0) }}</td></tr>
        @if(!empty($data['requirements']))
            <tr><td>Special Requirements</td><td>{{ $data['requirements'] }}</td></tr>
        @endif
    </table>

    <h2 class="section-heading"><i class="fas fa-calendar-days"></i> Day-by-Day Itinerary</h2>

    @php
        $itineraries = [
            'bali'      => [
                ['title' => 'Arrival in Ubud', 'desc' => 'Arrive at Ngurah Rai Airport. Transfer to villa in Ubud. Traditional Balinese welcome ceremony and sunset dinner.'],
                ['title' => 'Rice Terraces & Temples', 'desc' => 'Morning at Tegallalang Rice Terraces. Visit Tirta Empul temple for water purification ceremony. Afternoon art walk.'],
                ['title' => 'Adventure Day', 'desc' => 'White-water rafting on Ayung River. Afternoon at Sacred Monkey Forest. Evening Kecak fire dance performance.'],
                ['title' => 'Cooking & Culture', 'desc' => 'Balinese cooking class with a local family. Explore Ubud art market and silversmith workshops.'],
                ['title' => 'Beach Transfer', 'desc' => 'Transfer to Seminyak. Afternoon at Seminyak Beach. Sunset cocktails at a beachfront bar.'],
                ['title' => 'Island Exploration', 'desc' => 'Full-day trip to Nusa Penida — snorkelling, Kelingking cliff views, and Crystal Bay beach.'],
                ['title' => 'Spa & Departure', 'desc' => 'Morning traditional Balinese massage. Last-minute shopping at Seminyak boutiques. Airport transfer.'],
            ],
            'kyoto'     => [
                ['title' => 'Arrival in Kyoto', 'desc' => 'Arrive at Kansai Airport. Check into traditional ryokan. Evening stroll through the Gion district.'],
                ['title' => 'Golden Temples', 'desc' => 'Visit Kinkaku-ji (Golden Pavilion) and Ryoan-ji rock garden. Afternoon at Nijo Castle.'],
                ['title' => 'Bamboo Forest', 'desc' => 'Early walk through Arashiyama Bamboo Grove. Visit Tenryu-ji Zen garden. Boat ride on Oi River.'],
                ['title' => 'Geisha Culture', 'desc' => 'Morning at Fushimi Inari Shrine. Afternoon tea ceremony. Evening maiko performance in Gion.'],
                ['title' => 'Traditional Arts', 'desc' => 'Kimono fitting and photoshoot. Nishiki Market food tour. Pottery workshop in the afternoon.'],
                ['title' => 'Day Trip to Nara', 'desc' => 'Visit Todai-ji with its giant bronze Buddha. Hand-feed the free-roaming deer of Nara Park.'],
                ['title' => 'Farewell Kyoto', 'desc' => 'Final temple walk at dawn. Traditional kaiseki farewell dinner. Transfer to Kansai Airport.'],
            ],
            'santorini' => [
                ['title' => 'Arrival in Santorini', 'desc' => 'Arrive at Santorini Airport. Check into caldera-view hotel in Oia. Sunset dinner at a clifftop restaurant.'],
                ['title' => 'Caldera Cruise', 'desc' => 'Full-day catamaran cruise — hot springs, Red Beach, White Beach, and on-board BBQ lunch.'],
                ['title' => 'Wine Tasting', 'desc' => 'Tour of three traditional Santorini wineries. Tasting Assyrtiko with panoramic views over the caldera.'],
                ['title' => 'Ancient Akrotiri', 'desc' => 'Morning at the Akrotiri archaeological excavation. Afternoon relaxing at Perissa Black Sand Beach.'],
                ['title' => 'Villages & Views', 'desc' => 'Hike from Fira to Oia along the caldera rim. Explore the windmills and blue-domed churches of Oia.'],
                ['title' => 'Cooking Class', 'desc' => 'Traditional Greek cooking class — learn moussaka, spanakopita, and baklava. Lunch with your creations.'],
                ['title' => 'Farewell Santorini', 'desc' => 'Final sunrise over the caldera. Souvenir shopping. Transfer to Santorini Airport.'],
            ],
            'paris'     => [
                ['title' => 'Arrival in Paris', 'desc' => 'Arrive at CDG. Check in near the Louvre. Evening walk along the Seine and Notre-Dame exterior.'],
                ['title' => 'Eiffel & Louvre', 'desc' => 'Morning at the Eiffel Tower (book summit tickets in advance). Afternoon at the Louvre Museum.'],
                ['title' => 'Montmartre', 'desc' => 'Explore Sacré-Cœur Basilica and Montmartre artists\' quarter. Afternoon at the Centre Pompidou.'],
                ['title' => 'Versailles Day Trip', 'desc' => 'Full day at the Palace of Versailles — Hall of Mirrors, State Apartments, and the Grand Gardens.'],
                ['title' => 'Art & Fashion', 'desc' => 'Musée d\'Orsay (Impressionism). Shopping in Le Marais. Afternoon pastry and macaron tasting.'],
                ['title' => 'Food Tour', 'desc' => 'French cooking class with a Michelin-trained chef. Cheese and wine evening in the Latin Quarter.'],
                ['title' => 'Au Revoir Paris', 'desc' => 'Morning at Luxembourg Gardens. Final café crème and croissant. Transfer to CDG.'],
            ],
        ];

        $destination = strtolower($data['destination'] ?? 'bali');
        $days = $itineraries[$destination] ?? $itineraries['bali'];

        $mood = strtolower($data['mood'] ?? 'adventurous');
        if ($mood === 'relaxed') {
            $days[2]['desc'] = 'Full-day spa retreat with traditional treatments. Afternoon yoga and meditation session.';
            $days[4]['desc'] = 'Beach relaxation and traditional massage. Sunset mindfulness walk.';
        } elseif ($mood === 'foodie') {
            $days[3]['desc'] = 'Guided street-food market tour. Private cooking masterclass with a local chef.';
            $days[5]['desc'] = 'Winery and vineyard tour with sommelier pairing session. Fine-dining farewell dinner.';
        } elseif ($mood === 'cultural') {
            $days[2]['desc'] = 'Museum deep-dive and guided historical walking tour with a local expert.';
            $days[4]['desc'] = 'Traditional craft workshops and authentic local cultural performance.';
        }

        $budget = (int) ($data['budget'] ?? 2500);

        $moodBadges = [
            'adventurous' => ['icon' => 'fa-mountain',  'label' => 'Adventure'],
            'relaxed'     => ['icon' => 'fa-spa',        'label' => 'Relaxed'],
            'foodie'      => ['icon' => 'fa-utensils',   'label' => 'Foodie'],
            'cultural'    => ['icon' => 'fa-landmark',   'label' => 'Cultural'],
            'romantic'    => ['icon' => 'fa-heart',      'label' => 'Romantic'],
        ];
        $badge = $moodBadges[$mood] ?? ['icon' => 'fa-star', 'label' => 'Mixed'];
    @endphp

    @foreach($days as $index => $day)
        <div class="day-card">
            <div class="day-num">{{ $index + 1 }}</div>
            <div class="day-content">
                <h4>{{ $day['title'] }}</h4>
                <p>{{ $day['desc'] }}
                    @if($budget > 5000) Luxury accommodation and private guide included.
                    @elseif($budget < 1500) Budget-friendly options and group activities selected.
                    @endif
                </p>
                <span class="day-badge">
                    <i class="fas {{ $badge['icon'] }}"></i> {{ $badge['label'] }}
                </span>
            </div>
        </div>
    @endforeach

    <h2 class="section-heading"><i class="fas fa-lightbulb"></i> Practical Tips</h2>
    <div class="tips-box">
        <p><i class="fas fa-list-check"></i> <span><strong>Before you go:</strong> Check visa requirements, travel insurance, and vaccination recommendations for your destination.</span></p>
        <p><i class="fas fa-credit-card"></i> <span><strong>Money:</strong> Notify your bank of your travel dates. Carry a small amount of local currency for markets and taxis.</span></p>
        <p><i class="fas fa-mobile-screen"></i> <span><strong>Connectivity:</strong> Purchase a local SIM or international data plan on arrival. Download offline maps before you leave.</span></p>
        <p><i class="fas fa-cloud-sun"></i> <span><strong>Weather:</strong> Pack layers — mornings and evenings can be cooler even in warm destinations. Check local forecasts before departure.</span></p>
        @if(!empty($data['requirements']))
            <p><i class="fas fa-note-sticky"></i> <span><strong>Your notes:</strong> {{ $data['requirements'] }}</span></p>
        @endif
    </div>

    <h2 class="section-heading"><i class="fas fa-coins"></i> Estimated Budget Breakdown</h2>
    @php
        $total         = $budget;
        $accommodation = round($total * 0.35);
        $flights       = round($total * 0.25);
        $food          = round($total * 0.20);
        $activities    = round($total * 0.12);
        $transport     = round($total * 0.08);
    @endphp
    <table class="info-table">
        <tr><td>Accommodation</td><td>${{ number_format($accommodation) }}</td></tr>
        <tr><td>Flights</td><td>${{ number_format($flights) }}</td></tr>
        <tr><td>Food &amp; Dining</td><td>${{ number_format($food) }}</td></tr>
        <tr><td>Activities &amp; Tours</td><td>${{ number_format($activities) }}</td></tr>
        <tr><td>Local Transport</td><td>${{ number_format($transport) }}</td></tr>
        <tr style="font-weight:bold;background:#fff8f2;">
            <td>Total Estimated</td>
            <td>${{ number_format($total) }} per person</td>
        </tr>
    </table>

</div>

<div class="pdf-footer">
    <div>
        <div class="brand">Smart Booking</div>
        <div>smartbooking.com · support@smartbooking.com</div>
    </div>
    <div style="text-align:right;">
        Generated on {{ now()->format('d M Y \a\t H:i') }}<br>
        Itinerary ID: {{ $data['itineraryId'] ?? 'N/A' }}
    </div>
</div>

<script>
    const params = new URLSearchParams(window.location.search);
    if (params.has('auto_print')) {
        window.onload = () => setTimeout(() => window.print(), 500);
    }
</script>
</body>
</html>

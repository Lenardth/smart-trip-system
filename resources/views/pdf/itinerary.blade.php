@extends('layouts.public')

@section('title', 'Itinerary — Smart Booking')



@section('content')
<div class="cover">
    <div class="cover-left">
        <h1>Smart Booking</h1>
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
        <tr><td>Duration</td>
            <td>@if(isset($data['departureDate']) && isset($data['returnDate'])) {{ \Carbon\Carbon::parse($data['departureDate'])->diffInDays(\Carbon\Carbon::parse($data['returnDate'])) }} days @else — @endif</td>
        </tr>
        <tr><td>Budget Per Person</td><td>${{ number_format($data['budget'] ?? 0) }}</td></tr>
        @if(!empty($data['requirements']))<tr><td>Special Requirements</td><td>{{ $data['requirements'] }}</td></tr>@endif
    </table>

    <h2 class="section-heading"><i class="fas fa-calendar-days"></i> Day-by-Day Itinerary</h2>

    @php
        $itineraries = [
            'bali' => [['title' => 'Arrival in Ubud', 'desc' => 'Arrive at Ngurah Rai Airport. Transfer to villa in Ubud. Traditional Balinese welcome ceremony and sunset dinner.'],
                ['title' => 'Rice Terraces & Temples', 'desc' => 'Morning at Tegallalang Rice Terraces. Visit Tirta Empul temple for water purification ceremony. Afternoon art walk.'],
                ['title' => 'Adventure Day', 'desc' => 'White-water rafting on Ayung River. Afternoon at Sacred Monkey Forest. Evening Kecak fire dance performance.'],
                ['title' => 'Cooking & Culture', 'desc' => 'Balinese cooking class with a local family. Explore Ubud art market and silversmith workshops.'],
                ['title' => 'Beach Transfer', 'desc' => 'Transfer to Seminyak. Afternoon at Seminyak Beach. Sunset cocktails at a beachfront bar.'],
                ['title' => 'Island Exploration', 'desc' => 'Full-day trip to Nusa Penida — snorkelling, Kelingking cliff views, and Crystal Bay beach.'],
                ['title' => 'Spa & Departure', 'desc' => 'Morning traditional Balinese massage. Last-minute shopping at Seminyak boutiques. Airport transfer.']],
            'kyoto' => [['title' => 'Arrival in Kyoto', 'desc' => 'Arrive at Kansai Airport. Check into traditional ryokan. Evening stroll through the Gion district.'],
                ['title' => 'Golden Temples', 'desc' => 'Visit Kinkaku-ji (Golden Pavilion) and Ryoan-ji rock garden. Afternoon at Nijo Castle.'],
                ['title' => 'Bamboo Forest', 'desc' => 'Early walk through Arashiyama Bamboo Grove. Visit Tenryu-ji Zen garden. Boat ride on Oi River.'],
                ['title' => 'Geisha Culture', 'desc' => 'Morning at Fushimi Inari Shrine. Afternoon tea ceremony. Evening maiko performance in Gion.'],
                ['title' => 'Traditional Arts', 'desc' => 'Kimono fitting and photoshoot. Nishiki Market food tour. Pottery workshop in the afternoon.'],
                ['title' => 'Day Trip to Nara', 'desc' => 'Visit Todai-ji with its giant bronze Buddha. Hand-feed the free-roaming deer of Nara Park.'],
                ['title' => 'Farewell Kyoto', 'desc' => 'Final temple walk at dawn. Traditional kaiseki farewell dinner. Transfer to Kansai Airport.']],
            'santorini' => [['title' => 'Arrival in Santorini', 'desc' => 'Arrive at Santorini Airport. Check into caldera-view hotel in Oia. Sunset dinner at a clifftop restaurant.'],
                ['title' => 'Caldera Cruise', 'desc' => 'Full-day catamaran cruise — hot springs, Red Beach, White Beach, and on-board BBQ lunch.'],
                ['title' => 'Wine Tasting', 'desc' => 'Tour of three traditional Santorini wineries. Tasting Assyrtiko with panoramic views over the caldera.'],
                ['title' => 'Ancient Akrotiri', 'desc' => 'Morning at the Akrotiri archaeological excavation. Afternoon relaxing at Perissa Black Sand Beach.'],
                ['title' => 'Villages & Views', 'desc' => 'Hike from Fira to Oia along the caldera rim. Explore the windmills and blue-domed churches of Oia.'],
                ['title' => 'Cooking Class', 'desc' => 'Traditional Greek cooking class — learn moussaka, spanakopita, and baklava. Lunch with your creations.'],
                ['title' => 'Farewell Santorini', 'desc' => 'Final sunrise over the caldera. Souvenir shopping. Transfer to Santorini Airport.']],
            'paris' => [['title' => 'Arrival in Paris', 'desc' => 'Arrive at CDG. Check in near the Louvre. Evening walk along the Seine and Notre-Dame exterior.'],
                ['title' => 'Eiffel & Louvre', 'desc' => 'Morning at the Eiffel Tower (book summit tickets in advance). Afternoon at the Louvre Museum.'],
                ['title' => 'Montmartre', 'desc' => 'Explore Sacré-Cœur Basilica and Montmartre artists\' quarter. Afternoon at the Centre Pompidou.'],
                ['title' => 'Versailles Day Trip', 'desc' => 'Full day at the Palace of Versailles — Hall of Mirrors, State Apartments, and the Grand Gardens.'],
                ['title' => 'Art & Fashion', 'desc' => 'Musée d\'Orsay (Impressionism). Shopping in Le Marais. Afternoon pastry and macaron tasting.'],
                ['title' => 'Food Tour', 'desc' => 'French cooking class with a Michelin-trained chef. Cheese and wine evening in the Latin Quarter.'],
                ['title' => 'Au Revoir Paris', 'desc' => 'Morning at Luxembourg Gardens. Final café crème and croissant. Transfer to CDG.']],
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
        $moodBadges = ['adventurous' => ['icon' => 'fa-mountain', 'label' => 'Adventure'], 'relaxed' => ['icon' => 'fa-spa', 'label' => 'Relaxed'], 'foodie' => ['icon' => 'fa-utensils', 'label' => 'Foodie'], 'cultural' => ['icon' => 'fa-landmark', 'label' => 'Cultural'], 'romantic' => ['icon' => 'fa-heart', 'label' => 'Romantic']];
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
            <span class="day-badge"><i class="fas {{ $badge['icon'] }}"></i> {{ $badge['label'] }}</span>
        </div>
    </div>
    @endforeach

    <h2 class="section-heading"><i class="fas fa-lightbulb"></i> Practical Tips</h2>
    <div class="tips-box">
        <p><i class="fas fa-list-check"></i> <span><strong>Before you go:</strong> Check visa requirements, travel insurance, and vaccination recommendations for your destination.</span></p>
        <p><i class="fas fa-credit-card"></i> <span><strong>Money:</strong> Notify your bank of your travel dates. Carry a small amount of local currency for markets and taxis.</span></p>
        <p><i class="fas fa-mobile-screen"></i> <span><strong>Connectivity:</strong> Purchase a local SIM or international data plan on arrival. Download offline maps before you leave.</span></p>
        <p><i class="fas fa-cloud-sun"></i> <span><strong>Weather:</strong> Pack layers — mornings and evenings can be cooler even in warm destinations. Check local forecasts before departure.</span></p>
        @if(!empty($data['requirements']))<p><i class="fas fa-note-sticky"></i> <span><strong>Your notes:</strong> {{ $data['requirements'] }}</span></p>@endif
    </div>

    <h2 class="section-heading"><i class="fas fa-coins"></i> Estimated Budget Breakdown</h2>
    @php
        $total = $budget;
        $accommodation = round($total * 0.35);
        $flights = round($total * 0.25);
        $food = round($total * 0.20);
        $activities = round($total * 0.12);
        $transport = round($total * 0.08);
    @endphp
    <table class="info-table">
        <tr><td>Accommodation</td><td>${{ number_format($accommodation) }}</td></tr>
        <tr><td>Flights</td><td>${{ number_format($flights) }}</td></tr>
        <tr><td>Food &amp; Dining</td><td>${{ number_format($food) }}</td></tr>
        <tr><td>Activities &amp; Tours</td><td>${{ number_format($activities) }}</td></tr>
        <tr><td>Local Transport</td><td>${{ number_format($transport) }}</td></tr>
        <tr style="font-weight:bold;background:#fff8f2;"><td>Total Estimated</td><td>${{ number_format($total) }} per person</td></tr>
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
@endsection

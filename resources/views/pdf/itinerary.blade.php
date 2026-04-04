<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Smart Booking — Travel Itinerary</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Georgia','Times New Roman',serif; background:#fff; color:#2c2c2c; font-size:13px; line-height:1.6; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
.cover { background:#3b1f2b; color:#f5e6d3; padding:40px 50px 36px; display:flex; justify-content:space-between; align-items:flex-start; }
.cover-left h1 { font-size:28px; font-weight:normal; letter-spacing:1px; color:#c9a96e; margin-bottom:6px; }
.cover-left p { font-size:13px; color:#d4c4b0; }
.cover-right { text-align:right; font-size:12px; color:#d4c4b0; }
.cover-right .trip-id { font-size:16px; color:#c9a96e; font-weight:bold; margin-bottom:4px; }
.gold-bar { height:4px; background:#c9a96e; }
.summary-strip { background:#fff8f2; border-bottom:1px solid #e2d5c7; padding:22px 50px; display:grid; grid-template-columns:repeat(5,1fr); gap:16px; }
.sum-item .label { font-size:10px; text-transform:uppercase; letter-spacing:.8px; color:#6b5b4f; margin-bottom:4px; }
.sum-item .value { font-size:15px; color:#3b1f2b; font-weight:bold; }
.body-wrap { padding:36px 50px; }
.section-heading { font-size:15px; color:#3b1f2b; font-weight:normal; letter-spacing:.5px; border-bottom:2px solid #c9a96e; padding-bottom:8px; margin-bottom:20px; margin-top:32px; display:flex; align-items:center; gap:10px; }
.section-heading:first-child { margin-top:0; }
.section-heading i { color:#c9a96e; font-size:14px; width:18px; text-align:center; }
.day-card { display:flex; gap:20px; padding:16px 0; border-bottom:1px solid #e2d5c7; }
.day-card:last-child { border-bottom:none; }
.day-num { width:44px; height:44px; border-radius:50%; background:#3b1f2b; color:#c9a96e; display:flex; align-items:center; justify-content:center; font-size:16px; font-weight:bold; flex-shrink:0; }
.day-content h4 { color:#3b1f2b; font-size:14px; margin-bottom:4px; }
.day-content p { color:#6b5b4f; font-size:12px; line-height:1.6; }
.day-badge { display:inline-flex; align-items:center; gap:5px; background:rgba(201,169,110,.2); color:#3b1f2b; padding:3px 10px; border-radius:3px; font-size:10px; margin-top:6px; }
.info-table { width:100%; border-collapse:collapse; font-size:13px; }
.info-table td { padding:8px 12px; border-bottom:1px solid #e2d5c7; }
.info-table td:first-child { color:#6b5b4f; width:40%; font-weight:bold; }
.info-table td:last-child { color:#2c2c2c; }
.tips-box { background:#fff8f2; border:1px solid #e2d5c7; border-left:4px solid #c9a96e; border-radius:4px; padding:16px 20px; margin-top:8px; }
.tips-box p { color:#6b5b4f; font-size:12px; line-height:1.7; margin-bottom:6px; display:flex; align-items:flex-start; gap:8px; }
.tips-box p i { color:#c9a96e; font-size:12px; margin-top:3px; flex-shrink:0; width:14px; text-align:center; }
.pdf-footer { background:#3b1f2b; color:#d4c4b0; padding:18px 50px; display:flex; justify-content:space-between; align-items:center; font-size:11px; margin-top:40px; }
.pdf-footer .brand { color:#c9a96e; font-weight:bold; font-size:13px; }
@media print { body { font-size:11px; } .cover { padding:28px 36px; } .summary-strip { padding:16px 36px; } .body-wrap { padding:24px 36px; } .pdf-footer { padding:14px 36px; } .no-print { display:none !important; } .day-card { page-break-inside:avoid; } }
</style>
</head>
<body>

<div class="cover">
    <div class="cover-left">
        <h1>Smart Booking</h1>
        <p>Personalised Travel Itinerary</p>
        <p style="margin-top:14px;font-size:15px;color:#f5e6d3;">{{ $data['destination'] ?? 'Your Destination' }}</p>
    </div>
    <div class="cover-right">
        <div class="trip-id">{{ $data['itineraryId'] ?? ('TRIP-' . strtoupper(substr(md5(now()), 0, 8))) }}</div>
        <div>Generated: {{ now()->format('d M Y') }}</div>
        <div style="margin-top:8px;">Prepared for: <strong style="color:#f5e6d3;">{{ Auth::check() ? Auth::user()->name : 'Traveller' }}</strong></div>
    </div>
</div>
<div class="gold-bar"></div>

<div class="summary-strip">
    <div class="sum-item"><div class="label">Destination</div><div class="value">{{ ucfirst($data['destination'] ?? '—') }}</div></div>
    <div class="sum-item"><div class="label">Departure</div><div class="value">{{ isset($data['departureDate']) ? \Carbon\Carbon::parse($data['departureDate'])->format('d M Y') : '—' }}</div></div>
    <div class="sum-item"><div class="label">Return</div><div class="value">{{ isset($data['returnDate']) ? \Carbon\Carbon::parse($data['returnDate'])->format('d M Y') : '—' }}</div></div>
    <div class="sum-item"><div class="label">Travellers</div><div class="value">{{ $data['travelers'] ?? 1 }} person{{ ($data['travelers'] ?? 1) > 1 ? 's' : '' }}</div></div>
    <div class="sum-item"><div class="label">Budget</div><div class="value">${{ number_format($data['budget'] ?? 0) }}</div></div>
</div>

<div class="body-wrap">
    <h2 class="section-heading">Trip Overview</h2>
    <table class="info-table">
        <tr><td>Travel Style</td><td>{{ ucfirst($data['mood'] ?? 'Mixed') }}</td></tr>
        <tr><td>Travel Companions</td><td>{{ ucfirst($data['companion'] ?? 'Solo') }}</td></tr>
        <tr><td>Duration</td><td>
            @if(isset($data['departureDate']) && isset($data['returnDate']))
                {{ \Carbon\Carbon::parse($data['departureDate'])->diffInDays(\Carbon\Carbon::parse($data['returnDate'])) }} days
            @else — @endif
        </td></tr>
        <tr><td>Budget Per Person</td><td>${{ number_format($data['budget'] ?? 0) }}</td></tr>
        @if(!empty($data['requirements']))<tr><td>Special Requirements</td><td>{{ $data['requirements'] }}</td></tr>@endif
    </table>

    <h2 class="section-heading">Day-by-Day Itinerary</h2>

    @php
        $destination = strtolower($data['destination'] ?? 'bali');
        $mood        = strtolower($data['mood'] ?? 'adventurous');
        $budget      = (int)($data['budget'] ?? 2500);
        $itinerary   = $data['itinerary'] ?? [];
        $moodBadges  = ['adventurous'=>['label'=>'Adventure'],'relaxed'=>['label'=>'Relaxed'],'foodie'=>['label'=>'Foodie'],'cultural'=>['label'=>'Cultural'],'romantic'=>['label'=>'Romantic']];
        $badge       = $moodBadges[$mood] ?? ['label'=>'Mixed'];
    @endphp

    @forelse($itinerary as $day)
    <div class="day-card">
        <div class="day-num">{{ $day['day'] }}</div>
        <div class="day-content">
            <h4>{{ $day['title'] }}</h4>
            <p>{{ $day['desc'] }}</p>
            <span class="day-badge">{{ $badge['label'] }}</span>
        </div>
    </div>
    @empty
    <p style="color:#6b5b4f;padding:20px 0;">No itinerary data available.</p>
    @endforelse

    <h2 class="section-heading">Practical Tips</h2>
    <div class="tips-box">
        <p><strong>Before you go:</strong> Check visa requirements, travel insurance, and vaccination recommendations.</p>
        <p><strong>Money:</strong> Notify your bank of travel dates. Carry local currency for markets and taxis.</p>
        <p><strong>Connectivity:</strong> Purchase a local SIM or international data plan on arrival.</p>
        <p><strong>Weather:</strong> Pack layers — mornings and evenings can be cooler even in warm destinations.</p>
        @if(!empty($data['requirements']))<p><strong>Your notes:</strong> {{ $data['requirements'] }}</p>@endif
    </div>

    <h2 class="section-heading">Estimated Budget Breakdown</h2>
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
        <tr style="font-weight:bold;background:#fff8f2;"><td>Total Estimated</td><td>${{ number_format($total) }} per person</td></tr>
    </table>
</div>

<div class="pdf-footer">
    <div>
        <div class="brand">Smart Booking</div>
        <div>AI-Powered Travel Planning</div>
    </div>
    <div style="text-align:right;">
        Generated {{ now()->format('d M Y \a\t H:i') }}<br>
        ID: {{ $data['itineraryId'] ?? 'N/A' }}
    </div>
</div>

</body>
</html>

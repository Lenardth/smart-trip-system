<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trip Itinerary - Smart Booking</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #c9a96e;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #3b1f2b;
            margin: 0 0 10px;
            font-size: 32px;
        }
        .header p {
            color: #6b5b4f;
            margin: 5px 0;
            font-size: 14px;
        }
        .trip-summary {
            background: #fff8f2;
            border: 2px solid #c9a96e;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .trip-summary h2 {
            color: #3b1f2b;
            margin-top: 0;
            font-size: 20px;
            border-bottom: 1px solid #d4c4b0;
            padding-bottom: 10px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }
        .summary-item {
            margin-bottom: 10px;
        }
        .summary-label {
            color: #6b5b4f;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-value {
            color: #3b1f2b;
            font-size: 16px;
            font-weight: bold;
            margin-top: 3px;
        }
        .itinerary-section {
            margin-top: 30px;
        }
        .itinerary-section h2 {
            color: #3b1f2b;
            font-size: 22px;
            margin-bottom: 20px;
            border-bottom: 2px solid #c9a96e;
            padding-bottom: 10px;
        }
        .day-card {
            background: #fff;
            border-left: 4px solid #c9a96e;
            padding: 15px 20px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .day-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .day-number {
            background: #c9a96e;
            color: #fff;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            margin-right: 15px;
        }
        .day-title {
            color: #3b1f2b;
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        .day-description {
            color: #6b5b4f;
            margin: 10px 0 0 55px;
            line-height: 1.8;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #6b5b4f;
            font-size: 12px;
            border-top: 1px solid #d4c4b0;
            padding-top: 20px;
        }
        .requirements-section {
            background: #fff8f2;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        .requirements-section h3 {
            color: #3b1f2b;
            font-size: 16px;
            margin-top: 0;
        }
        .requirements-section p {
            color: #6b5b4f;
            margin: 5px 0 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🌍 Your Personalized Trip Itinerary</h1>
        <p><strong>Smart Booking</strong> - AI-Powered Travel Planning</p>
        <p>Generated for: {{ $user->name }} | {{ $generatedAt }}</p>
    </div>

    <div class="trip-summary">
        <h2>Trip Summary</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Destination</div>
                <div class="summary-value">{{ $destination }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Travel Mood</div>
                <div class="summary-value">{{ $mood }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Departure Date</div>
                <div class="summary-value">{{ $departureDate }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Return Date</div>
                <div class="summary-value">{{ $returnDate }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Duration</div>
                <div class="summary-value">{{ $duration }} days</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Budget (per person)</div>
                <div class="summary-value">${{ $budget }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Travel Companion</div>
                <div class="summary-value">{{ $companion }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Number of Travelers</div>
                <div class="summary-value">{{ $travelers }}</div>
            </div>
        </div>
    </div>

    @if($requirements !== 'None')
    <div class="requirements-section">
        <h3>Special Requirements</h3>
        <p>{{ $requirements }}</p>
    </div>
    @endif

    <div class="itinerary-section">
        <h2>Day-by-Day Itinerary</h2>

        @foreach($itinerary as $day)
        <div class="day-card">
            <div class="day-header">
                <div class="day-number">{{ $day['day'] }}</div>
                <h3 class="day-title">{{ $day['title'] }}</h3>
            </div>
            <p class="day-description">{{ $day['desc'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="footer">
        <p><strong>Smart Booking</strong> - Your AI-Powered Travel Assistant</p>
        <p>This itinerary was generated based on your preferences and can be customized further.</p>
        <p>© {{ date('Y') }} Smart Booking. All rights reserved.</p>
    </div>
</body>
</html>

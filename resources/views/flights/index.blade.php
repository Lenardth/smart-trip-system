@extends('layouts.public')

@section('title', 'Book Flights — Smart Booking')

@section('content')
{{-- Enhanced Hero Section with Search --}}
<section class="page-hero flights-hero" style="background: linear-gradient(160deg, rgba(5,15,40,0.85) 0%, rgba(20,40,80,0.75) 100%), url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1920&q=90'); background-size: cover; background-position: center; min-height: 550px; display: flex; align-items: center; position: relative; z-index: 1; padding: 120px 20px 80px;">
    <div style="width: 100%; max-width: 950px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 28px;">
            <h1 style="font-size: 40px; margin-bottom: 14px; color: white; text-shadow: 0 2px 8px rgba(0,0,0,0.5), 0 4px 16px rgba(0,0,0,0.3); position: relative; z-index: 10;"><i class="fas fa-plane-departure"></i> Book Your Flight</h1>
            <p style="font-size: 15px; opacity: 0.95; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.4); position: relative; z-index: 10; max-width: 600px; margin: 0 auto;">Search hundreds of airlines. Find the best price for your next journey.</p>
        </div>

        {{-- Search Form in Hero --}}
        <div class="search-card" style="background: rgba(255,255,255,0.98); backdrop-filter: blur(10px); border-radius: 14px; padding: 24px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 100%;">
            <div class="trip-type-tabs" style="display: flex; gap: 6px; margin-bottom: 18px; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;">
                <div class="trip-type-tab active" data-type="round-trip" style="flex: 1; text-align: center; padding: 9px 10px; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-weight: 500; font-size: 13px;">
                    <i class="fas fa-exchange-alt"></i> Round Trip
                </div>
                <div class="trip-type-tab" data-type="one-way" style="flex: 1; text-align: center; padding: 9px 10px; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-weight: 500; font-size: 13px;">
                    <i class="fas fa-arrow-right"></i> One Way
                </div>
                <div class="trip-type-tab" data-type="multi-city" style="flex: 1; text-align: center; padding: 9px 10px; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-weight: 500; font-size: 13px;">
                    <i class="fas fa-map-marked"></i> Multi-City
                </div>
            </div>

            <form id="flightSearchForm">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div class="form-group" style="margin: 0;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #333; font-size: 13px;">
                            <i class="fas fa-plane-departure"></i> From
                        </label>
                        <input type="text" id="from" name="from" placeholder="City or Airport" required
                               style="width: 100%; padding: 11px 13px; border: 2px solid #e0e0e0; border-radius: 7px; font-size: 14px; transition: all 0.2s; font-family: inherit;">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #333; font-size: 13px;">
                            <i class="fas fa-plane-arrival"></i> To
                        </label>
                        <input type="text" id="to" name="to" placeholder="City or Airport" required
                               style="width: 100%; padding: 11px 13px; border: 2px solid #e0e0e0; border-radius: 7px; font-size: 14px; transition: all 0.2s; font-family: inherit;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div class="form-group" style="margin: 0;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #333; font-size: 13px;">
                            <i class="fas fa-calendar-alt"></i> Departure
                        </label>
                        <input type="date" id="departure_date" name="departure_date" required
                               style="width: 100%; padding: 11px 13px; border: 2px solid #e0e0e0; border-radius: 7px; font-size: 14px; transition: all 0.2s; font-family: inherit; cursor: pointer;">
                    </div>
                    <div class="form-group" id="returnDateGroup" style="margin: 0;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #333; font-size: 13px;">
                            <i class="fas fa-calendar-check"></i> Return
                        </label>
                        <input type="date" id="return_date" name="return_date"
                               style="width: 100%; padding: 11px 13px; border: 2px solid #e0e0e0; border-radius: 7px; font-size: 14px; transition: all 0.2s; font-family: inherit; cursor: pointer;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div class="form-group" style="margin: 0;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #333; font-size: 13px;">
                            <i class="fas fa-users"></i> Passengers
                        </label>
                        <input type="number" id="passengers" name="passengers" min="1" max="9" value="1" required
                               style="width: 100%; padding: 11px 13px; border: 2px solid #e0e0e0; border-radius: 7px; font-size: 14px; transition: all 0.2s; font-family: inherit;">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #333; font-size: 13px;">
                            <i class="fas fa-chair"></i> Class
                        </label>
                        <select id="class" name="class" required
                                style="width: 100%; padding: 11px 13px; border: 2px solid #e0e0e0; border-radius: 7px; font-size: 14px; background: white; font-family: inherit; cursor: pointer;">
                            <option value="economy">Economy</option>
                            <option value="premium_economy">Premium Economy</option>
                            <option value="business">Business</option>
                            <option value="first">First Class</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="search-btn"
                        style="width: 100%; padding: 13px 28px; background: linear-gradient(135deg, #d4af37 0%, #c5a028 100%); color: #1a1a1a; border: none; border-radius: 7px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(212,175,55,0.4); font-family: inherit; height: 48px;">
                    <span class="btn-text"><i class="fas fa-search"></i> Search Flights</span>
                    <div class="spinner" style="display: none;"></div>
                </button>
            </form>
        </div>
    </div>
</section>

<div class="flights-container">

    {{-- Results --}}
    <div class="results-section" id="resultsSection" style="display:none;">
        <div class="results-header">
            <h3><i class="fas fa-list"></i> <span id="resultsCount">0</span> Flights Found</h3>
            <div class="sort-filter">
                <label>Sort by:</label>
                <select id="sortBy">
                    <option value="price">Best Price</option>
                    <option value="duration">Shortest Duration</option>
                    <option value="departure">Departure Time</option>
                    <option value="arrival">Arrival Time</option>
                </select>
            </div>
        </div>
        <div id="flightResults"></div>
    </div>

    {{-- Cheap flight suggestions --}}
    <section class="cheap-flights-section">
        <div class="cheap-flights-header">
            <div>
                <h3><i class="fas fa-tag"></i> Cheap Flights This Month</h3>
                <p>Live estimated fares — click any deal to search instantly</p>
            </div>
            <span class="cheap-flights-note"><i class="fas fa-info-circle"></i> Prices update hourly</span>
        </div>
        <div class="cheap-flights-grid">
            @foreach($deals as $deal)
            @php
                $aviationService = app(\App\Services\AviationstackService::class);
                $fromCity = collect($aviationService->searchAirports($deal['from']))->first()['city'] ?? $deal['from'];
                $toCity = collect($aviationService->searchAirports($deal['to']))->first()['city'] ?? $deal['to'];
            @endphp
            <div class="cheap-flight-card" onclick="fillRoute('{{ $fromCity }}', '{{ $toCity }}')">
                <div class="cheap-flight-tag"><i class="fas {{ $deal['icon'] }}"></i> {{ $deal['tag'] }}</div>
                <div class="cheap-flight-route">
                    <div class="cheap-flight-city">
                        <span class="cheap-city-name">{{ $fromCity }}</span>
                        <span class="cheap-city-code">{{ $deal['from'] }}</span>
                    </div>
                    <div class="cheap-flight-arrow">
                        <i class="fas fa-long-arrow-alt-right"></i>
                        <span class="cheap-duration">{{ $deal['duration'] }}</span>
                    </div>
                    <div class="cheap-flight-city cheap-flight-city--right">
                        <span class="cheap-city-name">{{ $toCity }}</span>
                        <span class="cheap-city-code">{{ $deal['to'] }}</span>
                    </div>
                </div>
                <div class="cheap-flight-footer">
                    <span class="cheap-airline"><i class="fas fa-plane-departure"></i> {{ $deal['airline'] }}</span>
                    <span class="cheap-price" data-price-usd="{{ $deal['price'] }}">${{ $deal['price'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- Popular routes --}}
    <div class="popular-routes">
        <h3><i class="fas fa-route"></i> Popular Routes</h3>
        <div class="routes-grid">
            @foreach($popularRoutes as $route)
            @php
                $aviationService = app(\App\Services\AviationstackService::class);
                $fromCity = collect($aviationService->searchAirports($route['from']))->first()['city'] ?? $route['from'];
                $toCity = collect($aviationService->searchAirports($route['to']))->first()['city'] ?? $route['to'];
            @endphp
            <div class="route-card" onclick="fillRoute('{{ $fromCity }}', '{{ $toCity }}')">
                <div class="route-flag"><i class="fas fa-plane"></i></div>
                <div class="route-cities">{{ $fromCity }} <i class="fas fa-long-arrow-alt-right"></i> {{ $toCity }}</div>
                <div class="route-price">from <span data-price-usd="{{ $route['price'] }}">${{ $route['price'] }}</span></div>
                <div class="route-info">
                    <i class="fas fa-clock"></i> {{ $route['duration'] }} &nbsp;
                    <i class="fas {{ $route['direct'] ? 'fa-check-circle' : 'fa-dot-circle' }}"></i> {{ $route['direct'] ? 'Direct' : '1 stop' }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

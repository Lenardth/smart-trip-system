@extends('layouts.public')

@section('title', 'Book Flights — Smart Booking')

@section('content')
<section class="page-hero" style="background: linear-gradient(160deg, rgba(5,15,40,0.78) 0%, rgba(59,31,43,0.55) 100%), url('https://images.unsplash.com/photo-1544551763-77ef2d0cfc6c?w=1920&q=90'); background-size: cover; background-position: center 30%;">
    <div>
        <h1><i class="fas fa-plane-departure"></i> Book Your Flight</h1>
        <p>Search hundreds of airlines. Find the best price for your next journey.</p>
    </div>
</section>

<div class="flights-container">

    {{-- Search card --}}
    <div class="search-card">
        <div class="trip-type-tabs">
            <div class="trip-type-tab active" data-type="round-trip">
                <i class="fas fa-exchange-alt"></i> Round Trip
            </div>
            <div class="trip-type-tab" data-type="one-way">
                <i class="fas fa-arrow-right"></i> One Way
            </div>
            <div class="trip-type-tab" data-type="multi-city">
                <i class="fas fa-map-marked"></i> Multi-City
            </div>
        </div>

        <form id="flightSearchForm">
            <div class="search-grid search-grid-wide">
                <div class="form-group">
                    <label><i class="fas fa-plane-departure"></i> From</label>
                    <input type="text" class="form-input" id="from" name="from" placeholder="City or Airport" required>
                </div>
                <div class="swap-btn-wrap">
                    <button type="button" class="swap-btn" id="swapBtn" title="Swap airports">
                        <i class="fas fa-exchange-alt"></i>
                    </button>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-plane-arrival"></i> To</label>
                    <input type="text" class="form-input" id="to" name="to" placeholder="City or Airport" required>
                </div>
            </div>

            <div class="search-grid" id="dateGrid">
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Departure Date</label>
                    <input type="date" class="form-input" id="departure_date" name="departure_date" required>
                </div>
                <div class="form-group" id="returnDateGroup">
                    <label><i class="fas fa-calendar-check"></i> Return Date</label>
                    <input type="date" class="form-input" id="return_date" name="return_date">
                </div>
            </div>

            <div class="passenger-class-grid">
                <div class="form-group">
                    <label><i class="fas fa-users"></i> Passengers</label>
                    <input type="number" class="form-input" id="passengers" name="passengers" min="1" max="9" value="1" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-chair"></i> Class</label>
                    <select class="form-input" id="class" name="class" required>
                        <option value="economy">Economy</option>
                        <option value="premium_economy">Premium Economy</option>
                        <option value="business">Business</option>
                        <option value="first">First Class</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="search-btn">
                <span class="btn-text"><i class="fas fa-search"></i> Search Flights</span>
                <div class="spinner"></div>
            </button>
        </form>
    </div>

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
                <p>Estimated fares — click any deal to search instantly</p>
            </div>
            <span class="cheap-flights-note"><i class="fas fa-info-circle"></i> Prices may vary</span>
        </div>
        <div class="cheap-flights-grid">
            @php
            $deals = [
                ['from'=>'Johannesburg','to'=>'Cape Town',  'from_code'=>'JNB','to_code'=>'CPT','price'=>'$89', 'duration'=>'2h 00m','airline'=>'FlySafair',   'icon'=>'fa-plane',          'tag'=>'Domestic Deal'],
                ['from'=>'Dubai',       'to'=>'Bangkok',    'from_code'=>'DXB','to_code'=>'BKK','price'=>'$210','duration'=>'6h 30m','airline'=>'Emirates',     'icon'=>'fa-star',           'tag'=>'Popular Route'],
                ['from'=>'London',      'to'=>'Lisbon',     'from_code'=>'LHR','to_code'=>'LIS','price'=>'$95', 'duration'=>'2h 30m','airline'=>'TAP Air',      'icon'=>'fa-fire',           'tag'=>'Hot Deal'],
                ['from'=>'New York',    'to'=>'Cancun',     'from_code'=>'JFK','to_code'=>'CUN','price'=>'$180','duration'=>'4h 15m','airline'=>'JetBlue',      'icon'=>'fa-umbrella-beach', 'tag'=>'Beach Escape'],
                ['from'=>'Singapore',   'to'=>'Bali',       'from_code'=>'SIN','to_code'=>'DPS','price'=>'$95', 'duration'=>'2h 30m','airline'=>'Scoot',        'icon'=>'fa-leaf',           'tag'=>'Weekend Getaway'],
                ['from'=>'Paris',       'to'=>'Barcelona',  'from_code'=>'CDG','to_code'=>'BCN','price'=>'$65', 'duration'=>'1h 55m','airline'=>'Vueling',      'icon'=>'fa-bolt',           'tag'=>'Flash Sale'],
                ['from'=>'Sydney',      'to'=>'Melbourne',  'from_code'=>'SYD','to_code'=>'MEL','price'=>'$79', 'duration'=>'1h 25m','airline'=>'Jetstar',      'icon'=>'fa-plane',          'tag'=>'Domestic Deal'],
                ['from'=>'Nairobi',     'to'=>'Zanzibar',   'from_code'=>'NBO','to_code'=>'ZNZ','price'=>'$120','duration'=>'1h 45m','airline'=>'Kenya Airways', 'icon'=>'fa-sun',           'tag'=>'Island Escape'],
            ];
            @endphp
            @foreach($deals as $deal)
            <div class="cheap-flight-card" onclick="fillRoute('{{ $deal['from'] }}', '{{ $deal['to'] }}')">
                <div class="cheap-flight-tag"><i class="fas {{ $deal['icon'] }}"></i> {{ $deal['tag'] }}</div>
                <div class="cheap-flight-route">
                    <div class="cheap-flight-city">
                        <span class="cheap-city-name">{{ $deal['from'] }}</span>
                        <span class="cheap-city-code">{{ $deal['from_code'] }}</span>
                    </div>
                    <div class="cheap-flight-arrow">
                        <i class="fas fa-long-arrow-alt-right"></i>
                        <span class="cheap-duration">{{ $deal['duration'] }}</span>
                    </div>
                    <div class="cheap-flight-city cheap-flight-city--right">
                        <span class="cheap-city-name">{{ $deal['to'] }}</span>
                        <span class="cheap-city-code">{{ $deal['to_code'] }}</span>
                    </div>
                </div>
                <div class="cheap-flight-footer">
                    <span class="cheap-airline"><i class="fas fa-plane-departure"></i> {{ $deal['airline'] }}</span>
                    <span class="cheap-price" data-price-usd="{{ ltrim($deal['price'], '$') }}">{{ $deal['price'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- Popular routes --}}
    <div class="popular-routes">
        <h3><i class="fas fa-route"></i> Popular Routes</h3>
        <div class="routes-grid">
            <div class="route-card" onclick="fillRoute('New York', 'London')">
                <div class="route-flag"><i class="fas fa-plane"></i></div>
                <div class="route-cities">New York <i class="fas fa-long-arrow-alt-right"></i> London</div>
                <div class="route-price">from <span data-price-usd="450">$450</span></div>
                <div class="route-info"><i class="fas fa-clock"></i> 7h 30m &nbsp;<i class="fas fa-check-circle"></i> Direct</div>
            </div>
            <div class="route-card" onclick="fillRoute('Paris', 'Tokyo')">
                <div class="route-flag"><i class="fas fa-plane"></i></div>
                <div class="route-cities">Paris <i class="fas fa-long-arrow-alt-right"></i> Tokyo</div>
                <div class="route-price">from <span data-price-usd="680">$680</span></div>
                <div class="route-info"><i class="fas fa-clock"></i> 12h 45m &nbsp;<i class="fas fa-dot-circle"></i> 1 stop</div>
            </div>
            <div class="route-card" onclick="fillRoute('Dubai', 'New York')">
                <div class="route-flag"><i class="fas fa-plane"></i></div>
                <div class="route-cities">Dubai <i class="fas fa-long-arrow-alt-right"></i> New York</div>
                <div class="route-price">from <span data-price-usd="550">$550</span></div>
                <div class="route-info"><i class="fas fa-clock"></i> 14h 20m &nbsp;<i class="fas fa-check-circle"></i> Direct</div>
            </div>
            <div class="route-card" onclick="fillRoute('Los Angeles', 'Sydney')">
                <div class="route-flag"><i class="fas fa-plane"></i></div>
                <div class="route-cities">Los Angeles <i class="fas fa-long-arrow-alt-right"></i> Sydney</div>
                <div class="route-price">from <span data-price-usd="720">$720</span></div>
                <div class="route-info"><i class="fas fa-clock"></i> 15h 10m &nbsp;<i class="fas fa-check-circle"></i> Direct</div>
            </div>
            <div class="route-card" onclick="fillRoute('Singapore', 'Bali')">
                <div class="route-flag"><i class="fas fa-plane"></i></div>
                <div class="route-cities">Singapore <i class="fas fa-long-arrow-alt-right"></i> Bali</div>
                <div class="route-price">from <span data-price-usd="180">$180</span></div>
                <div class="route-info"><i class="fas fa-clock"></i> 2h 30m &nbsp;<i class="fas fa-check-circle"></i> Multiple daily</div>
            </div>
            <div class="route-card" onclick="fillRoute('London', 'Dubai')">
                <div class="route-flag"><i class="fas fa-plane"></i></div>
                <div class="route-cities">London <i class="fas fa-long-arrow-alt-right"></i> Dubai</div>
                <div class="route-price">from <span data-price-usd="380">$380</span></div>
                <div class="route-info"><i class="fas fa-clock"></i> 7h 00m &nbsp;<i class="fas fa-check-circle"></i> Direct</div>
            </div>
        </div>
    </div>

</div>
@endsection

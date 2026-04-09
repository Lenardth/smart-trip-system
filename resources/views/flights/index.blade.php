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
    <div class="search-card">
        <h2><i class="fas fa-search"></i> Search Flights</h2>

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

    <div class="results-section" id="resultsSection">
        <div class="results-header">
            <h3><span id="resultsCount">0</span> Flights Found</h3>
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

    <div class="popular-routes">
        <h3>Popular Routes</h3>
        <div class="routes-grid">
            <div class="route-card" onclick="fillRoute('New York', 'London')">
                <div class="route-cities">New York ✈ London</div>
                <div class="route-price">from $450</div>
                <div class="route-info">7h 30m • Direct flights available</div>
            </div>
            <div class="route-card" onclick="fillRoute('Paris', 'Tokyo')">
                <div class="route-cities">Paris ✈ Tokyo</div>
                <div class="route-price">from $680</div>
                <div class="route-info">12h 45m • 1 stop</div>
            </div>
            <div class="route-card" onclick="fillRoute('Dubai', 'New York')">
                <div class="route-cities">Dubai ✈ New York</div>
                <div class="route-price">from $550</div>
                <div class="route-info">14h 20m • Direct flights available</div>
            </div>
            <div class="route-card" onclick="fillRoute('Los Angeles', 'Sydney')">
                <div class="route-cities">Los Angeles ✈ Sydney</div>
                <div class="route-price">from $720</div>
                <div class="route-info">15h 10m • Direct flights available</div>
            </div>
            <div class="route-card" onclick="fillRoute('Singapore', 'Bali')">
                <div class="route-cities">Singapore ✈ Bali</div>
                <div class="route-price">from $180</div>
                <div class="route-info">2h 30m • Multiple daily flights</div>
            </div>
            <div class="route-card" onclick="fillRoute('London', 'Dubai')">
                <div class="route-cities">London ✈ Dubai</div>
                <div class="route-price">from $380</div>
                <div class="route-info">7h 00m • Direct flights available</div>
            </div>
        </div>
    </div>
</div>
@endsection
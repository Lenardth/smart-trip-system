@extends('layouts.public')

@section('title', 'Book Flights - Smart Booking')

@section('content')
<section class="hero hero-with-image hero-pattern"
         data-bg="{{ $heroImage ?? '' }}">
    <div class="hero-search-wrap">
        <div class="hero-search-intro">
            <h1 class="hero-search-title"><i class="fas fa-plane-departure"></i> Book Your Flight</h1>
            <p class="hero-search-subtitle">Search live flight data for your next journey.</p>
        </div>

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
                <div class="hero-form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-plane-departure"></i> From</label>
                        <input type="text" id="from" name="from" class="form-input" placeholder="City or Airport" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-plane-arrival"></i> To</label>
                        <input type="text" id="to" name="to" class="form-input" placeholder="City or Airport" required>
                    </div>
                </div>

                <div class="hero-form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Departure</label>
                        <input type="date" id="departure_date" name="departure_date" class="form-input" required>
                    </div>
                    <div class="form-group" id="returnDateGroup">
                        <label><i class="fas fa-calendar-check"></i> Return</label>
                        <input type="date" id="return_date" name="return_date" class="form-input">
                    </div>
                </div>

                <div class="hero-form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-users"></i> Passengers</label>
                        <input type="number" id="passengers" name="passengers" class="form-input" min="1" max="9" value="1" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-chair"></i> Class</label>
                        <select id="class" name="class" class="form-input" required>
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
    </div>
</section>

<div class="flights-container">
    <div class="results-section" id="resultsSection">
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
</div>
@endsection

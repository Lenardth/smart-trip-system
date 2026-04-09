@extends('layouts.public')

@section('title', 'Accommodations — Smart Booking')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
@endpush

@push('scripts_body')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
@endpush

@section('content')
<section class="page-hero accommodations-hero" style="background: linear-gradient(160deg, rgba(10,25,20,0.72) 0%, rgba(59,31,43,0.50) 100%), url('https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1920&q=90'); background-size: cover; background-position: center;">
    <div>
        <h1><i class="fas fa-hotel"></i> Find Accommodation</h1>
        <p>Handpicked stays for every budget — from boutique hotels to eco-lodges.</p>
    </div>
</section>

<main class="accommodations-wrap">

    <section class="filters">
        <input id="searchInput" type="text" placeholder="Search by city, country, or name">
        <select id="styleSelect">
            <option value="any">Any style</option>
            <option value="hostel">Hostel</option>
            <option value="budget_hotel">Budget Hotel</option>
            <option value="boutique">Boutique</option>
            <option value="resort">Resort</option>
            <option value="villa">Villa</option>
            <option value="airbnb">Airbnb</option>
            <option value="glamping">Glamping</option>
        </select>
        <select id="budgetSelect">
            <option value="any">Any budget</option>
            <option value="backpacker">Backpacker</option>
            <option value="budget">Budget</option>
            <option value="mid">Mid-range</option>
            <option value="premium">Premium</option>
            <option value="luxury">Luxury</option>
        </select>
        <button id="reloadBtn" type="button"><i class="fas fa-search"></i> Search</button>
    </section>

    <section id="aiMatchPanel" class="ai-match" style="display:none;">
        <h3><i class="fas fa-wand-magic-sparkles"></i> AI Accommodation Match</h3>
        <p id="aiMatchSummary"></p>
    </section>

    <section id="locationPanel" class="location-panel" style="display:none;">

        <div class="location-map-wrap">
            <div class="location-panel-header">
                <i class="fas fa-map-marked-alt"></i>
                <span id="mapCityLabel">Location Map</span>
                <span class="location-panel-sub" id="mapAccomCount"></span>
            </div>
            <div id="accommodationsMap"></div>
            <p class="map-hint"><i class="fas fa-info-circle"></i> Pins show accommodations from your search. Click a pin for details.</p>
        </div>

        <div class="location-news-wrap classic-news">
            <div class="classic-news-header">
                <div class="classic-news-rule"></div>
                <div class="classic-news-title-row">
                    <i class="fas fa-newspaper"></i>
                    <span id="newsCityLabel">Local Dispatch</span>
                </div>
                <div class="classic-news-rule"></div>
                <div class="classic-news-dateline">
                    <span id="newsDateline"></span>
                    <a id="newsMoreLink" href="#" target="_blank" rel="noopener" class="classic-more-link" style="display:none;">
                        Full Coverage <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div id="newsLoading" class="news-loading" style="display:none;">
                <div class="news-spinner"></div>
                <span>Fetching latest dispatches…</span>
            </div>

            <div id="newsError" class="news-error" style="display:none;">
                <i class="fas fa-exclamation-circle"></i>
                <span id="newsErrorMsg">Could not load news.</span>
            </div>

            <div id="newsFeed" class="news-feed"></div>

            <div id="newsEmpty" class="news-empty" style="display:none;">
                <i class="fas fa-satellite-dish"></i>
                <p>No recent dispatches found for this location.</p>
            </div>

            <p class="classic-news-footer">
                <span class="classic-rule-short"></span>
                Sourced from GNews &mdash; real-time headlines
                <span class="classic-rule-short"></span>
            </p>
        </div>

    </section>

    <section id="accommodationsGrid" class="grid"></section>
    <div id="emptyState" class="empty" style="display:none;">No accommodations found.</div>

</main>
@endsection
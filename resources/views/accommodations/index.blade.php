@extends('layouts.public')

@section('title', 'Accommodations — Smart Booking')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
@endpush

@push('scripts_body')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
@endpush

@section('content')
{{-- Enhanced Hero Section with Search --}}
<section class="hero hero-with-image hero-pattern" 
         data-bg="{{ $heroImage ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1920&q=80' }}">
    <div class="hero-search-wrap hero-search-wrap--wide">
        <div class="hero-search-intro">
            <h1 class="hero-search-title"><i class="fas fa-hotel"></i> Find Your Perfect Stay</h1>
            <p class="hero-search-subtitle">Handpicked accommodations for every budget — from boutique hotels to eco-lodges.</p>
        </div>

        <div class="search-card">
            <form id="accommodationSearchForm">
                <div class="hero-form-grid hero-form-grid--stay">
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Where to?</label>
                        <input id="searchInput" type="text" class="form-input" placeholder="City, country, or hotel name">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-building"></i> Style</label>
                        <select id="styleSelect" class="form-input">
                            <option value="any">Any style</option>
                            <option value="hostel">Hostel</option>
                            <option value="budget_hotel">Budget Hotel</option>
                            <option value="boutique">Boutique</option>
                            <option value="resort">Resort</option>
                            <option value="villa">Villa</option>
                            <option value="airbnb">Airbnb</option>
                            <option value="glamping">Glamping</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-wallet"></i> Budget</label>
                        <select id="budgetSelect" class="form-input">
                            <option value="any">Any budget</option>
                            <option value="backpacker">Backpacker</option>
                            <option value="budget">Budget</option>
                            <option value="mid">Mid-range</option>
                            <option value="premium">Premium</option>
                            <option value="luxury">Luxury</option>
                        </select>
                    </div>
                    <button id="reloadBtn" type="button" class="hero-search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<main class="accommodations-wrap">

    {{-- ── Smart Recommendations (shown before search) ── --}}
    <section id="recommendationsPanel" class="recommendations-panel">
        <div class="rec-header">
            <div class="rec-title-row">
                <i class="fas fa-fire"></i>
                <span>Trending Right Now</span>
                <span class="rec-subtitle">Popular destinations travellers are searching today</span>
            </div>
            <button class="rec-shuffle-btn" id="recShuffleBtn" data-action="shuffleRecommendations">
                <i class="fas fa-random"></i> Shuffle
            </button>
        </div>
        <div class="rec-chips" id="recChips"></div>
        <div class="rec-fun-fact" id="recFunFact"></div>
    </section>

    <section id="aiMatchPanel" class="ai-match">
        <h3><i class="fas fa-wand-magic-sparkles"></i> AI Accommodation Match</h3>
        <p id="aiMatchSummary"></p>
    </section>

    <section id="locationPanel" class="location-panel">

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
                    <a id="newsMoreLink" href="#" target="_blank" rel="noopener" class="classic-more-link">
                        Full Coverage <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div id="newsLoading" class="news-loading">
                <div class="news-spinner"></div>
                <span>Fetching latest dispatches…</span>
            </div>

            <div id="newsError" class="news-error">
                <i class="fas fa-exclamation-circle"></i>
                <span id="newsErrorMsg">Could not load news.</span>
            </div>

            <div id="newsFeed" class="news-feed"></div>

            <div id="newsEmpty" class="news-empty">
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

    <div id="travelWarningBanner" class="travel-warning-banner hidden"></div>
    <div id="travelAdvisoryContainer" class="travel-advisory-container hidden"></div>

    <section id="accommodationsGrid" class="grid"></section>
    <div id="emptyState" class="empty">No accommodations found.</div>

</main>
@endsection
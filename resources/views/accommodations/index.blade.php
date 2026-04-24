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
<section class="page-hero accommodations-hero" style="background: linear-gradient(160deg, rgba(10,35,30,0.85) 0%, rgba(30,60,50,0.75) 100%), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1920&q=90'); background-size: cover; background-position: center; min-height: 450px; display: flex; align-items: center; position: relative; z-index: 1; padding-top: 100px;">
    <div style="width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 24px;">
        <div style="text-align: center; margin-bottom: 40px;">
            <h1 style="font-size: 48px; margin-bottom: 16px; color: white; text-shadow: 0 2px 8px rgba(0,0,0,0.5), 0 4px 16px rgba(0,0,0,0.3); position: relative; z-index: 10;"><i class="fas fa-hotel"></i> Find Your Perfect Stay</h1>
            <p style="font-size: 15px; opacity: 0.95; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.4); position: relative; z-index: 10; max-width: 600px; margin: 0 auto;">Handpicked accommodations for every budget — from boutique hotels to eco-lodges.</p>
        </div>

        {{-- Search Form in Hero --}}
        <div class="search-card" style="background: rgba(255,255,255,0.98); backdrop-filter: blur(10px); border-radius: 16px; padding: 32px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 100%;">
            <form id="accommodationSearchForm">
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 16px; align-items: end;">
                    <div class="form-group" style="margin: 0;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 14px;">
                            <i class="fas fa-map-marker-alt"></i> Where to?
                        </label>
                        <input id="searchInput" type="text" placeholder="City, country, or hotel name" 
                               style="width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: all 0.2s; font-family: inherit;">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 14px;">
                            <i class="fas fa-building"></i> Style
                        </label>
                        <select id="styleSelect" style="width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; background: white; font-family: inherit; cursor: pointer;">
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
                    <div class="form-group" style="margin: 0;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 14px;">
                            <i class="fas fa-wallet"></i> Budget
                        </label>
                        <select id="budgetSelect" style="width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; background: white; font-family: inherit; cursor: pointer;">
                            <option value="any">Any budget</option>
                            <option value="backpacker">Backpacker</option>
                            <option value="budget">Budget</option>
                            <option value="mid">Mid-range</option>
                            <option value="premium">Premium</option>
                            <option value="luxury">Luxury</option>
                        </select>
                    </div>
                    <button id="reloadBtn" type="button" 
                            style="padding: 14px 32px; background: linear-gradient(135deg, #d4af37 0%, #c5a028 100%); color: #1a1a1a; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(212,175,55,0.4); white-space: nowrap; font-family: inherit; height: 50px;">
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
            <button class="rec-shuffle-btn" id="recShuffleBtn" onclick="shuffleRecommendations()">
                <i class="fas fa-random"></i> Shuffle
            </button>
        </div>
        <div class="rec-chips" id="recChips"></div>
        <div class="rec-fun-fact" id="recFunFact"></div>
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

    <div id="travelWarningBanner" style="display:none;margin:0 0 20px;"></div>
    <div id="travelAdvisoryContainer" style="display:none;margin:0 0 24px;"></div>

    <section id="accommodationsGrid" class="grid"></section>
    <div id="emptyState" class="empty" style="display:none;">No accommodations found.</div>

</main>
@endsection
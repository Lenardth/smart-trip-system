@extends('layouts.public')

@section('title', 'Discover — Smart Booking')

@push('styles')
    @vite(['resources/css/blade/discover/index.css'])
@endpush

@push('scripts_body')
    @vite(['resources/js/blade/discover/index.js'])
@endpush

@section('content')
<section class="page-hero" style="background: linear-gradient(rgba(30, 15, 20, .6), rgba(30, 15, 20, .6)), url('https://images.pexels.com/photos/414171/pexels-photo-414171.jpeg'); background-size: cover; background-position: center;">
    <div>
        <h1><i class="fas fa-compass"></i> Discover</h1>
        <p>Explore trending destinations, hidden gems, and AI-curated picks.</p>
        <div class="hero-search">
            <input type="text" id="searchInput" placeholder="Search destinations, countries, experiences…" autocomplete="off">
            <button id="searchBtn"><i class="fas fa-search"></i> Search</button>
        </div>
    </div>
</section>

<div class="discover-wrap">
    <div class="filter-tabs" id="filterTabs">
        <span class="filter-tab active" data-category="all">All</span>
        <span class="filter-tab" data-category="trending">Trending</span>
        <span class="filter-tab" data-category="ai_pick">AI Picks</span>
        <span class="filter-tab" data-category="beach">Beach</span>
        <span class="filter-tab" data-category="mountain">Mountain</span>
        <span class="filter-tab" data-category="historical">Historical</span>
        <span class="filter-tab" data-category="food_culture">Food &amp; Culture</span>
        <span class="filter-tab" data-category="eco_tourism">Eco-Tourism</span>
    </div>

    <div class="region-row" id="regionRow">
        <div class="region-pill active" data-region="all"><i class="fas fa-globe-americas"></i> Worldwide</div>
        <div class="region-pill" data-region="asia"><i class="fas fa-globe-asia"></i> Asia</div>
        <div class="region-pill" data-region="europe"><i class="fas fa-globe-europe"></i> Europe</div>
        <div class="region-pill" data-region="america"><i class="fas fa-globe-americas"></i> America</div>
        <div class="region-pill" data-region="africa"><i class="fas fa-globe-africa"></i> Africa</div>
        <div class="region-pill" data-region="oceania"><i class="fas fa-globe-asia"></i> Oceania</div>
    </div>

    <div class="results-info" id="resultsInfo"></div>

    <div class="destinations-grid" id="destinationsGrid">
        @for ($i = 0; $i < 6; $i++)
        <div class="destination-card">
            <div class="destination-image skeleton"></div>
            <div class="destination-content">
                <div class="sk-line medium skeleton"></div>
                <div class="sk-line short skeleton"></div>
                <div class="sk-line full skeleton"></div>
                <div class="sk-line full skeleton"></div>
                <div class="sk-line medium skeleton" style="margin-top:10px;height:36px;border-radius:4px;"></div>
            </div>
        </div>
        @endfor
    </div>

    <div class="featured-section">
        <h2 class="section-title">Hidden Gems</h2>
        <p style="color:var(--text-sub);font-size:15px;margin-top:0;">
            Destinations our AI found that most travelers overlook — but love once they visit.
        </p>
        <div class="featured-grid" id="hiddenGemsGrid">
            @for ($i = 0; $i < 3; $i++)
            <div class="featured-card">
                <div class="feat-img skeleton"></div>
                <div class="feat-body">
                    <div class="sk-line medium skeleton" style="background:rgba(255,255,255,.15);"></div>
                    <div class="sk-line full skeleton" style="background:rgba(255,255,255,.1); margin-top:6px;"></div>
                </div>
            </div>
            @endfor
        </div>
    </div>
</div>

<div class="toast" id="toast">
    <i class="fas fa-info-circle"></i>
    <span id="toastMsg"></span>
</div>
@endsection

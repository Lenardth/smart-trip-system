@extends('layouts.public')

@section('title', 'Discover — Smart Booking')

@section('content')

{{-- Hero --}}
<section class="hero hero-with-image hero-pattern"
         data-bg="{{ $heroImage ?? 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=1920&q=80' }}">
    <div class="hero-content">
        <h1 class="hero-title">
            <i class="fas fa-compass"></i> Discover Your Next Adventure
        </h1>
        <p class="hero-subtitle">
            Explore handpicked destinations from around the world. Search by city, country, or travel mood to find your perfect getaway.
        </p>

        <div class="hero-search-bar">
            <form id="discoverSearchForm" class="discover-search-form" autocomplete="off">
                <div class="search-input-group">
                    <i class="fas fa-search search-icon"></i>
                    <input
                        type="text"
                        id="discoverSearchInput"
                        name="search"
                        placeholder="Search destinations, countries, or regions…"
                        autocomplete="off"
                    >
                </div>

                <div class="search-filters">
                    <div class="filter-wrapper">
                        <span class="filter-label">Country</span>
                        <select id="discoverRegionFilter" name="region" class="filter-select">
                            <option value="">All Countries</option>
                            @foreach($countries as $country)
                                <option value="{{ $country['code'] }}">{{ $country['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-wrapper">
                        <span class="filter-label">Mood</span>
                        <select id="discoverMoodFilter" name="mood" class="filter-select">
                            <option value="">All Moods</option>
                            @foreach($moodCategories as $cat)
                                <option value="{{ $cat->name }}">{{ preg_replace('/\s*[—–-]\s*/u', ' ', $cat->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary search-btn" id="discoverSearchBtn">
                        <span class="btn-text"><i class="fas fa-search"></i> Search</span>
                        <span class="btn-spinner hidden"><i class="fas fa-spinner fa-spin"></i> Searching…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

{{-- Main Content --}}
<div class="section">
    <div class="section-container">

        {{-- Results info bar --}}
        <div id="discoverResultsInfo" class="search-results-info hidden"></div>

        {{-- Section header (shown on default/no-search state) --}}
        <div id="discoverSectionHeader" class="section-header">
            <span class="section-badge">
                <i class="fas fa-star"></i> Featured Destinations
            </span>
            <h2 class="section-title">Handpicked destinations to inspire you</h2>
            <p class="section-description">
                Use the search above to explore any destination, country, or travel mood.
            </p>
        </div>

        {{-- Destinations grid --}}
        <div id="discoverGrid" class="grid grid-auto-fit">
            <div class="discover-loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading destinations…</p>
            </div>
        </div>

        {{-- Empty state --}}
        <div id="discoverEmpty" class="empty-state hidden">
            <i class="fas fa-compass empty-state-icon"></i>
            <h3 class="empty-state-title">No destinations found</h3>
            <p class="empty-state-text" id="discoverEmptyText">
                Try a different search term, country, or mood.
            </p>
            <button class="btn btn-primary" id="discoverClearBtn">
                <i class="fas fa-times"></i> Clear search
            </button>
        </div>

        {{-- Browse by Mood --}}
        @if($moodCategories->isNotEmpty())
        <div class="mood-section" id="discoverMoodSection">
            <div class="section-header">
                <h2 class="section-title">Browse by Mood</h2>
                <p class="section-description">Find destinations that match your travel style</p>
            </div>
            <div class="grid grid-cols-3">
                @foreach($moodCategories as $mood)
                <button type="button" class="card mood-category-card"
                        data-mood="{{ $mood->name }}"
                        id="moodBtn_{{ $loop->index }}">
                    <div class="card-content mood-card-content">
                        <div class="mood-icon-wrap"
                             data-mood-bg="{{ $mood->gradient }}"
                             data-mood-color="{{ $mood->color }}">
                            <i class="fas fa-{{ $mood->icon }}"></i>
                        </div>
                        <h3 class="card-title">{{ $mood->name }}</h3>
                        <p class="card-description">{{ $mood->description }}</p>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
        @endif

        {{-- CTA --}}
        <div class="discover-cta">
            <i class="fas fa-magic discover-cta-icon"></i>
            <h2 class="discover-cta-title">Not Sure Where to Go?</h2>
            <p class="discover-cta-text">
                Let our AI suggest the perfect destination based on your mood and preferences.
            </p>
            <a href="{{ route('plan-trip') }}" class="btn btn-lg btn-cta-white">
                <i class="fas fa-route"></i> Start Planning Your Trip
            </a>
        </div>

    </div>
</div>

@endsection

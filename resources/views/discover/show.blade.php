@extends('layouts.public')

@section('title', $destination->name . ' — Destination details')

@section('content')

{{-- Hero --}}
<section class="hero hero-with-image hero-pattern"
         data-bg="{{ $heroImage ?? 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1920&q=80' }}">
    <div class="hero-content">
        <h1 class="hero-title">
            @if(!empty($details['flag']))
                <img src="{{ $details['flag'] }}" alt="" class="hero-flag">
            @endif
            {{ $destination->name }}
        </h1>
        <p class="hero-subtitle">
            {{ $details['region'] ?? '' }}{{ !empty($details['subregion']) ? ' · ' . $details['subregion'] : '' }}
            {{ !empty($details['name']) && $details['name'] !== $destination->name ? ' · ' . $details['name'] : '' }}
        </p>

        <div class="hero-stat-pills">
            @if(!empty($details['capital']))
            <div class="hero-stat-pill">
                <i class="fas fa-city"></i>
                <span>{{ $details['capital'] }}</span>
            </div>
            @endif
            @if(!empty($details['currency']))
            <div class="hero-stat-pill">
                <i class="fas fa-coins"></i>
                <span>{{ $details['currency'] }}</span>
            </div>
            @endif
            @if(!empty($details['population']))
            <div class="hero-stat-pill">
                <i class="fas fa-users"></i>
                <span>{{ number_format($details['population']) }}</span>
            </div>
            @endif
            @if(!empty($details['languages']))
            <div class="hero-stat-pill">
                <i class="fas fa-language"></i>
                <span>{{ $details['languages'] }}</span>
            </div>
            @endif
        </div>

        <div class="hero-actions">
            <a href="{{ route('plan-trip') }}?destination={{ urlencode($destination->name) }}&country={{ urlencode($destination->country ?? '') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-route"></i> Plan a Trip Here
            </a>
            <a href="{{ route('flights.index') }}?destination={{ urlencode($destination->name) }}&country={{ urlencode($destination->country ?? '') }}" class="btn btn-secondary">
                <i class="fas fa-plane"></i> Find Flights
            </a>
            <a href="{{ route('accommodations.index') }}?destination={{ urlencode($destination->name) }}&country={{ urlencode($destination->country ?? '') }}" class="btn btn-secondary">
                <i class="fas fa-hotel"></i> Find Stays
            </a>
            <a href="{{ route('discover') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Discover
            </a>
        </div>
    </div>
</section>

{{-- Content --}}
<div class="section">
    <div class="section-container">

        {{-- About / Why Visit --}}
        @if(!empty($tourism['extract']) || !empty($tourism['description']))
        <div class="show-about-section">
            <div class="section-header">
                <span class="section-badge"><i class="fas fa-globe"></i> About the Destination</span>
                <h2 class="section-title">Why visit {{ $destination->name }}?</h2>
            </div>

            <div class="show-about-grid">
                <div class="show-about-text">
                    @if(!empty($tourism['description']))
                    <p class="show-about-lead">{{ $tourism['description'] }}</p>
                    @endif
                    @if(!empty($tourism['extract']))
                    <p class="show-about-body">{{ $tourism['extract'] }}</p>
                    @endif
                </div>

                @if(count($highlights))
                <div class="show-highlights">
                    <h3 class="show-highlights-title">
                        <i class="fas fa-bookmark"></i> Travel Highlights
                    </h3>
                    <ul class="show-highlights-list">
                        @foreach($highlights as $highlight)
                        <li class="show-highlight-item">
                            <i class="fas fa-check-circle"></i>
                            <span>{{ $highlight }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Destination details card --}}
        <div class="show-details-section">
            <div class="section-header">
                <h2 class="section-title">Destination overview</h2>
                <p class="section-description">Key information to help you plan your visit</p>
            </div>

            <div class="show-details-grid">
                <div class="show-detail-card">
                    <div class="show-detail-icon">
                        <i class="fas fa-map-pin"></i>
                    </div>
                    <div class="show-detail-body">
                        <p class="show-detail-label">Place</p>
                        <p class="show-detail-value">{{ $destination->name }}</p>
                    </div>
                </div>

                <div class="show-detail-card">
                    <div class="show-detail-icon">
                        <i class="fas fa-flag"></i>
                    </div>
                    <div class="show-detail-body">
                        <p class="show-detail-label">Country</p>
                        <p class="show-detail-value">{{ $destination->country ?? $details['name'] ?? 'Unknown' }}</p>
                    </div>
                </div>

                <div class="show-detail-card">
                    <div class="show-detail-icon">
                        <i class="fas fa-globe-africa"></i>
                    </div>
                    <div class="show-detail-body">
                        <p class="show-detail-label">Region</p>
                        <p class="show-detail-value">{{ $destination->region ?? $details['region'] ?? 'Global' }}</p>
                    </div>
                </div>

                @if(!empty($details['capital']))
                <div class="show-detail-card">
                    <div class="show-detail-icon">
                        <i class="fas fa-city"></i>
                    </div>
                    <div class="show-detail-body">
                        <p class="show-detail-label">Capital</p>
                        <p class="show-detail-value">{{ $details['capital'] }}</p>
                    </div>
                </div>
                @endif

                @if(!empty($details['currency']))
                <div class="show-detail-card">
                    <div class="show-detail-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="show-detail-body">
                        <p class="show-detail-label">Currency</p>
                        <p class="show-detail-value">{{ $details['currency'] }}</p>
                    </div>
                </div>
                @endif

                @if(!empty($details['languages']))
                <div class="show-detail-card">
                    <div class="show-detail-icon">
                        <i class="fas fa-language"></i>
                    </div>
                    <div class="show-detail-body">
                        <p class="show-detail-label">Languages</p>
                        <p class="show-detail-value">{{ $details['languages'] }}</p>
                    </div>
                </div>
                @endif

                @if(!empty($details['population']))
                <div class="show-detail-card">
                    <div class="show-detail-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="show-detail-body">
                        <p class="show-detail-label">Population</p>
                        <p class="show-detail-value">{{ number_format($details['population']) }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Plan CTA --}}
        <div class="discover-cta">
            <i class="fas fa-route discover-cta-icon"></i>
            <h2 class="discover-cta-title">Ready to visit {{ $destination->name }}?</h2>
            <p class="discover-cta-text">
                Start building your itinerary with personalised flight and accommodation recommendations.
            </p>
            <a href="{{ route('plan-trip') }}?destination={{ urlencode($destination->name) }}&country={{ urlencode($destination->country ?? '') }}" class="btn btn-lg btn-cta-white">
                <i class="fas fa-magic"></i> Plan My Trip
            </a>
        </div>

    </div>
</div>

@endsection

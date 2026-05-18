@extends('layouts.public')

@section('title', $destination->name . ' — Destination details')

@section('content')
<section class="hero hero-with-image hero-pattern"
         style="background-image: url('{{ $heroImage ?? 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1920&q=80' }}');">
    <div class="hero-content">
        <h1 class="hero-title">
            <i class="fas fa-map-pin"></i> {{ $destination->name }}
        </h1>
        <p class="hero-subtitle">
            Discover what makes this destination unique and the best experiences to enjoy in {{ $destination->country ?? $details['name'] ?? 'this region' }}.
        </p>
        <div class="hero-actions">
            <a href="{{ route('discover') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Discover
            </a>
        </div>
    </div>
</section>

<div class="section">
    <div class="section-container">
        <div class="section-header">
            <h2 class="section-title">Country snapshot</h2>
            <p class="section-description">
                Essential facts and travel inspiration for {{ $details['name'] ?? $destination->country ?? 'the destination' }}.
            </p>
        </div>

        <div class="grid grid-auto-fit">
            <article class="card">
                <div class="card-content">
                    <h3 class="card-title">Quick facts</h3>
                    @if(!empty($details['flag']))
                    <div style="margin-bottom:1rem; display:flex; align-items:center; gap:1rem;">
                        <img src="{{ $details['flag'] }}" alt="Flag of {{ $details['name'] ?? '' }}" style="max-width:70px; border-radius:8px; box-shadow:0 4px 14px rgba(0,0,0,.1);">
                        <div>
                            <strong>{{ $details['name'] ?? $destination->country }}</strong>
                            <div style="color: var(--text-muted);">{{ $details['region'] ?? 'Global' }} · {{ $details['subregion'] ?? '' }}</div>
                        </div>
                    </div>
                    @endif

                    <ul class="feature-list" style="padding-left:1.25rem; margin:0; list-style:disc; color: var(--text-muted);">
                        @if(!empty($details['capital']))
                        <li><strong>Capital:</strong> {{ $details['capital'] }}</li>
                        @endif
                        @if(!empty($details['population']))
                        <li><strong>Population:</strong> {{ number_format($details['population']) }}</li>
                        @endif
                        @if(!empty($details['currency']))
                        <li><strong>Currency:</strong> {{ $details['currency'] }}</li>
                        @endif
                        @if(!empty($details['languages']))
                        <li><strong>Languages:</strong> {{ $details['languages'] }}</li>
                        @endif
                    </ul>
                </div>
            </article>

            <article class="card">
                <div class="card-content">
                    <h3 class="card-title">Why visit?</h3>
                    <p class="card-description">
                        {{ $tourism['description'] ?? 'Travel inspiration' }}
                    </p>
                    <p>{{ $tourism['extract'] ?? 'Enjoy rich cultural heritage, memorable landscapes, and local experiences tailored for curious travelers.' }}</p>
                    @if(count($highlights))
                    <div style="margin-top:1rem;">
                        <h4 style="margin-bottom:.75rem;">Top travel highlights</h4>
                        <ul style="padding-left:1.25rem; margin:0; list-style:disc; color: var(--text-muted);">
                            @foreach($highlights as $highlight)
                            <li>{{ $highlight }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </article>
        </div>

        <div class="section-header" style="margin-top: var(--space-4xl);">
            <h2 class="section-title">About this place</h2>
            <p class="section-description">
                Information sourced from live API data to help you plan your next trip.
            </p>
        </div>

        <article class="card">
            <div class="card-content">
                <h3 class="card-title">Destination details</h3>
                <p><strong>Place:</strong> {{ $destination->name }}</p>
                <p><strong>Country:</strong> {{ $destination->country ?? $details['name'] ?? 'Unknown' }}</p>
                <p><strong>Region:</strong> {{ $destination->region ?? ($details['region'] ?? 'Unknown') }}</p>
                @if($destination->lat && $destination->lng)
                <p><strong>Coordinates:</strong> {{ $destination->lat }}, {{ $destination->lng }}</p>
                @endif
                @if(!empty($destination->raw_data['address']))
                <p><strong>Address:</strong> {{ $destination->raw_data['address'] }}</p>
                @endif
                <p>{{ $destination->description ?? 'This destination is curated from live global sources and enriched with travel facts.' }}</p>
            </div>
        </article>
    </div>
</div>
@endsection

@extends('layouts.public')

@section('title', $destination->name . ' — Smart Booking')

@section('content')

<script>
window.__destinationData = {
    id: {{ $destination->id }},
    name: @json($destination->name),
    country: @json($destination->country)
};
window.__isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};
</script>

<section class="dest-show-hero" style="background: linear-gradient(160deg, rgba(10,20,30,0.62) 0%, rgba(59,31,43,0.45) 100%), url('{{ $destination->image_url ?: 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=1800&q=80' }}') center/cover no-repeat; min-height: 450px; display: flex; align-items: center;">
    <div class="dest-show-hero-inner">
        <a href="{{ route('destinations') }}" class="dest-back-link">
            <i class="fas fa-arrow-left"></i> All Destinations
        </a>
        <div class="dest-show-badges">
            @if($destination->badge)
                <span class="dest-show-badge">{{ $destination->badge }}</span>
            @endif
            @if($destination->is_hidden_gem)
                <span class="dest-show-badge dest-show-badge--gem"><i class="fas fa-gem"></i> Hidden Gem</span>
            @endif
        </div>
        <h1>{{ $destination->name }}</h1>
        <p class="dest-show-location">
            <i class="fas fa-map-marker-alt"></i>
            {{ $destination->country }}
            @if($destination->region)
                &nbsp;·&nbsp; {{ ucwords(str_replace('_', ' ', $destination->region)) }}
            @endif
        </p>
        <div class="dest-show-meta-row">
            @if($destination->mood)
                <span class="dest-show-pill"><i class="fas fa-heart"></i> {{ ucfirst($destination->mood) }}</span>
            @endif
            @if($destination->category)
                <span class="dest-show-pill"><i class="fas fa-tag"></i> {{ ucwords(str_replace('_', ' ', $destination->category)) }}</span>
            @endif
            @if($destination->match_score)
                <span class="dest-show-pill dest-show-pill--gold"><i class="fas fa-star"></i> {{ $destination->match_score }}% match</span>
            @endif
        </div>
    </div>
</section>

<div class="dest-show-wrap">

    
    <div class="dest-show-main">

        <div class="dest-show-card">
            <h2><i class="fas fa-info-circle"></i> About {{ $destination->name }}</h2>
            <p class="dest-show-description">
                {{ $destination->description ?: 'No description available yet for this destination.' }}
            </p>
            
            @if(isset($enrichedData['fun_facts']['summary']) && $enrichedData['fun_facts']['summary'])
                <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--border);">
                    <h3 style="font-size:16px;margin-bottom:12px;color:var(--deep);"><i class="fas fa-lightbulb"></i> Did You Know?</h3>
                    <p style="line-height:1.7;color:var(--text);">{{ $enrichedData['fun_facts']['summary'] }}</p>
                </div>
            @endif
        </div>

        {{-- AI Cost Breakdown --}}
        <div class="dest-show-card" id="costBreakdownCard">
            <div class="cost-breakdown-header">
                <h2><i class="fas fa-calculator"></i> Cost Breakdown</h2>
                <div class="cost-duration-selector">
                    <label><i class="fas fa-calendar"></i> Duration:</label>
                    <input type="number" id="costDuration" min="1" max="365" value="7"
                        style="width:65px;padding:6px 8px;border:1.5px solid var(--border);border-radius:6px;font-size:13px;font-family:'Georgia',serif;background:#fff;color:var(--deep);"
                        onchange="loadCostBreakdown()">
                    <span style="font-size:13px;color:var(--text-muted);">days</span>
                </div>
            </div>
            <div id="costBreakdownContent">
                <div class="cost-loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading cost data from AI…
                </div>
            </div>
        </div>

        {{-- Activities from API --}}
        @if(isset($enrichedData['activities']) && count($enrichedData['activities']) > 0)
        <div class="dest-show-card">
            <h2><i class="fas fa-map-marked-alt"></i> Popular Activities</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-top:16px;">
                @foreach($enrichedData['activities'] as $activity)
                <div style="padding:16px;border:1px solid var(--border);border-radius:8px;background:#fff;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                        <i class="fas {{ $activity['icon'] }}" style="color:var(--gold);font-size:20px;"></i>
                        <h4 style="font-size:15px;margin:0;color:var(--deep);">{{ $activity['name'] }}</h4>
                    </div>
                    <p style="font-size:13px;color:var(--text-muted);margin:0;">{{ $activity['description'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Activities Manager (AI Cost Breakdown) --}}
        <div class="dest-show-card" id="activitiesCard" style="display:none;">
            <div class="activities-header">
                <h2><i class="fas fa-map-marked-alt"></i> Activities</h2>
                <span class="activities-subtitle">Toggle activities to update your estimated cost</span>
            </div>
            <div id="activitiesContent"></div>
            <div class="activities-total-row" id="activitiesTotalRow" style="display:none;">
                <span><i class="fas fa-tag"></i> Selected activities total:</span>
                <strong id="activitiesTotal">$0</strong>
            </div>
        </div>

        <div class="dest-show-card">
            <h2><i class="fas fa-list-ul"></i> Key Facts</h2>
            <div class="dest-show-facts">
                <div class="dest-fact">
                    <i class="fas fa-globe"></i>
                    <div>
                        <span class="fact-label">Country</span>
                        <span class="fact-value">{{ $destination->country ?: '—' }}</span>
                    </div>
                </div>
                <div class="dest-fact">
                    <i class="fas fa-map"></i>
                    <div>
                        <span class="fact-label">Region</span>
                        <span class="fact-value">{{ $destination->region ? ucwords(str_replace('_', ' ', $destination->region)) : '—' }}</span>
                    </div>
                </div>
                <div class="dest-fact">
                    <i class="fas fa-tag"></i>
                    <div>
                        <span class="fact-label">Category</span>
                        <span class="fact-value">{{ $destination->category ? ucwords(str_replace('_', ' ', $destination->category)) : '—' }}</span>
                    </div>
                </div>
                <div class="dest-fact">
                    <i class="fas fa-heart"></i>
                    <div>
                        <span class="fact-label">Travel Mood</span>
                        <span class="fact-value">{{ $destination->mood ? ucfirst($destination->mood) : '—' }}</span>
                    </div>
                </div>
                <div class="dest-fact">
                    <i class="fas fa-dollar-sign"></i>
                    <div>
                        <span class="fact-label">Starting From</span>
                        <span class="fact-value" data-price-usd="{{ $destination->price_from }}">${{ number_format($destination->price_from) }} <small>per person</small></span>
                    </div>
                </div>
                @if($destination->match_score)
                <div class="dest-fact">
                    <i class="fas fa-star"></i>
                    <div>
                        <span class="fact-label">Match Score</span>
                        <span class="fact-value">{{ $destination->match_score }}%</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Local Food & Culture --}}
        @if(isset($enrichedData['food']) || isset($enrichedData['culture']))
        <div class="dest-show-card">
            <h2><i class="fas fa-utensils"></i> Local Food & Culture</h2>
            
            @if(isset($enrichedData['food']['popular_dishes']) && count($enrichedData['food']['popular_dishes']) > 0)
            <div style="margin-bottom:24px;">
                <h3 style="font-size:15px;margin-bottom:10px;color:var(--deep);"><i class="fas fa-drumstick-bite"></i> Must-Try Dishes</h3>
                <ul style="list-style:none;padding:0;margin:0;">
                    @foreach($enrichedData['food']['popular_dishes'] as $dish)
                    <li style="padding:8px 0;border-bottom:1px solid var(--border-light);color:var(--text);">
                        <i class="fas fa-check" style="color:var(--gold);margin-right:8px;"></i>{{ $dish }}
                    </li>
                    @endforeach
                </ul>
                @if(isset($enrichedData['food']['dining_tips']))
                <p style="margin-top:12px;font-size:13px;color:var(--text-muted);font-style:italic;">
                    <i class="fas fa-info-circle"></i> {{ $enrichedData['food']['dining_tips'] }}
                </p>
                @endif
            </div>
            @endif

            @if(isset($enrichedData['culture']['tips']) && count($enrichedData['culture']['tips']) > 0)
            <div>
                <h3 style="font-size:15px;margin-bottom:10px;color:var(--deep);"><i class="fas fa-users"></i> Cultural Tips</h3>
                <ul style="list-style:none;padding:0;margin:0;">
                    @foreach($enrichedData['culture']['tips'] as $tip)
                    <li style="padding:8px 0;border-bottom:1px solid var(--border-light);color:var(--text);">
                        <i class="fas fa-lightbulb" style="color:var(--gold);margin-right:8px;"></i>{{ $tip }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
        @endif

        
        @if($related->count())
        <div class="dest-show-card">
            <h2><i class="fas fa-compass"></i> Similar Destinations</h2>
            <div class="dest-show-related">
                @foreach($related as $r)
                <a href="{{ route('destinations.show', $r->id) }}" class="dest-related-card">
                    <div class="dest-related-img" style="background-image:url('{{ $r->image_url ?: 'https://picsum.photos/seed/'.urlencode($r->name).'/400/260' }}')"></div>
                    <div class="dest-related-body">
                        <h4>{{ $r->name }}</h4>
                        <p>{{ $r->country }}</p>
                        @if($r->price_from)
                            <span class="dest-related-price" data-price-usd="{{ $r->price_from }}">From ${{ number_format($r->price_from) }}</span>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    
    <aside class="dest-show-sidebar">

        
        <div class="dest-show-card dest-show-cta-card">
            <div class="dest-cta-price">
                <span class="dest-cta-from">From</span>
                <span class="dest-cta-amount" data-price-usd="{{ $destination->price_from }}">${{ number_format($destination->price_from) }}</span>
                <span class="dest-cta-per">per person</span>
            </div>

            <a href="{{ route('plan-trip') }}?destination={{ urlencode($destination->name) }}&country={{ urlencode($destination->country ?? '') }}&mood={{ $destination->mood }}&region={{ $destination->region }}"
               class="primary-button dest-cta-btn"
               data-destination-country="{{ $destination->country ?? '' }}"
               data-destination-name="{{ $destination->name }}">
                <i class="fas fa-route"></i> Plan This Trip
            </a>

            <a href="{{ route('flights.index') }}?destination={{ urlencode($destination->name) }}&country={{ urlencode($destination->country ?? '') }}"
               class="secondary-button dest-cta-btn">
                <i class="fas fa-plane"></i> Search Flights
            </a>

            @auth
            <button class="secondary-button dest-cta-btn" id="wishlistBtn"
                    onclick="toggleWishlist({{ $destination->id }})">
                <i class="fas fa-heart"></i> Save to Wishlist
            </button>
            @else
            <a href="{{ route('login') }}" class="secondary-button dest-cta-btn">
                <i class="fas fa-heart"></i> Save to Wishlist
            </a>
            @endauth
        </div>

        
        <div class="dest-show-card">
            <h3><i class="fas fa-info-circle"></i> Quick Info</h3>
            <ul class="dest-quick-list">
                <li><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> {{ $destination->country }}</li>
                @if($destination->region)
                <li><i class="fas fa-globe"></i> <strong>Region:</strong> {{ ucwords(str_replace('_', ' ', $destination->region)) }}</li>
                @endif
                @if($destination->mood)
                <li><i class="fas fa-heart"></i> <strong>Best for:</strong> {{ ucfirst($destination->mood) }} travellers</li>
                @endif
                @if($destination->is_hidden_gem)
                <li><i class="fas fa-gem" style="color:var(--gold)"></i> <strong>Hidden Gem</strong> destination</li>
                @endif
            </ul>
        </div>

    </aside>

</div>

@endsection
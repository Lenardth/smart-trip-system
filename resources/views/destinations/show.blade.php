@extends('layouts.public')

@section('title', $destination->name . ' — Smart Booking')

@section('content')

{{-- Hero --}}
<section class="dest-show-hero" style="background: linear-gradient(160deg, rgba(10,20,30,0.62) 0%, rgba(59,31,43,0.45) 100%), url('{{ $destination->image_url ?: 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=1800&q=80' }}') center/cover no-repeat;">
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

{{-- Main content --}}
<div class="dest-show-wrap">

    {{-- Left column --}}
    <div class="dest-show-main">

        {{-- About --}}
        <div class="dest-show-card">
            <h2><i class="fas fa-info-circle"></i> About {{ $destination->name }}</h2>
            <p class="dest-show-description">
                {{ $destination->description ?: 'No description available yet for this destination.' }}
            </p>
        </div>

        {{-- Key facts --}}
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
                        <span class="fact-value">${{ number_format($destination->price_from) }} <small>per person</small></span>
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

        {{-- Related destinations --}}
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
                            <span class="dest-related-price">From ${{ number_format($r->price_from) }}</span>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- Right sidebar --}}
    <aside class="dest-show-sidebar">

        {{-- Price & CTA --}}
        <div class="dest-show-card dest-show-cta-card">
            <div class="dest-cta-price">
                <span class="dest-cta-from">From</span>
                <span class="dest-cta-amount">${{ number_format($destination->price_from) }}</span>
                <span class="dest-cta-per">per person</span>
            </div>

            <a href="{{ route('plan-trip') }}?destination={{ urlencode($destination->name) }}&mood={{ $destination->mood }}&region={{ $destination->region }}"
               class="primary-button dest-cta-btn">
                <i class="fas fa-route"></i> Plan This Trip
            </a>

            <a href="{{ route('flights.index') }}?destination={{ urlencode($destination->name) }}"
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

        {{-- Quick info --}}
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

@push('scripts')
<script>
@auth
(function () {
    const destId = {{ $destination->id }};
    const csrf   = document.querySelector('meta[name="csrf-token"]').content;
    const btn    = document.getElementById('wishlistBtn');

    let isSaved = false;

    fetch('/api/wishlist/count', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.ids && data.ids.includes(destId)) {
                isSaved = true;
                if (btn) btn.innerHTML = '<i class="fas fa-heart" style="color:#e74c3c"></i> Saved';
            }
        }).catch(() => {});

    window.toggleWishlist = function (id) {
        if (!btn) return;
        if (isSaved) {
            fetch('/wishlist/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' }
            }).then(r => r.json()).then(() => {
                isSaved = false;
                btn.innerHTML = '<i class="fas fa-heart"></i> Save to Wishlist';
                try { localStorage.setItem('smartBookingWishlistUpdated', String(Date.now())); } catch (_) {}
                if (window.__refreshWishlistBadge) window.__refreshWishlistBadge();
            }).catch(() => {});
        } else {
            fetch('/wishlist', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ destination_id: id })
            }).then(r => r.json()).then(() => {
                isSaved = true;
                btn.innerHTML = '<i class="fas fa-heart" style="color:#e74c3c"></i> Saved';
                try { localStorage.setItem('smartBookingWishlistUpdated', String(Date.now())); } catch (_) {}
                if (window.__refreshWishlistBadge) window.__refreshWishlistBadge();
            }).catch(() => {});
        }
    };
}());
@endauth
</script>
@endpush

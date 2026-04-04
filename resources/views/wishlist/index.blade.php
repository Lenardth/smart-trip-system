@extends('layouts.public')

@section('title', 'My Wishlist — Smart Booking')



@section('content')
<section class="page-hero" style="background: linear-gradient(160deg, rgba(180, 80, 0, 0.60) 0%, rgba(10, 20, 40, 0.55) 100%), url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1800&q=80'); background-size: cover; background-position: center bottom;">
    <div>
        <h1><i class="fas fa-heart"></i> My Wishlist</h1>
        <p>Your dream destinations await</p>
    </div>
</section>

<div class="wishlist-container">
    @if($wishlistItems->count() > 0)
    <div class="stats-card">
        <div class="stat-item">
            <div class="number">{{ $wishlistItems->count() }}</div>
            <div class="label">Saved Destinations</div>
        </div>
        <div class="stat-item">
            <div class="number">{{ $wishlistItems->unique('destination.continent')->count() }}</div>
            <div class="label">Continents</div>
        </div>
        <div class="stat-item">
            <div class="number">${{ number_format($wishlistItems->avg('destination.estimated_cost'), 0) }}</div>
            <div class="label">Avg. Budget</div>
        </div>
        <div class="stat-item">
            <div class="number">{{ $wishlistItems->unique('destination.category')->count() }}</div>
            <div class="label">Categories</div>
        </div>
    </div>

    <div class="filter-section">
        <h3><i class="fas fa-filter"></i> Filter Destinations</h3>
        <div class="filter-controls">
            <select id="filterContinent">
                <option value="all">All Continents</option>
                <option value="Asia">Asia</option>
                <option value="Europe">Europe</option>
                <option value="Africa">Africa</option>
                <option value="North America">North America</option>
                <option value="South America">South America</option>
                <option value="Oceania">Oceania</option>
            </select>
            <select id="filterCategory">
                <option value="all">All Categories</option>
                <option value="beach">Beach</option>
                <option value="mountain">Mountain</option>
                <option value="city">City</option>
                <option value="adventure">Adventure</option>
            </select>
            <input type="search" id="searchWishlist" placeholder="Search destinations...">
            <button class="clear-all-btn" onclick="Wishlist.clearAll()">
                <i class="fas fa-trash-alt"></i> Clear All
            </button>
        </div>
    </div>

    <div class="wishlist-grid" id="wishlistGrid">
        @foreach($wishlistItems as $item)
        <div class="wishlist-card"
             data-continent="{{ $item->destination->continent }}"
             data-category="{{ $item->destination->category }}"
             data-name="{{ strtolower($item->destination->name) }}">
            <div class="wishlist-image" style="background-image: url('{{ $item->destination->image ?? 'https://via.placeholder.com/400x300' }}')">
                <span class="wishlist-badge">{{ $item->destination->category }}</span>
                <button class="remove-btn" onclick="Wishlist.remove({{ $item->destination->id }}, '{{ $item->destination->name }}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="wishlist-content">
                <h3>{{ $item->destination->name }}</h3>
                <div class="wishlist-location">
                    <i class="fas fa-map-marker-alt"></i>
                    {{ $item->destination->city }}, {{ $item->destination->country }}
                </div>
                <p class="wishlist-description">
                    {{ Str::limit($item->destination->short_description ?? $item->destination->description, 100) }}
                </p>
                <div class="wishlist-meta">
                    <div class="wishlist-price">
                        ${{ number_format($item->destination->estimated_cost, 0) }}
                        <span>/ person</span>
                    </div>
                    <button class="plan-trip-btn" onclick="Wishlist.planTrip({{ $item->destination->id }}, '{{ $item->destination->name }}')">
                        <i class="fas fa-route"></i> Plan Trip
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <i class="fas fa-heart-broken"></i>
        <h3>Your Wishlist is Empty</h3>
        <p>Start building your dream travel list by exploring our amazing destinations</p>
        <a href="/discover" class="browse-btn">
            <i class="fas fa-compass"></i> Browse Destinations
        </a>
    </div>
    @endif
</div>
@endsection

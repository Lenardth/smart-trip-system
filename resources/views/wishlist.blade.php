<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Wishlist — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
       @vite([
        'resources/css/app.css',
        'resources/css/pages/base.css',
        'resources/css/pages/landing.css',
        'resources/js/pages/landing.js'
    ])

<header class="main-header">
    <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo">
    <span class="logo-text">Smart Booking</span>
    <div class="user-display">
        <i class="fas fa-user-circle"></i>
        <span>{{ Auth::user()->name }}</span>
    </div>
</header>


<nav class="nav-container">
    <a href="/"><i class="fas fa-home"></i> Home</a>
    <a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="/plan-trip"><i class="fas fa-route"></i> Plan Trip</a>
    <a href="/flights"><i class="fas fa-plane"></i> Book Flights</a>
    <a href="/discover"><i class="fas fa-compass"></i> Discover</a>
    <a href="/destinations"><i class="fas fa-map-marked-alt"></i> Destinations</a>
    <a href="/community"><i class="fas fa-users"></i> Community</a>
    <a href="/wishlist" class="active"><i class="fas fa-heart"></i> Wishlist</a>
    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
        @csrf
        <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </form>
</nav>


<section class="page-hero">
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
            <select id="filterContinent" onchange="filterWishlist()">
                <option value="all">All Continents</option>
                <option value="Asia">Asia</option>
                <option value="Europe">Europe</option>
                <option value="Africa">Africa</option>
                <option value="North America">North America</option>
                <option value="South America">South America</option>
                <option value="Oceania">Oceania</option>
            </select>
            <select id="filterCategory" onchange="filterWishlist()">
                <option value="all">All Categories</option>
                <option value="beach">Beach</option>
                <option value="mountain">Mountain</option>
                <option value="city">City</option>
                <option value="adventure">Adventure</option>
            </select>
            <input type="search" id="searchWishlist" placeholder="Search destinations..." onkeyup="filterWishlist()">
            <button class="clear-all-btn" onclick="clearAllWishlist()">
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
                <button class="remove-btn" onclick="removeFromWishlist({{ $item->destination->id }}, '{{ $item->destination->name }}')">
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
                    <button class="plan-trip-btn" onclick="planTrip({{ $item->destination->id }}, '{{ $item->destination->name }}')">
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


<footer class="footer">
    <p>&copy; 2024 Smart Booking. All rights reserved.</p>
    <div>
        <a href="/privacy">Privacy Policy</a>
        <a href="/terms">Terms of Service</a>
        <a href="/contact">Contact Us</a>
    </div>
</footer>


</body>
</html>

<header class="main-header">
    <a href="/" style="display:flex;align-items:center;gap:14px;text-decoration:none;">
        <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo">
        <span class="logo-text">Smart Booking</span>
    </a>
    @auth
    <div class="user-display">
        <i class="fas fa-user-circle"></i>
        <span>{{ Auth::user()->name }}</span>
    </div>
    @endauth
</header>

<button class="public-nav-toggle" type="button" onclick="togglePublicNav()" aria-label="Toggle navigation">
    <i class="fas fa-bars"></i>
</button>

<nav class="nav-container" id="publicNav">
    <a href="/" class="{{ request()->is('/') ? 'active' : '' }}"><i class="fas fa-home"></i> Home</a>
    @auth
    <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    @endauth
    <a href="/plan-trip" class="{{ request()->is('plan-trip') ? 'active' : '' }}"><i class="fas fa-route"></i> Plan Trip</a>
    <a href="/flights" class="{{ request()->is('flights') ? 'active' : '' }}"><i class="fas fa-plane"></i> Book Flights</a>
    <a href="/discover" class="{{ request()->is('discover') ? 'active' : '' }}"><i class="fas fa-compass"></i> Discover</a>
    <a href="/destinations" class="{{ request()->is('destinations') ? 'active' : '' }}"><i class="fas fa-map-marked-alt"></i> Destinations</a>
    <a href="/accommodations" class="{{ request()->is('accommodations') ? 'active' : '' }}"><i class="fas fa-hotel"></i> Accommodations</a>
    <a href="/community" class="{{ request()->is('community') ? 'active' : '' }}"><i class="fas fa-users"></i> Community</a>
    @auth
    <a href="/wishlist" class="{{ request()->is('wishlist') ? 'active' : '' }}"><i class="fas fa-heart"></i> Wishlist <span class="nav-badge" id="wishlistCount">0</span></a>
    @endauth
    @guest
    <a href="/login"><i class="fas fa-sign-in-alt"></i> Login</a>
    @endguest
    @auth
    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
        @csrf
        <button type="submit" class="nav-logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </form>
    @endauth
</nav>

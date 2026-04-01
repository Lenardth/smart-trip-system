<header class="main-header">
    <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo">
    <div class="logo-text">Smart Booking</div>
    @auth
        <div class="user-display">
            <i class="fas fa-user-circle"></i>
            <span>{{ Auth::user()->name }}</span>
        </div>
    @endauth
</header>

<nav class="nav-container" id="publicNav">
    <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">
        <i class="fas fa-home"></i> Home
    </a>
    @if(Route::has('discover'))
        <a href="{{ route('discover') }}" class="{{ request()->routeIs('discover') ? 'active' : '' }}">
            <i class="fas fa-compass"></i> Discover
        </a>
    @endif
    @if(Route::has('destinations'))
        <a href="{{ route('destinations') }}" class="{{ request()->routeIs('destinations') ? 'active' : '' }}">
            <i class="fas fa-map-marker-alt"></i> Destinations
        </a>
    @endif
    @if(Route::has('plan-trip'))
        <a href="{{ route('plan-trip') }}" class="{{ request()->routeIs('plan-trip') ? 'active' : '' }}">
            <i class="fas fa-map-marked-alt"></i> Plan Trip
        </a>
    @endif
    @if(Route::has('community'))
        <a href="{{ route('community') }}" class="{{ request()->routeIs('community') ? 'active' : '' }}">
            <i class="fas fa-users"></i> Community
        </a>
    @endif
    @if(Route::has('flights.index'))
        <a href="{{ route('flights.index') }}" class="{{ request()->routeIs('flights.*') ? 'active' : '' }}">
            <i class="fas fa-plane"></i> Flights
        </a>
    @endif
    @if(Route::has('accommodations.index'))
        <a href="{{ route('accommodations.index') }}" class="{{ request()->routeIs('accommodations.*') ? 'active' : '' }}">
            <i class="fas fa-hotel"></i> Accommodations
        </a>
    @endif
    @guest
        @if(Route::has('login'))
            <a href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i> Login</a>
        @endif
        @if(Route::has('register'))
            <a href="{{ route('register') }}"><i class="fas fa-user-plus"></i> Register</a>
        @endif
    @else
        @if(Route::has('dashboard'))
            <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        @endif
        @if(Route::has('bookings.index'))
            <a href="{{ route('bookings.index') }}"><i class="fas fa-ticket-alt"></i> My Bookings</a>
        @endif
        @if(Route::has('wishlist.index'))
            <a href="{{ route('wishlist.index') }}"><i class="fas fa-heart"></i> Wishlist</a>
        @endif
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
    @endguest
</nav>

<button class="public-nav-toggle" id="navToggle" aria-label="Toggle menu">
    <i class="fas fa-bars"></i>
</button>

@push('scripts')
<script>
    (function () {
        const navToggle = document.getElementById('navToggle');
        const publicNav = document.getElementById('publicNav');
        if (!navToggle || !publicNav) return;

        navToggle.addEventListener('click', function () {
            publicNav.classList.toggle('open');
        });

        document.addEventListener('click', function (e) {
            if (publicNav.classList.contains('open')
                && !publicNav.contains(e.target)
                && e.target !== navToggle) {
                publicNav.classList.remove('open');
            }
        });
    })();
</script>
@endpush

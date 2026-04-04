<header class="main-header">
    <a href="{{ url('/') }}" class="header-brand">
        <img src="{{ asset('img/logo.png') }}" alt="Smart Booking" class="header-logo-img">
        <span class="header-brand-text">Smart <span>Booking</span></span>
    </a>
    <button class="nav-hamburger" id="navHamburger" aria-label="Open menu">
        <span></span><span></span><span></span>
    </button>
    @auth
    <div class="user-display">
        <i class="fas fa-user-circle"></i>
        <span>{{ Auth::user()->name }}</span>
    </div>
    @endauth
</header>

<div class="nav-overlay" id="navOverlay"></div>

<nav class="nav-drawer" id="navDrawer">
    <div class="nav-drawer-header">
        <div style="display:flex;align-items:center;gap:8px;">
            <img src="{{ asset('img/logo.png') }}" alt="Smart Booking" style="height:38px;width:auto;">
            <span style="font-size:16px;font-weight:700;color:#fff;font-family:'Georgia',serif;letter-spacing:.5px;">Smart <span style="color:#c9a96e;">Booking</span></span>
        </div>
        <button class="nav-drawer-close" id="navClose" aria-label="Close menu">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="nav-drawer-links">
        <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}"><i class="fas fa-home"></i> Home</a>
        @if(Route::has('discover'))
        <a href="{{ route('discover') }}" class="nav-link {{ request()->routeIs('discover') ? 'active' : '' }}"><i class="fas fa-compass"></i> Discover</a>
        @endif
        @if(Route::has('destinations'))
        <a href="{{ route('destinations') }}" class="nav-link {{ request()->routeIs('destinations*') ? 'active' : '' }}"><i class="fas fa-map-marker-alt"></i> Destinations</a>
        @endif
        @if(Route::has('plan-trip'))
        <a href="{{ route('plan-trip') }}" class="nav-link {{ request()->routeIs('plan-trip') ? 'active' : '' }}"><i class="fas fa-map-marked-alt"></i> Plan Trip</a>
        @endif
        @if(Route::has('community'))
        <a href="{{ route('community') }}" class="nav-link {{ request()->routeIs('community*') ? 'active' : '' }}"><i class="fas fa-users"></i> Community</a>
        @endif
        @if(Route::has('flights.index'))
        <a href="{{ route('flights.index') }}" class="nav-link {{ request()->routeIs('flights.*') ? 'active' : '' }}"><i class="fas fa-plane"></i> Flights</a>
        @endif
        @if(Route::has('accommodations.index'))
        <a href="{{ route('accommodations.index') }}" class="nav-link {{ request()->routeIs('accommodations.*') ? 'active' : '' }}"><i class="fas fa-hotel"></i> Accommodations</a>
        @endif
        <div class="nav-drawer-divider"></div>
        @guest
            @if(Route::has('login'))
            <a href="{{ route('login') }}" class="nav-link"><i class="fas fa-sign-in-alt"></i> Login</a>
            @endif
            @if(Route::has('register'))
            <a href="{{ route('register') }}" class="nav-link"><i class="fas fa-user-plus"></i> Register</a>
            @endif
        @else
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="{{ route('bookings.index') }}" class="nav-link {{ request()->routeIs('bookings.*') ? 'active' : '' }}"><i class="fas fa-ticket-alt"></i> My Bookings</a>
            <a href="{{ route('wishlist.index') }}" class="nav-link {{ request()->routeIs('wishlist.*') ? 'active' : '' }}"><i class="fas fa-heart"></i> Wishlist</a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="nav-link nav-link--btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        @endguest
    </div>
</nav>

<nav class="nav-bar-desktop">
    <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}"><i class="fas fa-home"></i> Home</a>
    @if(Route::has('discover'))
    <a href="{{ route('discover') }}" class="{{ request()->routeIs('discover') ? 'active' : '' }}"><i class="fas fa-compass"></i> Discover</a>
    @endif
    @if(Route::has('destinations'))
    <a href="{{ route('destinations') }}" class="{{ request()->routeIs('destinations*') ? 'active' : '' }}"><i class="fas fa-map-marker-alt"></i> Destinations</a>
    @endif
    @if(Route::has('plan-trip'))
    <a href="{{ route('plan-trip') }}" class="{{ request()->routeIs('plan-trip') ? 'active' : '' }}"><i class="fas fa-map-marked-alt"></i> Plan Trip</a>
    @endif
    @if(Route::has('community'))
    <a href="{{ route('community') }}" class="{{ request()->routeIs('community*') ? 'active' : '' }}"><i class="fas fa-users"></i> Community</a>
    @endif
    @if(Route::has('flights.index'))
    <a href="{{ route('flights.index') }}" class="{{ request()->routeIs('flights.*') ? 'active' : '' }}"><i class="fas fa-plane"></i> Flights</a>
    @endif
    @if(Route::has('accommodations.index'))
    <a href="{{ route('accommodations.index') }}" class="{{ request()->routeIs('accommodations.*') ? 'active' : '' }}"><i class="fas fa-hotel"></i> Accommodations</a>
    @endif
    @guest
        @if(Route::has('login'))
        <a href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i> Login</a>
        @endif
        @if(Route::has('register'))
        <a href="{{ route('register') }}"><i class="fas fa-user-plus"></i> Register</a>
        @endif
    @else
        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <form method="POST" action="{{ route('logout') }}" style="display:contents;">
            @csrf
            <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--deep);font-size:14px;font-weight:700;padding:8px 14px;border-radius:4px;font-family:'Georgia',serif;display:flex;align-items:center;gap:7px;transition:background .2s;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
    @endguest
</nav>

@push('scripts')
<script>
(function () {
    const hamburger = document.getElementById('navHamburger');
    const drawer    = document.getElementById('navDrawer');
    const overlay   = document.getElementById('navOverlay');
    const closeBtn  = document.getElementById('navClose');
    const open  = () => { drawer.classList.add('open'); overlay.classList.add('show'); document.body.style.overflow = 'hidden'; };
    const close = () => { drawer.classList.remove('open'); overlay.classList.remove('show'); document.body.style.overflow = ''; };
    hamburger?.addEventListener('click', open);
    closeBtn?.addEventListener('click', close);
    overlay?.addEventListener('click', close);
    drawer?.querySelectorAll('.nav-link').forEach(l => l.addEventListener('click', close));
})();
</script>
@endpush

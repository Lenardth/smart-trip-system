<header class="main-header">
        <a href="{{ url('/') }}" class="header-brand">
        <img src="{{ asset('img/logo.png') }}" alt="Smart Booking" class="logo logo-nav">
        <span class="logo-text logo-text-nav">Smart <span class="logo-text-gold">Booking</span></span>
    </a>

    @auth
    <div class="user-display">
        <div class="nav-avatar">
            @if(Auth::user()->profile_picture)
                <img src="{{ asset('storage/'.Auth::user()->profile_picture) }}"
                     alt="{{ Auth::user()->name }}"
                     class="nav-avatar-img"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <span class="nav-avatar-init nav-avatar-init--hidden">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</span>
            @else
                <span class="nav-avatar-init">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</span>
            @endif
        </div>
        <span>{{ explode(' ', Auth::user()->name)[0] }}</span>
    </div>
    @endauth

    <button class="mob-hamburger" id="mobHamburger" aria-label="Open menu">
        <span></span><span></span><span></span>
    </button>
</header>

<nav class="nav-container desktop-nav">
    <a href="{{ url('/') }}"                     class="{{ request()->is('/')                  ? 'active' : '' }}"><i class="fas fa-home"></i> Home</a>
    <a href="{{ route('plan-trip') }}"            class="{{ request()->routeIs('plan-trip')     ? 'active' : '' }}"><i class="fas fa-route"></i> Plan Trip</a>
    <a href="{{ route('flights.index') }}"        class="{{ request()->routeIs('flights.*')     ? 'active' : '' }}"><i class="fas fa-plane"></i> Flights</a>
    <a href="{{ route('accommodations.index') }}" class="{{ request()->routeIs('accommodations.*') ? 'active' : '' }}"><i class="fas fa-hotel"></i> Stays</a>
    @guest
        <a href="{{ route('login') }}"    class="nav-link-ml-auto"><i class="fas fa-sign-in-alt"></i> Login</a>
        <a href="{{ route('register') }}"><i class="fas fa-user-plus"></i> Register</a>
    @else
        <a href="{{ route('dashboard') }}" class="nav-link-ml-auto"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="{{ route('bookings.index') }}"><i class="fas fa-ticket-alt"></i> Bookings</a>
        <form method="POST" action="{{ route('logout') }}" class="nav-form-inline">
            @csrf
            <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
    @endguest

    <div id="currencyPickerWrapper" class="currency-picker-wrapper"></div>
</nav>

<div class="mob-overlay" id="mobOverlay"></div>

<nav class="mob-drawer" id="mobDrawer">

    <div class="mob-drawer-head">
        <div class="mob-brand">
            <img src="{{ asset('img/logo.png') }}" alt="Smart Booking" class="mob-logo mob-logo-size">
            <span class="mob-brand-text mob-brand-text-size">Smart <span>Booking</span></span>
        </div>
        <button class="mob-drawer-close" id="mobDrawerClose" aria-label="Close menu">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="mob-drawer-links">
        <a href="{{ url('/') }}"                     class="mob-link {{ request()->is('/')                  ? 'active' : '' }}"><i class="fas fa-home"></i> Home</a>
        <a href="{{ route('plan-trip') }}"            class="mob-link {{ request()->routeIs('plan-trip')     ? 'active' : '' }}"><i class="fas fa-route"></i> Plan Trip</a>
        <a href="{{ route('flights.index') }}"        class="mob-link {{ request()->routeIs('flights.*')     ? 'active' : '' }}"><i class="fas fa-plane"></i> Flights</a>
        <a href="{{ route('accommodations.index') }}" class="mob-link {{ request()->routeIs('accommodations.*') ? 'active' : '' }}"><i class="fas fa-hotel"></i> Stays</a>

        <div class="mob-divider"></div>

        @guest
            <a href="{{ route('login') }}"    class="mob-link"><i class="fas fa-sign-in-alt"></i> Login</a>
            <a href="{{ route('register') }}" class="mob-link"><i class="fas fa-user-plus"></i> Register</a>
        @else
            <a href="{{ route('dashboard') }}"      class="mob-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="{{ route('bookings.index') }}"  class="mob-link"><i class="fas fa-ticket-alt"></i> My Bookings</a>
            <form method="POST" action="{{ route('logout') }}" class="mob-form-reset">
                @csrf
                <button type="submit" class="mob-link mob-link-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        @endguest
    </div>

</nav>

@push('scripts')
<script src="{{ asset('js/public-navigation.js') }}"></script>
@endpush

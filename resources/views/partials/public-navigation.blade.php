{{-- ═══════════════════════════════════════════════════════════════════════
     PUBLIC NAVIGATION
     Desktop : dark top bar  +  gold link bar below
     Mobile  : dark top bar  +  hamburger  →  full gold drawer slides in
════════════════════════════════════════════════════════════════════════ --}}

{{-- ── Top bar ──────────────────────────────────────────────────────────── --}}
<header class="main-header">
    <a href="{{ url('/') }}" class="header-brand">
        <img src="{{ asset('img/logo.png') }}" alt="Smart Booking" class="logo">
        <span class="logo-text">Smart <span style="color:var(--gold);">Booking</span></span>
    </a>

    @auth
    <div class="user-display">
        @if(Auth::user()->profile_picture)
            <img src="{{ asset('storage/'.Auth::user()->profile_picture) }}"
                 alt="{{ Auth::user()->name }}"
                 style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid var(--gold);"
                 onerror="this.style.display='none'">
        @else
            <i class="fas fa-user-circle" style="font-size:22px;color:var(--gold);"></i>
        @endif
        <span>{{ explode(' ', Auth::user()->name)[0] }}</span>
    </div>
    @endauth

    {{-- Hamburger — mobile only --}}
    <button class="mob-hamburger" id="mobHamburger" aria-label="Open menu">
        <span></span><span></span><span></span>
    </button>
</header>

{{-- ── Desktop gold nav bar ─────────────────────────────────────────────── --}}
<nav class="nav-container desktop-nav">
    <a href="{{ url('/') }}"                     class="{{ request()->is('/')                  ? 'active' : '' }}"><i class="fas fa-home"></i> Home</a>
    <a href="{{ route('discover') }}"             class="{{ request()->routeIs('discover')      ? 'active' : '' }}"><i class="fas fa-compass"></i> Discover</a>
    <a href="{{ route('destinations') }}"         class="{{ request()->routeIs('destinations*') ? 'active' : '' }}"><i class="fas fa-map-marker-alt"></i> Destinations</a>
    <a href="{{ route('plan-trip') }}"            class="{{ request()->routeIs('plan-trip')     ? 'active' : '' }}"><i class="fas fa-route"></i> Plan Trip</a>
    <a href="{{ route('flights.index') }}"        class="{{ request()->routeIs('flights.*')     ? 'active' : '' }}"><i class="fas fa-plane"></i> Flights</a>
    <a href="{{ route('accommodations.index') }}" class="{{ request()->routeIs('accommodations.*') ? 'active' : '' }}"><i class="fas fa-hotel"></i> Stays</a>
    <a href="{{ route('community') }}"            class="{{ request()->routeIs('community*')   ? 'active' : '' }}"><i class="fas fa-users"></i> Community</a>
    @guest
        <a href="{{ route('login') }}"    style="margin-left:auto;"><i class="fas fa-sign-in-alt"></i> Login</a>
        <a href="{{ route('register') }}"><i class="fas fa-user-plus"></i> Register</a>
    @else
        <a href="{{ route('dashboard') }}" style="margin-left:auto;"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <form method="POST" action="{{ route('logout') }}" style="display:contents;">
            @csrf
            <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
    @endguest
</nav>

{{-- ── Mobile: overlay + full gold drawer ──────────────────────────────── --}}
<div class="mob-overlay" id="mobOverlay"></div>

<nav class="mob-drawer" id="mobDrawer">

    {{-- Drawer header --}}
    <div class="mob-drawer-head">
        <div style="display:flex;align-items:center;gap:10px;">
            <img src="{{ asset('img/logo.png') }}" alt="Smart Booking"
                 style="height:40px;width:auto;filter:brightness(0) invert(1);">
            <span style="font-size:17px;font-weight:700;color:var(--deep);font-family:'Georgia',serif;letter-spacing:.5px;">
                Smart <span style="color:rgba(59,31,43,.6);">Booking</span>
            </span>
        </div>
        <button class="mob-drawer-close" id="mobDrawerClose" aria-label="Close menu">
            <i class="fas fa-times"></i>
        </button>
    </div>

    {{-- Nav links --}}
    <div class="mob-drawer-links">
        <a href="{{ url('/') }}"                     class="mob-link {{ request()->is('/')                  ? 'active' : '' }}"><i class="fas fa-home"></i> Home</a>
        <a href="{{ route('discover') }}"             class="mob-link {{ request()->routeIs('discover')      ? 'active' : '' }}"><i class="fas fa-compass"></i> Discover</a>
        <a href="{{ route('destinations') }}"         class="mob-link {{ request()->routeIs('destinations*') ? 'active' : '' }}"><i class="fas fa-map-marker-alt"></i> Destinations</a>
        <a href="{{ route('plan-trip') }}"            class="mob-link {{ request()->routeIs('plan-trip')     ? 'active' : '' }}"><i class="fas fa-route"></i> Plan Trip</a>
        <a href="{{ route('flights.index') }}"        class="mob-link {{ request()->routeIs('flights.*')     ? 'active' : '' }}"><i class="fas fa-plane"></i> Flights</a>
        <a href="{{ route('accommodations.index') }}" class="mob-link {{ request()->routeIs('accommodations.*') ? 'active' : '' }}"><i class="fas fa-hotel"></i> Stays</a>
        <a href="{{ route('community') }}"            class="mob-link {{ request()->routeIs('community*')   ? 'active' : '' }}"><i class="fas fa-users"></i> Community</a>

        <div class="mob-divider"></div>

        @guest
            <a href="{{ route('login') }}"    class="mob-link"><i class="fas fa-sign-in-alt"></i> Login</a>
            <a href="{{ route('register') }}" class="mob-link"><i class="fas fa-user-plus"></i> Register</a>
        @else
            <a href="{{ route('dashboard') }}"      class="mob-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="{{ route('bookings.index') }}"  class="mob-link"><i class="fas fa-ticket-alt"></i> My Bookings</a>
            <a href="{{ route('wishlist.index') }}"  class="mob-link"><i class="fas fa-heart"></i> Wishlist</a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="mob-link mob-link-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        @endguest
    </div>

</nav>

@push('scripts')
<script>
(function () {
    var btn     = document.getElementById('mobHamburger');
    var drawer  = document.getElementById('mobDrawer');
    var overlay = document.getElementById('mobOverlay');
    var closeBtn= document.getElementById('mobDrawerClose');

    function openDrawer() {
        drawer.classList.add('open');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeDrawer() {
        drawer.classList.remove('open');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    if (btn)      btn.addEventListener('click', openDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (overlay)  overlay.addEventListener('click', closeDrawer);
    if (drawer)   drawer.querySelectorAll('.mob-link').forEach(function(l) {
        l.addEventListener('click', closeDrawer);
    });
})();
</script>
@endpush

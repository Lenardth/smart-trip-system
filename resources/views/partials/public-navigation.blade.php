<header class="pub-header">
    <div class="pub-header-inner">

        {{-- Logo --}}
        <a href="{{ url('/') }}" class="pub-brand">
            <img src="{{ asset('img/logo.png') }}" alt="Smart Booking" class="pub-logo">
            <span class="pub-brand-text">Smart<span>Booking</span></span>
        </a>

        {{-- Desktop nav links --}}
        <nav class="pub-nav" id="pubNav">
            <a href="{{ url('/') }}"                  class="pub-nav-link {{ request()->is('/')                  ? 'active' : '' }}"><i class="fas fa-home"></i> Home</a>
            <a href="{{ route('discover') }}"          class="pub-nav-link {{ request()->routeIs('discover')      ? 'active' : '' }}"><i class="fas fa-compass"></i> Discover</a>
            <a href="{{ route('destinations') }}"      class="pub-nav-link {{ request()->routeIs('destinations*') ? 'active' : '' }}"><i class="fas fa-map-marker-alt"></i> Destinations</a>
            <a href="{{ route('plan-trip') }}"         class="pub-nav-link {{ request()->routeIs('plan-trip')     ? 'active' : '' }}"><i class="fas fa-route"></i> Plan Trip</a>
            <a href="{{ route('flights.index') }}"     class="pub-nav-link {{ request()->routeIs('flights.*')     ? 'active' : '' }}"><i class="fas fa-plane"></i> Flights</a>
            <a href="{{ route('accommodations.index') }}" class="pub-nav-link {{ request()->routeIs('accommodations.*') ? 'active' : '' }}"><i class="fas fa-hotel"></i> Stays</a>
            <a href="{{ route('community') }}"         class="pub-nav-link {{ request()->routeIs('community*')   ? 'active' : '' }}"><i class="fas fa-users"></i> Community</a>
        </nav>

        {{-- Right side actions --}}
        <div class="pub-header-actions">
            @guest
                <a href="{{ route('login') }}"    class="pub-btn-ghost">Login</a>
                <a href="{{ route('register') }}" class="pub-btn-solid">Get Started</a>
            @else
                <a href="{{ route('dashboard') }}" class="pub-btn-ghost"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <div class="pub-user-chip">
                    @if(Auth::user()->profile_picture)
                        <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}"
                             alt="{{ Auth::user()->name }}"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        <span class="pub-user-initial" style="display:none;">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</span>
                    @else
                        <span class="pub-user-initial">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</span>
                    @endif
                    <span class="pub-user-name">{{ explode(' ', Auth::user()->name)[0] }}</span>
                </div>
            @endguest

            {{-- Hamburger (mobile only) --}}
            <button class="pub-hamburger" id="pubHamburger" aria-label="Open menu">
                <span></span><span></span><span></span>
            </button>
        </div>

    </div>
</header>

{{-- Mobile drawer overlay --}}
<div class="pub-overlay" id="pubOverlay"></div>

{{-- Mobile slide-in drawer --}}
<nav class="pub-drawer" id="pubDrawer">
    <div class="pub-drawer-head">
        <div style="display:flex;align-items:center;gap:10px;">
            <img src="{{ asset('img/logo.png') }}" alt="Smart Booking" style="height:36px;width:auto;filter:brightness(0) invert(1);">
            <span style="font-size:16px;font-weight:700;color:#fff;font-family:'Georgia',serif;">Smart<span style="color:#c9a96e;">Booking</span></span>
        </div>
        <button class="pub-drawer-close" id="pubDrawerClose"><i class="fas fa-times"></i></button>
    </div>
    <div class="pub-drawer-links">
        <a href="{{ url('/') }}"                     class="pub-drawer-link {{ request()->is('/')                  ? 'active' : '' }}"><i class="fas fa-home"></i> Home</a>
        <a href="{{ route('discover') }}"             class="pub-drawer-link {{ request()->routeIs('discover')      ? 'active' : '' }}"><i class="fas fa-compass"></i> Discover</a>
        <a href="{{ route('destinations') }}"         class="pub-drawer-link {{ request()->routeIs('destinations*') ? 'active' : '' }}"><i class="fas fa-map-marker-alt"></i> Destinations</a>
        <a href="{{ route('plan-trip') }}"            class="pub-drawer-link {{ request()->routeIs('plan-trip')     ? 'active' : '' }}"><i class="fas fa-route"></i> Plan Trip</a>
        <a href="{{ route('flights.index') }}"        class="pub-drawer-link {{ request()->routeIs('flights.*')     ? 'active' : '' }}"><i class="fas fa-plane"></i> Flights</a>
        <a href="{{ route('accommodations.index') }}" class="pub-drawer-link {{ request()->routeIs('accommodations.*') ? 'active' : '' }}"><i class="fas fa-hotel"></i> Stays</a>
        <a href="{{ route('community') }}"            class="pub-drawer-link {{ request()->routeIs('community*')   ? 'active' : '' }}"><i class="fas fa-users"></i> Community</a>
        <div style="height:1px;background:rgba(255,255,255,.1);margin:8px 16px;"></div>
        @guest
            <a href="{{ route('login') }}"    class="pub-drawer-link"><i class="fas fa-sign-in-alt"></i> Login</a>
            <a href="{{ route('register') }}" class="pub-drawer-link"><i class="fas fa-user-plus"></i> Register</a>
        @else
            <a href="{{ route('dashboard') }}"     class="pub-drawer-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="{{ route('bookings.index') }}" class="pub-drawer-link"><i class="fas fa-ticket-alt"></i> My Bookings</a>
            <a href="{{ route('wishlist.index') }}" class="pub-drawer-link"><i class="fas fa-heart"></i> Wishlist</a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="pub-drawer-link" style="width:100%;background:none;border:none;cursor:pointer;text-align:left;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        @endguest
    </div>
</nav>

@push('scripts')
<script>
(function () {
    var hamburger = document.getElementById('pubHamburger');
    var drawer    = document.getElementById('pubDrawer');
    var overlay   = document.getElementById('pubOverlay');
    var closeBtn  = document.getElementById('pubDrawerClose');
    function open()  { drawer.classList.add('open'); overlay.classList.add('show'); document.body.style.overflow = 'hidden'; }
    function close() { drawer.classList.remove('open'); overlay.classList.remove('show'); document.body.style.overflow = ''; }
    if (hamburger) hamburger.addEventListener('click', open);
    if (closeBtn)  closeBtn.addEventListener('click', close);
    if (overlay)   overlay.addEventListener('click', close);
    if (drawer)    drawer.querySelectorAll('.pub-drawer-link').forEach(function(l){ l.addEventListener('click', close); });
})();
</script>
@endpush

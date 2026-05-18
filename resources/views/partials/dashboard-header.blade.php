<header class="dash-header">
    <div class="dash-header-left">
        <button class="dash-menu-btn" data-action="toggleSidebar" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>

        <a href="{{ route('dashboard') }}" class="dash-logo-link">
            <img src="{{ asset('img/logo.png') }}" alt="Smart Booking" class="dash-logo-img">
        </a>
    </div>

    <div class="dash-header-right">
        <div class="dash-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search flights or plan a trip…" id="dashSearchInput"
                   data-keydown-action="dashboardSearch" data-key="Enter">
        </div>

        <a href="{{ route('dashboard') }}" class="dash-profile-chip" title="Dashboard">
            @auth
            <div class="dash-avatar-wrap">
                @if(Auth::user()->profile_picture)
                    <img src="{{ asset('storage/'.Auth::user()->profile_picture) }}"
                         alt="{{ Auth::user()->name }}"
                         class="dash-avatar-img"
                         data-error-action="hide-show-next">
                    <span class="dash-avatar-init hidden">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</span>
                @else
                    <span class="dash-avatar-init">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</span>
                @endif
            </div>
            <span class="dash-profile-name">{{ explode(' ', Auth::user()->name)[0] }}</span>
            @endauth
        </a>
    </div>
</header>

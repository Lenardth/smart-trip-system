<div class="sidebar" id="sidebar">

    <nav class="sidebar-menu">
        @php
            $dashboardTab = request()->routeIs('dashboard') ? request('tab', 'overview') : null;

            $menuSections = [
                [
                    'label' => 'Travel',
                    'items' => [
                        ['href' => '/',                   'icon' => 'fa-home',           'label' => 'Home'],
                        ['href' => route('dashboard'),    'icon' => 'fa-tachometer-alt', 'label' => 'Dashboard', 'dashboard_tab' => 'overview'],
                        ['href' => '/discover',           'icon' => 'fa-compass',        'label' => 'Discover'],
                        ['href' => '/plan-trip',          'icon' => 'fa-route',          'label' => 'Plan Trip'],
                        ['href' => '/flights',            'icon' => 'fa-plane',          'label' => 'Flights'],
                        ['href' => '/accommodations',     'icon' => 'fa-hotel',          'label' => 'Stays'],
                        ['href' => '/bookings',           'icon' => 'fa-ticket-alt',     'label' => 'Bookings', 'badge' => 'bookingsCount'],
                    ],
                ],
                [
                    'label' => 'Account',
                    'items' => [
                        ['href' => route('dashboard', ['tab' => 'settings']), 'icon' => 'fa-cog', 'label' => 'Settings', 'dashboard_tab' => 'settings'],
                    ],
                ],
            ];
        @endphp

        @foreach($menuSections as $section)
            <p class="sidebar-section-label">{{ $section['label'] }}</p>
            @foreach($section['items'] as $item)
                @php
                    if (isset($item['dashboard_tab'])) {
                        $isActive = $dashboardTab === $item['dashboard_tab'];
                    } else {
                        $path = parse_url($item['href'], PHP_URL_PATH) ?: '/';
                        $isActive = ($activeMenu ?? request()->getPathInfo()) === $path;
                    }
                @endphp
                <a href="{{ $item['href'] }}" class="menu-item {{ $isActive ? 'active' : '' }}">
                    <i class="fas {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                    @if(!empty($item['badge']) && $item['badge'] === 'bookingsCount')
                        @php
                            $badgeVal = \App\Models\Booking::where('user_id', Auth::id())->whereIn('status', ['confirmed', 'pending'])->count();
                        @endphp
                        <span class="menu-badge" id="bookingsCount">{{ $badgeVal ?: 0 }}</span>
                    @endif
                </a>
            @endforeach
        @endforeach
    </nav>

    <div class="sidebar-utilities">
        <p class="sidebar-section-label">Preferences</p>
        <div id="currencyPickerWrapper" class="currency-picker-wrapper currency-picker-sidebar"></div>
    </div>

    <div class="sidebar-footer">
        <div class="user-profile">
            <div class="user-avatar">
                @if(Auth::check() && Auth::user()->profile_picture)
                    <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}"
                         alt="{{ Auth::user()->name }}"
                         class="avatar-img"
                         data-error-action="hide-show-next">
                    <div class="avatar-placeholder hidden">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                @else
                    <div class="avatar-placeholder">
                        {{ Auth::check() ? strtoupper(substr(Auth::user()->name, 0, 1)) : 'U' }}
                    </div>
                @endif
            </div>
            <div class="user-info">
                <h4>{{ Auth::user()->name ?? 'User' }}</h4>
                <span class="user-type-label">{{ Auth::user()->user_type ?? 'traveler' }}</span>
            </div>
            <button class="logout-btn" data-action="logout" title="Logout" aria-label="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </div>
</div>

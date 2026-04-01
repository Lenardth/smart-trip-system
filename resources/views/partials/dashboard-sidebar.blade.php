<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo">
        <div class="logo-text">Smart Booking</div>
    </div>

    <nav class="sidebar-menu">
        @php
            $menu = [
                ['href' => '/',              'icon' => 'fa-home',           'label' => 'Home'],
                ['href' => '/dashboard',     'icon' => 'fa-tachometer-alt', 'label' => 'Dashboard'],
                ['href' => '/plan-trip',     'icon' => 'fa-route',          'label' => 'Plan Trip'],
                ['href' => '/flights',       'icon' => 'fa-plane',          'label' => 'Book Flights'],
                ['href' => '/bookings',      'icon' => 'fa-ticket-alt',     'label' => 'My Bookings', 'badge' => 'bookingsCount'],
                ['href' => '/discover',      'icon' => 'fa-compass',        'label' => 'Discover'],
                ['href' => '/destinations',  'icon' => 'fa-map-marked-alt', 'label' => 'Destinations'],
                ['href' => '/community',     'icon' => 'fa-users',          'label' => 'Community'],
                ['href' => '/wishlist',      'icon' => 'fa-heart',          'label' => 'Wishlist',    'badge' => 'savedCount'],
                ['href' => '/chat',          'icon' => 'fa-comment-dots',   'label' => 'Messages'],
                ['href' => '/notifications', 'icon' => 'fa-bell',           'label' => 'Notifications'],
            ];
            $active = $activeMenu ?? request()->getPathInfo();
        @endphp

        @foreach($menu as $item)
            <a href="{{ $item['href'] }}" class="menu-item {{ $active === $item['href'] ? 'active' : '' }}">
                <i class="fas {{ $item['icon'] }}"></i>
                <span>{{ $item['label'] }}</span>
                @if(!empty($item['badge']))
                    <span class="menu-badge" id="{{ $item['badge'] }}">0</span>
                @endif
            </a>
        @endforeach

        <a href="#" class="menu-item" onclick="openSettings(); return false;">
            <i class="fas fa-cog"></i><span>Settings</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-profile">
            <div class="user-avatar" onclick="viewProfile()">
                @if(Auth::check() && Auth::user()->avatar)
                    <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}">
                @else
                    <div class="avatar-placeholder">
                        {{ Auth::check() ? strtoupper(substr(Auth::user()->name, 0, 1)) : 'U' }}
                    </div>
                @endif
            </div>
            <div class="user-info">
                <h4>{{ Auth::user()->name ?? 'User' }}</h4>
            </div>
            <button class="logout-btn" onclick="logout()" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </div>
</div>

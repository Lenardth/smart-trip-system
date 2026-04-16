<div class="sidebar" id="sidebar">

    <nav class="sidebar-menu">
        @php
            $menu = [
                ['href' => '/',              'icon' => 'fa-home',           'label' => 'Home'],
                ['href' => '/dashboard',     'icon' => 'fa-tachometer-alt', 'label' => 'Dashboard'],
                ['href' => '/plan-trip',     'icon' => 'fa-route',          'label' => 'Plan Trip'],
                ['href' => '/itineraries',   'icon' => 'fa-map-marked-alt', 'label' => 'Itineraries'],
                ['href' => '/flights',       'icon' => 'fa-plane',          'label' => 'Book Flights'],
                ['href' => '/bookings',      'icon' => 'fa-ticket-alt',     'label' => 'My Bookings', 'badge' => 'bookingsCount'],
                ['href' => '/discover',      'icon' => 'fa-compass',        'label' => 'Discover'],
                ['href' => '/destinations',  'icon' => 'fa-globe-americas', 'label' => 'Destinations'],
                ['href' => '/community',     'icon' => 'fa-users',          'label' => 'Community'],
                ['href' => '/wishlist',      'icon' => 'fa-heart',          'label' => 'Wishlist', 'badge' => 'savedCount'],
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
                    @php
                        $badgeVal = match($item['badge']) {
                            'bookingsCount' => \App\Models\Booking::where('user_id', Auth::id())->whereIn('status',['confirmed','pending'])->count(),
                            'savedCount'    => \App\Models\SavedDestination::where('user_id', Auth::id())->count(),
                            default         => 0,
                        };
                    @endphp
                    <span class="menu-badge" id="{{ $item['badge'] }}"
                          style="{{ $badgeVal > 0 ? '' : 'display:none;' }}">{{ $badgeVal ?: 0 }}</span>
                @endif
            </a>
        @endforeach

        <a href="#" class="menu-item" onclick="openSettings(); return false;">
            <i class="fas fa-cog"></i><span>Settings</span>
        </a>
        <a href="{{ route('premium') }}" class="menu-item {{ request()->is('premium') ? 'active' : '' }}" style="color:var(--gold);">
            <i class="fas fa-crown"></i>
            <span>{{ Auth::user()->is_premium ? 'Premium' : 'Upgrade' }}</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-profile">
            <div class="user-avatar" onclick="viewProfile()">
                @if(Auth::check() && Auth::user()->profile_picture)
                    <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}"
                         alt="{{ Auth::user()->name }}"
                         style="width:100%;height:100%;object-fit:cover;border-radius:50%;"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="avatar-placeholder" style="display:none;">
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
                <span style="font-size:11px;color:var(--text-sub);text-transform:capitalize;">{{ Auth::user()->user_type ?? 'traveler' }}</span>
            </div>
            <button class="logout-btn" onclick="logout()" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </div>
</div>
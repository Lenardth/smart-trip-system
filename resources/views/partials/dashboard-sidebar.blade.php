<div class="sidebar" id="sidebar">

    <nav class="sidebar-menu">
        @php
            $menu = [
                ['href' => '/',                  'icon' => 'fa-home',           'label' => 'Home'],
                ['href' => '/dashboard',         'icon' => 'fa-tachometer-alt', 'label' => 'Dashboard'],
                ['href' => '/plan-trip',         'icon' => 'fa-route',          'label' => 'Plan Trip'],
                ['href' => '/flights',           'icon' => 'fa-plane',          'label' => 'Flights'],
                ['href' => '/accommodations',    'icon' => 'fa-hotel',          'label' => 'Stays'],
                ['href' => '/bookings',          'icon' => 'fa-ticket-alt',     'label' => 'Bookings', 'badge' => 'bookingsCount'],
            ];
            $active = $activeMenu ?? request()->getPathInfo();
        @endphp

        @foreach($menu as $item)
            <a href="{{ $item['href'] }}" class="menu-item {{ $active === $item['href'] ? 'active' : '' }}">
                <i class="fas {{ $item['icon'] }}"></i>
                <span>{{ $item['label'] }}</span>
                @if(!empty($item['badge']) && $item['badge'] === 'bookingsCount')
                    @php
                        $badgeVal = \App\Models\Booking::where('user_id', Auth::id())->whereIn('status',['confirmed','pending'])->count();
                    @endphp
                    <span class="menu-badge" id="bookingsCount">{{ $badgeVal ?: 0 }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <div class="user-profile">
            <div class="user-avatar">
                @if(Auth::check() && Auth::user()->profile_picture)
                    <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}"
                         alt="{{ Auth::user()->name }}"
                         style="width:100%;height:100%;object-fit:cover;border-radius:50%;"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="avatar-placeholder">
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

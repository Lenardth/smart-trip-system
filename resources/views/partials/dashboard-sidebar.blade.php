<div class="sidebar" id="sidebar">

    <nav class="sidebar-menu">
        @foreach($sidebarSections ?? [] as $section)
            <p class="sidebar-section-label">{{ $section['label'] }}</p>
            @foreach($section['items'] as $item)
                @continue(!empty($item['user_type']) && $item['user_type'] !== (Auth::user()->user_type ?? null))
                <a
                    href="{{ $item['href'] }}"
                    @class([
                        'menu-item',
                        'active' => isset($item['dashboard_tab'])
                            ? $dashboardTab === $item['dashboard_tab']
                            : (($activeMenu ?? request()->getPathInfo()) === (parse_url($item['href'], PHP_URL_PATH) ?: '/')),
                    ])
                >
                    <i class="fas {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                    @if(!empty($item['badge']) && $item['badge'] === 'bookingsCount')
                        <span class="menu-badge" id="bookingsCount">{{ $activeBookingsCount ?? 0 }}</span>
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

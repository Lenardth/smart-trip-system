<header class="dash-header">
    <div class="dash-header-left">
        {{-- Mobile sidebar toggle --}}
        <button class="dash-menu-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <div class="dash-page-info">
            <h1 class="dash-page-title">@yield('page-title', 'Dashboard')</h1>
            <p class="dash-page-sub">
                @hasSection('page-description')
                    @yield('page-description')
                @else
                    Welcome back, {{ Auth::user()->name ?? 'traveller' }}!
                @endif
            </p>
        </div>
    </div>

    <div class="dash-header-right">
        {{-- Search --}}
        <div class="dash-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search destinations, flights…" id="dashSearchInput"
                   onkeydown="if(event.key==='Enter'&&this.value.trim())window.location='/discover?q='+encodeURIComponent(this.value.trim())">
        </div>

        {{-- Notifications bell --}}
        <div class="dash-notif-wrap">
            <button class="dash-icon-btn" onclick="toggleNotifications()" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                <span class="dash-badge" id="notificationCount" style="display:none;">0</span>
            </button>

            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-header">
                    <h3><i class="fas fa-bell"></i> Notifications</h3>
                    <div class="notification-actions">
                        <button class="compose-message-btn" onclick="openComposeMessage()" title="New message">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                        <button class="mark-all-read" onclick="markAllRead()">Mark all read</button>
                    </div>
                </div>
                <div class="notification-tabs">
                    <div class="notification-tab active" data-tab="all"      onclick="switchNotificationTab('all')"><i class="fas fa-th-large"></i> All</div>
                    <div class="notification-tab"        data-tab="chat"     onclick="switchNotificationTab('chat')"><i class="fas fa-comments"></i> Chat</div>
                    <div class="notification-tab"        data-tab="activity" onclick="switchNotificationTab('activity')"><i class="fas fa-bell"></i> Activity</div>
                </div>
                <div class="notification-list" id="notificationList"></div>
                <div class="notification-footer">
                    <a href="{{ route('notifications.index') }}" class="view-all-notifications">View all notifications</a>
                </div>
            </div>
        </div>

        {{-- Messages --}}
        <a href="{{ route('chat.index') }}" class="dash-icon-btn" aria-label="Messages">
            <i class="fas fa-comment-dots"></i>
        </a>

        {{-- Profile chip --}}
        <a href="{{ route('profile.edit') }}" class="dash-profile-chip">
            @auth
                @if(Auth::user()->profile_picture)
                    <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}"
                         alt="{{ Auth::user()->name }}"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <span class="dash-avatar-init" style="display:none;">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</span>
                @else
                    <span class="dash-avatar-init">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</span>
                @endif
                <span class="dash-profile-name">{{ explode(' ', Auth::user()->name)[0] }}</span>
                <i class="fas fa-chevron-down" style="font-size:10px;opacity:.5;"></i>
            @endauth
        </a>
    </div>
</header>

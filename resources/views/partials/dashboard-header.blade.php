<header class="dash-header">
    <div class="dash-header-left">
        <button class="dash-menu-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>

        <a href="{{ route('dashboard') }}" class="dash-logo-link">
            <img src="{{ asset('img/logo.png') }}" alt="Smart Booking" class="dash-logo-img">
        </a>
    </div>

    <div class="dash-header-right">
        <div class="dash-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search…" id="dashSearchInput"
                   onkeydown="if(event.key==='Enter'&&this.value.trim())window.location='/discover?q='+encodeURIComponent(this.value.trim())">
        </div>

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
                    <a href="{{ route('notifications.index') }}" class="view-all-notifications">View all</a>
                </div>
            </div>
        </div>

        <a href="{{ route('chat.index') }}" class="dash-icon-btn" aria-label="Messages">
            <i class="fas fa-comment-dots"></i>
        </a>

        <a href="{{ route('profile.edit') }}" class="dash-profile-chip">
            @auth
            <div class="dash-avatar-wrap">
                @if(Auth::user()->profile_picture)
                    <img src="{{ asset('storage/'.Auth::user()->profile_picture) }}"
                         alt="{{ Auth::user()->name }}"
                         class="dash-avatar-img"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <span class="dash-avatar-init" style="display:none;">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</span>
                @else
                    <span class="dash-avatar-init">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</span>
                @endif
            </div>
            <span class="dash-profile-name">{{ explode(' ', Auth::user()->name)[0] }}</span>
            @endauth
        </a>
    </div>
</header>
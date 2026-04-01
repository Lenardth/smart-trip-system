<div class="top-nav">
    <div class="nav-left">
        <h1>@yield('page-title', 'Dashboard')</h1>
        <p>@yield('page-description', 'Welcome back! Here\'s what\'s happening with your trips today.')</p>
    </div>
    <div class="nav-right">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search destinations, hotels, flights...">
        </div>

        <div class="notification-wrapper">
            <button class="notification-btn" onclick="toggleNotifications()">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notificationCount" style="display:none;">0</span>
            </button>

            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-header">
                    <h3><i class="fas fa-bell"></i> Notifications</h3>
                    <div class="notification-actions">
                        <button class="compose-message-btn" onclick="openComposeMessage()" title="Send a message">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                        <button class="mark-all-read" onclick="markAllRead()">Mark all as read</button>
                    </div>
                </div>

                <div class="notification-tabs">
                    <div class="notification-tab active" data-tab="all" onclick="switchNotificationTab('all')">
                        <i class="fas fa-th-large"></i> All
                    </div>
                    <div class="notification-tab" data-tab="chat" onclick="switchNotificationTab('chat')">
                        <i class="fas fa-comments"></i> Chat
                    </div>
                    <div class="notification-tab" data-tab="activity" onclick="switchNotificationTab('activity')">
                        <i class="fas fa-bell"></i> Activity
                    </div>
                </div>

                <div class="notification-list" id="notificationList"></div>

                <div class="notification-footer">
                    <a href="{{ route('notifications.index') }}" class="view-all-notifications">View All Notifications</a>
                </div>
            </div>
        </div>

        <div class="nav-profile-pic" onclick="viewProfile()">
            @auth
                @if(Auth::user()->avatar)
                    <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}">
                @else
                    <div class="placeholder">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                @endif
            @endauth
        </div>
    </div>
</div>

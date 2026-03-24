<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite([
    'resources/css/blade/notifications/index.css',
    'resources/js/blade/notifications/index.js',
    'resources/css/blade/base.css',
    'resources/js/blade/base.js',
])

</head>
<body>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo">
            <div class="logo-text">Smart Booking</div>
        </div>
        <nav class="sidebar-menu">
            <a href="/" class="menu-item"><i class="fas fa-home"></i><span>Home</span></a>
            <a href="/dashboard" class="menu-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="/plan-trip" class="menu-item"><i class="fas fa-route"></i><span>Plan Trip</span></a>
            <a href="/flights" class="menu-item"><i class="fas fa-plane"></i><span>Book Flights</span></a>
            <a href="/bookings" class="menu-item"><i class="fas fa-ticket-alt"></i><span>My Bookings</span></a>
            <a href="/discover" class="menu-item"><i class="fas fa-compass"></i><span>Discover</span></a>
            <a href="/destinations" class="menu-item"><i class="fas fa-map-marked-alt"></i><span>Destinations</span></a>
            <a href="/community" class="menu-item"><i class="fas fa-users"></i><span>Community</span></a>
            <a href="/wishlist" class="menu-item"><i class="fas fa-heart"></i><span>Wishlist</span></a>
            <a href="/chat" class="menu-item"><i class="fas fa-comment-dots"></i><span>Messages</span></a>
            <a href="/notifications" class="menu-item active"><i class="fas fa-bell"></i><span>Notifications</span></a>
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

    <div class="notif-page">

        <div class="notif-header">
            <div class="notif-header-left">
                <h1><i class="fas fa-bell" style="color:var(--gold);margin-right:10px;"></i>Notifications</h1>
                <p id="notifSubtitle">Loading…</p>
            </div>
            <div class="notif-header-actions">
                <button class="btn-mark-all" onclick="markAllRead()">
                    <i class="fas fa-check-double"></i> Mark all as read
                </button>
            </div>
        </div>

        <div class="notif-tabs">
            <div class="notif-tab active" data-tab="all" onclick="switchTab('all')">
                <i class="fas fa-th-large"></i> All
                <span class="tab-count" id="countAll">0</span>
            </div>
            <div class="notif-tab" data-tab="chat" onclick="switchTab('chat')">
                <i class="fas fa-comments"></i> Messages
                <span class="tab-count" id="countChat">0</span>
            </div>
            <div class="notif-tab" data-tab="activity" onclick="switchTab('activity')">
                <i class="fas fa-bell"></i> Activity
                <span class="tab-count" id="countActivity">0</span>
            </div>
        </div>

        <div class="notif-list" id="notifList">
            <div class="notif-loading">
                <i class="fas fa-spinner fa-spin"></i>
                Loading notifications…
            </div>
        </div>

    </div>

    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    </body>
</html>

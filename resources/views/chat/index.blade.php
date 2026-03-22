<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite([
        'resources/css/app.css',
        'resources/css/pages/base.css',
        'resources/css/pages/dashboard.css',
        'resources/css/pages/chat.css',
        'resources/js/pages/base.js',
        'resources/js/pages/chat.js'
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
            <a href="/chat" class="menu-item active"><i class="fas fa-comment-dots"></i><span>Messages</span></a>
            <a href="#" class="menu-item" onclick="openSettings(); return false;"><i class="fas fa-cog"></i><span>Settings</span></a>
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

    <div class="chat-page" id="chatPage">

        <div class="chat-sidebar" id="chatSidebar">
            <div class="chat-sidebar-header">
                <i class="fas fa-comment-dots" style="color:var(--gold);font-size:20px;"></i>
                <h2>Messages</h2>
                <a href="/dashboard" title="Back to Dashboard"><i class="fas fa-arrow-left"></i></a>
            </div>

            <div class="chat-sidebar-search">
                <i class="fas fa-search chat-sidebar-search-icon"></i>
                <input type="text" id="pageSearchInput" placeholder="Search people…"
                    oninput="PageChat.onSearch(event)" autocomplete="off">
                <div id="pageSearchResults" class="search-results-dropdown" style="display:none;"></div>
            </div>

            <div class="conv-list" id="pageConvList">
                <div class="conv-empty">
                    <i class="fas fa-spinner fa-spin"></i>
                    Loading…
                </div>
            </div>
        </div>

        <div class="chat-thread" id="chatThread">

            <div class="thread-empty-state" id="threadEmptyState">
                <i class="fas fa-comment-dots"></i>
                <h3>Your Messages</h3>
                <p>Select a conversation or search for someone to start chatting.</p>
            </div>

            <div id="threadView">
                <div class="thread-header">
                    <div class="thread-header-avatar" id="threadAvatar"></div>
                    <div class="thread-header-info">
                        <strong id="threadName"></strong>
                        <small id="threadSub"></small>
                    </div>
                </div>
                <div class="thread-messages" id="threadMessages"></div>
                <div class="thread-input-area">
                    <textarea id="threadInput" rows="1" placeholder="Type a message… (Enter to send)"
                        oninput="PageChat.autoResize(this)"
                        onkeydown="PageChat.handleKey(event)"></textarea>
                    <button class="thread-send-btn" id="threadSendBtn" onclick="PageChat.send()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

    <script>
        window.__dashboardConfig = {
            pusherKey:     "{{ config('broadcasting.connections.pusher.key') }}",
            pusherCluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
            userId:        {{ Auth::id() ?? 'null' }},
            user: {
                name:   "{{ Auth::user()->name   ?? '' }}",
                avatar: "{{ Auth::user()->avatar ?? '' }}",
                id:     {{ Auth::id() ?? 'null' }},
            },
            openUserId:     {{ isset($other) ? $other->id   : 'null' }},
            openUserName:   "{{ isset($other) ? $other->name : '' }}",
            openUserAvatar: "{{ isset($other) ? ($other->avatar ?? '') : '' }}",
        };
    </script>

</body>
</html>

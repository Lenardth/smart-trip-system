<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite([
        'resources/css/app.css',
        'resources/css/pages/base.css',
        'resources/css/pages/dashboard.css',
        'resources/js/pages/base.js',
        'resources/js/pages/dashboard.js'
    ])
    <style>
        .notif-page {
            flex: 1;
            margin-left: 260px;
            max-width: calc(100% - 260px);
            min-height: 100vh;
            background: var(--cream);
            padding: 30px;
        }

        .notif-header {
            background: linear-gradient(135deg, white, var(--card-bg));
            border-radius: 15px;
            padding: 24px 30px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid var(--border);
            box-shadow: 0 4px 15px rgba(59,31,43,.08);
        }

        .notif-header-left h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--deep);
            margin: 0 0 4px;
        }

        .notif-header-left p {
            font-size: 14px;
            color: var(--text-muted);
            margin: 0;
        }

        .notif-header-actions {
            display: flex;
            gap: 10px;
        }

        .btn-mark-all {
            background: linear-gradient(135deg, var(--gold), var(--gold-hover));
            color: var(--deep);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all .25s;
            box-shadow: 0 4px 8px rgba(201,169,110,.3);
        }

        .btn-mark-all:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(201,169,110,.4);
        }

        .notif-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
        }

        .notif-tab {
            padding: 10px 22px;
            border-radius: 8px;
            border: 2px solid var(--border);
            background: white;
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .notif-tab:hover {
            border-color: var(--gold);
            color: var(--deep);
        }

        .notif-tab.active {
            background: linear-gradient(135deg, var(--deep), var(--deep-alt));
            border-color: var(--deep);
            color: white;
        }

        .notif-tab .tab-count {
            background: rgba(255,255,255,.25);
            border-radius: 10px;
            padding: 1px 7px;
            font-size: 11px;
            font-weight: 700;
        }

        .notif-tab:not(.active) .tab-count {
            background: var(--border);
            color: var(--deep);
        }

        .notif-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .notif-item {
            background: linear-gradient(135deg, white, var(--card-bg));
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px 22px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            cursor: pointer;
            transition: all .2s;
            position: relative;
            box-shadow: 0 2px 8px rgba(59,31,43,.05);
        }

        .notif-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(59,31,43,.1);
            border-color: var(--gold);
        }

        .notif-item.unread {
            border-left: 4px solid var(--gold);
            background: linear-gradient(135deg, #fffaf4, var(--card-bg));
        }

        .notif-item.unread::before {
            content: '';
            position: absolute;
            top: 20px;
            right: 20px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--gold);
        }

        .notif-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
        }

        .notif-icon.chat     { background: linear-gradient(135deg,#e3f2fd,#bbdefb); color: #1565c0; }
        .notif-icon.booking  { background: linear-gradient(135deg,#e8f5e9,#c8e6c9); color: #2e7d32; }
        .notif-icon.trip     { background: linear-gradient(135deg,#fff3e0,#ffe0b2); color: #e65100; }
        .notif-icon.photo    { background: linear-gradient(135deg,#f3e5f5,#e1bee7); color: #6a1b9a; }
        .notif-icon.system   { background: linear-gradient(135deg,#fce4ec,#f8bbd0); color: #880e4f; }

        .notif-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }

        .notif-body {
            flex: 1;
            min-width: 0;
        }

        .notif-body h4 {
            font-size: 15px;
            font-weight: 600;
            color: var(--deep);
            margin: 0 0 5px;
        }

        .notif-body p {
            font-size: 13px;
            color: var(--text-muted);
            margin: 0 0 8px;
            line-height: 1.5;
        }

        .notif-time {
            font-size: 12px;
            color: var(--border-soft);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .notif-time i { color: var(--gold); }

        .notif-empty {
            text-align: center;
            padding: 80px 20px;
            background: linear-gradient(135deg, white, var(--card-bg));
            border-radius: 15px;
            border: 1px solid var(--border);
        }

        .notif-empty i {
            font-size: 56px;
            color: var(--border-soft);
            display: block;
            margin-bottom: 16px;
        }

        .notif-empty h3 {
            font-size: 22px;
            color: var(--deep);
            font-weight: normal;
            margin: 0 0 8px;
        }

        .notif-empty p {
            font-size: 15px;
            color: var(--text-muted);
            margin: 0;
        }

        .notif-loading {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
            font-size: 15px;
        }

        .notif-loading i {
            font-size: 32px;
            color: var(--gold);
            display: block;
            margin-bottom: 12px;
        }

        @media (max-width: 768px) {
            .notif-page {
                margin-left: 0;
                max-width: 100%;
                padding: 16px;
            }
            .notif-header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }
            .notif-tabs {
                flex-wrap: wrap;
            }
        }
    </style>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        window.__dashboardConfig = {
            userId: {{ Auth::id() ?? 'null' }},
            user: {
                name:   "{{ Auth::user()->name   ?? '' }}",
                avatar: "{{ Auth::user()->avatar ?? '' }}",
                type:   "{{ Auth::user()->type   ?? 'traveler' }}",
            }
        };

        var allNotifications = [];
        var currentTab       = 'all';

        function csrfToken() {
            var m = document.querySelector('meta[name="csrf-token"]');
            return m ? m.content : '';
        }

        function loadNotifications() {
            fetch('/api/notifications')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    allNotifications = data.notifications || [];
                    updateCounts();
                    render();
                })
                .catch(function () {
                    document.getElementById('notifList').innerHTML =
                        '<div class="notif-empty"><i class="fas fa-exclamation-circle"></i><h3>Could not load notifications</h3><p>Please try refreshing the page.</p></div>';
                });
        }

        function updateCounts() {
            var chat     = allNotifications.filter(function (n) { return n.type === 'chat'; });
            var activity = allNotifications.filter(function (n) { return n.type !== 'chat'; });
            document.getElementById('countAll').textContent      = allNotifications.length;
            document.getElementById('countChat').textContent     = chat.length;
            document.getElementById('countActivity').textContent = activity.length;

            var unread = allNotifications.filter(function (n) { return !n.read; }).length;
            document.getElementById('notifSubtitle').textContent =
                unread > 0 ? unread + ' unread notification' + (unread > 1 ? 's' : '') : 'You\'re all caught up!';
        }

        function switchTab(tab) {
            currentTab = tab;
            document.querySelectorAll('.notif-tab').forEach(function (t) { t.classList.remove('active'); });
            document.querySelector('[data-tab="' + tab + '"]').classList.add('active');
            render();
        }

        var TYPE_ICONS = {
            chat:    'fas fa-comments',
            booking: 'fas fa-ticket-alt',
            trip:    'fas fa-route',
            photo:   'fas fa-images',
            system:  'fas fa-info-circle'
        };

        var TYPE_ROUTES = {
            chat:    '/chat',
            booking: '/bookings',
            trip:    '/plan-trip'
        };

        function render() {
            var list = document.getElementById('notifList');
            var items = allNotifications;
            if (currentTab === 'chat')     items = allNotifications.filter(function (n) { return n.type === 'chat'; });
            if (currentTab === 'activity') items = allNotifications.filter(function (n) { return n.type !== 'chat'; });

            if (!items.length) {
                list.innerHTML =
                    '<div class="notif-empty">' +
                        '<i class="fas fa-bell-slash"></i>' +
                        '<h3>No notifications</h3>' +
                        '<p>Nothing here yet — check back later.</p>' +
                    '</div>';
                return;
            }

            list.innerHTML = items.map(function (n) {
                var iconContent;
                if (n.user && n.user.avatar) {
                    iconContent = '<img src="' + n.user.avatar + '">';
                } else if (n.user && n.user.initials) {
                    iconContent = '<span style="font-size:16px;font-weight:700;color:white;">' + n.user.initials + '</span>';
                } else {
                    iconContent = '<i class="' + (TYPE_ICONS[n.type] || 'fas fa-bell') + '"></i>';
                }

                var iconBg = (n.user && !n.user.avatar)
                    ? 'style="background:linear-gradient(135deg,var(--gold),var(--deep));"'
                    : '';

                return '<div class="notif-item ' + (n.read ? '' : 'unread') + '" data-id="' + n.id + '">' +
                    '<div class="notif-icon ' + n.type + '" ' + iconBg + '>' + iconContent + '</div>' +
                    '<div class="notif-body">' +
                        '<h4>' + n.title + '</h4>' +
                        '<p>' + n.message + '</p>' +
                        '<div class="notif-time"><i class="fas fa-clock"></i> ' + n.time + '</div>' +
                    '</div>' +
                '</div>';
            }).join('');
        }

        document.getElementById('notifList').addEventListener('click', function (e) {
            var item = e.target.closest('.notif-item[data-id]');
            if (!item) return;
            var id    = item.getAttribute('data-id');
            var notif = allNotifications.find(function (n) { return n.id === id; });
            if (!notif) return;

            if (!notif.read) {
                notif.read = true;
                item.classList.remove('unread');
                item.querySelector('.notif-item\\.unread\\.before');
                updateCounts();
                fetch('/api/notifications/mark-read', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                    body:    JSON.stringify({ ids: [id] }),
                }).catch(function () {});
            }

            var dest = notif.url || TYPE_ROUTES[notif.type];
            if (dest) {
                window.location.href = dest;
            } else if (notif.type === 'photo') {
                window.location.href = '/dashboard';
            }
        });

        function markAllRead() {
            allNotifications = allNotifications.map(function (n) { return Object.assign({}, n, { read: true }); });
            updateCounts();
            render();
            fetch('/api/notifications/mark-all-read', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            }).catch(function () {});
            Swal.fire({ title: 'All marked as read', icon: 'success', timer: 1500, showConfirmButton: false, confirmButtonColor: '#c9a96e' });
        }

        document.addEventListener('DOMContentLoaded', loadNotifications);
    </script>

</body>
</html>

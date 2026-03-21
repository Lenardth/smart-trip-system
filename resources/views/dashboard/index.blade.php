<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard — Smart Booking</title>
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
        .trip-card {
            background: linear-gradient(135deg, white, var(--card-bg, #fff8f2));
            border: 1px solid var(--border, #e2d5c7);
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 14px;
            position: relative;
            transition: box-shadow .25s, transform .25s;
        }
        .trip-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(59,31,43,.12);
        }
        .trip-card-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 10px;
        }
        .trip-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: linear-gradient(135deg, #c9a96e, #b8955a);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            flex-shrink: 0;
        }
        .trip-info { flex: 1; min-width: 0; }
        .trip-info h4 {
            margin: 0 0 3px;
            font-size: 15px;
            font-weight: 700;
            color: #3b1f2b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .trip-info p {
            margin: 0;
            font-size: 12px;
            color: #6b5b4f;
        }
        .trip-cost {
            font-size: 17px;
            font-weight: 700;
            color: #3b1f2b;
            flex-shrink: 0;
        }
        .trip-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 12px;
            color: #6b5b4f;
        }
        .trip-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .trip-meta i { color: #c9a96e; }
        .trip-delete-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            background: none;
            border: none;
            color: #d4c4b0;
            font-size: 13px;
            cursor: pointer;
            padding: 4px;
            transition: color .2s;
        }
        .trip-delete-btn:hover { color: #f44336; }
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
            <a href="/dashboard" class="menu-item active"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="#" class="menu-item" onclick="openGallery(); return false;">
                <i class="fas fa-images"></i><span>My Photos</span>
                <span class="menu-badge" id="photosCount">0</span>
            </a>
            <a href="/plan-trip" class="menu-item"><i class="fas fa-route"></i><span>Plan Trip</span></a>
            <a href="/flights" class="menu-item"><i class="fas fa-plane"></i><span>Book Flights</span></a>
            <a href="/bookings" class="menu-item">
                <i class="fas fa-ticket-alt"></i><span>My Bookings</span>
                <span class="menu-badge" id="bookingsCount">0</span>
            </a>
            <a href="/discover" class="menu-item"><i class="fas fa-compass"></i><span>Discover</span></a>
            <a href="/destinations" class="menu-item"><i class="fas fa-map-marked-alt"></i><span>Destinations</span></a>
            <a href="/community" class="menu-item"><i class="fas fa-users"></i><span>Community</span></a>
            <a href="/wishlist" class="menu-item">
                <i class="fas fa-heart"></i><span>Wishlist</span>
                <span class="menu-badge" id="savedCount">0</span>
            </a>
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
                            {{ Auth::check()
                                ? strtoupper(substr(Auth::user()->name, 0, 1) . (str_contains(Auth::user()->name, ' ') ? substr(Auth::user()->name, strpos(Auth::user()->name, ' ') + 1, 1) : ''))
                                : 'U' }}
                        </div>
                    @endif
                </div>

                <div class="user-info">
                    <h4 id="userName">{{ Auth::user()->name ?? 'User' }}</h4>
                    <div class="user-badges">
                        <span class="user-type-badge {{ Auth::user()->type ?? 'traveler' }}" id="userTypeBadge">
                            <i class="fas fa-user"></i>
                            <span id="userTypeText">{{ ucfirst(Auth::user()->type ?? 'Traveler') }}</span>
                        </span>
                        @if(Auth::check() && Auth::user()->verified)
                            <span class="verified-badge">
                                <i class="fas fa-check-circle"></i> Verified
                            </span>
                        @endif
                    </div>
                </div>

                <button class="logout-btn" onclick="logout()" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">

        <div class="top-nav">
            <div class="nav-left">
                <h1 id="welcomeMessage">Welcome Back!</h1>
                <p>Here's what's happening with your trips today</p>
            </div>
            <div class="nav-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search destinations, hotels, flights...">
                </div>

                <button class="notification-btn" onclick="toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="notificationCount" style="display:none;">0</span>
                </button>

                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h3><i class="fas fa-bell"></i> Notifications</h3>
                        <div style="display:flex;gap:10px;">
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
                        <a href="/notifications" class="view-all-notifications">View All Notifications</a>
                    </div>
                </div>

                <div class="nav-profile-pic" onclick="viewProfile()">
                    @if(Auth::check() && Auth::user()->avatar)
                        <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}">
                    @else
                        <div class="placeholder">
                            {{ Auth::check()
                                ? strtoupper(substr(Auth::user()->name, 0, 1) . (str_contains(Auth::user()->name, ' ') ? substr(Auth::user()->name, strpos(Auth::user()->name, ' ') + 1, 1) : ''))
                                : 'U' }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card" onclick="openGallery()">
                <div class="stat-icon photos"><i class="fas fa-images"></i></div>
                <div class="stat-info">
                    <h3 id="statPhotosCount">0</h3>
                    <p>Total Photos</p>
                    <div class="stat-change"><span>Upload to get started</span></div>
                </div>
            </div>
            <div class="stat-card" onclick="window.location.href='/plan-trip'" style="cursor:pointer;">
                <div class="stat-icon trips"><i class="fas fa-route"></i></div>
                <div class="stat-info">
                    <h3 id="statTripsCount">0</h3>
                    <p>Planned Trips</p>
                    <div class="stat-change"><span>No trips yet</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bookings"><i class="fas fa-ticket-alt"></i></div>
                <div class="stat-info">
                    <h3 id="statBookingsCount">0</h3>
                    <p>Active Bookings</p>
                    <div class="stat-change"><span>No bookings yet</span></div>
                </div>
            </div>
            <div class="stat-card" onclick="window.location.href='/wishlist'" style="cursor:pointer;">
                <div class="stat-icon saved"><i class="fas fa-heart"></i></div>
                <div class="stat-info">
                    <h3 id="statSavedCount">0</h3>
                    <p>Saved Places</p>
                    <div class="stat-change"><span>View your wishlist</span></div>
                </div>
            </div>
        </div>

        <div class="actions-grid">
            <div class="action-btn" onclick="uploadPhotos()">
                <i class="fas fa-upload"></i><span>Upload Photos</span>
            </div>
            <div class="action-btn" onclick="window.location.href='/plan-trip'">
                <i class="fas fa-plus-circle"></i><span>Plan Trip</span>
            </div>
            <div class="action-btn" onclick="window.location.href='/flights'">
                <i class="fas fa-plane"></i><span>Book Flights</span>
            </div>
            <div class="action-btn" onclick="window.location.href='/bookings'">
                <i class="fas fa-ticket-alt"></i><span>My Bookings</span>
            </div>
            <div class="action-btn" onclick="window.location.href='/discover'">
                <i class="fas fa-compass"></i><span>Discover</span>
            </div>
            <div class="action-btn" onclick="openSettings()">
                <i class="fas fa-cog"></i><span>Settings</span>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-route"></i> Upcoming Trips</h2>
                    <button class="btn" onclick="window.location.href='/plan-trip'">
                        <i class="fas fa-plus"></i> New Trip
                    </button>
                </div>
                <div class="section-content" id="upcomingTripsContent">
                    <div class="empty-state">
                        <i class="fas fa-route"></i>
                        <h3>No Trips Planned Yet</h3>
                        <p>Start planning your next adventure!</p>
                        <button class="btn" onclick="window.location.href='/plan-trip'">
                            <i class="fas fa-plus"></i> Create Your First Trip
                        </button>
                    </div>
                </div>
            </div>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-clock"></i> Recent Activity</h2>
                </div>
                <div class="section-content">
                    <div class="empty-state">
                        <i class="fas fa-clock"></i>
                        <h3>No Activity Yet</h3>
                        <p>Your recent actions will appear here</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="gallery-modal" id="galleryModal">
        <div class="gallery-header">
            <h3><i class="fas fa-images"></i> My Photos &amp; Videos</h3>
            <button class="gallery-close" onclick="closeGallery()">Done</button>
        </div>
        <div class="gallery-content" id="galleryContent">
            <div class="upload-area" id="uploadArea" onclick="triggerFileInput()">
                <i class="fas fa-cloud-upload-alt"></i>
                <h3>Upload Photos &amp; Videos</h3>
                <p>Drag and drop files here or click to browse</p>
                <input type="file" id="mediaInput" multiple accept="image/*,video/*" onchange="handleFileSelect(event)">
            </div>
            <div class="gallery-grid" id="galleryGrid"></div>
        </div>
        <div class="gallery-toolbar">
            <button onclick="triggerFileInput()"><i class="fas fa-plus"></i></button>
            <button onclick="selectAll()"><i class="fas fa-check-double"></i></button>
            <button onclick="deleteSelected()"><i class="fas fa-trash"></i></button>
            <button onclick="shareSelected()"><i class="fas fa-share"></i></button>
        </div>
    </div>

    <div class="media-viewer" id="mediaViewer">
        <div class="viewer-header">
            <button class="gallery-close" onclick="closeViewer()">
                <i class="fas fa-arrow-left"></i> Back
            </button>
            <div class="viewer-actions">
                <button onclick="editMedia()"><i class="fas fa-edit"></i></button>
                <button onclick="downloadMedia()"><i class="fas fa-download"></i></button>
                <button onclick="shareMedia()"><i class="fas fa-share"></i></button>
                <button onclick="deleteMedia()"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        <div class="viewer-content" id="viewerContent"></div>
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
                name:      "{{ Auth::user()->name      ?? '' }}",
                firstName: "{{ Auth::user()->first_name ?? (Auth::user() ? explode(' ', Auth::user()->name)[0] : '') }}",
                avatar:    "{{ Auth::user()->avatar     ?? '' }}",
                type:      "{{ Auth::user()->type       ?? 'traveler' }}",
                verified:  {{ Auth::user()->verified ? 'true' : 'false' }},
                id:        "{{ Auth::user()->id         ?? '' }}"
            }
        };
    </script>

</body>
</html>

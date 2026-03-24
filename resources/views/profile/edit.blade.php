<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Real-Time Chat Libraries (Pusher + Laravel Echo) -->
    @vite([
    'resources/css/blade/profile/edit.css',
    'resources/js/blade/profile/edit.js',
    'resources/css/blade/base.css',
    'resources/js/blade/base.js',
])

</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <!-- Use actual logo image matching homepage -->
            <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo" id="appLogo">
            <div class="logo-text">Smart Booking</div>
        </div>

        <nav class="sidebar-menu">
            <a href="/" class="menu-item">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="/dashboard" class="menu-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="menu-item" onclick="openGallery(); return false;">
                <i class="fas fa-images"></i>
                <span>My Photos</span>
                <span class="menu-badge" id="photosCount">0</span>
            </a>
            <a href="/plan-trip" class="menu-item">
                <i class="fas fa-route"></i>
                <span>Plan Trip</span>
            </a>
            <a href="/flights" class="menu-item">
                <i class="fas fa-plane"></i>
                <span>Book Flights</span>
            </a>
            <a href="/bookings" class="menu-item">
                <i class="fas fa-ticket-alt"></i>
                <span>My Bookings</span>
                <span class="menu-badge" id="bookingsCount">0</span>
            </a>
            <a href="/discover" class="menu-item">
                <i class="fas fa-compass"></i>
                <span>Discover</span>
            </a>
            <a href="/destinations" class="menu-item">
                <i class="fas fa-map-marked-alt"></i>
                <span>Destinations</span>
            </a>
            <a href="/community" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Community</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-heart"></i>
                <span>Saved</span>
                <span class="menu-badge" id="savedCount">0</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar" onclick="viewProfile()">
                    <!-- Real user avatar -->
                    @if(Auth::check() && Auth::user()->avatar)
                    <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" id="userAvatarImg">
                    @else
                    <div class="avatar-placeholder" id="userInitials">
                        {{ Auth::check() ? strtoupper(substr(Auth::user()->name, 0, 1) . (strpos(Auth::user()->name, ' ') !== false ? substr(Auth::user()->name, strpos(Auth::user()->name, ' ') + 1, 1) : '')) : 'U' }}
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
                <button class="logout-btn" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
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
                    <span class="notification-badge" id="notificationCount" style="display: none;">0</span>
                </button>

                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h3><i class="fas fa-bell"></i> Notifications</h3>
                        <div style="display: flex; gap: 10px;">
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

                    <div class="notification-list" id="notificationList">
                        <!-- Notifications will be loaded here -->
                    </div>

                    <div class="notification-footer">
                        <a href="/notifications" class="view-all-notifications">View All Notifications</a>
                    </div>
                </div>

                <div class="nav-profile-pic" onclick="viewProfile()">
                    @if(Auth::check() && Auth::user()->avatar)
                    <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}">
                    @else
                    <div class="placeholder" id="navUserInitials">
                        {{ Auth::check() ? strtoupper(substr(Auth::user()->name, 0, 1) . (strpos(Auth::user()->name, ' ') !== false ? substr(Auth::user()->name, strpos(Auth::user()->name, ' ') + 1, 1) : '')) : 'U' }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card" onclick="openGallery()">
                <div class="stat-icon photos">
                    <i class="fas fa-images"></i>
                </div>
                <div class="stat-info">
                    <h3 id="statPhotosCount">0</h3>
                    <p>Total Photos</p>
                    <div class="stat-change">
                        <span>Upload to get started</span>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon trips">
                    <i class="fas fa-route"></i>
                </div>
                <div class="stat-info">
                    <h3 id="statTripsCount">0</h3>
                    <p>Planned Trips</p>
                    <div class="stat-change">
                        <span>No trips yet</span>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bookings">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="stat-info">
                    <h3 id="statBookingsCount">0</h3>
                    <p>Active Bookings</p>
                    <div class="stat-change">
                        <span>No bookings yet</span>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon saved">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-info">
                    <h3 id="statSavedCount">0</h3>
                    <p>Saved Places</p>
                    <div class="stat-change">
                        <span>Save your favorites</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="actions-grid">
            <div class="action-btn" onclick="uploadPhotos()">
                <i class="fas fa-upload"></i>
                <span>Upload Photos</span>
            </div>
            <div class="action-btn" onclick="window.location.href='/plan-trip'">
                <i class="fas fa-plus-circle"></i>
                <span>Plan Trip</span>
            </div>
            <div class="action-btn" onclick="window.location.href='/flights'">
                <i class="fas fa-plane"></i>
                <span>Book Flights</span>
            </div>
            <div class="action-btn" onclick="window.location.href='/bookings'">
                <i class="fas fa-ticket-alt"></i>
                <span>My Bookings</span>
            </div>
            <div class="action-btn" onclick="window.location.href='/discover'">
                <i class="fas fa-compass"></i>
                <span>Discover</span>
            </div>
            <div class="action-btn" onclick="openSettings()">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Upcoming Trips -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>
                        <i class="fas fa-route"></i>
                        Upcoming Trips
                    </h2>
                    <button class="btn" onclick="window.location.href='/plan-trip'">
                        <i class="fas fa-plus"></i>
                        New Trip
                    </button>
                </div>
                <div class="section-content">
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

            <!-- Recent Activity -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>
                        <i class="fas fa-clock"></i>
                        Recent Activity
                    </h2>
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

    <!-- iPhone-Style Gallery Modal -->
    <div class="gallery-modal" id="galleryModal">
        <div class="gallery-header">
            <h3><i class="fas fa-images"></i> My Photos & Videos</h3>
            <button class="gallery-close" onclick="closeGallery()">Done</button>
        </div>
        <div class="gallery-content" id="galleryContent">
            <div class="upload-area" onclick="triggerFileInput()" id="uploadArea">
                <i class="fas fa-cloud-upload-alt"></i>
                <h3>Upload Photos & Videos</h3>
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

    <!-- Media Viewer -->
    <div class="media-viewer" id="mediaViewer">
        <div class="viewer-header">
            <button class="gallery-close" onclick="closeViewer()"><i class="fas fa-arrow-left"></i> Back</button>
            <div class="viewer-actions">
                <button onclick="editMedia()"><i class="fas fa-edit"></i></button>
                <button onclick="downloadMedia()"><i class="fas fa-download"></i></button>
                <button onclick="shareMedia()"><i class="fas fa-share"></i></button>
                <button onclick="deleteMedia()"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        <div class="viewer-content" id="viewerContent"></div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    </body>

</html>

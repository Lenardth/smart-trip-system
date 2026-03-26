@extends('layouts.authenticated')

@section('title', 'Dashboard — Smart Booking')

@push('styles')
    @vite(['resources/css/blade/dashboard/index.css', 'resources/js/blade/dashboard/index.js'])
@endpush

@push('body-attrs')
    data-dashboard-user-id="{{ Auth::id() }}"
    data-dashboard-user-name="{{ Auth::user()->name ?? '' }}"
    data-dashboard-user-avatar="{{ Auth::user()->avatar ?? '' }}"
    data-dashboard-user-type="{{ Auth::user()->type ?? '' }}"
    data-dashboard-user-verified="{{ Auth::user()->verified ? '1' : '0' }}"
@endpush

@section('page-class', 'main-content')
@section('page-id', 'mainContent')

@section('content')

    {{-- Config available to JS --}}
    <script>
        window.__dashboardConfig = {
            pusherKey:    "{{ config('broadcasting.connections.pusher.key') }}",
            pusherCluster:"{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}",
            userId: {{ Auth::id() ?? 'null' }},
            user: {
                id:        {{ Auth::id() ?? 'null' }},
                name:      @json(Auth::user()->name ?? ''),
                firstName: @json(Auth::check() ? explode(' ', Auth::user()->name)[0] : ''),
                avatar:    @json(Auth::user()->avatar ?? ''),
                type:      @json(Auth::user()->type ?? ''),
                verified:  {{ Auth::user()->verified ? 'true' : 'false' }}
            }
        };
    </script>

    {{-- Top nav --}}
    <div class="top-nav">
        <div class="nav-left">
            <h1 id="welcomeMessage">Welcome Back!</h1>
            <p>Here's what's happening with your trips today</p>
        </div>
        <div class="nav-right">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search destinations, hotels, flights…">
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
                            ? strtoupper(substr(Auth::user()->name, 0, 1) . (str_contains(Auth::user()->name, ' ')
                                ? substr(Auth::user()->name, strpos(Auth::user()->name, ' ') + 1, 1)
                                : ''))
                            : 'U' }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Stats --}}
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

    {{-- Quick actions --}}
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

    {{-- Trips & Activity --}}
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

@endsection

@push('modals')

    {{-- Photo Gallery Modal --}}
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
                <input type="file" id="mediaInput" multiple accept="image/*,video/*"
                    onchange="handleFileSelect(event)">
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

    {{-- Media Viewer --}}
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

@endpush

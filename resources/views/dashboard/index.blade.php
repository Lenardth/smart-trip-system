@extends('layouts.authenticated')

@section('title', 'Dashboard — Smart Booking')
@section('page-class', 'main-content')
@section('page-id', 'mainContent')

@push('scripts')
    <script>
        (function () {
            window.__dashboardConfig = {
                pusherKey:     "{{ config('broadcasting.connections.pusher.key') }}",
                pusherCluster: "{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}",
                userId: {{ Auth::id() ?? 'null' }},
                user: {
                    id:        {{ Auth::id() ?? 'null' }},
                    name:      @json(Auth::user()->name ?? ''),
                    firstName: @json(Auth::check() ? explode(' ', Auth::user()->name)[0] : ''),
                    avatar:    @json(Auth::user()->avatar ?? ''),
                    type:      @json(Auth::user()->user_type ?? ''),
                    verified:  {{ Auth::user()->hasVerifiedEmail() ? 'true' : 'false' }}
                }
            };
        })();
    </script>
@endpush

@section('content')

    
    @php
        $user      = Auth::user();
        $firstName = explode(' ', $user->name)[0];
        $isNew     = $user->created_at->diffInDays(now()) < 1;
        $hour      = now()->hour;
        $greeting  = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    @endphp

    <div class="welcome-banner">
        <div class="welcome-avatar">
            @if($user->profile_picture)
                <img src="{{ asset('storage/'.$user->profile_picture) }}"
                     alt="{{ $user->name }}"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <span style="display:none;">{{ strtoupper(substr($user->name,0,1)) }}</span>
            @else
                <span>{{ strtoupper(substr($user->name,0,1)) }}</span>
            @endif
        </div>
        <div class="welcome-text">
            @if($isNew)
                <h2>Welcome to Smart Booking, {{ $firstName }}! 🎉</h2>
                <p>Your account is all set. Start by planning your first trip or exploring destinations.</p>
            @else
                <h2>{{ $greeting }}, {{ $firstName }}!</h2>
                <p>Welcome back — here's what's happening with your travels today.</p>
            @endif
        </div>
        <a href="{{ route('plan-trip') }}" class="welcome-cta">
            <i class="fas fa-route"></i>
            {{ $isNew ? 'Plan Your First Trip' : 'Plan a Trip' }}
        </a>
    </div>

    <div class="stats-grid">
        <div class="stat-card" onclick="openGallery()" style="cursor:pointer;">
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
        <div class="stat-card" onclick="window.location.href='/bookings'" style="cursor:pointer;">
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

    <div class="settings-shortcut-bar">
        <a href="{{ route('profile.edit') }}" class="settings-shortcut-item">
            <i class="fas fa-user-edit"></i> Edit Profile
        </a>
        <a href="{{ route('notifications.index') }}" class="settings-shortcut-item">
            <i class="fas fa-bell"></i> Notifications
        </a>
        <a href="{{ route('wishlist.index') }}" class="settings-shortcut-item">
            <i class="fas fa-heart"></i> Wishlist
        </a>
        <a href="{{ route('bookings.index') }}" class="settings-shortcut-item">
            <i class="fas fa-ticket-alt"></i> Bookings
        </a>
        <a href="{{ route('settings') }}" class="settings-shortcut-item">
            <i class="fas fa-cog"></i> Settings
        </a>
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
        <div class="action-btn" onclick="window.location.href='/discover'">
            <i class="fas fa-compass"></i><span>Discover</span>
        </div>
        <div class="action-btn" onclick="window.location.href='/community'">
            <i class="fas fa-users"></i><span>Community</span>
        </div>
        <div class="action-btn" onclick="window.location.href='/chat'">
            <i class="fas fa-comment-dots"></i><span>Messages</span>
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
                <a href="{{ route('bookings.index') }}" style="font-size:13px;color:var(--gold);text-decoration:none;">View all</a>
            </div>
            <div class="section-content" id="recentActivityContent">
                <div class="empty-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading activity…</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('modals')

    
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
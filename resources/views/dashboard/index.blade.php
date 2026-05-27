@extends('layouts.authenticated')

@section('title', 'Dashboard — Smart Booking')
@section('page-class', 'main-content')
@section('page-id', 'mainContent')

@section('content')

{{-- Welcome banner --}}
<div class="welcome-banner">
    <div class="welcome-avatar">
        @if($user->profile_picture)
            <img src="{{ asset('storage/'.$user->profile_picture) }}"
                 alt="{{ $user->name }}"
                 class="welcome-avatar-img"
                 data-error-action="hide-show-next">
            <span class="welcome-avatar-init hidden">{{ strtoupper(substr($user->name,0,1)) }}</span>
        @else
            <span class="welcome-avatar-init">{{ strtoupper(substr($user->name,0,1)) }}</span>
        @endif
    </div>
    <div class="welcome-text">
        <h2 id="welcomeMessage">{{ $greeting }}, {{ $firstName }}!</h2>
        <p id="welcomeSubtext">
            @if($isNew)
                Your account is ready. Search flights, find stays, and manage bookings from here.
            @else
                Welcome back — here is a snapshot of your travel activity.
            @endif
        </p>
    </div>
    <a href="{{ route('plan-trip') }}" class="welcome-cta">
        <i class="fas fa-route"></i> Plan a Trip
    </a>
</div>

{{-- ── OVERVIEW ──────────────────────────────────────────────────────────── --}}
@if($activeTab === 'overview')

<div class="stats-grid">
    <div class="stat-card clickable" data-action="navigate" data-url="/plan-trip">
        <div class="stat-icon trips"><i class="fas fa-route"></i></div>
        <div class="stat-info">
            <h3 id="statTripsCount">{{ $tripsCount }}</h3>
            <p>Planned Trips</p>
            <div class="stat-change">
                <span id="tripsSubtext">{{ $tripsCount > 0 ? $tripsCount.' trip'.($tripsCount!==1?'s':'').' planned' : 'No trips yet' }}</span>
            </div>
        </div>
    </div>
    <div class="stat-card clickable" data-action="navigate" data-url="/bookings">
        <div class="stat-icon bookings"><i class="fas fa-ticket-alt"></i></div>
        <div class="stat-info">
            <h3 id="statBookingsCount">{{ $bookingsCount }}</h3>
            <p>Active Bookings</p>
            <div class="stat-change">
                <span id="bookingsSubtext">{{ $bookingsCount > 0 ? $bookingsCount.' booking'.($bookingsCount!==1?'s':'').' active' : 'No bookings yet' }}</span>
            </div>
        </div>
    </div>
    <div class="stat-card clickable" data-action="navigate" data-url="/accommodations">
        <div class="stat-icon saved"><i class="fas fa-hotel"></i></div>
        <div class="stat-info">
            <h3 id="statStaySearchesCount">{{ $staySearchesCount }}</h3>
            <p>Stay Searches</p>
            <div class="stat-change">
                <span id="staySearchesSubtext">{{ $staySearchesCount > 0 ? $staySearchesCount.' search'.($staySearchesCount!==1?'es':'') : 'Browse accommodations' }}</span>
            </div>
        </div>
    </div>
</div>

<div class="actions-grid">
    <div class="action-btn clickable" data-action="navigate" data-url="/plan-trip">
        <i class="fas fa-plus-circle"></i><span>Plan Trip</span>
    </div>
    <div class="action-btn clickable" data-action="navigate" data-url="/flights">
        <i class="fas fa-plane"></i><span>Book Flights</span>
    </div>
    <div class="action-btn clickable" data-action="navigate" data-url="/accommodations">
        <i class="fas fa-hotel"></i><span>Find Stays</span>
    </div>
    <div class="action-btn clickable" data-action="navigate" data-url="/bookings">
        <i class="fas fa-ticket-alt"></i><span>My Bookings</span>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-section">
        <div class="section-header">
            <h2><i class="fas fa-route"></i> Upcoming Trips</h2>
            <button class="btn" data-action="navigate" data-url="/plan-trip">
                <i class="fas fa-plus"></i> New Trip
            </button>
        </div>
        <div class="section-content" id="upcomingTripsContent">
            <div class="empty-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading trips…</p>
            </div>
        </div>
    </div>

    <div class="dashboard-section">
        <div class="section-header">
            <h2><i class="fas fa-clock"></i> Recent Activity</h2>
            <a href="{{ route('bookings.index') }}" class="section-link">View bookings</a>
        </div>
        <div class="section-content" id="recentActivityContent">
            <div class="empty-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading activity…</p>
            </div>
        </div>
    </div>
</div>

@endif

{{-- ── SETTINGS TAB ──────────────────────────────────────────────────────── --}}
@if($activeTab === 'settings')

<div class="settings-grid">

    {{-- Profile picture --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fas fa-camera"></i>
            <h3>Profile Picture</h3>
        </div>
        <div class="settings-card-body">
            <div class="profile-avatar-preview">
                @if($user->profile_picture)
                    <img id="avatarPreview"
                         src="{{ asset('storage/'.$user->profile_picture) }}"
                         alt="{{ $user->name }}"
                         class="avatar-preview-img">
                @else
                    <div id="avatarInitial" class="avatar-initial-large">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
            </div>

            @if(session('status') === 'profile-picture-updated')
                <p class="form-success"><i class="fas fa-check-circle"></i> Picture updated.</p>
            @endif
            @if(session('status') === 'profile-picture-deleted')
                <p class="form-success"><i class="fas fa-check-circle"></i> Picture removed.</p>
            @endif

            <form method="POST" action="{{ route('profile.picture.upload') }}"
                  enctype="multipart/form-data" class="settings-form">
                @csrf
                <label class="file-upload-label">
                    <i class="fas fa-upload"></i> Choose image
                    <input type="file" name="profile_picture" accept="image/*"
                           data-change-action="previewAvatar" class="file-input-hidden">
                </label>
                @error('profile_picture')
                    <p class="form-error">{{ $message }}</p>
                @enderror
                <button type="submit" class="primary-button">
                    <i class="fas fa-save"></i> Save Picture
                </button>
            </form>

            @if($user->profile_picture)
                <form method="POST" action="{{ route('profile.picture.delete') }}" class="settings-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="secondary-button">
                        <i class="fas fa-trash"></i> Remove Picture
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Change password --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fas fa-lock"></i>
            <h3>Change Password</h3>
        </div>
        <div class="settings-card-body">
            @if(session('status') === 'password-updated')
                <p class="form-success"><i class="fas fa-check-circle"></i> Password updated.</p>
            @endif

            <form method="POST" action="{{ route('profile.password.update') }}" class="settings-form">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required autocomplete="current-password">
                    @error('current_password')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" required autocomplete="new-password">
                    @error('password')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password">
                </div>
                <button type="submit" class="primary-button">
                    <i class="fas fa-lock"></i> Update Password
                </button>
            </form>
        </div>
    </div>

    {{-- Account info --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fas fa-user"></i>
            <h3>Account Info</h3>
        </div>
        <div class="settings-card-body">
            <div class="account-info-row">
                <span class="account-info-label">Name</span>
                <span class="account-info-value">{{ $user->name }}</span>
            </div>
            <div class="account-info-row">
                <span class="account-info-label">Email</span>
                <span class="account-info-value">{{ $user->email }}</span>
            </div>
            <div class="account-info-row">
                <span class="account-info-label">Account Type</span>
                <span class="account-info-value">{{ ucfirst($user->user_type ?? 'user') }}</span>
            </div>
            <div class="account-info-row">
                <span class="account-info-label">Member Since</span>
                <span class="account-info-value">{{ $user->created_at->format('M Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Danger zone --}}
    <div class="settings-card settings-card-danger">
        <div class="settings-card-header">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Danger Zone</h3>
        </div>
        <div class="settings-card-body">
            <p class="danger-description">Permanently delete your account and all associated data. This cannot be undone.</p>
            <button class="danger-button" id="openDeleteModal">
                <i class="fas fa-user-times"></i> Delete My Account
            </button>
        </div>
    </div>

</div>

{{-- Delete account modal --}}
<div id="deleteAccountModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header modal-header-danger">
            <h2><i class="fas fa-exclamation-triangle"></i> Delete Account</h2>
            <button class="modal-close" id="closeDeleteModal">&times;</button>
        </div>
        <div class="modal-body">
            <p class="modal-description">Enter your password to confirm. All your trips, bookings, and data will be permanently removed.</p>
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required autocomplete="current-password">
                    @error('password')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="secondary-button" id="cancelDeleteModal">Cancel</button>
                    <button type="submit" class="danger-button">
                        <i class="fas fa-trash"></i> Delete Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endif

@endsection

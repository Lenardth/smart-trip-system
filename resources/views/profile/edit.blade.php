@extends('layouts.app-no-nav')

@section('title', 'Edit Profile — Smart Booking')

@push('styles')
    @vite(['resources/css/blade/profile/edit.css'])
@endpush

@push('scripts')
    @vite(['resources/js/blade/profile/edit.js'])
@endpush

@section('content')
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo" id="appLogo">
        <div class="logo-text">Smart Booking</div>
    </div>

    <nav class="sidebar-menu">
        <a href="/" class="menu-item"><i class="fas fa-home"></i><span>Home</span></a>
        <a href="/dashboard" class="menu-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
        <a href="#" class="menu-item" onclick="openGallery(); return false;"><i class="fas fa-images"></i><span>My Photos</span><span class="menu-badge" id="photosCount">0</span></a>
        <a href="/plan-trip" class="menu-item"><i class="fas fa-route"></i><span>Plan Trip</span></a>
        <a href="/flights" class="menu-item"><i class="fas fa-plane"></i><span>Book Flights</span></a>
        <a href="/bookings" class="menu-item"><i class="fas fa-ticket-alt"></i><span>My Bookings</span><span class="menu-badge" id="bookingsCount">0</span></a>
        <a href="/discover" class="menu-item"><i class="fas fa-compass"></i><span>Discover</span></a>
        <a href="/destinations" class="menu-item"><i class="fas fa-map-marked-alt"></i><span>Destinations</span></a>
        <a href="/community" class="menu-item"><i class="fas fa-users"></i><span>Community</span></a>
        <a href="/wishlist" class="menu-item"><i class="fas fa-heart"></i><span>Wishlist</span><span class="menu-badge" id="savedCount">0</span></a>
        <a href="#" class="menu-item" onclick="openSettings(); return false;"><i class="fas fa-cog"></i><span>Settings</span></a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-profile">
            <div class="user-avatar" onclick="viewProfile()">
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
                        <span class="verified-badge"><i class="fas fa-check-circle"></i> Verified</span>
                    @endif
                </div>
            </div>
            <button class="logout-btn" onclick="logout()"><i class="fas fa-sign-out-alt"></i></button>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</div>

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

<button class="mobile-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>
@endsection

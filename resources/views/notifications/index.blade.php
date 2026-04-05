@extends('layouts.authenticated')

@section('title', 'Notifications — Smart Booking')
@section('page-class', 'main-content notifications-page')
@section('page-id', 'notificationsPage')

@push('body-attrs')
    data-user-id="{{ Auth::id() }}"
    data-pusher-key="{{ config('broadcasting.connections.pusher.key') }}"
    data-pusher-cluster="{{ config('broadcasting.connections.pusher.options.cluster') }}"
@endpush

@section('content')

    <div class="notifications-header">
        <div>
            <h2><i class="fas fa-bell"></i> All Notifications</h2>
            <p>Stay up to date with your trips, bookings, and messages.</p>
        </div>
        <div class="notifications-header-actions">
            <button class="btn-secondary" id="markAllReadBtn" onclick="markAllRead()">
                <i class="fas fa-check-double"></i> Mark All as Read
            </button>
        </div>
    </div>

    <div class="notification-filter-tabs" id="notifTabs">
        <button class="notif-tab active" data-tab="all"     onclick="switchTab('all')">
            <i class="fas fa-th-large"></i> All
            <span id="countAll" class="notif-count"></span>
        </button>
        <button class="notif-tab" data-tab="chat"           onclick="switchTab('chat')">
            <i class="fas fa-comments"></i> Messages
            <span id="countChat" class="notif-count"></span>
        </button>
        <button class="notif-tab" data-tab="activity"       onclick="switchTab('activity')">
            <i class="fas fa-ticket-alt"></i> Activity
            <span id="countActivity" class="notif-count"></span>
        </button>
    </div>

    <div id="notifList" class="notif-list-wrap">
        <div class="notif-empty">
            <i class="fas fa-bell-slash"></i>
            <h3>Loading…</h3>
            <p>Fetching your notifications.</p>
        </div>
    </div>

    <p id="notifSubtitle" style="text-align:center;color:var(--text-muted);font-size:13px;margin-top:8px;"></p>

@endsection
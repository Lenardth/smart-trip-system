
@extends('layouts.authenticated')

@section('title', 'Notifications — Smart Booking')
@section('page-class', 'main-content')
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
        <button class="notif-tab active" data-tab="all"      onclick="filterNotifs('all')">
            <i class="fas fa-th-large"></i> All
        </button>
        <button class="notif-tab"        data-tab="chat"     onclick="filterNotifs('chat')">
            <i class="fas fa-comments"></i> Messages
        </button>
        <button class="notif-tab"        data-tab="booking"  onclick="filterNotifs('booking')">
            <i class="fas fa-ticket-alt"></i> Bookings
        </button>
        <button class="notif-tab"        data-tab="system"   onclick="filterNotifs('system')">
            <i class="fas fa-info-circle"></i> System
        </button>
    </div>

    <div class="notifications-list" id="notificationsList">
        <div class="notifications-loading" id="notifsLoading">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading notifications…</p>
        </div>
        <div class="notifications-empty" id="notifsEmpty" style="display:none;">
            <i class="fas fa-bell-slash"></i>
            <h3>You're all caught up!</h3>
            <p>No notifications to show right now. Check back later.</p>
        </div>
        <div id="notifsContent"></div>
    </div>

@endsection

@extends('layouts.authenticated')

@section('title', 'Notifications — Smart Booking')

@push('styles')
    @vite(['resources/css/blade/notifications/index.css', 'resources/js/blade/notifications/index.js'])
@endpush

@section('page-class', 'notif-page')
@section('page-id', 'notifPage')

@section('content')

    <div class="notif-header">
        <div class="notif-header-left">
            <h1>
                <i class="fas fa-bell" style="color:var(--gold);margin-right:10px;"></i>
                Notifications
            </h1>
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

@endsection

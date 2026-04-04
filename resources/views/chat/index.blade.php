@extends('layouts.authenticated')

@section('title', 'Messages — Smart Booking')
@section('page-class', 'chat-page')
@section('page-id', 'chatPage')

@php
    $hideHeader = true;
    $fullPage   = true;
@endphp

@push('styles')
<script>
window.__dashboardConfig = window.__dashboardConfig || {};
window.__dashboardConfig.userId        = {{ Auth::id() }};
window.__dashboardConfig.pusherKey     = @json(config('broadcasting.connections.pusher.key'));
window.__dashboardConfig.pusherCluster = @json(config('broadcasting.connections.pusher.options.cluster', 'mt1'));
window.__dashboardConfig.openUserId    = {{ isset($other) ? $other->id : 'null' }};
window.__dashboardConfig.openUserName  = @json(isset($other) ? $other->name : '');
window.__dashboardConfig.openUserAvatar= @json(isset($other) ? ($other->avatar ?? '') : '');
window.__dashboardConfig.user = {
    id:     {{ Auth::id() }},
    name:   @json(Auth::user()->name ?? ''),
    avatar: @json(Auth::user()->avatar ?? ''),
};
</script>
@endpush

@section('content')
<div class="chat-container">

    <div class="chat-sidebar" id="chatSidebar">
        <div class="chat-sidebar-header">
            <div class="header-title">
                <i class="fas fa-comment-dots"></i>
                <h2>Messages</h2>
            </div>
            <a href="{{ route('dashboard') }}" class="back-button" title="Back to Dashboard">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>
        <div class="chat-sidebar-search">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="pageSearchInput" placeholder="Search people…" autocomplete="off">
            </div>
            <div id="pageSearchResults" class="search-results-dropdown" style="display:none;"></div>
        </div>
        <div class="conv-list" id="pageConvList">
            <div class="conv-empty">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading…</p>
            </div>
        </div>
    </div>

    <div class="chat-thread" id="chatThread">
        <button class="chat-open-sidebar-btn" onclick="document.getElementById('chatSidebar').classList.add('open')">
            <i class="fas fa-comments"></i> Conversations
        </button>

        <div class="thread-empty-state" id="threadEmptyState">
            <i class="fas fa-comments"></i>
            <h3>Your Messages</h3>
            <p>Select a conversation or search for someone to start chatting.</p>
        </div>

        <div id="threadView" style="display:none;flex-direction:column;height:100%;">
            <div class="thread-header">
                <button class="thread-back-btn" onclick="document.getElementById('chatSidebar').classList.add('open')">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="thread-header-avatar" id="threadAvatar"></div>
                <div class="thread-header-info">
                    <strong id="threadName"></strong>
                    <small id="threadSub">Active now</small>
                </div>
            </div>
            <div class="thread-messages" id="threadMessages"></div>
            <div class="thread-input-area">
                <div class="input-wrapper">
                    <textarea id="threadInput" rows="1"
                              placeholder="Type a message…"
                              oninput="window.ChatSystem?.autoResize(this)"
                              onkeydown="window.ChatSystem?.handleKey(event)"></textarea>
                    <button class="thread-send-btn" onclick="window.ChatSystem?.send()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="chatOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:299;"
     onclick="document.getElementById('chatSidebar').classList.remove('open');this.style.display='none';"></div>

@push('scripts')
<script>
(function () {
    const sidebar = document.getElementById('chatSidebar');
    const overlay = document.getElementById('chatOverlay');
    if (!sidebar || !overlay) return;
    new MutationObserver(() => {
        overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
    }).observe(sidebar, { attributes: true, attributeFilter: ['class'] });
})();
</script>
@endpush
@endsection

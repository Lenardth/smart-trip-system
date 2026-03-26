@extends('layouts.authenticated')

@section('title', 'Messages — Smart Booking')

@push('styles')
    @vite(['resources/css/blade/chat/index.css', 'resources/js/blade/chat/index.js'])
@endpush

@push('body-attrs')
    data-chat-user-id="{{ Auth::id() }}"
    data-chat-user-name="{{ Auth::user()->name ?? '' }}"
    data-chat-user-avatar="{{ Auth::user()->avatar ?? '' }}"
    data-chat-open-user-id="{{ isset($other) ? $other->id : '' }}"
    data-chat-open-user-name="{{ isset($other) ? $other->name : '' }}"
    data-chat-open-user-avatar="{{ isset($other) ? ($other->avatar ?? '') : '' }}"
    data-pusher-key="{{ config('broadcasting.connections.pusher.key') }}"
    data-pusher-cluster="{{ config('broadcasting.connections.pusher.options.cluster') }}"
@endpush

@section('page-class', 'chat-page')
@section('page-id', 'chatPage')

@section('content')

    {{-- Conversation list panel --}}
    <div class="chat-sidebar" id="chatSidebar">
        <div class="chat-sidebar-header">
            <i class="fas fa-comment-dots" style="color:var(--gold);font-size:20px;"></i>
            <h2>Messages</h2>
            <a href="/dashboard" title="Back to Dashboard">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>

        <div class="chat-sidebar-search">
            <i class="fas fa-search chat-sidebar-search-icon"></i>
            <input type="text" id="pageSearchInput" placeholder="Search people…"
                oninput="ChatSystem.onSearchInput(event)" autocomplete="off">
            <div id="pageSearchResults" class="search-results-dropdown" style="display:none;"></div>
        </div>

        <div class="conv-list" id="pageConvList">
            <div class="conv-empty">
                <i class="fas fa-spinner fa-spin"></i>
                Loading…
            </div>
        </div>
    </div>

    {{-- Thread / message panel --}}
    <div class="chat-thread" id="chatThread">

        <div class="thread-empty-state" id="threadEmptyState">
            <i class="fas fa-comment-dots"></i>
            <h3>Your Messages</h3>
            <p>Select a conversation or search for someone to start chatting.</p>
        </div>

        <div id="threadView">
            <div class="thread-header">
                <div class="thread-header-avatar" id="threadAvatar"></div>
                <div class="thread-header-info">
                    <strong id="threadName"></strong>
                    <small id="threadSub"></small>
                </div>
            </div>

            <div class="thread-messages" id="threadMessages"></div>

            <div class="thread-input-area">
                <textarea id="threadInput" rows="1" placeholder="Type a message… (Enter to send)"
                    oninput="ChatSystem.autoResize(this)"
                    onkeydown="ChatSystem.handleKey(event)"></textarea>
                <button class="thread-send-btn" id="threadSendBtn" onclick="ChatSystem.send()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>

    </div>

@endsection


@extends('layouts.authenticated')

@section('title', 'Messages — Smart Booking')


@section('page-class', 'chat-page')
@section('page-id', 'chatPage')


@php
    $hideHeader = true;
    $fullPage = true;
@endphp


@push('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize chat system when DOM is ready
            if (typeof window.ChatSystem !== 'undefined') {
                window.ChatSystem.init();
            }

            // Add mobile sidebar toggle functionality
            if (window.innerWidth <= 768) {
                const toggleBtn = document.createElement('button');
                toggleBtn.className = 'chat-mobile-toggle';
                toggleBtn.innerHTML = '<i class="fas fa-comments"></i>';
                toggleBtn.onclick = function(e) {
                    e.stopPropagation();
                    const sidebar = document.getElementById('chatSidebar');
                    if (sidebar) {
                        sidebar.classList.toggle('open');
                    }
                };
                document.body.appendChild(toggleBtn);

                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(e) {
                    const sidebar = document.getElementById('chatSidebar');
                    const toggle = document.querySelector('.chat-mobile-toggle');
                    if (sidebar && sidebar.classList.contains('open')) {
                        if (!sidebar.contains(e.target) && e.target !== toggle && !toggle?.contains(e.target)) {
                            sidebar.classList.remove('open');
                        }
                    }
                });
            }
        });
    </script>
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
                    <input type="text"
                           id="pageSearchInput"
                           placeholder="Search conversations or people..."
                           autocomplete="off">
                </div>
                <div id="pageSearchResults" class="search-results-dropdown" style="display: none;"></div>
            </div>

            <div class="conv-list" id="pageConvList">
                <div class="conv-empty">
                    <i class="fas fa-comment-dots"></i>
                    <h3>No conversations yet</h3>
                    <p>Start a conversation by searching for someone above</p>
                </div>
            </div>
        </div>

        
        <div class="chat-thread" id="chatThread">
            <div class="thread-empty-state" id="threadEmptyState">
                <i class="fas fa-comment-dots"></i>
                <h3>Your Messages</h3>
                <p>Select a conversation or search for someone to start chatting.</p>
            </div>

            <div id="threadView" style="display: none;">
                <div class="thread-header">
                    <div class="thread-header-avatar" id="threadAvatar">
                        <div class="avatar-placeholder"></div>
                    </div>
                    <div class="thread-header-info">
                        <strong id="threadName"></strong>
                        <small id="threadSub"></small>
                    </div>
                    <div class="thread-header-actions">
                        <button class="thread-action-btn" onclick="window.ChatSystem?.viewProfile()" title="View Profile">
                            <i class="fas fa-user"></i>
                        </button>
                    </div>
                </div>

                <div class="thread-messages" id="threadMessages">
                    <div class="messages-loading">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Loading messages...</p>
                    </div>
                </div>

                <div class="thread-input-area">
                    <div class="input-wrapper">
                        <textarea id="threadInput"
                                  rows="1"
                                  placeholder="Type a message... (Press Enter to send, Shift+Enter for new line)"
                                  oninput="window.ChatSystem?.autoResize(this)"
                                  onkeydown="window.ChatSystem?.handleKey(event)"></textarea>
                        <button class="thread-send-btn" id="threadSendBtn" onclick="window.ChatSystem?.send()">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

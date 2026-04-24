@extends('layouts.public')

@section('title', 'My Feed — Smart Booking')

@push('styles')
<script>
window.__dashboardConfig = window.__dashboardConfig || {};
@auth
window.__dashboardConfig.userId    = {{ Auth::id() }};
window.__dashboardConfig.user      = { id: {{ Auth::id() }}, name: @json(Auth::user()->name), avatar: @json(Auth::user()->avatar ?? '') };
@endauth
</script>
@endpush

@section('content')
<section class="page-hero" style="background: linear-gradient(160deg, rgba(20,10,30,0.72) 0%, rgba(59,31,43,0.55) 100%), url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1920&q=90'); background-size: cover; background-position: center; min-height: 450px; display: flex; align-items: center;">
    <div>
        <h1 style="margin-bottom: 16px;"><i class="fas fa-stream"></i> My Feed</h1>
        <p style="font-size: 15px; max-width: 600px; margin: 0 auto;">See what people you follow are up to</p>
    </div>
</section>

<div class="feed-container">
    <div class="feed-layout">
        <!-- Main Feed -->
        <div class="feed-main">
            <div class="feed-header">
                <h2><i class="fas fa-fire"></i> Latest from People You Follow</h2>
                <div class="feed-filters">
                    <button class="filter-btn active" data-filter="all" onclick="Feed.filterBy('all')">
                        <i class="fas fa-th"></i> All
                    </button>
                    <button class="filter-btn" data-filter="stories" onclick="Feed.filterBy('stories')">
                        <i class="fas fa-camera"></i> Stories
                    </button>
                    <button class="filter-btn" data-filter="topics" onclick="Feed.filterBy('topics')">
                        <i class="fas fa-comments"></i> Topics
                    </button>
                </div>
            </div>

            <div id="feedContent">
                <!-- Loading skeleton -->
                @for ($i = 0; $i < 3; $i++)
                <div class="feed-item skeleton-item">
                    <div class="feed-item-header">
                        <div class="sk-circle skeleton" style="width:44px;height:44px;"></div>
                        <div style="flex:1;">
                            <div class="sk-line skeleton" style="width:150px;height:16px;margin-bottom:6px;"></div>
                            <div class="sk-line skeleton" style="width:100px;height:12px;"></div>
                        </div>
                    </div>
                    <div class="sk-line skeleton" style="width:100%;height:200px;margin:12px 0;"></div>
                    <div class="sk-line skeleton" style="width:80%;height:14px;"></div>
                </div>
                @endfor
            </div>

            <div id="emptyFeed" style="display:none;text-align:center;padding:60px 20px;">
                <i class="fas fa-users" style="font-size:64px;color:var(--text-muted);margin-bottom:20px;"></i>
                <h3 style="color:var(--deep);margin-bottom:12px;">Your Feed is Empty</h3>
                <p style="color:var(--text-muted);margin-bottom:24px;">Follow other travelers to see their stories and posts here!</p>
                <button class="primary-button" onclick="window.location.href='/community'">
                    <i class="fas fa-search"></i> Discover People
                </button>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="feed-sidebar">
            <div class="sidebar-card">
                <h3><i class="fas fa-user-friends"></i> Suggestions</h3>
                <div id="suggestedUsers">
                    <div class="sk-line skeleton" style="height:60px;margin-bottom:10px;"></div>
                    <div class="sk-line skeleton" style="height:60px;"></div>
                </div>
            </div>

            <div class="sidebar-card">
                <h3><i class="fas fa-fire"></i> Trending Topics</h3>
                <div class="tag-cloud" id="trendingTags">
                    <div class="sk-line skeleton" style="height:28px;width:100%;"></div>
                </div>
            </div>

            <div class="sidebar-card">
                <h3><i class="fas fa-chart-line"></i> Your Stats</h3>
                <div class="user-stats" id="userStats">
                    <div class="stat-row">
                        <span>Following</span>
                        <strong id="statFollowing">-</strong>
                    </div>
                    <div class="stat-row">
                        <span>Followers</span>
                        <strong id="statFollowers">-</strong>
                    </div>
                    <div class="stat-row">
                        <span>Posts</span>
                        <strong id="statPosts">-</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="toast" id="toast">
    <i class="fas fa-check-circle"></i>
    <span id="toastMsg"></span>
</div>
@endsection

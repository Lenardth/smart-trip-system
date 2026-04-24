@extends('layouts.public')

@section('title', 'User Profile — Smart Booking')

@push('styles')
<script>
window.__dashboardConfig = window.__dashboardConfig || {};
@auth
window.__dashboardConfig.userId    = {{ Auth::id() }};
window.__dashboardConfig.user      = { id: {{ Auth::id() }}, name: @json(Auth::user()->name), avatar: @json(Auth::user()->avatar ?? '') };
@endauth
window.__profileUserId = {{ $user->id }};
</script>
@endpush

@section('content')
<div class="profile-container">
    <div class="profile-header">
        <div class="profile-header-content">
            <div class="profile-avatar-large" id="profileAvatar">
                <div class="sk-circle skeleton"></div>
            </div>
            <div class="profile-info">
                <h1 id="profileName" class="skeleton-text">Loading...</h1>
                <p id="profileBio" class="skeleton-text">Loading bio...</p>
                <div class="profile-meta">
                    <span id="profileLocation"><i class="fas fa-map-marker-alt"></i> <span class="skeleton-text">Location</span></span>
                    <span id="profileJoined"><i class="fas fa-calendar"></i> <span class="skeleton-text">Joined</span></span>
                </div>
                <div class="profile-actions" id="profileActions">
                    <!-- Actions will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <div class="profile-stats">
        <div class="profile-stat">
            <div class="ps-num" id="statStories"><span class="sk-line skeleton" style="width:40px;height:24px;"></span></div>
            <div class="ps-label">Stories & Vlogs</div>
        </div>
        <div class="profile-stat">
            <div class="ps-num" id="statTopics"><span class="sk-line skeleton" style="width:40px;height:24px;"></span></div>
            <div class="ps-label">Forum Topics</div>
        </div>
        <div class="profile-stat">
            <div class="ps-num" id="statFollowers"><span class="sk-line skeleton" style="width:40px;height:24px;"></span></div>
            <div class="ps-label">Followers</div>
        </div>
        <div class="profile-stat">
            <div class="ps-num" id="statFollowing"><span class="sk-line skeleton" style="width:40px;height:24px;"></span></div>
            <div class="ps-label">Following</div>
        </div>
    </div>

    <div class="profile-tabs">
        <button class="profile-tab active" data-tab="stories" onclick="UserProfile.switchTab('stories')">
            <i class="fas fa-th"></i> Stories & Vlogs
        </button>
        <button class="profile-tab" data-tab="topics" onclick="UserProfile.switchTab('topics')">
            <i class="fas fa-comments"></i> Forum Posts
        </button>
        <button class="profile-tab" data-tab="trips" onclick="UserProfile.switchTab('trips')">
            <i class="fas fa-suitcase"></i> Trips
        </button>
    </div>

    <div class="profile-content">
        <!-- Post creation box for own profile -->
        <div id="createPostBox" style="display:none;margin-bottom:24px;">
            <div class="create-post-card">
                <h3><i class="fas fa-plus-circle"></i> Create New Post</h3>
                <button class="primary-button" onclick="UserProfile.openStoryModal()">
                    <i class="fas fa-camera"></i> Share Story/Vlog
                </button>
                <button class="secondary-button" onclick="UserProfile.openTopicModal()">
                    <i class="fas fa-comments"></i> Start Discussion
                </button>
            </div>
        </div>

        <div class="profile-tab-content active" id="tab-stories">
            <div class="stories-grid" id="userStories">
                @for ($i = 0; $i < 3; $i++)
                <div class="story-card">
                    <div class="story-img skeleton"></div>
                    <div class="story-body">
                        <div class="sk-line full skeleton"></div>
                        <div class="sk-line medium skeleton"></div>
                    </div>
                </div>
                @endfor
            </div>
        </div>

        <div class="profile-tab-content" id="tab-topics">
            <div id="userTopics">
                <div class="sk-line full skeleton" style="height:60px;margin-bottom:10px;"></div>
                <div class="sk-line full skeleton" style="height:60px;"></div>
            </div>
        </div>

        <div class="profile-tab-content" id="tab-trips">
            <div class="trips-grid" id="userTrips">
                <div class="sk-line full skeleton" style="height:100px;margin-bottom:10px;"></div>
                <div class="sk-line full skeleton" style="height:100px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="toast" id="toast">
    <i class="fas fa-check-circle"></i>
    <span id="toastMsg"></span>
</div>

<!-- Story/Vlog Modal -->
<div class="modal-overlay" id="storyModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-camera" style="color:var(--gold);margin-right:8px;"></i> Create Story/Vlog</h2>
            <button class="modal-close" onclick="UserProfile.closeModal('storyModal')">&#x2715;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Media Type</label>
                <div style="display:flex;gap:12px;margin-top:8px;">
                    <label style="display:flex;align-items:center;cursor:pointer;">
                        <input type="radio" name="mediaType" value="image" checked onchange="UserProfile.toggleMediaType('image')" style="margin-right:6px;">
                        <i class="fas fa-image" style="margin-right:4px;"></i> Photo
                    </label>
                    <label style="display:flex;align-items:center;cursor:pointer;">
                        <input type="radio" name="mediaType" value="video" onchange="UserProfile.toggleMediaType('video')" style="margin-right:6px;">
                        <i class="fas fa-video" style="margin-right:4px;"></i> Video (Vlog)
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label for="storyTitle">Title</label>
                <input type="text" id="storyTitle" placeholder="e.g. My Amazing Trip to Bali">
            </div>
            <div class="form-group">
                <label for="storyExcerpt">Description</label>
                <textarea id="storyExcerpt" rows="3" placeholder="Share a brief description of your story..."></textarea>
            </div>
            <div class="form-group" id="imageUrlGroup">
                <label for="storyImageUrl">Image URL</label>
                <input type="url" id="storyImageUrl" placeholder="https://example.com/image.jpg">
            </div>
            <div class="form-group" id="videoUrlGroup" style="display:none;">
                <label for="storyVideoUrl">Video URL</label>
                <input type="url" id="storyVideoUrl" placeholder="https://example.com/video.mp4">
            </div>
            <div class="form-group" id="thumbnailUrlGroup" style="display:none;">
                <label for="storyThumbnailUrl">Video Thumbnail URL (optional)</label>
                <input type="url" id="storyThumbnailUrl" placeholder="https://example.com/thumbnail.jpg">
            </div>
            <div class="form-group" id="durationGroup" style="display:none;">
                <label for="storyDuration">Video Duration (seconds)</label>
                <input type="number" id="storyDuration" placeholder="e.g. 120" min="1">
            </div>
            <div class="modal-footer">
                <button class="secondary-button" onclick="UserProfile.closeModal('storyModal')">Cancel</button>
                <button class="primary-button" id="submitStoryBtn" onclick="UserProfile.submitStory()">
                    <i class="fas fa-paper-plane"></i> Post
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Topic Modal -->
<div class="modal-overlay" id="topicModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-plus" style="color:var(--gold);margin-right:8px;"></i> New Forum Topic</h2>
            <button class="modal-close" onclick="UserProfile.closeModal('topicModal')">&#x2715;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="topicTitle">Topic Title</label>
                <input type="text" id="topicTitle" placeholder="e.g. Best hidden gems in Portugal?">
            </div>
            <div class="form-group">
                <label for="topicTags">Tags <span style="font-weight:normal;color:var(--text-muted)">(comma separated)</span></label>
                <input type="text" id="topicTags" placeholder="e.g. Portugal, Budget, Solo">
            </div>
            <div class="form-group">
                <label for="topicBody">Message</label>
                <textarea id="topicBody" placeholder="Share your question or experience..."></textarea>
            </div>
            <div class="modal-footer">
                <button class="secondary-button" onclick="UserProfile.closeModal('topicModal')">Cancel</button>
                <button class="primary-button" id="submitTopicBtn" onclick="UserProfile.submitTopic()">
                    <i class="fas fa-paper-plane"></i> Post Topic
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

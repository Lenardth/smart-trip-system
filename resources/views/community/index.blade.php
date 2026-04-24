@extends('layouts.public')

@section('title', 'Community — Smart Booking')

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
<section class="page-hero" style="background: linear-gradient(160deg, rgba(20,10,30,0.72) 0%, rgba(59,31,43,0.55) 100%), url('https://images.unsplash.com/photo-1539635278303-d4002c07eae3?w=1920&q=90'); background-size: cover; background-position: center 60%; min-height: 450px; display: flex; align-items: center;">
    <div>
        <h1 style="margin-bottom: 16px;"><i class="fas fa-users"></i> Community</h1>
        <p style="font-size: 15px; max-width: 600px; margin: 0 auto;">Connect with fellow travellers, share stories, and join group adventures.</p>
    </div>
</section>

<div class="community-wrap">
    <!-- Community Tabs -->
    <div class="community-tabs">
        <button class="community-tab" data-tab="explore" onclick="Community.switchTab('explore')">
            <i class="fas fa-compass"></i> Explore
        </button>
        @auth
        <button class="community-tab active" data-tab="feed" onclick="Community.switchTab('feed')">
            <i class="fas fa-stream"></i> My Feed
        </button>
        @else
        <button class="community-tab active" data-tab="explore" onclick="Community.switchTab('explore')">
            <i class="fas fa-compass"></i> Explore
        </button>
        @endauth
        <button class="community-tab" data-tab="members" onclick="Community.switchTab('members')">
            <i class="fas fa-user-friends"></i> Members
        </button>
    </div>

    <!-- Feed Tab Content -->
    @auth
    <div class="community-tab-content active" id="tab-feed">
        <div class="feed-section">
            <div class="feed-header-inline">
                <h2><i class="fas fa-fire"></i> Latest from People You Follow</h2>
                <div class="feed-filters-inline">
                    <button class="filter-btn-inline active" data-filter="all" onclick="Community.filterFeed('all')">
                        All
                    </button>
                    <button class="filter-btn-inline" data-filter="stories" onclick="Community.filterFeed('stories')">
                        Stories
                    </button>
                    <button class="filter-btn-inline" data-filter="topics" onclick="Community.filterFeed('topics')">
                        Topics
                    </button>
                </div>
            </div>

            <div id="feedContent" class="feed-grid">
                <!-- Loading skeleton -->
                @for ($i = 0; $i < 3; $i++)
                <div class="feed-item-card skeleton-item">
                    <div class="sk-line skeleton" style="width:100%;height:200px;margin-bottom:12px;"></div>
                    <div class="sk-line skeleton" style="width:80%;height:16px;margin-bottom:8px;"></div>
                    <div class="sk-line skeleton" style="width:60%;height:14px;"></div>
                </div>
                @endfor
            </div>

            <div id="emptyFeed" style="display:none;text-align:center;padding:60px 20px;">
                <i class="fas fa-users" style="font-size:64px;color:var(--text-muted);margin-bottom:20px;"></i>
                <h3 style="color:var(--deep);margin-bottom:12px;">Your Feed is Empty</h3>
                <p style="color:var(--text-muted);margin-bottom:24px;">Follow other travelers to see their stories and posts here!</p>
                <button class="primary-button" onclick="Community.switchTab('members')">
                    <i class="fas fa-search"></i> Discover People
                </button>
            </div>
        </div>
    </div>
    @endauth

    <!-- Explore Tab Content (Default) -->
    <div class="community-tab-content {{ Auth::guest() ? 'active' : '' }}" id="tab-explore">
    <div class="community-stats">
        <div class="comm-stat clickable" onclick="Community.filterByMembers()">
            <div class="cs-num" id="stat-members"><span class="sk-line medium skeleton" style="display:inline-block;width:80px;height:32px;"></span></div>
            <div class="cs-label">Active Members</div>
        </div>
        <div class="comm-stat clickable" onclick="Community.filterByStories()">
            <div class="cs-num" id="stat-stories"><span class="sk-line medium skeleton" style="display:inline-block;width:60px;height:32px;"></span></div>
            <div class="cs-label">Travel Stories</div>
        </div>
        <div class="comm-stat clickable" onclick="Community.filterByGroups()">
            <div class="cs-num" id="stat-groups"><span class="sk-line medium skeleton" style="display:inline-block;width:60px;height:32px;"></span></div>
            <div class="cs-label">Active Groups</div>
        </div>
        <div class="comm-stat clickable" onclick="Community.filterByTopics()">
            <div class="cs-num" id="stat-topics"><span class="sk-line medium skeleton" style="display:inline-block;width:60px;height:32px;"></span></div>
            <div class="cs-label">Forum Topics</div>
        </div>
    </div>

    <div class="comm-grid">
        <div class="forum-section">
            <h3>
                <span>
                    <i class="fas fa-comments" style="color:var(--gold);margin-right:8px;"></i>
                    Forum Topics
                    <span class="live-badge"><span class="live-dot"></span> Live</span>
                </span>
                <button class="secondary-button" onclick="Community.openTopicModal()">
                    <i class="fas fa-plus"></i> New Topic
                </button>
            </h3>
            <div id="forumTopics">
                <div style="padding:12px 0;">
                    <div class="sk-line full skeleton" style="height:60px;margin-bottom:10px;border-radius:6px;"></div>
                    <div class="sk-line full skeleton" style="height:60px;margin-bottom:10px;border-radius:6px;"></div>
                    <div class="sk-line full skeleton" style="height:60px;border-radius:6px;"></div>
                </div>
            </div>
        </div>

        <div>
            <div class="sidebar-section">
                <h3><i class="fas fa-users" style="color:var(--gold);margin-right:6px;"></i> Group Trips</h3>
                <div id="groupTrips">
                    <div class="sk-line full skeleton" style="height:52px;margin-bottom:10px;border-radius:6px;"></div>
                    <div class="sk-line full skeleton" style="height:52px;border-radius:6px;"></div>
                </div>
                <div style="text-align:center;margin-top:16px;">
                    <button class="primary-button" style="font-size:13px;padding:9px 18px;" onclick="Community.openGroupModal()">
                        <i class="fas fa-plus"></i> Create Group
                    </button>
                </div>
            </div>

            <div class="sidebar-section">
                <h3><i class="fas fa-fire" style="color:var(--gold);margin-right:6px;"></i> Trending Tags</h3>
                <div class="tag-cloud" id="trendingTags">
                    <div class="sk-line medium skeleton" style="height:28px;width:100%;border-radius:3px;"></div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="section-title">Travel Stories & Vlogs</h2>
    <p class="section-subtitle">Real experiences from our community — inspiring tales and videos from around the globe.</p>
    <div style="text-align:center;margin-bottom:20px;">
        <button class="primary-button" onclick="Community.openStoryModal()">
            <i class="fas fa-plus"></i> Create Story/Vlog
        </button>
    </div>
    <div class="stories-grid" id="storiesGrid">
        @for ($i = 0; $i < 3; $i++)
        <div class="story-card">
            <div class="story-img skeleton"></div>
            <div class="story-body">
                <div class="sk-line full skeleton"></div>
                <div class="sk-line medium skeleton"></div>
                <div class="sk-line short skeleton"></div>
            </div>
        </div>
        @endfor
    </div>

    <h2 class="section-title">Top Travelers</h2>
    <p class="section-subtitle">Connect with our most active community members.</p>
    <div class="travelers-grid" id="travelersGrid">
        @for ($i = 0; $i < 4; $i++)
        <div class="traveler-card skeleton" style="height:200px;"></div>
        @endfor
    </div>
    </div>
    <!-- End Explore Tab -->

    <!-- Members Tab Content -->
    <div class="community-tab-content" id="tab-members">
        <div class="members-embed">
            <div class="members-embed-header">
                <h2>Active Members</h2>
                <input type="text" id="searchMembersInline" placeholder="Search members..." class="search-input-inline">
            </div>
            <div id="membersGridInline" class="members-grid-inline">
                <!-- Will be loaded dynamically -->
            </div>
        </div>
    </div>
    <!-- End Members Tab -->

</div>

<div class="modal-overlay" id="topicModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-plus" style="color:var(--gold);margin-right:8px;"></i> New Forum Topic</h2>
            <button class="modal-close" onclick="Community.closeModal('topicModal')">&#x2715;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="topicAuthor">Your Name</label>
                <input type="text" id="topicAuthor" placeholder="e.g. Jane Smith" autocomplete="name">
            </div>
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
                <button class="secondary-button" onclick="Community.closeModal('topicModal')">Cancel</button>
                <button class="primary-button" id="submitTopicBtn" onclick="Community.submitTopic()">
                    <i class="fas fa-paper-plane"></i> Post Topic
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="groupModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-users" style="color:var(--gold);margin-right:8px;"></i> Create Group Trip</h2>
            <button class="modal-close" onclick="Community.closeModal('groupModal')">&#x2715;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="groupOrganizer">Organizer Name</label>
                <input type="text" id="groupOrganizer" placeholder="Your name" autocomplete="name">
            </div>
            <div class="form-group">
                <label for="groupName">Trip Name</label>
                <input type="text" id="groupName" placeholder="e.g. Morocco Desert Adventure">
            </div>
            <div class="form-group">
                <label for="groupDest">Destination</label>
                <input type="text" id="groupDest" placeholder="e.g. Marrakech, Morocco">
            </div>
            <div class="form-group">
                <label for="groupDate">Travel Date</label>
                <input type="text" id="groupDate" placeholder="e.g. May 2026">
            </div>
            <div class="form-group">
                <label for="groupSpots">Available Spots</label>
                <input type="number" id="groupSpots" placeholder="e.g. 6" min="1" max="50">
            </div>
            <div class="modal-footer">
                <button class="secondary-button" onclick="Community.closeModal('groupModal')">Cancel</button>
                <button class="primary-button" id="submitGroupBtn" onclick="Community.submitGroup()">
                    <i class="fas fa-plus"></i> Create Group
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="inviteModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-paper-plane" style="color:var(--gold);margin-right:8px;"></i> Send Invite</h2>
            <button class="modal-close" onclick="Community.closeModal('inviteModal')">&#x2715;</button>
        </div>
        <div class="modal-body">
            <div class="invite-preview">
                <div class="invite-avatar" id="inviteAvatar"></div>
                <div>
                    <div class="invite-name" id="inviteName"></div>
                    <div class="invite-sub" id="inviteSub"></div>
                </div>
            </div>
            <div class="form-group">
                <label for="inviteMsg">Message</label>
                <textarea id="inviteMsg" rows="4" placeholder="Hey! I saw your post on the community and wanted to connect…"></textarea>
            </div>
            <div class="modal-footer">
                <button class="secondary-button" onclick="Community.closeModal('inviteModal')">Cancel</button>
                <button class="primary-button" id="submitInviteBtn" onclick="Community.sendInvite()">
                    <i class="fas fa-paper-plane"></i> Send &amp; Open Chat
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="storyModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-camera" style="color:var(--gold);margin-right:8px;"></i> Create Story/Vlog</h2>
            <button class="modal-close" onclick="Community.closeModal('storyModal')">&#x2715;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Media Type</label>
                <div style="display:flex;gap:12px;margin-top:8px;">
                    <label style="display:flex;align-items:center;cursor:pointer;">
                        <input type="radio" name="mediaType" value="image" checked onchange="Community.toggleMediaType('image')" style="margin-right:6px;">
                        <i class="fas fa-image" style="margin-right:4px;"></i> Photo
                    </label>
                    <label style="display:flex;align-items:center;cursor:pointer;">
                        <input type="radio" name="mediaType" value="video" onchange="Community.toggleMediaType('video')" style="margin-right:6px;">
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
                <button class="secondary-button" onclick="Community.closeModal('storyModal')">Cancel</button>
                <button class="primary-button" id="submitStoryBtn" onclick="Community.submitStory()">
                    <i class="fas fa-paper-plane"></i> Post
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="storyViewModal">
    <div class="modal story-view-modal">
        <button class="modal-close" onclick="Community.closeModal('storyViewModal')" style="position:absolute;top:20px;right:20px;z-index:10;">&#x2715;</button>
        <div class="story-view-content" id="storyViewContent">
            <!-- Story content will be loaded here -->
        </div>
    </div>
</div>

<div class="toast" id="toast">
    <i class="fas fa-check-circle"></i>
    <span id="toastMsg"></span>
</div>
@endsection

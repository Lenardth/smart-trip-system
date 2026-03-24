<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Community — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite([
        'resources/css/blade/base.css',
        'resources/css/blade/community/index.css',
        'resources/js/blade/base.js',
        'resources/js/blade/community/index.js'
    ])
</head>
<body>

@include('partials.public-navigation')

<section class="page-hero" style="background:linear-gradient(rgba(20,8,14,.55),rgba(20,8,14,.65)),url('https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=1600&q=80&fit=crop') center/cover no-repeat;">
    <div>
        <h1><i class="fas fa-users"></i> Community</h1>
        <p>Connect with fellow travelers, share stories, and join group adventures.</p>
    </div>
</section>

<div class="community-wrap">

    <div class="community-stats">
        <div class="comm-stat">
            <div class="cs-num" id="stat-members"><span class="sk-line medium skeleton" style="display:inline-block;width:80px;height:32px;"></span></div>
            <div class="cs-label">Active Members</div>
        </div>
        <div class="comm-stat">
            <div class="cs-num" id="stat-stories"><span class="sk-line medium skeleton" style="display:inline-block;width:60px;height:32px;"></span></div>
            <div class="cs-label">Travel Stories</div>
        </div>
        <div class="comm-stat">
            <div class="cs-num" id="stat-groups"><span class="sk-line medium skeleton" style="display:inline-block;width:60px;height:32px;"></span></div>
            <div class="cs-label">Active Groups</div>
        </div>
        <div class="comm-stat">
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

    <h2 class="section-title">Travel Stories</h2>
    <p class="section-subtitle">Real experiences from our community — inspiring tales from around the globe.</p>
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

<!-- New Topic Modal -->
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

<!-- Create Group Modal -->
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

<!-- Invite to Chat Modal -->
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

<div class="toast" id="toast">
    <i class="fas fa-check-circle"></i>
    <span id="toastMsg"></span>
</div>

@include('partials.public-footer')

</body>
</html>

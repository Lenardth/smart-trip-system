window.__COMMUNITY__ = (function () {
    var dc = window.__dashboardConfig || {};
    return {
        pusherKey:     dc.pusherKey     || '',
        pusherCluster: dc.pusherCluster || 'mt1',
        csrfToken:     document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        authUserId:    dc.userId        || null,
        isLoggedIn:    !!dc.userId,
    };
})();

(function () {
    var cfg = window.__COMMUNITY__ || {};
    var allFeedItems = [];
    var currentFilter = 'all';

    function csrfToken() { return cfg.csrfToken || ''; }

    function apiFetch(url, opts) {
        opts = opts || {};
        opts.headers = Object.assign({
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken()
        }, opts.headers || {});
        opts.credentials = 'same-origin';
        return fetch(url, opts).then(function (r) { return r.json(); });
    }

    function initials(name) {
        return (name || '').split(' ').map(function (w) { return w[0]; }).join('').toUpperCase().substring(0, 2);
    }

    function avatar(user, size) {
        size = size || 44;
        var s = 'width:' + size + 'px;height:' + size + 'px;';
        if (user && user.avatar) {
            return '<div style="' + s + 'border-radius:50%;overflow:hidden;flex-shrink:0;"><img src="' + user.avatar + '" style="width:100%;height:100%;object-fit:cover;"></div>';
        }
        return '<div style="' + s + 'border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--deep));color:white;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:' + Math.round(size * 0.36) + 'px;flex-shrink:0;">' + initials(user && user.name || 'U') + '</div>';
    }

    function showToast(msg) {
        var t = document.getElementById('toast');
        var m = document.getElementById('toastMsg');
        if (!t || !m) return;
        m.textContent = msg;
        t.classList.add('show');
        setTimeout(function () { t.classList.remove('show'); }, 3000);
    }

    function formatDuration(seconds) {
        var mins = Math.floor(seconds / 60);
        var secs = seconds % 60;
        return mins + ':' + (secs < 10 ? '0' : '') + secs;
    }

    function loadFeed() {
        apiFetch('/api/feed').then(function (data) {
            allFeedItems = data.stories || [];
            
            if (allFeedItems.length === 0) {
                document.getElementById('feedContent').style.display = 'none';
                document.getElementById('emptyFeed').style.display = 'block';
                return;
            }

            document.getElementById('feedContent').style.display = 'block';
            document.getElementById('emptyFeed').style.display = 'none';
            renderFeed();
        }).catch(function () {
            showToast('Could not load feed');
        });
    }

    function renderFeed() {
        var el = document.getElementById('feedContent');
        if (!el) return;

        var items = currentFilter === 'all' ? allFeedItems : 
                    allFeedItems.filter(function(item) {
                        return item.media_type === currentFilter || 
                               (currentFilter === 'stories' && item.media_type);
                    });

        if (items.length === 0) {
            el.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-muted);">No posts to show for this filter.</div>';
            return;
        }

        el.innerHTML = items.map(function (item) {
            var isVideo = item.media_type === 'video';
            var mediaUrl = isVideo ? (item.thumbnail_url || item.video_url) : item.image_url;
            var likedClass = item.is_liked ? 'liked' : '';
            var isOwnPost = item.user_id === cfg.authUserId;

            var videoOverlay = isVideo ? '<div class="video-overlay"><i class="fas fa-play-circle"></i>' + 
                (item.duration ? '<span class="video-duration">' + formatDuration(item.duration) + '</span>' : '') + 
                '</div>' : '';

            var followButton = !isOwnPost ? 
                '<button class="feed-follow-btn" onclick="Feed.followUser(' + item.user_id + ', this)">' +
                    '<i class="fas fa-user-plus"></i>' +
                '</button>' : '';

            return '<div class="feed-item">' +
                '<div class="feed-item-header">' +
                    '<div onclick="Feed.viewProfile(' + item.user_id + ')" style="cursor:pointer;display:flex;align-items:center;gap:12px;flex:1;">' +
                        avatar({ name: item.author, avatar: item.author_avatar }, 44) +
                        '<div>' +
                            '<strong style="color:var(--deep);font-size:15px;">' + item.author + '</strong>' +
                            '<div style="color:var(--text-muted);font-size:13px;">' + item.created_at + '</div>' +
                        '</div>' +
                    '</div>' +
                    followButton +
                '</div>' +
                '<div class="feed-item-content" onclick="Feed.viewStory(' + item.id + ')">' +
                    '<div class="feed-media" style="background-image:url(\'' + mediaUrl + '\');">' +
                        videoOverlay +
                    '</div>' +
                    '<div class="feed-text">' +
                        '<h3>' + item.title + '</h3>' +
                        '<p>' + (item.excerpt || '') + '</p>' +
                    '</div>' +
                '</div>' +
                '<div class="feed-item-actions">' +
                    '<button class="feed-action-btn ' + likedClass + '" onclick="Feed.likeStory(' + item.id + ', this)">' +
                        '<i class="fas fa-heart"></i> <span class="like-count">' + (item.likes || 0) + '</span>' +
                    '</button>' +
                    '<button class="feed-action-btn" onclick="Feed.openComments(' + item.id + ', \'' + item.title.replace(/'/g, "\\'") + '\')">' +
                        '<i class="fas fa-comment"></i> ' + (item.comments || 0) +
                    '</button>' +
                    '<button class="feed-action-btn" onclick="Feed.shareStory(' + item.id + ')">' +
                        '<i class="fas fa-share"></i> Share' +
                    '</button>' +
                    (isVideo ? '<button class="feed-action-btn"><i class="fas fa-eye"></i> ' + (item.views || 0) + '</button>' : '') +
                '</div>' +
            '</div>';
        }).join('');
    }

    function loadSuggestions() {
        apiFetch('/api/community/travelers').then(function (data) {
            var el = document.getElementById('suggestedUsers');
            if (!el) return;
            
            var travelers = (data.travelers || data || [])
                .filter(function(user) {
                    return user.id !== cfg.authUserId; // Don't suggest yourself
                })
                .slice(0, 5);
            
            if (travelers.length === 0) {
                el.innerHTML = '<p style="text-align:center;color:var(--text-muted);font-size:13px;">No suggestions available</p>';
                return;
            }
            
            el.innerHTML = travelers.map(function (user) {
                return '<div class="suggested-user">' +
                    '<div onclick="Feed.viewProfile(' + user.id + ')" style="cursor:pointer;display:flex;align-items:center;gap:10px;flex:1;">' +
                        avatar({ name: user.name, avatar: user.avatar }, 40) +
                        '<div>' +
                            '<strong style="font-size:14px;">' + user.name + '</strong>' +
                            '<div style="color:var(--text-muted);font-size:12px;">' + (user.location || 'Traveler') + '</div>' +
                        '</div>' +
                    '</div>' +
                    '<button class="mini-follow-btn" onclick="Feed.followUser(' + user.id + ', this)">' +
                        '<i class="fas fa-user-plus"></i>' +
                    '</button>' +
                '</div>';
            }).join('');
        }).catch(function () {});
    }

    function loadTags() {
        apiFetch('/api/community/tags').then(function (data) {
            var el = document.getElementById('trendingTags');
            if (!el) return;
            var tags = data.tags || data || [];
            el.innerHTML = tags.slice(0, 8).map(function (tag) {
                var name = tag.name || tag;
                return '<button class="tag-item" onclick="window.location.href=\'/community\'">#' + name + '</button>';
            }).join('');
        }).catch(function () {});
    }

    function loadUserStats() {
        if (!cfg.authUserId) return;
        
        apiFetch('/api/users/' + cfg.authUserId + '/profile').then(function (data) {
            var stats = data.stats || {};
            document.getElementById('statFollowing').textContent = stats.following || 0;
            document.getElementById('statFollowers').textContent = stats.followers || 0;
            document.getElementById('statPosts').textContent = stats.stories || 0;
        }).catch(function () {});
    }

    function filterBy(filter) {
        currentFilter = filter;
        
        // Update active button
        var buttons = document.querySelectorAll('.filter-btn');
        buttons.forEach(function (btn) {
            if (btn.getAttribute('data-filter') === filter) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        renderFeed();
    }

    function viewProfile(userId) {
        window.location.href = '/users/' + userId + '/profile';
    }

    function viewStory(storyId) {
        window.location.href = '/community?story=' + storyId;
    }

    function likeStory(id, btn) {
        apiFetch('/api/community/stories/' + id + '/like', { method: 'POST' })
            .then(function(data) {
                if (btn) {
                    var countEl = btn.querySelector('.like-count');
                    if (countEl) countEl.textContent = data.likes || 0;
                    
                    if (data.liked) {
                        btn.classList.add('liked');
                    } else {
                        btn.classList.remove('liked');
                    }
                }
            })
            .catch(function() { showToast('Could not like story'); });
    }

    function followUser(userId, btn) {
        if (userId === cfg.authUserId) {
            showToast('You cannot follow yourself');
            return;
        }

        apiFetch('/api/users/' + userId + '/follow', { method: 'POST' })
            .then(function(data) {
                if (data.is_following) {
                    btn.innerHTML = '<i class="fas fa-user-check"></i>';
                    btn.classList.add('following');
                    showToast('Following!');
                } else {
                    btn.innerHTML = '<i class="fas fa-user-plus"></i>';
                    btn.classList.remove('following');
                    showToast('Unfollowed');
                }
                loadUserStats(); // Refresh stats
            })
            .catch(function() {
                showToast('Could not update follow status');
            });
    }

    function openComments(storyId, title) {
        window.location.href = '/community?story=' + storyId + '&comments=1';
    }

    function shareStory(storyId) {
        var url = window.location.origin + '/community?story=' + storyId;
        
        if (navigator.share) {
            navigator.share({
                title: 'Check out this story',
                url: url
            }).catch(function() {});
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(url).then(function() {
                showToast('Link copied to clipboard!');
            }).catch(function() {
                showToast('Could not copy link');
            });
        }
    }

    function init() {
        if (!cfg.isLoggedIn) {
            window.location.href = '/login';
            return;
        }

        loadFeed();
        loadSuggestions();
        loadTags();
        loadUserStats();
    }

    if (document.readyState !== 'loading') init();
    else document.addEventListener('DOMContentLoaded', init);

    window.Feed = {
        filterBy: filterBy,
        viewProfile: viewProfile,
        viewStory: viewStory,
        likeStory: likeStory,
        followUser: followUser,
        openComments: openComments,
        shareStory: shareStory,
    };

}());

window.__COMMUNITY__ = (function () {
    var dc = window.__dashboardConfig || {};
    return {
        csrfToken:     document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        authUserId:    dc.userId        || null,
        isLoggedIn:    !!dc.userId,
    };
})();

(function () {
    var cfg = window.__COMMUNITY__ || {};
    var allMembers = [];
    var filteredMembers = [];
    var currentView = 'grid';
    var currentSort = 'newest';
    var currentPage = 1;
    var membersPerPage = 24;

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

    function showToast(msg) {
        var t = document.getElementById('toast');
        var m = document.getElementById('toastMsg');
        if (!t || !m) return;
        m.textContent = msg;
        t.classList.add('show');
        setTimeout(function () { t.classList.remove('show'); }, 3000);
    }

    function loadMembers() {
        apiFetch('/api/community/members').then(function (data) {
            allMembers = data.members || [];
            filteredMembers = allMembers.slice();
            
            // Update stats
            document.getElementById('totalMembers').textContent = allMembers.length;
            document.getElementById('onlineMembers').textContent = Math.floor(allMembers.length * 0.15); // Simulate
            document.getElementById('newMembers').textContent = Math.floor(allMembers.length * 0.08); // Simulate
            
            sortMembers();
            renderMembers();
        }).catch(function () {
            showToast('Could not load members');
        });
    }

    function sortMembers() {
        switch(currentSort) {
            case 'newest':
                filteredMembers.sort(function(a, b) {
                    return new Date(b.joined) - new Date(a.joined);
                });
                break;
            case 'active':
                filteredMembers.sort(function(a, b) {
                    return (b.posts || 0) - (a.posts || 0);
                });
                break;
            case 'popular':
                filteredMembers.sort(function(a, b) {
                    return (b.followers || 0) - (a.followers || 0);
                });
                break;
            case 'name':
                filteredMembers.sort(function(a, b) {
                    return a.name.localeCompare(b.name);
                });
                break;
        }
    }

    function renderMembers() {
        var el = document.getElementById('membersGrid');
        if (!el) return;

        var displayMembers = filteredMembers.slice(0, currentPage * membersPerPage);
        
        document.getElementById('memberCount').textContent = 
            'Showing ' + displayMembers.length + ' of ' + filteredMembers.length + ' members';

        if (currentView === 'grid') {
            el.className = 'members-grid';
            el.innerHTML = displayMembers.map(function (member) {
                return renderMemberCard(member);
            }).join('');
        } else {
            el.className = 'members-list';
            el.innerHTML = displayMembers.map(function (member) {
                return renderMemberListItem(member);
            }).join('');
        }

        // Show/hide load more button
        var loadMoreBtn = document.getElementById('loadMoreContainer');
        if (loadMoreBtn) {
            loadMoreBtn.style.display = displayMembers.length < filteredMembers.length ? 'block' : 'none';
        }
    }

    function renderMemberCard(member) {
        var isOwnProfile = member.id === cfg.authUserId;
        var avatarHtml = member.avatar
            ? '<img src="' + member.avatar + '" alt="' + member.name + '">'
            : '<div class="member-avatar-initials">' + initials(member.name) + '</div>';

        var actionButtons = '';
        if (cfg.isLoggedIn && !isOwnProfile) {
            actionButtons = '<div class="member-actions">' +
                '<button class="action-btn follow-btn" onclick="Members.followMember(' + member.id + ', this)">' +
                    '<i class="fas fa-user-plus"></i>' +
                '</button>' +
                '<button class="action-btn message-btn" onclick="Members.messageMember(' + member.id + ')">' +
                    '<i class="fas fa-comment"></i>' +
                '</button>' +
            '</div>';
        }

        return '<div class="member-card" onclick="Members.viewProfile(' + member.id + ')">' +
            '<div class="member-avatar">' + avatarHtml + '</div>' +
            '<h3>' + member.name + '</h3>' +
            '<p class="member-location">' + (member.location || 'Traveler') + '</p>' +
            '<div class="member-stats-mini">' +
                '<div><strong>' + (member.posts || 0) + '</strong> Posts</div>' +
                '<div><strong>' + (member.followers || 0) + '</strong> Followers</div>' +
            '</div>' +
            (member.badge ? '<div class="member-badge">' + member.badge + '</div>' : '') +
            actionButtons +
        '</div>';
    }

    function renderMemberListItem(member) {
        var isOwnProfile = member.id === cfg.authUserId;
        var avatarHtml = member.avatar
            ? '<img src="' + member.avatar + '" alt="' + member.name + '">'
            : '<div class="member-avatar-initials">' + initials(member.name) + '</div>';

        var actionButtons = '';
        if (cfg.isLoggedIn && !isOwnProfile) {
            actionButtons = '<div class="member-list-actions">' +
                '<button class="secondary-button" onclick="event.stopPropagation();Members.followMember(' + member.id + ', this)">' +
                    '<i class="fas fa-user-plus"></i> Follow' +
                '</button>' +
                '<button class="primary-button" onclick="event.stopPropagation();Members.messageMember(' + member.id + ')">' +
                    '<i class="fas fa-comment"></i> Message' +
                '</button>' +
            '</div>';
        }

        return '<div class="member-list-item" onclick="Members.viewProfile(' + member.id + ')">' +
            '<div class="member-list-avatar">' + avatarHtml + '</div>' +
            '<div class="member-list-info">' +
                '<h3>' + member.name + '</h3>' +
                '<p>' + (member.bio || 'Travel enthusiast exploring the world') + '</p>' +
                '<div class="member-list-meta">' +
                    '<span><i class="fas fa-map-marker-alt"></i> ' + (member.location || 'Unknown') + '</span>' +
                    '<span><i class="fas fa-calendar"></i> Joined ' + member.joined + '</span>' +
                '</div>' +
            '</div>' +
            '<div class="member-list-stats">' +
                '<div class="stat-box"><strong>' + (member.posts || 0) + '</strong><span>Posts</span></div>' +
                '<div class="stat-box"><strong>' + (member.followers || 0) + '</strong><span>Followers</span></div>' +
                '<div class="stat-box"><strong>' + (member.following || 0) + '</strong><span>Following</span></div>' +
            '</div>' +
            actionButtons +
        '</div>';
    }

    function changeView(view) {
        currentView = view;
        
        // Update buttons
        var buttons = document.querySelectorAll('.view-btn');
        buttons.forEach(function (btn) {
            if (btn.getAttribute('data-view') === view) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        renderMembers();
    }

    function sortBy(sort) {
        currentSort = sort;
        currentPage = 1;
        sortMembers();
        renderMembers();
    }

    function filterType(checkbox) {
        // Implementation for filtering by type
        // For now, just re-render
        renderMembers();
    }

    function searchMembers() {
        var searchTerm = document.getElementById('searchMembers').value.toLowerCase();
        
        if (!searchTerm) {
            filteredMembers = allMembers.slice();
        } else {
            filteredMembers = allMembers.filter(function(member) {
                return member.name.toLowerCase().includes(searchTerm) ||
                       (member.location && member.location.toLowerCase().includes(searchTerm)) ||
                       (member.bio && member.bio.toLowerCase().includes(searchTerm));
            });
        }
        
        currentPage = 1;
        sortMembers();
        renderMembers();
    }

    function resetFilters() {
        document.getElementById('searchMembers').value = '';
        document.getElementById('sortBy').value = 'newest';
        currentSort = 'newest';
        currentPage = 1;
        filteredMembers = allMembers.slice();
        sortMembers();
        renderMembers();
    }

    function loadMore() {
        currentPage++;
        renderMembers();
    }

    function viewProfile(userId) {
        window.location.href = '/users/' + userId + '/profile';
    }

    function followMember(userId, btn) {
        if (!cfg.isLoggedIn) {
            showToast('Please login to follow members');
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
            })
            .catch(function() {
                showToast('Could not update follow status');
            });
    }

    function messageMember(userId) {
        if (!cfg.isLoggedIn) {
            showToast('Please login to send messages');
            return;
        }
        window.location.href = '/chat/' + userId;
    }

    function closeQuickView() {
        document.getElementById('quickViewModal').classList.remove('open');
    }

    function init() {
        loadMembers();
        
        // Setup search with debounce
        var searchInput = document.getElementById('searchMembers');
        if (searchInput) {
            var timeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(searchMembers, 300);
            });
        }

        // Close modal on overlay click
        document.addEventListener('click', function (e) {
            var modal = document.getElementById('quickViewModal');
            if (modal && e.target === modal) {
                closeQuickView();
            }
        });
    }

    if (document.readyState !== 'loading') init();
    else document.addEventListener('DOMContentLoaded', init);

    window.Members = {
        changeView: changeView,
        sortBy: sortBy,
        filterType: filterType,
        resetFilters: resetFilters,
        loadMore: loadMore,
        viewProfile: viewProfile,
        followMember: followMember,
        messageMember: messageMember,
        closeQuickView: closeQuickView,
    };

}());

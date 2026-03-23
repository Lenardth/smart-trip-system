window.__DISCOVER__ = {
            csrfToken: "null",
            auth: null
        };
const Discover = (() => {

    const CSRF = window.__DISCOVER__.csrfToken;
    const IS_AUTH = window.__DISCOVER__.auth === true;

    const MOOD_ICONS = {
        relaxed:      'fa-spa',
        adventurous:  'fa-mountain',
        cultural:     'fa-landmark',
        romantic:     'fa-heart',
        foodie:       'fa-utensils',
        eco:          'fa-leaf',
        beach:        'fa-umbrella-beach',
        mountain:     'fa-mountain',
        historical:   'fa-landmark',
        food_culture: 'fa-utensils',
        eco_tourism:  'fa-leaf',
    };

    const state = {
        category: 'all',
        region:   'all',
        query:    '',
        debounceTimer: null,
    };

    /* ── Utilities ── */



    function showToast(msg, icon = 'fa-info-circle') {
        const t = document.getElementById('toast');
        t.querySelector('i').className = `fas ${icon}`;
        document.getElementById('toastMsg').textContent = msg;
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 3000);
    }

    function buildQuery() {
        const p = new URLSearchParams();
        if (state.category !== 'all') p.set('category', state.category);
        if (state.region   !== 'all') p.set('region',   state.region);
        if (state.query.trim())       p.set('q',        state.query.trim());
        return p.toString() ? '?' + p.toString() : '';
    }

    /* ── Render destinations ── */

    /* ── Wishlist Model ── */

    let wishlistedIds = new Set();

    function apiFetch(url, options = {}) {
        return fetch(url, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            ...options
        }).then(r => {
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            return r.json();
        });
    }

    function loadWishlistCount() {
        if (!IS_AUTH) return;
        apiFetch('/api/wishlist/count')
            .then(data => {
                wishlistedIds = new Set(data.ids ?? []);
                updateBadge(data.count ?? wishlistedIds.size);
            })
            .catch(() => {});
    }

    function updateBadge(count) {
        const badge = document.getElementById('wishlistCount');
        if (badge) badge.textContent = count;
    }

    function toggleWishlist(destinationId, btn) {
        if (!IS_AUTH) {
            window.location.href = '/login';
            return;
        }
        const inList = wishlistedIds.has(destinationId);
        const icon = btn.querySelector('i');

        if (inList) {
            apiFetch(`/wishlist/${destinationId}`, { method: 'DELETE' })
                .then(() => {
                    wishlistedIds.delete(destinationId);
                    updateBadge(wishlistedIds.size);
                    icon.className = 'far fa-heart';
                    btn.title = 'Save to Wishlist';
                    showToast('Removed from wishlist', 'fa-heart-broken');
                })
                .catch(() => showToast('Could not remove', 'fa-exclamation-circle'));
        } else {
            apiFetch('/wishlist', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({ destination_id: destinationId })
            })
                .then(() => {
                    wishlistedIds.add(destinationId);
                    updateBadge(wishlistedIds.size);
                    icon.className = 'fas fa-heart';
                    btn.title = 'Remove from Wishlist';
                    showToast('Saved to Wishlist!', 'fa-heart');
                })
                .catch(() => showToast('Could not save', 'fa-exclamation-circle'));
        }
    }

    /* ── View ── */

    function renderDestinations(destinations) {
        const grid = document.getElementById('destinationsGrid');
        const info = document.getElementById('resultsInfo');

        if (!destinations.length) {
            grid.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <p>No destinations found. Try adjusting your filters.</p>
                </div>`;
            info.textContent = '';
            return;
        }

        info.textContent = `${destinations.length} destination${destinations.length !== 1 ? 's' : ''} found`;

        grid.innerHTML = destinations.map(d => {
            const moodIcon = MOOD_ICONS[d.mood?.toLowerCase()] || 'fa-globe';
            const moodLabel = d.mood
                ? d.mood.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
                : '';
            const badgeHtml = d.badge
                ? `<span class="dest-badge">${d.badge}</span>`
                : '';
            const imgStyle = d.image_url
                ? `background-image:url('${d.image_url}')`
                : '';
            const inList  = wishlistedIds.has(d.id);
            const heartCls = inList ? 'fas fa-heart' : 'far fa-heart';
            const heartTip = inList ? 'Remove from Wishlist' : 'Save to Wishlist';

            return `
                <div class="destination-card" data-id="${d.id}">
                    <div class="destination-image" style="${imgStyle}">
                        ${badgeHtml}
                        <button class="wishlist-toggle" data-id="${d.id}" title="${heartTip}">
                            <i class="${heartCls}"></i>
                        </button>
                    </div>
                    <div class="destination-content">
                        <h3>${d.name}${d.country ? ', ' + d.country : ''}</h3>
                        <div class="destination-meta">
                            <span class="price-tag">$${Number(d.price_from).toLocaleString()}+</span>
                            <span class="mood-indicator">
                                <i class="fas ${moodIcon}"></i> ${moodLabel}
                            </span>
                        </div>
                        <p>${d.description ?? ''}</p>
                        <a href="/destinations/${d.id}" class="primary-button">
                            Explore <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>`;
        }).join('');

        grid.querySelectorAll('.wishlist-toggle').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                e.stopPropagation();
                toggleWishlist(Number(btn.dataset.id), btn);
            });
        });
    }

    function skeletonGrid(count = 6) {
        return Array.from({ length: count }, () => `
            <div class="destination-card">
                <div class="destination-image skeleton"></div>
                <div class="destination-content">
                    <div class="sk-line medium skeleton"></div>
                    <div class="sk-line short skeleton"></div>
                    <div class="sk-line full skeleton"></div>
                    <div class="sk-line full skeleton"></div>
                    <div class="sk-line medium skeleton" style="margin-top:10px;height:36px;border-radius:4px;"></div>
                </div>
            </div>`).join('');
    }

    function loadDestinations() {
        const grid = document.getElementById('destinationsGrid');
        document.getElementById('resultsInfo').textContent = '';
        grid.innerHTML = skeletonGrid();

        apiFetch('/api/discover/destinations' + buildQuery())
            .then(data => renderDestinations(data.data ?? data))
            .catch(() => {
                grid.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Could not load destinations. Please try again.</p>
                    </div>`;
            });
    }

    /* ── Render hidden gems ── */

    function renderHiddenGems(gems) {
        const grid = document.getElementById('hiddenGemsGrid');
        if (!gems.length) {
            grid.innerHTML = '<p style="color:var(--text-sub);grid-column:1/-1;">No hidden gems found.</p>';
            return;
        }
        grid.innerHTML = gems.map(g => {
            const imgStyle = g.image_url ? `background-image:url('${g.image_url}')` : '';
            return `
                <div class="featured-card" data-id="${g.id}">
                    <div class="feat-img" style="${imgStyle}"></div>
                    <div class="feat-body">
                        <h4>${g.name}${g.country ? ', ' + g.country : ''}</h4>
                        <p>${g.description ?? ''}</p>
                        ${g.match_score ? `<span class="feat-tag">${g.match_score}% Match</span>` : ''}
                    </div>
                </div>`;
        }).join('');
    }

    function loadHiddenGems() {
        const grid = document.getElementById('hiddenGemsGrid');
        grid.innerHTML = Array.from({ length: 3 }, () => `
            <div class="featured-card">
                <div class="feat-img skeleton"></div>
                <div class="feat-body">
                    <div class="sk-line medium skeleton" style="background:rgba(255,255,255,.15);"></div>
                    <div class="sk-line full skeleton" style="background:rgba(255,255,255,.1);margin-top:6px;"></div>
                </div>
            </div>`).join('');

        apiFetch('/api/discover/hidden-gems')
            .then(data => renderHiddenGems(data.data ?? data))
            .catch(() => { grid.innerHTML = ''; });
    }

    /* ── Filter tabs ── */

    function initTabs() {
        document.getElementById('filterTabs').addEventListener('click', e => {
            const tab = e.target.closest('.filter-tab');
            if (!tab) return;
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            state.category = tab.dataset.category;
            loadDestinations();
        });
    }

    /* ── Region pills ── */

    function initRegions() {
        document.getElementById('regionRow').addEventListener('click', e => {
            const pill = e.target.closest('.region-pill');
            if (!pill) return;
            document.querySelectorAll('.region-pill').forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            state.region = pill.dataset.region;
            loadDestinations();
        });
    }

    /* ── Search ── */

    function initSearch() {
        const input = document.getElementById('searchInput');
        const btn   = document.getElementById('searchBtn');

        btn.addEventListener('click', () => {
            state.query = input.value;
            loadDestinations();
        });

        input.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                state.query = input.value;
                loadDestinations();
            }
        });

        input.addEventListener('input', () => {
            clearTimeout(state.debounceTimer);
            state.debounceTimer = setTimeout(() => {
                state.query = input.value;
                loadDestinations();
            }, 450);
        });
    }

    /* ── Boot ── */

    function init() {
        loadWishlistCount();
        initTabs();
        initRegions();
        initSearch();
        loadDestinations();
        loadHiddenGems();
    }

    document.addEventListener('DOMContentLoaded', init);

    return {};

})();

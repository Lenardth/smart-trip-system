const Discover = (() => {

    const CSRF = window.__DISCOVER__.csrfToken;

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

    function apiFetch(url) {
        return fetch(url, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        }).then(r => {
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            return r.json();
        });
    }

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

            return `
                <div class="destination-card" data-id="${d.id}">
                    <div class="destination-image" style="${imgStyle}">
                        ${badgeHtml}
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
        initTabs();
        initRegions();
        initSearch();
        loadDestinations();
        loadHiddenGems();
    }

    document.addEventListener('DOMContentLoaded', init);

    return {};

})();

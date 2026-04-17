const _dc = window.__dashboardConfig || {};
const CSRF    = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const IS_AUTH = !!(_dc.userId);

const MOOD_ICONS = {
    relaxed:      'fa-spa',
    adventurous:  'fa-mountain',
    cultural:     'fa-landmark',
    romantic:     'fa-heart',
    foodie:       'fa-utensils',
    eco:          'fa-leaf',
    eco_tourism:  'fa-leaf',
    beach:        'fa-umbrella-beach',
    mountain:     'fa-mountain',
    historical:   'fa-landmark',
    food_culture: 'fa-utensils',
    nature:       'fa-tree',
    general:      'fa-globe',
};

const state = {
    category:      'all',
    region:        'all',
    query:         '',
    debounceTimer: null,
};

let wishlistedIds = new Set();

function showToast(msg, icon) {
    icon = icon || 'fa-info-circle';
    const t = document.getElementById('toast');
    if (!t) return;
    const i = t.querySelector('i');
    if (i) i.className = 'fas ' + icon;
    const m = document.getElementById('toastMsg');
    if (m) m.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
}

function apiFetch(url, options) {
    options = options || {};
    const headers = Object.assign({ 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, options.headers || {});
    return fetch(url, Object.assign({}, options, { headers, credentials: 'same-origin' }))
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); });
}

function buildQuery() {
    const p = new URLSearchParams();
    if (state.category !== 'all') p.set('category', state.category);
    if (state.region   !== 'all') p.set('region',   state.region);
    if (state.query.trim())       p.set('q',        state.query.trim());
    return p.toString() ? '?' + p.toString() : '';
}

function loadWishlistCount() {
    if (!IS_AUTH) return;
    apiFetch('/api/wishlist/count')
        .then(data => {
            wishlistedIds = new Set(data.ids ?? []);
            const badge = document.getElementById('wishlistCount');
            if (badge) badge.textContent = data.count ?? wishlistedIds.size;
        })
        .catch(() => {});
}

function toggleWishlist(destinationId, btn) {
    if (!IS_AUTH) { window.location.href = '/login'; return; }

    const inList = wishlistedIds.has(destinationId);
    const icon   = btn.querySelector('i');

    if (inList) {
        apiFetch('/wishlist/' + destinationId, { method: 'DELETE' })
            .then(() => {
                wishlistedIds.delete(destinationId);
                if (icon) icon.className = 'far fa-heart';
                btn.title = 'Save to Wishlist';
                showToast('Removed from wishlist', 'fa-heart-broken');
                try { localStorage.setItem('smartBookingWishlistUpdated', String(Date.now())); } catch (_) {}
                if (window.__refreshWishlistBadge) window.__refreshWishlistBadge();
            })
            .catch(() => showToast('Could not remove', 'fa-exclamation-circle'));
    } else {
        apiFetch('/wishlist', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ destination_id: destinationId }),
        })
            .then(() => {
                wishlistedIds.add(destinationId);
                if (icon) icon.className = 'fas fa-heart';
                btn.title = 'Remove from Wishlist';
                showToast('Saved to Wishlist!', 'fa-heart');
                try { localStorage.setItem('smartBookingWishlistUpdated', String(Date.now())); } catch (_) {}
                if (window.__refreshWishlistBadge) window.__refreshWishlistBadge();
            })
            .catch(() => showToast('Could not save', 'fa-exclamation-circle'));
    }
}

function renderDestinations(destinations) {
    const grid = document.getElementById('destinationsGrid');
    const info = document.getElementById('resultsInfo');
    if (!grid) return;

    if (!destinations.length) {
        grid.innerHTML = '<div class="empty-state"><i class="fas fa-search"></i><p>No destinations found. Try adjusting your filters.</p></div>';
        if (info) info.textContent = '';
        return;
    }

    if (info) info.textContent = destinations.length + ' destination' + (destinations.length !== 1 ? 's' : '') + ' found';

    grid.innerHTML = destinations.map(d => {
        const moodIcon  = MOOD_ICONS[d.mood?.toLowerCase()] || 'fa-globe';
        const moodLabel = d.mood ? d.mood.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) : '';
        const badgeHtml = d.badge ? `<span class="dest-badge">${d.badge}</span>` : '';
        const imgStyle  = d.image_url ? `background-image:url('${d.image_url}')` : '';
        const inList    = wishlistedIds.has(d.id);
        
        // Use the converted price from API with currency symbol
        const price = d.price_from > 0
            ? '<span>' + (d.currency && typeof window.Currency !== 'undefined' ? window.Currency.symbol(d.currency) : '$') + d.price_from.toLocaleString() + '+</span>'
            : '';

        return `<div class="destination-card" data-id="${d.id}">
            <div class="destination-image" style="${imgStyle}">
                ${badgeHtml}
                <button class="wishlist-toggle" data-id="${d.id}" title="${inList ? 'Remove from Wishlist' : 'Save to Wishlist'}">
                    <i class="${inList ? 'fas' : 'far'} fa-heart"></i>
                </button>
            </div>
            <div class="destination-content">
                <h3>${d.name}${d.country ? ', ' + d.country : ''}</h3>
                <div class="destination-meta">
                    ${price ? `<span class="price-tag">${price}</span>` : ''}
                    ${moodLabel ? `<span class="mood-indicator"><i class="fas ${moodIcon}"></i> ${moodLabel}</span>` : ''}
                </div>
                <p>${d.description ? d.description.substring(0, 110) + (d.description.length > 110 ? '…' : '') : ''}</p>
                <a href="/destinations/${d.id}" class="primary-button" style="text-decoration:none;width:100%;justify-content:center;">
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

function skeletonGrid(count) {
    count = count || 6;
    return Array.from({ length: count }, () =>
        `<div class="destination-card">
            <div class="destination-image skeleton"></div>
            <div class="destination-content">
                <div class="sk-line medium skeleton"></div>
                <div class="sk-line short skeleton"></div>
                <div class="sk-line full skeleton"></div>
                <div class="sk-line full skeleton"></div>
                <div class="sk-line medium skeleton" style="margin-top:10px;height:36px;border-radius:4px;"></div>
            </div>
        </div>`
    ).join('');
}

function loadDestinations() {
    const grid = document.getElementById('destinationsGrid');
    const info = document.getElementById('resultsInfo');
    if (!grid) return;
    if (info) info.textContent = '';
    grid.innerHTML = skeletonGrid();

    apiFetch('/api/discover/destinations' + buildQuery())
        .then(data => renderDestinations(data.data ?? data))
        .catch(() => {
            grid.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Could not load destinations. Please try again.</p></div>';
        });
}

function renderHiddenGems(gems) {
    const grid = document.getElementById('hiddenGemsGrid');
    if (!grid) return;
    if (!gems.length) {
        grid.innerHTML = '<p style="color:rgba(255,255,255,.6);grid-column:1/-1;text-align:center;">No hidden gems found.</p>';
        return;
    }
    grid.innerHTML = gems.map(g => {
        const imgStyle = g.image_url ? `background-image:url('${g.image_url}')` : '';
        return `<a href="/destinations/${g.id}" class="featured-card" style="text-decoration:none;display:block;">
            <div class="feat-img" style="${imgStyle}"></div>
            <div class="feat-body">
                <h4>${g.name}${g.country ? ', ' + g.country : ''}</h4>
                <p>${g.description ? g.description.substring(0, 80) + (g.description.length > 80 ? '…' : '') : ''}</p>
                ${g.match_score ? `<span class="feat-tag">${g.match_score}% Match</span>` : ''}
            </div>
        </a>`;
    }).join('');
}

function loadHiddenGems() {
    const grid = document.getElementById('hiddenGemsGrid');
    if (!grid) return;
    grid.innerHTML = Array.from({ length: 3 }, () =>
        `<div class="featured-card">
            <div class="feat-img skeleton"></div>
            <div class="feat-body">
                <div class="sk-line medium skeleton" style="background:rgba(255,255,255,.15);"></div>
                <div class="sk-line full skeleton" style="background:rgba(255,255,255,.1);margin-top:6px;"></div>
            </div>
        </div>`
    ).join('');

    apiFetch('/api/discover/hidden-gems')
        .then(data => renderHiddenGems(data.data ?? data))
        .catch(() => { if (grid) grid.innerHTML = ''; });
}

function initTabs() {
    const tabs = document.getElementById('filterTabs');
    if (!tabs) return;
    tabs.addEventListener('click', e => {
        const tab = e.target.closest('.filter-tab');
        if (!tab) return;
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        state.category = tab.dataset.category;
        loadDestinations();
    });
}

function initRegions() {
    const row = document.getElementById('regionRow');
    if (!row) return;
    row.addEventListener('click', e => {
        const pill = e.target.closest('.region-pill');
        if (!pill) return;
        document.querySelectorAll('.region-pill').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        state.region = pill.dataset.region;
        loadDestinations();
    });
}

function initSearch() {
    const input = document.getElementById('searchInput');
    const btn   = document.getElementById('searchBtn');
    if (!input || !btn) return;

    const doSearch = () => { state.query = input.value; loadDestinations(); };

    btn.addEventListener('click', doSearch);
    input.addEventListener('keydown', e => { if (e.key === 'Enter') doSearch(); });
    input.addEventListener('input', () => {
        clearTimeout(state.debounceTimer);
        state.debounceTimer = setTimeout(doSearch, 450);
    });
}

function init() {
    loadWishlistCount();
    initTabs();
    initRegions();
    initSearch();
    loadDestinations();
    loadHiddenGems();
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}

// When currency changes, reload data from API (which will return new currency)
if (typeof window.Currency !== 'undefined') {
    window.Currency.onCurrencyChange(function() { 
        loadDestinations(); 
        loadHiddenGems(); 
    });
} else {
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.Currency !== 'undefined') {
            window.Currency.onCurrencyChange(function() { 
                loadDestinations(); 
                loadHiddenGems(); 
            });
        }
    });
}
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
        
        // Use the converted price from API with proper formatting
        const price = d.price_from > 0
            ? '<span class="dest-price">' + 
              (d.currency && typeof window.Currency !== 'undefined' 
                ? window.Currency.symbol(d.currency) 
                : '$') + 
              Math.round(d.price_from).toLocaleString() + 
              ' <span>/ person</span></span>'
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
                    ${moodLabel ? `<span class="mood-indicator"><i class="fas ${moodIcon}"></i> ${moodLabel}</span>` : ''}
                </div>
                <p>${d.description ? d.description.substring(0, 110) + (d.description.length > 110 ? '…' : '') : ''}</p>
                <div style="display:flex;flex-direction:column;gap:10px;margin-top:auto;">
                    ${price}
                    <a href="/destination-info/${d.id}" class="primary-button" style="text-decoration:none;padding:8px 16px;font-size:13px;text-align:center;width:100%;">
                        View Details
                    </a>
                </div>
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
        return `<a href="/destination-info/${g.id}" class="featured-card" style="text-decoration:none;display:block;">
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

    const doSearch = () => { 
        state.query = input.value.trim(); 
        if (state.query.length >= 2) {
            // Use search endpoint for queries
            loadSearchResults();
        } else {
            // Load regular destinations if query is empty
            loadDestinations();
        }
    };

    btn.addEventListener('click', doSearch);
    input.addEventListener('keydown', e => { if (e.key === 'Enter') doSearch(); });
    input.addEventListener('input', () => {
        clearTimeout(state.debounceTimer);
        state.debounceTimer = setTimeout(doSearch, 450);
    });
}

function loadSearchResults() {
    const grid = document.getElementById('destinationsGrid');
    const info = document.getElementById('resultsInfo');
    if (!grid) return;
    if (info) info.textContent = 'Searching...';
    grid.innerHTML = skeletonGrid();

    apiFetch('/api/discover/search?q=' + encodeURIComponent(state.query))
        .then(data => {
            const results = data || [];
            if (!results.length) {
                grid.innerHTML = '<div class="empty-state"><i class="fas fa-search"></i><p>No destinations found for "' + state.query + '". Try a different search.</p></div>';
                if (info) info.textContent = '';
                return;
            }

            if (info) info.textContent = results.length + ' result' + (results.length !== 1 ? 's' : '') + ' found';

            // Convert search results to destination format
            const destinations = results.map(r => ({
                id: r.id,
                name: r.name,
                country: r.country,
                description: r.description || '',
                image_url: r.image_url || 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=800&q=80',
                price_from: 0,
                mood: 'general',
                badge: r.type === 'new' ? 'New' : null,
            }));

            renderDestinations(destinations);
        })
        .catch(err => {
            console.error('Search error:', err);
            grid.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Could not search destinations. Please try again.</p><p style="font-size:13px;color:var(--text-muted);margin-top:8px;">Error: ' + err.message + '</p></div>';
            if (info) info.textContent = '';
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


// ═══════════════════════════════════════════════════════════════════════════
// Destination Insights (News, Sites, Things to Do)
// ═══════════════════════════════════════════════════════════════════════════

let currentInsightsDestination = null;

function showInsights(destinationName, country) {
    const insightsSection = document.getElementById('destinationInsights');
    const insightsDestination = document.getElementById('insightsDestination');
    
    if (!insightsSection || !insightsDestination) return;
    
    currentInsightsDestination = country || destinationName;
    insightsDestination.textContent = destinationName + (country ? ', ' + country : '');
    insightsSection.style.display = 'block';
    
    // Scroll to insights
    insightsSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    // Load all insights
    loadDestinationNews(currentInsightsDestination);
    loadTouristSites(currentInsightsDestination);
    loadThingsToDo(currentInsightsDestination);
}

function closeInsights() {
    const insightsSection = document.getElementById('destinationInsights');
    if (insightsSection) {
        insightsSection.style.display = 'none';
    }
    currentInsightsDestination = null;
}

window.closeInsights = closeInsights;

async function loadDestinationNews(destination) {
    const newsContent = document.getElementById('newsContent');
    if (!newsContent) return;
    
    newsContent.innerHTML = '<div class="insight-loading"><i class="fas fa-spinner fa-spin"></i> Loading news...</div>';
    
    try {
        const response = await fetch(`/api/destination-news?destination=${encodeURIComponent(destination)}`);
        const data = await response.json();
        
        if (data.success && data.articles && data.articles.length > 0) {
            newsContent.innerHTML = data.articles.slice(0, 5).map(article => `
                <div class="insight-item">
                    <div class="insight-item-title">
                        <i class="fas fa-newspaper"></i>
                        ${escapeHtml(article.title)}
                    </div>
                    ${article.description ? `<div class="insight-item-desc">${escapeHtml(article.description)}</div>` : ''}
                    <div class="insight-item-meta">
                        ${article.source ? `<span><i class="fas fa-building"></i> ${escapeHtml(article.source)}</span>` : ''}
                        ${article.publishedAt ? `<span><i class="fas fa-clock"></i> ${formatDate(article.publishedAt)}</span>` : ''}
                    </div>
                    ${article.url ? `<a href="${article.url}" target="_blank" rel="noopener" class="insight-item-link">Read more <i class="fas fa-external-link-alt"></i></a>` : ''}
                </div>
            `).join('');
        } else {
            newsContent.innerHTML = '<div class="insight-empty"><i class="fas fa-newspaper"></i>No recent news available for this destination.</div>';
        }
    } catch (error) {
        console.error('Failed to load news:', error);
        newsContent.innerHTML = '<div class="insight-empty"><i class="fas fa-exclamation-circle"></i>Failed to load news. Please try again later.</div>';
    }
}

async function loadTouristSites(destination) {
    const sitesContent = document.getElementById('sitesContent');
    if (!sitesContent) return;
    
    sitesContent.innerHTML = '<div class="insight-loading"><i class="fas fa-spinner fa-spin"></i> Loading sites...</div>';
    
    try {
        // Use Wikipedia API to get tourist attractions
        const response = await fetch(`https://en.wikipedia.org/api/rest_v1/page/related/${encodeURIComponent(destination)}`);
        const data = await response.json();
        
        if (data.pages && data.pages.length > 0) {
            const sites = data.pages.filter(page => 
                page.description && 
                (page.description.toLowerCase().includes('museum') ||
                 page.description.toLowerCase().includes('monument') ||
                 page.description.toLowerCase().includes('palace') ||
                 page.description.toLowerCase().includes('temple') ||
                 page.description.toLowerCase().includes('church') ||
                 page.description.toLowerCase().includes('castle') ||
                 page.description.toLowerCase().includes('park') ||
                 page.description.toLowerCase().includes('landmark'))
            ).slice(0, 6);
            
            if (sites.length > 0) {
                sitesContent.innerHTML = sites.map(site => `
                    <div class="insight-item">
                        <div class="insight-item-title">
                            <i class="fas fa-map-marker-alt"></i>
                            ${escapeHtml(site.title)}
                        </div>
                        ${site.description ? `<div class="insight-item-desc">${escapeHtml(site.description)}</div>` : ''}
                        ${site.content_urls && site.content_urls.desktop ? `<a href="${site.content_urls.desktop.page}" target="_blank" rel="noopener" class="insight-item-link">Learn more <i class="fas fa-external-link-alt"></i></a>` : ''}
                    </div>
                `).join('');
            } else {
                loadDefaultTouristSites(destination, sitesContent);
            }
        } else {
            loadDefaultTouristSites(destination, sitesContent);
        }
    } catch (error) {
        console.error('Failed to load tourist sites:', error);
        loadDefaultTouristSites(destination, sitesContent);
    }
}

function loadDefaultTouristSites(destination, sitesContent) {
    // Fallback: Show generic tourist site categories
    const categories = [
        { icon: 'fa-landmark', title: 'Historical Landmarks', desc: 'Explore ancient monuments and historical sites' },
        { icon: 'fa-building', title: 'Museums & Galleries', desc: 'Discover art, culture, and history' },
        { icon: 'fa-tree', title: 'Parks & Gardens', desc: 'Enjoy nature and outdoor spaces' },
        { icon: 'fa-utensils', title: 'Local Markets', desc: 'Experience authentic local culture' },
        { icon: 'fa-camera', title: 'Photo Spots', desc: 'Capture memorable moments' }
    ];
    
    sitesContent.innerHTML = categories.map(cat => `
        <div class="insight-item">
            <div class="insight-item-title">
                <i class="fas ${cat.icon}"></i>
                ${cat.title}
            </div>
            <div class="insight-item-desc">${cat.desc}</div>
        </div>
    `).join('');
}

async function loadThingsToDo(destination) {
    const thingsContent = document.getElementById('thingsContent');
    if (!thingsContent) return;
    
    thingsContent.innerHTML = '<div class="insight-loading"><i class="fas fa-spinner fa-spin"></i> Loading activities...</div>';
    
    // Show curated activities based on destination type
    const activities = [
        { icon: 'fa-walking', title: 'Walking Tours', desc: 'Explore the city on foot with guided tours', popular: true },
        { icon: 'fa-utensils', title: 'Food & Dining', desc: 'Try local cuisine and restaurants', popular: true },
        { icon: 'fa-shopping-bag', title: 'Shopping', desc: 'Browse local markets and boutiques', popular: false },
        { icon: 'fa-camera', title: 'Photography', desc: 'Capture stunning views and landmarks', popular: true },
        { icon: 'fa-bus', title: 'City Tours', desc: 'Hop-on hop-off bus tours', popular: false },
        { icon: 'fa-water', title: 'Water Activities', desc: 'Beaches, boats, and water sports', popular: false },
        { icon: 'fa-mountain', title: 'Outdoor Adventures', desc: 'Hiking, climbing, and nature', popular: false },
        { icon: 'fa-music', title: 'Nightlife & Entertainment', desc: 'Bars, clubs, and live music', popular: true },
        { icon: 'fa-spa', title: 'Wellness & Spa', desc: 'Relax and rejuvenate', popular: false },
        { icon: 'fa-ticket-alt', title: 'Events & Shows', desc: 'Concerts, theater, and performances', popular: false }
    ];
    
    // Shuffle and show 6 activities
    const shuffled = activities.sort(() => 0.5 - Math.random()).slice(0, 6);
    
    thingsContent.innerHTML = shuffled.map(activity => `
        <div class="insight-item">
            <div class="insight-item-title">
                <i class="fas ${activity.icon}"></i>
                ${activity.title}
                ${activity.popular ? '<span style="background:var(--gold);color:var(--deep);font-size:10px;padding:2px 6px;border-radius:3px;margin-left:8px;font-weight:600;">POPULAR</span>' : ''}
            </div>
            <div class="insight-item-desc">${activity.desc}</div>
        </div>
    `).join('');
}

function formatDate(dateString) {
    try {
        const date = new Date(dateString);
        const now = new Date();
        const diffTime = Math.abs(now - date);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays === 0) return 'Today';
        if (diffDays === 1) return 'Yesterday';
        if (diffDays < 7) return `${diffDays} days ago`;
        if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
        
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    } catch (e) {
        return dateString;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Show insights when clicking on a destination card
document.addEventListener('click', function(e) {
    const destCard = e.target.closest('.destination-card');
    if (destCard && !e.target.closest('.wishlist-toggle') && !e.target.closest('.primary-button')) {
        const heading = destCard.querySelector('h3');
        if (heading) {
            const fullText = heading.textContent;
            const parts = fullText.split(',');
            const name = parts[0].trim();
            const country = parts[1] ? parts[1].trim() : '';
            showInsights(name, country);
        }
    }
});

// Show insights when searching
const originalSearchBtn = document.getElementById('searchBtn');
if (originalSearchBtn) {
    originalSearchBtn.addEventListener('click', function() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput && searchInput.value.trim()) {
            setTimeout(() => {
                showInsights(searchInput.value.trim(), '');
            }, 1000);
        }
    });
}

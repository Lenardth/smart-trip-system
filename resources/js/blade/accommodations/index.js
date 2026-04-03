let map = null;
let markers = [];
let lastCity = '';
let newsAbort = null;

let searchInput, styleSelect, budgetSelect, reloadBtn;
let accommodationsGrid, emptyState, aiMatchPanel, aiMatchSummary;
let locationPanel, mapCityLabel, mapAccomCount, newsCityLabel, newsDateline;
let newsLoading, newsError, newsErrorMsg, newsFeed, newsEmpty, newsMoreLink;

document.addEventListener('DOMContentLoaded', () => {
    searchInput        = document.getElementById('searchInput');
    styleSelect        = document.getElementById('styleSelect');
    budgetSelect       = document.getElementById('budgetSelect');
    reloadBtn          = document.getElementById('reloadBtn');
    accommodationsGrid = document.getElementById('accommodationsGrid');
    emptyState         = document.getElementById('emptyState');
    aiMatchPanel       = document.getElementById('aiMatchPanel');
    aiMatchSummary     = document.getElementById('aiMatchSummary');
    locationPanel      = document.getElementById('locationPanel');
    mapCityLabel       = document.getElementById('mapCityLabel');
    mapAccomCount      = document.getElementById('mapAccomCount');
    newsCityLabel      = document.getElementById('newsCityLabel');
    newsDateline       = document.getElementById('newsDateline');
    newsLoading        = document.getElementById('newsLoading');
    newsError          = document.getElementById('newsError');
    newsErrorMsg       = document.getElementById('newsErrorMsg');
    newsFeed           = document.getElementById('newsFeed');
    newsEmpty          = document.getElementById('newsEmpty');
    newsMoreLink       = document.getElementById('newsMoreLink');

    reloadBtn.addEventListener('click', doSearch);
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') doSearch();
    });

    loadAccommodations();
});

async function doSearch() {
    const city = searchInput.value.trim();
    await loadAccommodations();

    if (city && city.length >= 2) {
        locationPanel.style.display = 'grid';
        updateMapCity(city);
        updateNewsCity(city);
    } else {
        locationPanel.style.display = 'none';
        clearMap();
        clearNews();
        lastCity = '';
    }
}

async function loadAccommodations() {
    const query  = searchInput ? searchInput.value.trim() : '';
    const style  = styleSelect ? styleSelect.value : 'any';
    const budget = budgetSelect ? budgetSelect.value : 'any';

    accommodationsGrid.innerHTML = '<div class="grid-loading"><i class="fas fa-spinner fa-spin"></i> Loading…</div>';
    emptyState.style.display = 'none';

    try {
        const params = new URLSearchParams();
        if (query)            params.set('q', query);
        if (style  !== 'any') params.set('style', style);
        if (budget !== 'any') params.set('budget', budget);

        const res  = await fetch(`/api/accommodations?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const data = await res.json();
        const list = data.data || data.accommodations || data || [];

        renderGrid(list);
        renderMapMarkers(list);

        mapAccomCount.textContent = `${list.length} result${list.length !== 1 ? 's' : ''}`;

        if (data.ai_summary) {
            aiMatchSummary.textContent = data.ai_summary;
            aiMatchPanel.style.display = 'block';
        } else {
            aiMatchPanel.style.display = 'none';
        }
    } catch (err) {
        accommodationsGrid.innerHTML = '<p class="grid-error"><i class="fas fa-exclamation-triangle"></i> Failed to load accommodations.</p>';
    }
}

function renderGrid(list) {
    if (!list.length) {
        accommodationsGrid.innerHTML = '';
        emptyState.style.display = 'block';
        return;
    }

    emptyState.style.display = 'none';

    accommodationsGrid.innerHTML = list.map((a) => {
        const rating    = Number(a.rating || 0);
        const stars     = rating ? '★'.repeat(rating) + '☆'.repeat(Math.max(0, 5 - rating)) : '';
        const amenities = (a.amenities || []).slice(0, 4)
            .map((am) => `<span class="accom-amenity"><i class="fas fa-check"></i> ${esc(am)}</span>`)
            .join('');
        const distBadge = a.distance_km
            ? `<span class="accom-dist"><i class="fas fa-location-arrow"></i> ${esc(a.distance_km)}</span>`
            : '';
        const price = a.price_per_night ?? a.nightly_rate ?? '';

        return `
        <div class="accom-card" data-lat="${a.lat || ''}" data-lng="${a.lng || ''}" data-id="${esc(String(a.id || ''))}">
            <div class="accom-image" style="background-image: url('${esc(a.image_url || a.image || '')}')">
                <span class="accom-badge">${esc(styleLabel(a.style))}</span>
                ${distBadge}
            </div>
            <div class="accom-body">
                <div class="accom-header">
                    <h3 class="accom-name">${esc(a.name)}</h3>
                    ${stars ? `<span class="accom-stars">${stars}</span>` : ''}
                </div>
                <p class="accom-location">
                    <i class="fas fa-map-marker-alt"></i>
                    ${esc(a.city || '')}${a.country ? ', ' + esc(a.country) : ''}
                </p>
                <p class="accom-desc">${esc((a.description || '').replace(/[★☆]/g, '').trim())}</p>
                ${amenities ? `<div class="accom-amenities">${amenities}</div>` : ''}
                <div class="accom-meta">
                    <span class="accom-price">
                        ${price !== '' ? `<span class="price-from">from</span> $${esc(price)}<span class="price-unit">/night</span>` : ''}
                    </span>
                    <span class="accom-budget-tag">${esc(budgetLabel(a.budget_tier))}</span>
                </div>
                <div class="accom-actions">
                    <button class="primary-button" onclick="window.location.href='/bookings/create?accommodation_id=${esc(String(a.id || ''))}'">
                        <i class="fas fa-calendar-check"></i> Book Now
                    </button>
                    <button class="secondary-button" onclick="jumpToNews('${esc(a.city || '')}')">
                        <i class="fas fa-newspaper"></i> Local News
                    </button>
                </div>
            </div>
        </div>`;
    }).join('');
}

function initMap() {
    if (map) return;

    map = L.map('accommodationsMap', {
        zoomControl: true,
        scrollWheelZoom: false,
    }).setView([20, 0], 2);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 18,
    }).addTo(map);
}

function clearMap() {
    if (!map) return;
    markers.forEach((m) => m.remove());
    markers = [];
}

async function updateMapCity(city) {
    initMap();
    clearMap();
    mapCityLabel.textContent = city;

    try {
        const geo    = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(city)}&format=json&limit=1`);
        const places = await geo.json();

        if (places.length) {
            const { lat, lon } = places[0];
            map.setView([parseFloat(lat), parseFloat(lon)], 11);
            const m = L.marker([lat, lon]).addTo(map);
            markers.push(m);
        }
    } catch (_) {}
}

function renderMapMarkers(list) {
    if (!map) return;

    list.forEach((a) => {
        if (!a.lat || !a.lng) return;
        const price = a.price_per_night ?? a.nightly_rate ?? '';
        const m = L.marker([a.lat, a.lng])
            .addTo(map)
            .bindPopup(`<strong>${esc(a.name)}</strong><br>${price ? '$' + esc(price) : ''}`);
        markers.push(m);
    });
}

function clearNews() {
    newsFeed.innerHTML         = '';
    newsEmpty.style.display    = 'none';
    newsError.style.display    = 'none';
    newsLoading.style.display  = 'none';
    newsMoreLink.style.display = 'none';
    if (newsDateline) newsDateline.textContent = '';
}

async function updateNewsCity(city) {
    if (city === lastCity) return;
    lastCity = city;

    if (newsAbort) newsAbort.abort();
    newsAbort = new AbortController();

    clearNews();

    newsCityLabel.textContent = `${city} Dispatch`;

    if (newsDateline) {
        newsDateline.textContent = new Date().toLocaleDateString('en-US', {
            weekday: 'long',
            year:    'numeric',
            month:   'long',
            day:     'numeric',
        });
    }

    newsLoading.style.display = 'flex';

    try {
        let articles = await loadAccommodationNews(`${city} travel tourism`, newsAbort.signal);

        if (!articles.length) {
            articles = await loadAccommodationNews(`${city} tourism`, newsAbort.signal);
        }

        if (!articles.length) {
            articles = await loadAccommodationNews('travel tourism', newsAbort.signal);
        }

        newsLoading.style.display = 'none';

        if (!articles.length) {
            newsEmpty.style.display = 'flex';
            return;
        }

        renderNews(articles, city);

    } catch (err) {
        if (err.name === 'AbortError') return;
        newsLoading.style.display = 'none';
        showNewsError(err.message || 'Could not load news.');
    }
}

async function loadAccommodationNews(query, signal = null) {
    const url = new URL('/api/accommodation-news', window.location.origin);
    url.searchParams.set('q', query);

    const res  = await fetch(url.toString(), {
        signal,
        headers: { 'Accept': 'application/json' },
    });

    const data = await res.json();

    if (!res.ok) throw new Error(data.message || 'Failed to load news.');

    return data.articles ?? [];
}

function renderNews(articles, city) {
    newsFeed.innerHTML = articles.map((a) => {
        const date    = new Date(a.publishedAt);
        const dateStr = isNaN(date.getTime())
            ? ''
            : date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

        const source = a.source?.name || '';
        const meta   = [source, dateStr].filter(Boolean).join(' &mdash; ');

        return `
        <div class="news-item">
            <a class="news-item-title" href="${esc(a.url)}" target="_blank" rel="noopener">${esc(a.title)}</a>
            ${meta ? `<div class="news-item-meta">${meta}</div>` : ''}
            ${a.description ? `<p class="news-item-desc">${esc(a.description)}</p>` : ''}
        </div>`;
    }).join('');

    newsMoreLink.href          = `https://news.google.com/search?q=${encodeURIComponent(city + ' travel')}`;
    newsMoreLink.style.display = 'inline-flex';
}

function showNewsError(msg) {
    newsErrorMsg.textContent = msg;
    newsError.style.display  = 'flex';
}

window.jumpToNews = function (city) {
    if (!city) return;

    searchInput.value           = city;
    locationPanel.style.display = 'grid';
    lastCity                    = '';

    updateMapCity(city);
    updateNewsCity(city);

    locationPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

function esc(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function styleLabel(s)  { return s || 'Stay'; }
function budgetLabel(b) { return b || ''; }

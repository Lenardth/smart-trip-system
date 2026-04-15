let map = null;
let markers = [];
let lastCity = '';
let newsAbort = null;

let searchInput, styleSelect, budgetSelect, reloadBtn;
let accommodationsGrid, emptyState, aiMatchPanel, aiMatchSummary;
let locationPanel, mapCityLabel, mapAccomCount, newsCityLabel, newsDateline;
let newsLoading, newsError, newsErrorMsg, newsFeed, newsEmpty, newsMoreLink;

function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
}

ready(() => {
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

    if (reloadBtn) reloadBtn.addEventListener('click', doSearch);
    if (searchInput) searchInput.addEventListener('keydown', (e) => {
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

        // Load travel advisory for searched city
        if (typeof window.renderTravelAdvisory === 'function') {
            window.renderTravelAdvisory('travelAdvisoryContainer', city, city);
        }

        // Load news-based travel warnings
        loadTravelWarnings(city);
    } else {
        locationPanel.style.display = 'none';
        clearMap();
        clearNews();
        lastCity = '';
        const advisoryContainer = document.getElementById('travelAdvisoryContainer');
        if (advisoryContainer) advisoryContainer.style.display = 'none';
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
    accommodationsGrid.innerHTML = list.map(function(a) {
        var rate       = Number(a.nightly_rate || 0);
        var rating     = Number(a.rating || 0).toFixed(1);
        var reviews    = Number(a.review_count || 0).toLocaleString();
        var ratingWord = rating >= 4.5 ? 'Exceptional' : rating >= 4.0 ? 'Excellent' : rating >= 3.5 ? 'Very Good' : rating >= 3.0 ? 'Good' : 'Okay';
        var amenitiesHtml = (a.amenities || []).slice(0, 5)
            .map(function(am) { return '<span class="accom-amenity"><i class="fas fa-check-circle"></i>' + esc(am) + '</span>'; })
            .join('');
        var dealBadge = a.deal_badge
            ? '<span class="accom-deal-badge"><i class="fas fa-tag"></i> ' + esc(a.deal_badge) + '</span>'
            : '';
        var styleBadge = '<span class="accom-style-badge">' + esc(styleLabel(a.style)) + '</span>';
        var priceFormatted = rate > 0
            ? (typeof window.Currency !== 'undefined' ? window.Currency.format(rate) : '$' + Math.round(rate))
            : '';
        var bookingUrl = a.booking_url || '#';
        var mapsUrl = 'https://maps.google.com/?q=' + encodeURIComponent((a.name || '') + ' ' + (a.city || ''));
        return '<div class="accom-card" data-lat="' + (a.lat||'') + '" data-lng="' + (a.lng||'') + '" data-id="' + esc(String(a.id||'')) + '">'
            + '<div class="accom-image" style="background-image:url(\'' + esc(a.image_url||'') + '\')">'
            + styleBadge + dealBadge
            + '<div class="accom-image-overlay"></div></div>'
            + '<div class="accom-body">'
            + '<div class="accom-top-row"><h3 class="accom-name">' + esc(a.name) + '</h3>'
            + '<div class="accom-score-wrap"><span class="accom-score">' + rating + '</span></div></div>'
            + '<div class="accom-rating-row"><span class="accom-rating-word">' + ratingWord + '</span>'
            + '<span class="accom-reviews">' + reviews + ' reviews</span></div>'
            + '<p class="accom-location"><i class="fas fa-map-marker-alt"></i> '
            + esc(a.city||'') + (a.country ? ', ' + esc(a.country) : '')
            + ' <a href="' + mapsUrl + '" target="_blank" rel="noopener" class="accom-map-link"><i class="fas fa-external-link-alt"></i> Map</a></p>'
            + (amenitiesHtml ? '<div class="accom-amenities">' + amenitiesHtml + '</div>' : '')
            + '<div class="accom-footer">'
            + '<div class="accom-price-block">'
            + (rate > 0 ? '<div class="accom-price-main" data-price-usd="' + rate + '">' + priceFormatted + '<span class="accom-price-unit">/night</span></div>' : '')
            + '<div class="accom-budget-tag">' + esc(budgetLabel(a.budget_tier)) + '</div></div>'
            + '<div class="accom-cta-wrap">'
            + '<a href="' + esc(bookingUrl) + '" target="_blank" rel="noopener" class="accom-book-btn">'
            + '<i class="fas fa-external-link-alt"></i> See Deals</a>'
            + '<button class="accom-news-btn" onclick="jumpToNews(\'' + esc(a.city||'') + '\')">'
            + '<i class="fas fa-newspaper"></i></button>'
            + '</div></div></div></div>';
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

function styleLabel(s) {
    const map = {
        hostel: 'Hostel', budget_hotel: 'Budget Hotel', boutique: 'Boutique',
        resort: 'Resort', villa: 'Villa', airbnb: 'Apartment', glamping: 'Glamping',
        hotel: 'Hotel', apartment: 'Apartment', guest_house: 'Guest House', motel: 'Motel',
    };
    return map[s] || (s ? s.charAt(0).toUpperCase() + s.slice(1) : 'Stay');
}

function budgetLabel(b) {
    const map = {
        backpacker: 'Backpacker', budget: 'Budget', mid: 'Mid-range',
        premium: 'Premium', luxury: 'Luxury',
    };
    return map[b] || (b || '');
}

async function loadTravelWarnings(city) {
    const container = document.getElementById('travelWarningBanner');
    if (!container) return;

    container.style.display = 'none';
    container.innerHTML = '';

    try {
        const res  = await fetch('/api/travel-warning?q=' + encodeURIComponent(city), {
            headers: { 'Accept': 'application/json' },
        });
        const data = await res.json();
        const warnings = data.warnings || [];

        if (!warnings.length) return;

        container.style.display = 'block';
        container.innerHTML = '<div class="warning-banner-inner">'
            + '<div class="warning-banner-title"><i class="fas fa-exclamation-triangle"></i> Travel Heads-Up for ' + esc(city) + '</div>'
            + '<div class="warning-banner-subtitle">Based on current news — stay informed before you book</div>'
            + warnings.map(function(w) {
                return '<div class="warning-item">'
                    + '<a href="' + esc(w.url) + '" target="_blank" rel="noopener" class="warning-item-title">'
                    + '<i class="fas fa-newspaper"></i> ' + esc(w.title) + '</a>'
                    + (w.description ? '<p class="warning-item-desc">' + esc(w.description.substring(0, 120)) + '…</p>' : '')
                    + '<span class="warning-item-source">' + esc((w.source && w.source.name) || '') + '</span>'
                    + '</div>';
            }).join('')
            + '</div>';
    } catch (_) {}
}

// ── Recommendations & Fun Facts ──────────────────────────────────────────────

const TRENDING_DESTINATIONS = [
    { city: 'Bali',         country: 'Indonesia',      icon: 'fa-umbrella-beach', tag: 'Tropical escape' },
    { city: 'Lisbon',       country: 'Portugal',       icon: 'fa-landmark',       tag: 'Culture & food' },
    { city: 'Tokyo',        country: 'Japan',          icon: 'fa-torii-gate',     tag: 'City adventure' },
    { city: 'Cape Town',    country: 'South Africa',   icon: 'fa-mountain',       tag: 'Nature & wine' },
    { city: 'Marrakech',    country: 'Morocco',        icon: 'fa-mosque',         tag: 'Exotic & vibrant' },
    { city: 'Santorini',    country: 'Greece',         icon: 'fa-sun',            tag: 'Romantic getaway' },
    { city: 'Bangkok',      country: 'Thailand',       icon: 'fa-utensils',       tag: 'Street food heaven' },
    { city: 'New York',     country: 'USA',            icon: 'fa-city',           tag: 'Never sleeps' },
    { city: 'Dubai',        country: 'UAE',            icon: 'fa-building',       tag: 'Luxury & glam' },
    { city: 'Amsterdam',    country: 'Netherlands',    icon: 'fa-bicycle',        tag: 'Canals & culture' },
    { city: 'Nairobi',      country: 'Kenya',          icon: 'fa-paw',            tag: 'Safari gateway' },
    { city: 'Buenos Aires', country: 'Argentina',      icon: 'fa-music',          tag: 'Tango & steak' },
    { city: 'Kyoto',        country: 'Japan',          icon: 'fa-leaf',           tag: 'Temples & tradition' },
    { city: 'Barcelona',    country: 'Spain',          icon: 'fa-palette',        tag: 'Art & beaches' },
    { city: 'Maldives',     country: 'Maldives',       icon: 'fa-water',          tag: 'Overwater bliss' },
    { city: 'Prague',       country: 'Czech Republic', icon: 'fa-chess-rook',     tag: 'Fairy-tale city' },
    { city: 'Medellin',     country: 'Colombia',       icon: 'fa-seedling',       tag: 'City of eternal spring' },
    { city: 'Reykjavik',    country: 'Iceland',        icon: 'fa-snowflake',      tag: 'Northern lights' },
];

const FUN_FACTS = [
    { icon: 'fa-globe', text: 'There are 195 countries in the world. The average person visits only 10 in their lifetime.' },
    { icon: 'fa-plane', text: 'Booking flights on Tuesday or Wednesday is typically 15-20% cheaper than booking on weekends.' },
    { icon: 'fa-bed', text: 'Hotels near airports charge up to 40% more than equivalent hotels just 2km away.' },
    { icon: 'fa-map-marked-alt', text: 'France is the most visited country in the world with over 90 million tourists annually.' },
    { icon: 'fa-suitcase-rolling', text: 'Rolling clothes instead of folding fits up to 30% more in your bag and reduces wrinkles.' },
    { icon: 'fa-utensils', text: 'Street food stalls with long queues of locals are almost always safer and tastier than tourist restaurants.' },
    { icon: 'fa-user', text: 'Solo travel has grown by over 40% in the last decade. Most solo travellers report it as life-changing.' },
    { icon: 'fa-camera', text: 'The best light for photography is the 30 minutes after sunrise and before sunset — called golden hour.' },
    { icon: 'fa-mountain', text: 'Altitude sickness can affect anyone above 2,500m regardless of fitness. Acclimatise for 24 hours before hiking.' },
    { icon: 'fa-wifi', text: 'Download offline maps before you travel. Google Maps offline works without any data connection.' },
    { icon: 'fa-credit-card', text: 'Notify your bank before travelling abroad to avoid having your card blocked on the first transaction.' },
    { icon: 'fa-shield-alt', text: 'Travel insurance costs about 4-8% of your trip cost but can save you tens of thousands in emergencies.' },
    { icon: 'fa-clock', text: 'Jet lag recovery takes roughly one day per time zone crossed. Adjust your sleep schedule 3 days before flying.' },
    { icon: 'fa-tint', text: 'Dehydration is the most common travel complaint. Drink water before, during, and after every flight.' },
    { icon: 'fa-star', text: 'Checking in online 24 hours before your flight gives you the best seat selection at no extra cost.' },
];

let recShuffleIndex = 0;

function initRecommendations() {
    renderRecommendationChips();
    renderFunFact();
}

function renderRecommendationChips() {
    const container = document.getElementById('recChips');
    if (!container) return;

    const shuffled = [...TRENDING_DESTINATIONS].sort(() => Math.random() - 0.5).slice(0, 8);

    container.innerHTML = shuffled.map(function(d) {
        return '<button class="rec-chip" onclick="applyRecommendation(\'' + d.city + '\')">'
            + '<span class="rec-chip-icon"><i class="fas ' + d.icon + '"></i></span>'
            + '<span class="rec-chip-city">' + d.city + '</span>'
            + '<span class="rec-chip-tag">' + d.tag + '</span>'
            + '</button>';
    }).join('');
}

function renderFunFact() {
    const el = document.getElementById('recFunFact');
    if (!el) return;
    const fact = FUN_FACTS[Math.floor(Math.random() * FUN_FACTS.length)];
    el.innerHTML = '<i class="fas ' + fact.icon + '"></i> <span>' + fact.text + '</span>';
}

window.shuffleRecommendations = function() {
    const btn = document.getElementById('recShuffleBtn');
    if (btn) { btn.classList.add('spinning'); setTimeout(function() { btn.classList.remove('spinning'); }, 500); }
    renderRecommendationChips();
    renderFunFact();
};

window.applyRecommendation = function(city) {
    if (searchInput) {
        searchInput.value = city;
        searchInput.focus();
        searchInput.style.borderColor = 'var(--gold)';
        searchInput.style.boxShadow = '0 0 0 3px rgba(201,169,110,.25)';
        setTimeout(function() {
            searchInput.style.borderColor = '';
            searchInput.style.boxShadow = '';
        }, 1000);
    }
    doSearch();
};

// Init recommendations on page load
ready(function() {
    initRecommendations();

    // Re-render accommodation prices when currency changes
    if (typeof window.Currency !== 'undefined') {
        window.Currency.onCurrencyChange(function() {
            // Re-render the grid with current data
            var grid = document.getElementById('accommodationsGrid');
            if (grid && grid.querySelectorAll('[data-price-usd]').length > 0) {
                grid.querySelectorAll('[data-price-usd]').forEach(function(el) {
                    var usd = parseFloat(el.dataset.priceUsd);
                    if (!isNaN(usd) && usd > 0) {
                        el.childNodes[0] && el.childNodes[0].nodeType === 3
                            ? (el.childNodes[0].textContent = window.Currency.format(usd))
                            : null;
                    }
                });
            }
        });
    }
});

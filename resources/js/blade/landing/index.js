document.addEventListener('DOMContentLoaded', function () {
    const wrapper  = document.getElementById('moodSelectWrapper');
    const trigger  = document.getElementById('moodSelectTrigger');
    const dropdown = document.getElementById('moodDropdown');
    const hidden   = document.getElementById('moodSelect');

    if (!wrapper || !trigger || !dropdown || !hidden) return;

    const iconEl = trigger.querySelector('.custom-select-icon');
    const textEl = trigger.querySelector('.custom-select-text');

    trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        wrapper.classList.toggle('open');
    });

    dropdown.querySelectorAll('.custom-select-option').forEach(function (opt) {
        opt.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdown.querySelectorAll('.custom-select-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');

            const iconTag = this.querySelector('i');
            iconEl.innerHTML = iconTag ? iconTag.outerHTML : '';
            textEl.textContent = this.textContent.trim();
            hidden.value = this.dataset.value;

            wrapper.classList.remove('open');
        });
    });

    document.addEventListener('click', function () {
        wrapper.classList.remove('open');
    });
});

(function () {
    const grid       = document.getElementById('destinationsGrid');
    const loading    = document.getElementById('destinationsLoading');
    const empty      = document.getElementById('destinationsEmpty');
    const filterTags = document.querySelectorAll('.filter-tag');

    let allDestinations = [];
    let activeFilter    = 'all';

    async function fetchDestinations() {
        loading.style.display = 'block';
        grid.style.display    = 'none';
        empty.style.display   = 'none';

        try {
            const res = await fetch('/api/discover/destinations?active=1', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!res.ok) {
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }

            const data = await res.json();
            console.log('[Destinations] Raw API response:', data);

            if (Array.isArray(data)) {
                allDestinations = data;
            } else if (data && Array.isArray(data.data)) {
                allDestinations = data.data;
            } else if (data && Array.isArray(data.destinations)) {
                allDestinations = data.destinations;
            } else {
                allDestinations = [];
                console.warn('[Destinations] Unexpected response shape:', data);
            }

            console.log('[Destinations] Loaded:', allDestinations.length, 'destinations');
            window._allDestinations = allDestinations;
            renderGrid(allDestinations.slice(0, 8));

        } catch (err) {
            console.error('[Destinations] Fetch failed:', err);
            loading.style.display = 'block';
            loading.innerHTML = `
                <div style="padding:40px;text-align:center;">
                    <i class="fas fa-exclamation-triangle" style="font-size:36px;color:var(--deep);opacity:0.7;"></i>
                    <p style="margin:12px 0 4px;font-size:15px;color:var(--text);">Could not load destinations</p>
                    <p style="font-size:12px;color:var(--text-muted);margin:0 0 16px;">${err.message}</p>
                    <button class="secondary-button" onclick="initDestinations()" style="font-size:13px;">
                        <i class="fas fa-redo"></i> Retry
                    </button>
                </div>`;
        }
    }

    function applyFilter(filter) {
        activeFilter = filter;
        let results;
        if (filter === 'all') {
            results = allDestinations;
        } else if (filter === 'hidden_gem') {
            results = allDestinations.filter(d => Number(d.is_hidden_gem) === 1);
        } else if (filter === 'romantic') {
            results = allDestinations.filter(d => d.mood === 'romantic' || (d.badge && d.badge.toLowerCase().includes('romantic')));
        } else if (filter === 'adventurous') {
            results = allDestinations.filter(d => d.mood === 'adventurous' || d.category === 'adventurous');
        } else {
            results = allDestinations.filter(d => d.category === filter || d.mood === filter);
        }
        renderGrid(results.slice(0, 8));
    }

    function renderGrid(destinations) {
        loading.style.display = 'none';

        if (!destinations || destinations.length === 0) {
            grid.style.display  = 'none';
            empty.style.display = 'block';
            return;
        }

        empty.style.display = 'none';
        grid.style.display  = 'grid';
        grid.innerHTML      = destinations.map(d => buildCard(d)).join('');
    }

    function buildCard(d) {
        const image       = d.image_url || `https://picsum.photos/seed/${encodeURIComponent(d.name)}/600/400`;
        const price       = d.price_from ? `From $${Number(d.price_from).toLocaleString()}` : '';
        const badge       = d.badge ? `<span class="destination-badge">${d.badge}</span>` : '';
        const hiddenGem   = Number(d.is_hidden_gem) === 1
            ? `<span class="destination-badge" style="background:rgba(138,43,226,0.85);"><i class="fas fa-gem"></i> Hidden Gem</span>` : '';
        const matchScore  = d.match_score
            ? `<div class="match-score"><i class="fas fa-star"></i> ${d.match_score}% match</div>` : '';
        const description = d.description
            ? (d.description.length > 110 ? d.description.substring(0, 110) + '…' : d.description)
            : '';
        const regionLabel = formatRegion(d.region);
        const moodIcon    = moodIconMap(d.mood);

        return `
        <div class="destination-card" data-category="${d.category}" data-mood="${d.mood}" data-hidden="${d.is_hidden_gem}">
            <div class="destination-image" style="background-image:url('${image}');background-size:cover;background-position:center;height:200px;position:relative;border-radius:6px 6px 0 0;overflow:hidden;">
                <div style="position:absolute;inset:0;background:linear-gradient(to bottom,transparent 40%,rgba(0,0,0,0.6));"></div>
                <div style="position:absolute;top:12px;left:12px;display:flex;gap:6px;flex-wrap:wrap;">
                    ${badge}${hiddenGem}
                </div>
                ${matchScore ? `<div style="position:absolute;top:12px;right:12px;">${matchScore}</div>` : ''}
                <div style="position:absolute;bottom:12px;left:14px;right:14px;">
                    <h3 style="color:#fff;margin:0;font-size:17px;font-weight:700;text-shadow:0 1px 4px rgba(0,0,0,0.7);">${d.name}</h3>
                    <p style="color:rgba(255,255,255,0.85);margin:3px 0 0;font-size:13px;">
                        <i class="fas fa-map-marker-alt" style="margin-right:4px;"></i>${d.country} &nbsp;·&nbsp; ${regionLabel}
                    </p>
                </div>
            </div>
            <div class="destination-info">
                <p style="color:var(--text-muted);font-size:13px;margin:0 0 12px;line-height:1.5;">${description}</p>
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span style="font-size:12px;color:var(--text-muted);background:rgba(201,169,110,0.12);border:1px solid var(--border);border-radius:20px;padding:3px 10px;">${moodIcon} ${formatLabel(d.mood)}</span>
                        <span style="font-size:12px;color:var(--text-muted);background:rgba(201,169,110,0.12);border:1px solid var(--border);border-radius:20px;padding:3px 10px;"><i class="fas fa-tag"></i> ${formatLabel(d.category)}</span>
                    </div>
                    <div style="text-align:right;">
                        ${price ? `<div style="font-size:15px;font-weight:700;color:var(--deep);">${price}</div><div style="font-size:11px;color:var(--text-muted);">per person</div>` : ''}
                    </div>
                </div>
                <a href="/destinations/${d.id ?? ''}" class="primary-button" style="text-decoration:none;padding:9px;font-size:13px;">
                    <i class="fas fa-compass"></i> Explore
                </a>
            </div>
        </div>`;
    }

    function formatRegion(region) {
        const map = {
            europe: 'Europe', asia: 'Asia', middle_east: 'Middle East',
            africa: 'Africa', america: 'Americas', oceania: 'Oceania', general: 'World'
        };
        return map[region] || (region ? region.replace(/_/g, ' ') : 'World');
    }

    function formatLabel(str) {
        if (!str) return '';
        return str.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }

    function moodIconMap(mood) {
        const icons = {
            adventurous: '<i class="fas fa-hiking"></i>',
            relaxed:     '<i class="fas fa-umbrella-beach"></i>',
            cultural:    '<i class="fas fa-landmark"></i>',
            romantic:    '<i class="fas fa-heart"></i>',
            foodie:      '<i class="fas fa-utensils"></i>',
            wellness:    '<i class="fas fa-spa"></i>',
            eco:         '<i class="fas fa-leaf"></i>',
            eco_tourism: '<i class="fas fa-leaf"></i>',
            nature:      '<i class="fas fa-tree"></i>',
            general:     '<i class="fas fa-globe"></i>',
            beach:       '<i class="fas fa-umbrella-beach"></i>',
            mountain:    '<i class="fas fa-mountain"></i>',
            nightlife:   '<i class="fas fa-music"></i>',
            spiritual:   '<i class="fas fa-place-of-worship"></i>',
            wellness:    '<i class="fas fa-spa"></i>',
            road_trip:   '<i class="fas fa-car"></i>',
            backpacking: '<i class="fas fa-backpack"></i>',
            city_break:  '<i class="fas fa-city"></i>',
            safari:      '<i class="fas fa-paw"></i>',
            cruise:      '<i class="fas fa-ship"></i>',
            honeymoon:   '<i class="fas fa-ring"></i>',
            photography: '<i class="fas fa-camera"></i>',
        };
        return icons[mood] || '<i class="fas fa-map-marker-alt"></i>';
    }

    filterTags.forEach(tag => {
        tag.addEventListener('click', function () {
            filterTags.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            applyFilter(this.dataset.filter);
        });
    });

    window.initDestinations = fetchDestinations;
    fetchDestinations();
})();

function filterByStyle(style, cardEl) {
    const styleMap = {
        adventure: { moods: ['adventurous'], categories: ['adventurous', 'mountain'], label: 'Adventure Travel' },
        beach:     { moods: ['relaxed', 'beach'], categories: ['beach'],              label: 'Beach & Relaxation' },
        cultural:  { moods: ['cultural'],          categories: ['historical', 'food_culture', 'general'], label: 'Cultural Immersion' },
        food:      { moods: ['foodie'],             categories: ['food_culture'],      label: 'Culinary Tours' },
    };

    const styleGrid    = document.getElementById('styleDestinationsGrid');
    const styleEmpty   = document.getElementById('styleEmpty');
    const styleHeader  = document.getElementById('styleResultsHeader');
    const styleTitle   = document.getElementById('styleResultsTitle');
    const styleCount   = document.getElementById('styleResultsCount');
    const styleViewAll = document.getElementById('styleViewAll');

    document.querySelectorAll('.category-card').forEach(c => c.classList.remove('active-style'));
    if (cardEl) cardEl.classList.add('active-style');

    const mapping = styleMap[style];
    if (!mapping || !window._allDestinations || window._allDestinations.length === 0) return;

    const results = window._allDestinations.filter(d =>
        mapping.moods.includes(d.mood) || mapping.categories.includes(d.category)
    ).slice(0, 8);

    styleHeader.style.display = 'block';
    styleTitle.textContent    = mapping.label;
    styleCount.textContent    = results.length + ' destinations';

    styleGrid.style.display   = results.length ? 'grid' : 'none';
    styleEmpty.style.display  = results.length ? 'none' : 'block';
    styleViewAll.style.display = 'block';

    styleGrid.innerHTML = results.map(d => buildStyleCard(d)).join('');

    styleGrid.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function buildStyleCard(d) {
    const image       = d.image_url || 'https://picsum.photos/seed/' + encodeURIComponent(d.name) + '/600/400';
    const price       = d.price_from ? 'From $' + Number(d.price_from).toLocaleString() : '';
    const badge       = d.badge ? '<span class="destination-badge">' + d.badge + '</span>' : '';
    const hiddenGem   = Number(d.is_hidden_gem) === 1
        ? '<span class="destination-badge" style="background:rgba(138,43,226,0.85);"><i class="fas fa-gem"></i> Hidden Gem</span>' : '';
    const description = d.description
        ? (d.description.length > 110 ? d.description.substring(0, 110) + '…' : d.description) : '';

    return '<div class="destination-card">' +
        '<div class="destination-image" style="background-image:url(\'' + image + '\');background-size:cover;background-position:center;height:200px;position:relative;border-radius:6px 6px 0 0;overflow:hidden;">' +
            '<div style="position:absolute;inset:0;background:linear-gradient(to bottom,transparent 40%,rgba(0,0,0,0.6));"></div>' +
            '<div style="position:absolute;top:12px;left:12px;display:flex;gap:6px;flex-wrap:wrap;">' + badge + hiddenGem + '</div>' +
            '<div style="position:absolute;bottom:12px;left:14px;right:14px;">' +
                '<h3 style="color:#fff;margin:0;font-size:17px;font-weight:700;text-shadow:0 1px 4px rgba(0,0,0,0.7);">' + d.name + '</h3>' +
                '<p style="color:rgba(255,255,255,0.85);margin:3px 0 0;font-size:13px;"><i class="fas fa-map-marker-alt" style="margin-right:4px;"></i>' + d.country + '</p>' +
            '</div>' +
        '</div>' +
        '<div class="destination-info">' +
            '<p style="color:var(--text-muted);font-size:13px;margin:0 0 12px;line-height:1.5;">' + description + '</p>' +
            '<div style="display:flex;justify-content:space-between;align-items:center;">' +
                '<span style="font-size:12px;color:var(--text-muted);background:rgba(201,169,110,0.12);border:1px solid var(--border);border-radius:20px;padding:3px 10px;"><i class="fas fa-tag"></i> ' + (d.category || '').replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase()) + '</span>' +
                (price ? '<div style="font-size:15px;font-weight:700;color:var(--deep);">' + price + '</div>' : '') +
            '</div>' +
            '<a href="/destinations/' + (d.id || '') + '" class="primary-button" style="text-decoration:none;padding:9px;font-size:13px;">' +
                '<i class="fas fa-compass"></i> Explore' +
            '</a>' +
        '</div>' +
    '</div>';
}
function getMoodText(m) {
    return {
        adventurous:'Adventurous 🏔️', relaxed:'Relaxed 🌴', cultural:'Cultural 🏛️',
        romantic:'Romantic 💖', foodie:'Foodie 🍽️', wellness:'Wellness 🧘',
        nightlife:'Nightlife 🎉', nature:'Nature 🌿'
    }[m] || m;
}
function getBudgetText(b) {
    return {
        backpacker:'Backpacker 🎒', budget:'Budget 💰', mid:'Mid-range 💵',
        premium:'Premium 💳', luxury:'Luxury 💎'
    }[b] || b;
}
function getDurationText(d) {
    return {
        weekend:'Long Weekend', week:'One Week', two_weeks:'Two Weeks',
        month:'One Month+', flexible:'Flexible'
    }[d] || d;
}
function getCompanionText(c) {
    return {
        solo:'Solo 🧍', couple:'Couple 👫', family_young:'Family (young kids) 👨‍👩‍👧',
        family_teens:'Family (teens) 👨‍👩‍👦', friends_small:'Small group 👯',
        friends_large:'Large group 🎊', business:'Business 💼'
    }[c] || c;
}
function getRegionText(r) {
    return {
        any:'Anywhere 🌍', europe:'Europe', southeast_asia:'Southeast Asia',
        east_asia:'East Asia', south_asia:'South Asia', middle_east:'Middle East',
        africa:'Africa', north_america:'North America', central_america:'Central America & Caribbean',
        south_america:'South America', oceania:'Oceania'
    }[r] || r;
}

function getVal(id) {
    const el = document.getElementById(id);
    return el ? el.value : '';
}

async function generateQuickPlan(e) {
    const mood          = getVal('moodSelect');
    const budget        = getVal('budgetSelect');
    const duration      = getVal('durationSelect');
    const companion     = getVal('companionSelect');
    const month         = getVal('monthSelect');
    const region        = getVal('regionSelect');
    const accommodation = getVal('accommodationSelect');
    const origin        = getVal('originInput');
    const experience    = getVal('experienceSelect');

    const button       = e.currentTarget;
    const originalHTML = button.innerHTML;
    button.innerHTML   = '<i class="fas fa-spinner fa-spin"></i> Consulting AI...';
    button.disabled    = true;

    const modal = document.createElement('div');
    modal.id = 'aiSuggestionModal';
    modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.82);display:flex;justify-content:center;align-items:center;z-index:9999;';

    const modalContent = document.createElement('div');
    modalContent.style.cssText = 'background:var(--card-bg);padding:40px;border-radius:10px;max-width:660px;width:90%;max-height:88vh;overflow-y:auto;position:relative;border:2px solid var(--gold);box-shadow:0 20px 60px rgba(59,31,43,0.35);';

    modalContent.innerHTML = `
        <h2 style="color:var(--deep);margin-top:0;font-weight:normal;letter-spacing:1px;">
            <i class="fas fa-compass" style="color:var(--gold);margin-right:10px;"></i>Finding Your Perfect Trip…
        </h2>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px;">
            <span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:4px 12px;border-radius:20px;font-size:12px;border:1px solid var(--border);">${getMoodText(mood)}</span>
            <span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:4px 12px;border-radius:20px;font-size:12px;border:1px solid var(--border);">${getBudgetText(budget)}</span>
            <span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:4px 12px;border-radius:20px;font-size:12px;border:1px solid var(--border);">${getDurationText(duration)}</span>
            <span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:4px 12px;border-radius:20px;font-size:12px;border:1px solid var(--border);">${getCompanionText(companion)}</span>
            ${month ? `<span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:4px 12px;border-radius:20px;font-size:12px;border:1px solid var(--border);">📅 ${month.charAt(0).toUpperCase()+month.slice(1)}</span>` : ''}
            ${region && region !== 'any' ? `<span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:4px 12px;border-radius:20px;font-size:12px;border:1px solid var(--border);">📍 ${getRegionText(region)}</span>` : ''}
        </div>
        <div style="text-align:center;padding:40px 0;">
            <i class="fas fa-globe-americas fa-3x fa-spin" style="color:var(--gold);opacity:0.7;"></i>
            <p style="color:var(--text-muted);margin-top:20px;">Our AI is crafting personalised recommendations just for you…</p>
        </div>`;

    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    modal.addEventListener('click', (ev) => { if (ev.target === modal) modal.remove(); });

    try {
        const response = await fetch('/ai/suggest', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ mood, budget, duration, companion, month, region, accommodation, origin, experience }),
        });

        const json = await response.json();
        if (!response.ok || !json.success) throw new Error(json.message ?? 'Unknown error');

        const suggestions = Array.isArray(json.data) ? json.data : [json.data];
        const flags = ['🌏','🌍','🌎','🌐','🗺️'];

        const cards = suggestions.map((s, i) => {
            const activities = Array.isArray(s.top_activities) ? s.top_activities.join(', ') : s.top_activities;
            const slug = (s.destination||'').toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'');
            return `
            <div style="border:1px solid var(--border);border-radius:8px;overflow:hidden;margin-bottom:16px;">
                <div style="background:linear-gradient(135deg,var(--deep),var(--deep-alt));padding:18px 22px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <span style="color:var(--gold);font-size:11px;letter-spacing:2px;text-transform:uppercase;">Option ${i+1} ${flags[i]||'✈️'}</span>
                        ${s.country ? `<span style="background:rgba(201,169,110,0.2);color:var(--gold);padding:3px 10px;border-radius:20px;font-size:12px;">${s.country}</span>` : ''}
                    </div>
                    <h3 style="color:var(--text-light);margin:0 0 6px;font-size:19px;font-weight:normal;">${s.destination}</h3>
                    <p style="color:var(--text-sub);margin:0;font-size:13px;line-height:1.6;">${s.description}</p>
                </div>
                <div style="padding:14px 22px;background:var(--card-bg);">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:12px;">
                        <div>
                            <p style="color:var(--gold);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 2px;">Est. Cost</p>
                            <p style="color:var(--deep);margin:0;font-size:13px;font-weight:bold;">${s.estimated_cost}</p>
                        </div>
                        <div>
                            <p style="color:var(--gold);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 2px;">Best Time</p>
                            <p style="color:var(--deep);margin:0;font-size:13px;">${s.best_time_to_visit}</p>
                        </div>
                        <div>
                            <p style="color:var(--gold);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 2px;">Visa</p>
                            <p style="color:var(--deep);margin:0;font-size:13px;">${s.visa_info || 'Check embassy'}</p>
                        </div>
                    </div>
                    <p style="color:var(--gold);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 4px;">Top Activities</p>
                    <p style="color:var(--text-muted);margin:0 0 10px;font-size:13px;">${activities}</p>
                    ${s.flight_info ? `
                    <div style="background:rgba(201,169,110,0.08);border-radius:4px;padding:8px 12px;margin-bottom:10px;border:1px solid rgba(201,169,110,0.2);">
                        <p style="color:var(--gold);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 2px;">✈️ Flight Info from ${origin.replace('_',' ')}</p>
                        <p style="color:var(--text-muted);margin:0;font-size:12px;">${s.flight_info}</p>
                    </div>` : ''}
                    <div style="border-left:2px solid var(--gold);padding-left:10px;margin-bottom:12px;">
                        <p style="color:var(--text-muted);margin:0;font-size:12px;font-style:italic;">💡 ${s.travel_tip}</p>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <a href="/flights?destination=${encodeURIComponent(slug)}&origin=${encodeURIComponent(origin)}&mood=${mood}&budget=${budget}"
                            class="primary-button"
                            style="flex:1;font-size:12px;padding:9px;background:var(--deep);color:var(--text-light);text-decoration:none;justify-content:center;">
                            <i class="fas fa-plane"></i> Search Flights
                        </a>
                        <a href="/plan-trip?destination=${encodeURIComponent(s.destination)}&mood=${mood}&budget=${budget}&duration=${duration}&companion=${companion}&month=${month}&accommodation=${accommodation}"
                            class="primary-button"
                            style="flex:1;font-size:12px;padding:9px;justify-content:center;text-decoration:none;">
                            <i class="fas fa-map"></i> Plan Trip
                        </a>
                    </div>
                </div>
            </div>`;
        }).join('');

        modalContent.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                <h2 style="color:var(--deep);margin:0;font-weight:normal;letter-spacing:1px;">
                    <i class="fas fa-globe" style="color:var(--gold);margin-right:10px;"></i>Your AI Travel Matches
                </h2>
                <button onclick="document.getElementById('aiSuggestionModal').remove()"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--text-muted);line-height:1;">&times;</button>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:20px;">
                <span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:3px 10px;border-radius:20px;font-size:11px;border:1px solid var(--border);">${getMoodText(mood)}</span>
                <span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:3px 10px;border-radius:20px;font-size:11px;border:1px solid var(--border);">${getBudgetText(budget)}</span>
                <span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:3px 10px;border-radius:20px;font-size:11px;border:1px solid var(--border);">${getDurationText(duration)}</span>
                <span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:3px 10px;border-radius:20px;font-size:11px;border:1px solid var(--border);">${getCompanionText(companion)}</span>
                ${month ? `<span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:3px 10px;border-radius:20px;font-size:11px;border:1px solid var(--border);">📅 ${month.charAt(0).toUpperCase()+month.slice(1)}</span>` : ''}
            </div>
            ${cards}
            <button class="primary-button"
                onclick="document.getElementById('aiSuggestionModal').remove()"
                style="width:100%;background:var(--card-bg);color:var(--deep);border:1px solid var(--border);margin-top:4px;">
                Close
            </button>`;

    } catch (err) {
        modalContent.innerHTML = `
            <h2 style="color:var(--deep);margin-top:0;">Something went wrong</h2>
            <p style="color:var(--text-muted);">${err.message || 'Unable to fetch suggestions right now. Please try again.'}</p>
            <button class="primary-button" onclick="document.getElementById('aiSuggestionModal').remove()">Close</button>`;
    } finally {
        button.innerHTML = originalHTML;
        button.disabled  = false;
    }
}

function subscribeNewsletter() {
    const emailInput = document.querySelector('.newsletter-input input');
    if (!emailInput) return;
    const email = emailInput.value;
    if (!email || !email.includes('@')) { alert('Please enter a valid email address'); return; }

    const btn = document.querySelector('.newsletter-input button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Subscribed!';
    btn.style.background = '#6b8f6b';
    btn.disabled = true;

    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.style.background = '';
        btn.disabled = false;
        emailInput.value = '';
        const msg = document.createElement('div');
        msg.textContent = 'Thank you for subscribing! Check your email for confirmation.';
        msg.style.cssText = 'position:fixed;bottom:20px;right:20px;background:var(--deep);color:var(--text-light);padding:15px 25px;border-radius:5px;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,0.2);';
        document.body.appendChild(msg);
        setTimeout(() => msg.remove(), 3000);
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function () {

    const generateBtn = document.getElementById('generatePlanBtn');
    if (generateBtn) generateBtn.addEventListener('click', generateQuickPlan);

    document.querySelectorAll('.qb-next-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const nextStep = parseInt(this.getAttribute('data-next'));
            showStep(nextStep);
        });
    });

    document.querySelectorAll('.qb-back-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const backStep = parseInt(this.getAttribute('data-back'));
            showStep(backStep);
        });
    });

    function showStep(step) {
        [1, 2, 3].forEach(n => {
            const panel = document.getElementById(`qbPanel${n}`);
            const stepEl = document.querySelector(`.qb-step[data-step="${n}"]`);
            const lines = document.querySelectorAll('.qb-step-line');
            if (panel) panel.style.display = n === step ? 'block' : 'none';
            if (stepEl) {
                stepEl.classList.toggle('active', n === step);
                stepEl.classList.toggle('done', n < step);
            }
            if (lines[n - 1]) lines[n - 1].classList.toggle('done', n < step);
        });
    }

    const nextBtn = document.querySelector('.next-btn');
    const prevBtn = document.querySelector('.prev-btn');
    const slideshowContainer = document.querySelector('.slideshow-container');
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    const slideNumber = document.querySelector('.slide-number');
    const totalSlides = slides.length;

    if (totalSlides > 0 && nextBtn && prevBtn && slideshowContainer) {
        let currentSlide = 0;
        let nextSlideIndex = 0;
        let isTransitioning = false;
        let slideInterval;
        let slideshowDirection = 1;

        function updateSlide(immediate = false) {
            if (isTransitioning) return;
            isTransitioning = true;
            slides.forEach(s => s.classList.remove('active', 'exiting'));
            if (!immediate && slides[currentSlide]) slides[currentSlide].classList.add('exiting');
            indicators.forEach(ind => ind.classList.remove('active'));
            setTimeout(() => {
                if (slides[currentSlide]) slides[currentSlide].classList.remove('exiting');
                slides[nextSlideIndex].classList.add('active');
                if (indicators[nextSlideIndex]) indicators[nextSlideIndex].classList.add('active');
                if (slideNumber) slideNumber.textContent = `${nextSlideIndex + 1} / ${totalSlides}`;
                currentSlide = nextSlideIndex;
                setTimeout(() => { isTransitioning = false; }, 1200);
            }, immediate ? 0 : 300);
        }

        function nextSlide() {
            if (isTransitioning) return;
            slideshowDirection = 1;
            nextSlideIndex = (currentSlide + 1) % totalSlides;
            updateSlide();
        }

        function prevSlide() {
            if (isTransitioning) return;
            slideshowDirection = -1;
            nextSlideIndex = (currentSlide - 1 + totalSlides) % totalSlides;
            updateSlide();
        }

        function startAutoSlide() {
            clearInterval(slideInterval);
            slideInterval = setInterval(() => {
                slideshowDirection === 1 ? nextSlide() : prevSlide();
                if (Math.random() < 0.1) slideshowDirection *= -1;
            }, 6000);
        }

        function stopAutoSlide() { clearInterval(slideInterval); }

        function goToSlide(index) {
            if (isTransitioning || index === currentSlide) return;
            slideshowDirection = index > currentSlide ? 1 : -1;
            nextSlideIndex = index;
            updateSlide();
        }

        nextBtn.addEventListener('click', () => { slideshowDirection = 1; nextSlide(); startAutoSlide(); });
        prevBtn.addEventListener('click', () => { slideshowDirection = -1; prevSlide(); startAutoSlide(); });

        indicators.forEach(indicator => {
            indicator.addEventListener('click', function () {
                goToSlide(parseInt(this.getAttribute('data-slide')));
                startAutoSlide();
            });
        });

        slideshowContainer.addEventListener('mouseenter', stopAutoSlide);
        slideshowContainer.addEventListener('mouseleave', startAutoSlide);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft')  { prevSlide(); startAutoSlide(); }
            if (e.key === 'ArrowRight') { nextSlide(); startAutoSlide(); }
        });

        updateSlide(true);
        startAutoSlide();
    }

    document.querySelectorAll('.filter-tag').forEach(tag => {
        tag.addEventListener('click', function () {
            document.querySelectorAll('.filter-tag').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const filter = this.textContent.trim().toLowerCase();
            const cards = document.querySelectorAll('.destination-card');
            cards.forEach(card => { card.style.opacity = '0.5'; card.style.transform = 'scale(0.95)'; });
            setTimeout(() => {
                cards.forEach(card => {
                    const title = card.querySelector('h3')?.textContent.toLowerCase() ?? '';
                    const mood  = card.querySelector('.mood-indicator')?.textContent.toLowerCase() ?? '';
                    const price = card.querySelector('.price-tag')?.textContent.toLowerCase() ?? '';
                    const ok = filter === 'all' || title.includes(filter) || mood.includes(filter) || price.includes(filter);
                    card.style.display   = ok ? 'flex' : 'none';
                    card.style.opacity   = ok ? '1' : '0.5';
                    card.style.transform = ok ? 'scale(1)' : 'scale(0.95)';
                });
            }, 300);
        });
    });

    document.querySelectorAll('.destination-card').forEach(card => {
        card.addEventListener('mouseenter', function () { this.style.transform='translateY(-5px) scale(1.02)'; this.style.boxShadow='0 8px 22px rgba(59,31,43,0.15)'; });
        card.addEventListener('mouseleave', function () { this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 3px 10px rgba(59,31,43,0.08)'; });
    });

    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('mouseenter', function () { this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 22px rgba(59,31,43,0.15)'; });
        card.addEventListener('mouseleave', function () { this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 10px rgba(59,31,43,0.08)'; });
    });

    document.querySelectorAll('.tile').forEach(tile => {
        tile.addEventListener('mouseenter', function () { this.style.transform='translateY(-5px)'; this.style.boxShadow='0 6px 18px rgba(59,31,43,0.13)'; });
        tile.addEventListener('mouseleave', function () { this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 10px rgba(59,31,43,0.08)'; });
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                document.querySelectorAll('.stat-number').forEach((stat, i) => {
                    setTimeout(() => {
                        stat.style.transform = 'scale(1.1)';
                        setTimeout(() => { stat.style.transform = 'scale(1)'; }, 300);
                    }, i * 200);
                });
            }
        });
    }, { threshold: 0.5 });

    const aiBanner = document.querySelector('.ai-features-banner');
    if (aiBanner) observer.observe(aiBanner);

    document.querySelectorAll('.testimonial-card').forEach(card => {
        card.addEventListener('click', function () {
            this.style.transform = 'scale(1.02)';
            this.style.boxShadow = '0 8px 25px rgba(59,31,43,0.15)';
            setTimeout(() => { this.style.transform='scale(1)'; this.style.boxShadow='0 3px 10px rgba(59,31,43,0.08)'; }, 200);
        });
    });

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (ev) {
            ev.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            const target = document.querySelector(targetId);
            if (target) window.scrollTo({ top: target.offsetTop - 80, behavior: 'smooth' });
        });
    });

    const footerP = document.querySelector('.footer p');
    if (footerP) footerP.innerHTML = footerP.innerHTML.replace('2026', new Date().getFullYear());
});

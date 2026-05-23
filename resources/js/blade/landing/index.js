function ready(fn) {
    if (document.readyState !== 'loading') { fn(); }
    else { document.addEventListener('DOMContentLoaded', fn); }
}

function getVal(id) {
    const el = document.getElementById(id);
    return el ? el.value : '';
}

function getMoodText(m) {
    return { adventurous:'Adventurous 🏔️', relaxed:'Relaxed 🌴', cultural:'Cultural 🏛️', romantic:'Romantic 💖', foodie:'Foodie 🍽️', wellness:'Wellness 🧘', nightlife:'Nightlife 🎉', nature:'Nature 🌿' }[m] || m;
}
function getBudgetText(b) {
    return { backpacker:'Backpacker 🎒', budget:'Budget 💰', mid:'Mid-range 💵', premium:'Premium 💳', luxury:'Luxury 💎' }[b] || b;
}
function getDurationText(d) {
    return { weekend:'Long Weekend', week:'One Week', two_weeks:'Two Weeks', month:'One Month+', flexible:'Flexible' }[d] || d;
}
function getCompanionText(c) {
    return { solo:'Solo 🧍', couple:'Couple 👫', family_young:'Family (young kids) 👨‍👩‍👧', family_teens:'Family (teens) 👨‍👩‍👦', friends_small:'Small group 👯', friends_large:'Large group 🎊', business:'Business 💼' }[c] || c;
}
function getRegionText(r) {
    return { any:'Anywhere 🌍', europe:'Europe', southeast_asia:'Southeast Asia', east_asia:'East Asia', south_asia:'South Asia', middle_east:'Middle East', africa:'Africa', north_america:'North America', central_america:'Central America & Caribbean', south_america:'South America', oceania:'Oceania' }[r] || r;
}

function formatRegion(region) {
    const map = { europe:'Europe', asia:'Asia', middle_east:'Middle East', africa:'Africa', america:'Americas', oceania:'Oceania', general:'World' };
    return map[region] || (region ? region.replace(/_/g,' ') : 'World');
}
function formatLabel(str) {
    if (!str) return '';
    return str.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase());
}
function moodIconMap(mood) {
    const icons = {
        adventurous:'<i class="fas fa-hiking"></i>', relaxed:'<i class="fas fa-umbrella-beach"></i>',
        cultural:'<i class="fas fa-landmark"></i>', romantic:'<i class="fas fa-heart"></i>',
        foodie:'<i class="fas fa-utensils"></i>', wellness:'<i class="fas fa-spa"></i>',
        eco:'<i class="fas fa-leaf"></i>', eco_tourism:'<i class="fas fa-leaf"></i>',
        nature:'<i class="fas fa-tree"></i>', general:'<i class="fas fa-globe"></i>',
        beach:'<i class="fas fa-umbrella-beach"></i>', mountain:'<i class="fas fa-mountain"></i>',
        nightlife:'<i class="fas fa-music"></i>', spiritual:'<i class="fas fa-place-of-worship"></i>',
        road_trip:'<i class="fas fa-car"></i>', city_break:'<i class="fas fa-city"></i>',
        safari:'<i class="fas fa-paw"></i>', cruise:'<i class="fas fa-ship"></i>',
        honeymoon:'<i class="fas fa-ring"></i>', photography:'<i class="fas fa-camera"></i>',
    };
    return icons[mood] || '<i class="fas fa-map-marker-alt"></i>';
}

window.toggleQuickBuilder = function () {
    const wrapper = document.getElementById('quickBuilderWrapper');
    if (!wrapper) return;
    const isOpen = wrapper.classList.toggle('open');
    const btn = document.querySelector('[data-action="toggleQuickBuilder"]');
    if (btn) btn.classList.toggle('active', isOpen);
    if (isOpen) {
        setTimeout(() => wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 50);
    }
};

window.showStep = function (step) {
    [1, 2, 3].forEach(n => {
        const panel  = document.getElementById('qbPanel' + n);
        const stepEl = document.querySelector('.qb-step[data-step="' + n + '"]');
        const lines  = document.querySelectorAll('.qb-step-line');
        if (panel)  panel.style.display = n === step ? 'block' : 'none';
        if (stepEl) {
            stepEl.classList.toggle('active', n === step);
            stepEl.classList.toggle('done', n < step);
            // Reveal step when it becomes reachable
            if (n <= step) {
                stepEl.classList.remove('qb-step-hidden');
                stepEl.classList.add('qb-step-visible');
            }
        }
        if (lines[n - 1]) {
            lines[n - 1].classList.toggle('done', n < step);
            // Reveal connecting line when step before it is reached
            if (n <= step) {
                lines[n - 1].classList.remove('qb-step-line-hidden');
                lines[n - 1].classList.add('qb-step-line-visible');
            }
        }
    });
};

window.applyDestinationFilter = function (filter, tagEl) {
    document.querySelectorAll('.filter-tag').forEach(t => t.classList.remove('active'));
    if (tagEl) tagEl.classList.add('active');

    const allDest = window._allDestinations || [];
    let results;
    if (filter === 'all') {
        results = allDest;
    } else if (filter === 'hidden_gem') {
        results = allDest.filter(d => Number(d.is_hidden_gem) === 1);
    } else if (filter === 'romantic') {
        results = allDest.filter(d => d.mood === 'romantic' || (d.badge && d.badge.toLowerCase().includes('romantic')));
    } else if (filter === 'adventurous') {
        results = allDest.filter(d => d.mood === 'adventurous' || d.category === 'adventurous');
    } else {
        results = allDest.filter(d => d.category === filter || d.mood === filter);
    }
    renderGrid(results.slice(0, 8));
};

function buildCard(d) {
    const image       = d.image_url || 'https://picsum.photos/seed/' + encodeURIComponent(d.name) + '/600/400';
    const priceUsd    = d.price_from ? Number(d.price_from) : 0;
    const price       = priceUsd > 0
        ? 'From <span data-price-usd="' + priceUsd + '">' + (typeof window.Currency !== 'undefined' ? window.Currency.format(priceUsd) : '$' + priceUsd.toLocaleString()) + '</span>'
        : '';
    const badge       = d.badge ? '<span class="destination-badge">' + d.badge + '</span>' : '';
    const hiddenGem   = Number(d.is_hidden_gem) === 1
        ? '<span class="destination-badge" style="background:rgba(138,43,226,0.85);"><i class="fas fa-gem"></i> Hidden Gem</span>' : '';
    const matchScore  = d.match_score
        ? '<div class="match-score"><i class="fas fa-star"></i> ' + d.match_score + '% match</div>' : '';
    const description = d.description
        ? (d.description.length > 110 ? d.description.substring(0, 110) + '…' : d.description) : '';

    return '<div class="destination-card" data-category="' + d.category + '" data-mood="' + d.mood + '">' +
        '<div class="destination-image" style="background-image:url(\'' + image + '\');background-size:cover;background-position:center;height:200px;position:relative;border-radius:6px 6px 0 0;overflow:hidden;">' +
            '<div style="position:absolute;inset:0;background:linear-gradient(to bottom,transparent 40%,rgba(0,0,0,0.6));"></div>' +
            '<div style="position:absolute;top:12px;left:12px;display:flex;gap:6px;flex-wrap:wrap;">' + badge + hiddenGem + '</div>' +
            (matchScore ? '<div style="position:absolute;top:12px;right:12px;">' + matchScore + '</div>' : '') +
            '<div style="position:absolute;bottom:12px;left:14px;right:14px;">' +
                '<h3 style="color:#fff;margin:0;font-size:17px;font-weight:700;text-shadow:0 1px 4px rgba(0,0,0,0.7);">' + d.name + '</h3>' +
                '<p style="color:rgba(255,255,255,0.85);margin:3px 0 0;font-size:13px;"><i class="fas fa-map-marker-alt" style="margin-right:4px;"></i>' + d.country + ' &nbsp;·&nbsp; ' + formatRegion(d.region) + '</p>' +
            '</div>' +
        '</div>' +
        '<div class="destination-info" style="padding:16px;display:flex;flex-direction:column;flex:1;">' +
            '<p style="color:var(--text-muted);font-size:13px;margin:0 0 12px;line-height:1.5;">' + description + '</p>' +
            '<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:12px;">' +
                '<span style="font-size:12px;color:var(--text-muted);background:rgba(201,169,110,0.12);border:1px solid var(--border);border-radius:20px;padding:3px 10px;">' + moodIconMap(d.mood) + ' ' + formatLabel(d.mood) + '</span>' +
                (price ? '<div style="font-size:15px;font-weight:700;color:var(--deep);">' + price + '</div>' : '') +
            '</div>' +
            '<a href="/plan-trip" class="primary-button" style="text-decoration:none;padding:9px;font-size:13px;margin-top:auto;">' +
                '<i class="fas fa-route"></i> Plan trip' +
            '</a>' +
        '</div>' +
    '</div>';
}

function renderGrid(destinations) {
    const grid    = document.getElementById('destinationsGrid');
    const loading = document.getElementById('destinationsLoading');
    const empty   = document.getElementById('destinationsEmpty');
    if (!grid) return;
    if (loading) loading.style.display = 'none';
    if (!destinations || destinations.length === 0) {
        grid.style.display  = 'none';
        if (empty) empty.style.display = 'block';
        return;
    }
    if (empty) empty.style.display = 'none';
    grid.style.display = 'grid';
    grid.innerHTML     = destinations.map(d => buildCard(d)).join('');
}

function getStaticLandingDestinations() {
    return [
        { id: 1, name: 'Lisbon', country: 'Portugal', region: 'europe', mood: 'cultural', category: 'general', price_from: 89, description: 'Historic neighborhoods, river views, and Atlantic breezes.', badge: 'Editor pick', image_url: 'https://images.unsplash.com/photo-1585208798174-6cedd86e019a?w=800&q=80' },
        { id: 2, name: 'Kyoto', country: 'Japan', region: 'east_asia', mood: 'cultural', category: 'historical', price_from: 120, description: 'Temples, gardens, and timeless traditions.', badge: null, image_url: 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?w=800&q=80' },
        { id: 3, name: 'Cape Town', country: 'South Africa', region: 'africa', mood: 'adventurous', category: 'mountain', price_from: 95, description: 'Coastline, mountains, and vibrant culture.', badge: 'Adventure', image_url: 'https://images.unsplash.com/photo-1580060839134-75a5dca7b532?w=800&q=80' },
        { id: 4, name: 'Barcelona', country: 'Spain', region: 'europe', mood: 'foodie', category: 'food_culture', price_from: 102, description: 'Architecture, markets, and Mediterranean flavor.', badge: null, image_url: 'https://images.unsplash.com/photo-1583422409516-2895a77efded?w=800&q=80' },
        { id: 5, name: 'Reykjavik', country: 'Iceland', region: 'europe', mood: 'nature', category: 'general', price_from: 140, description: 'Northern lights, hot springs, and dramatic landscapes.', badge: 'Nature', image_url: 'https://images.unsplash.com/photo-1504893524553-b855bce32c67?w=800&q=80' },
        { id: 6, name: 'Mexico City', country: 'Mexico', region: 'north_america', mood: 'foodie', category: 'food_culture', price_from: 78, description: 'World-class cuisine and buzzing neighborhoods.', badge: null, image_url: 'https://images.unsplash.com/photo-1518659526055-ea5caba8c9ae?w=800&q=80' },
    ];
}

async function fetchDestinations() {
    const grid    = document.getElementById('destinationsGrid');
    const loading = document.getElementById('destinationsLoading');
    const empty   = document.getElementById('destinationsEmpty');
    if (!grid) return;

    if (loading) loading.style.display = 'block';
    if (grid)    grid.style.display    = 'none';
    if (empty)   empty.style.display   = 'none';

    try {
        const res  = await fetch('/api/landing/destinations', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        const all  = Array.isArray(data) ? data : (data.data || data.destinations || []);
        if (!all.length) {
            window._allDestinations = getStaticLandingDestinations();
            renderGrid(window._allDestinations.slice(0, 8));
            if (loading) loading.style.display = 'none';
            return;
        }
        window._allDestinations = all;
        renderGrid(all.slice(0, 8));
    } catch (err) {
        window._allDestinations = getStaticLandingDestinations();
        renderGrid(window._allDestinations.slice(0, 8));
        if (loading) loading.style.display = 'none';
    }
}

window.initDestinations = fetchDestinations;

function populateTestimonials() {
    const grid = document.getElementById('testimonialsGrid');
    if (!grid) return;
    const testimonials = [
        { text: "The AI suggestions were spot on — found a destination I'd never considered and it turned out to be my best trip ever.", name: "Sarah M.", initials: "SM", trip: "Bali, Indonesia" },
        { text: "Planned our honeymoon in under 20 minutes. Having budget tracking and flight search in one place is genuinely useful.", name: "James & Priya", initials: "JP", trip: "Santorini, Greece" },
        { text: "Travelled solo for the first time. The mood-based suggestions gave me exactly the kind of off-the-beaten-path trip I wanted.", name: "Marcus L.", initials: "ML", trip: "Kyoto, Japan" },
        { text: "Used it for a group of 8. The filters made it easy to find somewhere that worked for everyone's travel style.", name: "Aisha K.", initials: "AK", trip: "Marrakech, Morocco" },
        { text: "The visa and flight info in the AI suggestions saved me hours of research. Everything I needed was already there.", name: "Tom H.", initials: "TH", trip: "Cape Town, South Africa" },
        { text: "Booked three trips through this. The AI cost estimates have been surprisingly accurate every single time.", name: "Yuki T.", initials: "YT", trip: "Reykjavik, Iceland" },
    ];
    grid.innerHTML = testimonials.map(t =>
        '<div class="testimonial-card">' +
            '<p>"' + t.text + '"</p>' +
            '<div class="user-info">' +
                '<div class="user-avatar">' + t.initials + '</div>' +
                '<div>' +
                    '<div class="user-name">' + t.name + '</div>' +
                    '<div class="user-trip"><i class="fas fa-map-marker-alt" style="color:var(--gold);margin-right:4px;font-size:11px;"></i>' + t.trip + '</div>' +
                '</div>' +
            '</div>' +
        '</div>'
    ).join('');
}

window.filterByStyle = function (style, cardEl) {
    const styleMap = {
        adventure: { moods: ['adventurous'],      categories: ['adventurous', 'mountain'],               label: 'Adventure Travel' },
        beach:     { moods: ['relaxed', 'beach'], categories: ['beach'],                                 label: 'Beach & Relaxation' },
        cultural:  { moods: ['cultural'],         categories: ['historical', 'food_culture', 'general'], label: 'Cultural Immersion' },
        food:      { moods: ['foodie'],           categories: ['food_culture'],                          label: 'Culinary Tours' },
    };

    document.querySelectorAll('.category-card').forEach(c => c.classList.remove('active-style'));
    if (cardEl) cardEl.classList.add('active-style');

    const mapping    = styleMap[style];
    const styleGrid  = document.getElementById('styleDestinationsGrid');
    const styleEmpty = document.getElementById('styleEmpty');
    const styleHeader = document.getElementById('styleResultsHeader');
    const styleTitle  = document.getElementById('styleResultsTitle');
    const styleCount  = document.getElementById('styleResultsCount');
    const styleViewAll = document.getElementById('styleViewAll');

    if (!mapping || !window._allDestinations || !window._allDestinations.length) return;

    const results = window._allDestinations.filter(d =>
        mapping.moods.includes(d.mood) || mapping.categories.includes(d.category)
    ).slice(0, 8);

    if (styleHeader)  styleHeader.style.display  = 'block';
    if (styleTitle)   styleTitle.textContent      = mapping.label;
    if (styleCount)   styleCount.textContent      = results.length + ' destinations';
    if (styleViewAll) styleViewAll.style.display  = 'block';

    if (!results.length) {
        if (styleGrid)  styleGrid.style.display  = 'none';
        if (styleEmpty) styleEmpty.style.display = 'block';
        return;
    }
    if (styleEmpty) styleEmpty.style.display = 'none';
    if (styleGrid) {
        styleGrid.style.display = 'grid';
        styleGrid.innerHTML = results.map(d => buildCard(d)).join('');
        styleGrid.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
};

window.generateQuickPlan = async function (e) {
    const btn = e && (e.currentTarget || e.target);
    const mood          = getVal('moodSelect');
    const budget        = getVal('budgetSelect');
    const duration      = getVal('durationSelect');
    const companion     = getVal('companionSelect');
    const month         = getVal('monthSelect');
    const region        = getVal('regionSelect');
    const accommodation = getVal('accommodationSelect');
    const origin        = getVal('originInput');
    const experience    = getVal('experienceSelect');

    const originalHTML = btn ? btn.innerHTML : '';
    if (btn) { btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Consulting AI…'; btn.disabled = true; }

    const modal = document.createElement('div');
    modal.id = 'aiSuggestionModal';
    modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.82);display:flex;justify-content:center;align-items:center;z-index:9999;padding:20px;';
    const box = document.createElement('div');
    box.style.cssText = 'background:var(--card-bg);padding:40px;border-radius:10px;max-width:660px;width:100%;max-height:88vh;overflow-y:auto;position:relative;border:2px solid var(--gold);box-shadow:0 20px 60px rgba(59,31,43,0.35);';
    box.innerHTML = '<h2 style="color:var(--deep);margin-top:0;font-weight:normal;"><i class="fas fa-compass" style="color:var(--gold);margin-right:10px;"></i>Finding Your Perfect Trip…</h2>' +
        '<div style="text-align:center;padding:40px 0;"><i class="fas fa-globe-americas fa-3x fa-spin" style="color:var(--gold);opacity:0.7;"></i>' +
        '<p style="color:var(--text-muted);margin-top:20px;">Our AI is crafting personalised recommendations just for you…</p></div>';
    modal.appendChild(box);
    document.body.appendChild(modal);
    modal.addEventListener('click', ev => { if (ev.target === modal) modal.remove(); });

    try {
        const res  = await fetch('/ai/suggest', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ mood, budget, duration, companion, month, region, accommodation, origin, experience }),
        });
        const json = await res.json();
        if (!res.ok || !json.success) throw new Error(json.message ?? 'Unknown error');

        const suggestions = Array.isArray(json.data) ? json.data : [json.data];
        const flags = ['🌏','🌍','🌎','🌐','🗺️'];

        const cards = suggestions.map((s, i) => {
            const activities = Array.isArray(s.top_activities) ? s.top_activities.join(', ') : s.top_activities;
            const slug = (s.destination || '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            return '<div style="border:1px solid var(--border);border-radius:8px;overflow:hidden;margin-bottom:16px;">' +
                '<div style="background:linear-gradient(135deg,var(--deep),var(--deep-alt));padding:18px 22px;">' +
                    '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">' +
                        '<span style="color:var(--gold);font-size:11px;letter-spacing:2px;text-transform:uppercase;">Option ' + (i+1) + ' ' + (flags[i]||'✈️') + '</span>' +
                        (s.country ? '<span style="background:rgba(201,169,110,0.2);color:var(--gold);padding:3px 10px;border-radius:20px;font-size:12px;">' + s.country + '</span>' : '') +
                    '</div>' +
                    '<h3 style="color:var(--text-light);margin:0 0 6px;font-size:19px;font-weight:normal;">' + s.destination + '</h3>' +
                    '<p style="color:var(--text-sub);margin:0;font-size:13px;line-height:1.6;">' + s.description + '</p>' +
                '</div>' +
                '<div style="padding:14px 22px;background:var(--card-bg);">' +
                    '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:12px;">' +
                        '<div><p style="color:var(--gold);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 2px;">Est. Cost</p><p style="color:var(--deep);margin:0;font-size:13px;font-weight:bold;">' + s.estimated_cost + '</p></div>' +
                        '<div><p style="color:var(--gold);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 2px;">Best Time</p><p style="color:var(--deep);margin:0;font-size:13px;">' + s.best_time_to_visit + '</p></div>' +
                        '<div><p style="color:var(--gold);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 2px;">Visa</p><p style="color:var(--deep);margin:0;font-size:13px;">' + (s.visa_info || 'Check embassy') + '</p></div>' +
                    '</div>' +
                    '<p style="color:var(--gold);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 4px;">Top Activities</p>' +
                    '<p style="color:var(--text-muted);margin:0 0 10px;font-size:13px;">' + activities + '</p>' +
                    (s.travel_tip ? '<div style="border-left:2px solid var(--gold);padding-left:10px;margin-bottom:12px;"><p style="color:var(--text-muted);margin:0;font-size:12px;font-style:italic;">💡 ' + s.travel_tip + '</p></div>' : '') +
                    '<div style="display:flex;gap:8px;">' +
                        '<a href="/flights?destination=' + encodeURIComponent(slug) + '&origin=' + encodeURIComponent(origin) + '&mood=' + mood + '&budget=' + budget + '" class="primary-button" style="flex:1;font-size:12px;padding:9px;background:var(--deep);color:var(--text-light);text-decoration:none;justify-content:center;"><i class="fas fa-plane"></i> Search Flights</a>' +
                        '<a href="/plan-trip?destination=' + encodeURIComponent(s.destination) + '&mood=' + mood + '&budget=' + budget + '" class="primary-button" style="flex:1;font-size:12px;padding:9px;justify-content:center;text-decoration:none;"><i class="fas fa-map"></i> Plan Trip</a>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');

        box.innerHTML = '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;">' +
            '<h2 style="color:var(--deep);margin:0;font-weight:normal;"><i class="fas fa-globe" style="color:var(--gold);margin-right:10px;"></i>Your AI Travel Matches</h2>' +
            '<button onclick="document.getElementById(\'aiSuggestionModal\').remove()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--text-muted);line-height:1;">&times;</button>' +
            '</div>' + cards +
            '<button class="primary-button" onclick="document.getElementById(\'aiSuggestionModal\').remove()" style="width:100%;background:var(--card-bg);color:var(--deep);border:1px solid var(--border);margin-top:4px;">Close</button>';

    } catch (err) {
        box.innerHTML = '<h2 style="color:var(--deep);margin-top:0;">Something went wrong</h2>' +
            '<p style="color:var(--text-muted);">' + (err.message || 'Unable to fetch suggestions. Please try again.') + '</p>' +
            '<button class="primary-button" onclick="document.getElementById(\'aiSuggestionModal\').remove()">Close</button>';
    } finally {
        if (btn) { btn.innerHTML = originalHTML; btn.disabled = false; }
    }
};

window.subscribeNewsletter = function () {
    const emailInput = document.querySelector('.newsletter-input input');
    if (!emailInput) return;
    const email = emailInput.value.trim();
    if (!email || !email.includes('@')) { alert('Please enter a valid email address'); return; }
    const btn = document.querySelector('.newsletter-input button');
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Subscribed!';
    btn.disabled  = true;
    setTimeout(() => {
        btn.innerHTML = orig; btn.disabled = false; emailInput.value = '';
        const msg = document.createElement('div');
        msg.textContent = 'Thank you for subscribing!';
        msg.style.cssText = 'position:fixed;bottom:20px;right:20px;background:var(--deep);color:var(--text-light);padding:15px 25px;border-radius:5px;z-index:1000;';
        document.body.appendChild(msg);
        setTimeout(() => msg.remove(), 3000);
    }, 2000);
};

ready(function () {
    window.showStep(1);

    
    const wrapper  = document.getElementById('moodSelectWrapper');
    const trigger  = document.getElementById('moodSelectTrigger');
    const dropdown = document.getElementById('moodDropdown');
    const hidden   = document.getElementById('moodSelect');

    if (wrapper && trigger && dropdown && hidden) {
        const iconEl = trigger.querySelector('.custom-select-icon');
        const textEl = trigger.querySelector('.custom-select-text');
        trigger.addEventListener('click', e => { e.stopPropagation(); wrapper.classList.toggle('open'); });
        dropdown.querySelectorAll('.custom-select-option').forEach(opt => {
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
        document.addEventListener('click', () => wrapper.classList.remove('open'));
    }

    
    const filterContainer = document.querySelector('.filter-tags');
    if (filterContainer) {
        filterContainer.addEventListener('click', function (e) {
            const tag = e.target.closest('.filter-tag');
            if (!tag) return;
            window.applyDestinationFilter(tag.dataset.filter, tag);
        });
    }

    
    const generateBtn = document.getElementById('generatePlanBtn');
    if (generateBtn) {
        generateBtn.addEventListener('click', function (e) {
            window.generateQuickPlan(e);
        });
    }

    
    document.addEventListener('click', function (e) {
        const nextBtn = e.target.closest('.qb-next-btn');
        const backBtn = e.target.closest('.qb-back-btn');
        if (nextBtn) window.showStep(parseInt(nextBtn.getAttribute('data-next')));
        if (backBtn) window.showStep(parseInt(backBtn.getAttribute('data-back')));
    });

    
    const nextBtn            = document.querySelector('.next-btn');
    const prevBtn            = document.querySelector('.prev-btn');
    const slideshowContainer = document.querySelector('.slideshow-container');
    const slides             = document.querySelectorAll('.slide');
    const indicators         = document.querySelectorAll('.indicator');
    const slideNumber        = document.querySelector('.slide-number');
    const totalSlides        = slides.length;

    if (totalSlides > 0 && nextBtn && prevBtn) {
        let currentSlide    = 0;
        let nextSlideIndex  = 0;
        let isTransitioning = false;
        let slideInterval;

        function updateSlide(immediate) {
            if (isTransitioning) return;
            isTransitioning = true;
            slides.forEach(s => s.classList.remove('active', 'exiting'));
            if (!immediate && slides[currentSlide]) slides[currentSlide].classList.add('exiting');
            indicators.forEach(ind => ind.classList.remove('active'));
            setTimeout(() => {
                if (slides[currentSlide]) slides[currentSlide].classList.remove('exiting');
                slides[nextSlideIndex].classList.add('active');
                if (indicators[nextSlideIndex]) indicators[nextSlideIndex].classList.add('active');
                if (slideNumber) slideNumber.textContent = (nextSlideIndex + 1) + ' / ' + totalSlides;
                currentSlide = nextSlideIndex;
                setTimeout(() => { isTransitioning = false; }, 1200);
            }, immediate ? 0 : 300);
        }

        function nextSlide() {
            if (isTransitioning) return;
            nextSlideIndex = (currentSlide + 1) % totalSlides;
            updateSlide(false);
        }
        function prevSlide() {
            if (isTransitioning) return;
            nextSlideIndex = (currentSlide - 1 + totalSlides) % totalSlides;
            updateSlide(false);
        }
        function startAuto() {
            clearInterval(slideInterval);
            slideInterval = setInterval(nextSlide, 6000);
        }

        nextBtn.addEventListener('click', () => { nextSlide(); startAuto(); });
        prevBtn.addEventListener('click', () => { prevSlide(); startAuto(); });

        indicators.forEach(ind => {
            ind.addEventListener('click', function () {
                const idx = parseInt(this.getAttribute('data-slide'));
                if (!isTransitioning && idx !== currentSlide) {
                    nextSlideIndex = idx;
                    updateSlide(false);
                }
                startAuto();
            });
        });

        if (slideshowContainer) {
            slideshowContainer.addEventListener('mouseenter', () => clearInterval(slideInterval));
            slideshowContainer.addEventListener('mouseleave', startAuto);
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'ArrowLeft')  { prevSlide(); startAuto(); }
            if (e.key === 'ArrowRight') { nextSlide(); startAuto(); }
        });

        updateSlide(true);
        startAuto();
    }


    fetchDestinations();
    populateTestimonials();

    // Re-render destination cards when currency changes
    if (typeof window.Currency !== 'undefined') {
        window.Currency.onCurrencyChange(function() {
            window.Currency.refreshAllPrices();
            updateLandingBudgetDropdown();
        });
        updateLandingBudgetDropdown();
    }
});

function updateLandingBudgetDropdown() {
    var sel = document.getElementById('budgetSelect');
    if (!sel) return;
    var fmt = typeof window.Currency !== 'undefined' ? window.Currency.format : function(n) { return '$' + n.toLocaleString(); };
    var currentVal = sel.value;
    var opts = [
        { value: 'backpacker', text: 'Backpacker — under ' + fmt(500) },
        { value: 'budget',     text: 'Budget — ' + fmt(500) + ' – ' + fmt(1500) },
        { value: 'mid',        text: 'Mid-range — ' + fmt(1500) + ' – ' + fmt(4000) },
        { value: 'premium',    text: 'Premium — ' + fmt(4000) + ' – ' + fmt(8000) },
        { value: 'luxury',     text: 'Luxury — ' + fmt(8000) + '+' },
    ];
    opts.forEach(function(o) {
        var el = sel.querySelector('option[value="' + o.value + '"]');
        if (el) el.textContent = o.text;
    });
    sel.value = currentVal;
}
document.addEventListener('currency:changed', function() { if (window._allDestinations) { renderGrid(window._allDestinations.slice(0, 8)); } });
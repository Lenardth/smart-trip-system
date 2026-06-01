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
    return { backpacker:'Backpacker 🎒', budget:'Budget 💰', mid:'Mid range 💵', premium:'Premium 💳', luxury:'Luxury 💎' }[b] || b;
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

function esc(value) {
    return String(value ?? '').replace(/[&<>"']/g, ch => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
    }[ch]));
}

function uniqueByCountry(destinations) {
    const seen = new Set();
    return (destinations || []).filter(d => {
        const key = String(d.country_code || d.country || d.region || d.name || '').trim().toLowerCase();
        if (!key || seen.has(key)) return false;
        seen.add(key);
        return true;
    });
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
        ? '<span class="destination-badge destination-badge-hidden"><i class="fas fa-gem"></i> Hidden Gem</span>' : '';
    const matchScore  = d.match_score
        ? '<div class="match-score"><i class="fas fa-star"></i> ' + d.match_score + '% match</div>' : '';
    const description = d.description
        ? (d.description.length > 110 ? d.description.substring(0, 110) + '…' : d.description) : '';

    const planUrl = '/plan-trip?destination=' + encodeURIComponent(d.name || '') +
        '&country=' + encodeURIComponent(d.country || '') +
        '&mood=' + encodeURIComponent(d.mood || getVal('moodSelect')) +
        '&budget=' + encodeURIComponent(getVal('budgetSelect') || 'mid') +
        '&region=' + encodeURIComponent(d.region || getVal('regionSelect')) +
        '&accommodation=' + encodeURIComponent(getVal('accommodationSelect') || 'any');

    return '<div class="destination-card" data-category="' + esc(d.category) + '" data-mood="' + esc(d.mood) + '">' +
        '<div class="destination-image destination-image-overlay" data-image-url="' + esc(image) + '">' +
            '<div class="destination-card-badges">' + badge + hiddenGem + '</div>' +
            (matchScore ? '<div class="destination-card-score">' + matchScore + '</div>' : '') +
            '<div class="destination-card-title">' +
                '<h3>' + esc(d.name) + '</h3>' +
                '<p><i class="fas fa-map-marker-alt"></i>' + esc(d.country) + ' &nbsp;·&nbsp; ' + esc(formatRegion(d.region)) + '</p>' +
            '</div>' +
        '</div>' +
        '<div class="destination-info">' +
            '<p class="destination-description">' + esc(description) + '</p>' +
            '<div class="destination-card-meta">' +
                '<span class="destination-mood-pill">' + moodIconMap(d.mood) + ' ' + esc(formatLabel(d.mood)) + '</span>' +
                (price ? '<div class="destination-price">' + price + '</div>' : '') +
            '</div>' +
            '<a href="' + planUrl + '" class="primary-button destination-plan-link">' +
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
    grid.querySelectorAll('.destination-image[data-image-url]').forEach(el => {
        el.style.backgroundImage = "url('" + el.dataset.imageUrl + "')";
    });
}

function renderFeaturedSlides(destinations) {
    const slidesWrap = document.getElementById('featuredSlides');
    const indicatorsWrap = document.querySelector('.slide-indicators');
    const slideNumber = document.querySelector('.slide-number');

    if (!slidesWrap || !indicatorsWrap) return;

    const featured = (destinations || []).filter(d => d.image_url).slice(0, 8);

    if (!featured.length) {
        slidesWrap.innerHTML = '';
        indicatorsWrap.innerHTML = '';
        if (slideNumber) slideNumber.textContent = '0 / 0';
        return;
    }

    slidesWrap.innerHTML = featured.map((d, index) => {
        const description = d.description
            ? (d.description.length > 150 ? d.description.substring(0, 150) + '...' : d.description)
            : '';

        return '<div class="slide' + (index === 0 ? ' active' : '') + '" data-bg="' + esc(d.image_url) + '">' +
            '<div class="slide-content">' +
                '<h3>' + esc(d.name) + (d.country ? ', ' + esc(d.country) : '') + '</h3>' +
                '<p>' + esc(description) + '</p>' +
            '</div>' +
        '</div>';
    }).join('');

    slidesWrap.querySelectorAll('.slide[data-bg]').forEach(slide => {
        slide.style.backgroundImage = "url('" + slide.dataset.bg + "')";
    });

    indicatorsWrap.innerHTML = featured.map((_, index) =>
        '<span class="indicator' + (index === 0 ? ' active' : '') + '" data-slide="' + index + '"></span>'
    ).join('');

    initSlideshow();
}

function initSlideshow() {
    const nextBtn = document.querySelector('.next-btn');
    const prevBtn = document.querySelector('.prev-btn');
    const slideshowContainer = document.querySelector('.slideshow-container');
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    const slideNumber = document.querySelector('.slide-number');
    const totalSlides = slides.length;

    if (totalSlides === 0 || !nextBtn || !prevBtn) return;

    let currentSlide = 0;
    let nextSlideIndex = 0;
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

    nextBtn.onclick = () => { nextSlide(); startAuto(); };
    prevBtn.onclick = () => { prevSlide(); startAuto(); };

    indicators.forEach(ind => {
        ind.onclick = function () {
            const idx = parseInt(this.getAttribute('data-slide'));
            if (!isTransitioning && idx !== currentSlide) {
                nextSlideIndex = idx;
                updateSlide(false);
            }
            startAuto();
        };
    });

    if (slideshowContainer) {
        slideshowContainer.onmouseenter = () => clearInterval(slideInterval);
        slideshowContainer.onmouseleave = startAuto;
    }

    updateSlide(true);
    startAuto();
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
        const params = new URLSearchParams();
        ['moodSelect', 'budgetSelect', 'companionSelect'].forEach(id => {
            const value = getVal(id);
            if (value) params.set(id.replace('Select', ''), value);
        });

        const url = '/api/landing/destinations' + (params.toString() ? '?' + params.toString() : '');
        const res  = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        const all  = uniqueByCountry(Array.isArray(data) ? data : (data.data || data.destinations || []));
        if (!all.length) {
            window._allDestinations = [];
            renderGrid([]);
            renderFeaturedSlides([]);
            if (loading) loading.style.display = 'none';
            return;
        }
        window._allDestinations = all;
        renderGrid(all.slice(0, 8));
        renderFeaturedSlides(all);
    } catch (err) {
        window._allDestinations = [];
        renderGrid([]);
        renderFeaturedSlides([]);
        if (loading) loading.style.display = 'none';
    }
}

window.initDestinations = fetchDestinations;

window.filterByStyle = function (style, cardEl) {
    const styleMap = {
        adventure: { moods: ['adventurous'],      categories: ['adventurous', 'mountain'],               label: 'Adventure Travel' },
        beach:     { moods: ['relaxed', 'beach'], categories: ['beach'],                                 label: 'Beach & Relaxation' },
        cultural:  { moods: ['cultural'],         categories: ['historical', 'food_culture', 'general'], label: 'Cultural Immersion' },
        food:      { moods: ['foodie'],           categories: ['food_culture'],                          label: 'Culinary Tours' },
    };

    document.querySelectorAll('.category-card').forEach(c => c.classList.remove('is-active-style'));
    if (cardEl) cardEl.classList.add('is-active-style');

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
    modal.className = 'ai-suggestion-modal';
    const box = document.createElement('div');
    box.className = 'ai-suggestion-box';
    box.innerHTML = '<h2 class="ai-modal-title"><i class="fas fa-compass"></i>Finding Your Perfect Trip…</h2>' +
        '<div class="ai-modal-loading"><i class="fas fa-globe-americas fa-3x fa-spin"></i>' +
        '<p>Our AI is crafting personalised recommendations just for you…</p></div>';
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
            const aiMatchScore = s.match_score
                ? '<span class="match-score"><i class="fas fa-star"></i> ' + s.match_score + '% match</span>' : '';
            return '<div class="ai-suggestion-card">' +
                '<div class="ai-suggestion-card-head">' +
                    '<div class="ai-suggestion-card-meta">' +
                        '<span class="ai-option-label">Option ' + (i+1) + ' ' + (flags[i]||'') + '</span>' +
                        (s.country ? '<span class="ai-country-pill">' + esc(s.country) + '</span>' : '') +
                        aiMatchScore +
                    '</div>' +
                    '<h3>' + esc(s.destination) + '</h3>' +
                    '<p>' + esc(s.description) + '</p>' +
                '</div>' +
                '<div class="ai-suggestion-card-body">' +
                    '<div class="ai-facts">' +
                        '<div><p>Est. Cost</p><strong>' + esc(formatSuggestionCost(s)) + '</strong></div>' +
                        '<div><p>Best Time</p><span>' + esc(s.best_time_to_visit) + '</span></div>' +
                        '<div><p>Visa</p><span>' + esc(s.visa_info || 'Check embassy') + '</span></div>' +
                    '</div>' +
                    '<p class="ai-section-label">Top Activities</p>' +
                    '<p class="ai-activities">' + esc(activities) + '</p>' +
                    (s.travel_tip ? '<div class="ai-travel-tip"><p>' + esc(s.travel_tip) + '</p></div>' : '') +
                    '<div class="ai-action-row">' +
                        '<a href="/flights?destination=' + encodeURIComponent(s.destination) + '&country=' + encodeURIComponent(s.country || '') + '&origin=' + encodeURIComponent(origin) + '&mood=' + mood + '&budget=' + budget + '" class="primary-button ai-flight-link"><i class="fas fa-plane"></i> Search Flights</a>' +
                        '<a href="/plan-trip?destination=' + encodeURIComponent(s.destination) + '&mood=' + mood + '&budget=' + budget + '" class="primary-button ai-plan-link"><i class="fas fa-map"></i> Plan Trip</a>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');

        box.innerHTML = '<div class="ai-modal-header">' +
            '<h2 class="ai-modal-title"><i class="fas fa-globe"></i>Your AI Travel Matches</h2>' +
            '<button class="ai-modal-close ai-icon-close">&times;</button>' +
            '</div>' + cards +
            '<button class="primary-button ai-modal-close ai-close-button">Close</button>';
        box.querySelectorAll('.ai-modal-close').forEach(closeBtn => {
            closeBtn.addEventListener('click', () => modal.remove());
        });

    } catch (err) {
        box.innerHTML = '<h2 class="ai-modal-title">Something went wrong</h2>' +
            '<p class="ai-error-text">' + esc(err.message || 'Unable to fetch suggestions. Please try again.') + '</p>' +
            '<button class="primary-button ai-modal-close">Close</button>';
        box.querySelector('.ai-modal-close')?.addEventListener('click', () => modal.remove());
    } finally {
        if (btn) { btn.innerHTML = originalHTML; btn.disabled = false; }
    }
};

function formatSuggestionCost(suggestion) {
    const min = Number(suggestion.cost_min_usd || 0);
    const max = Number(suggestion.cost_max_usd || 0);

    if (min > 0 && max > 0) {
        return formatCurrency(min) + ' - ' + formatCurrency(max);
    }

    return suggestion.estimated_cost || 'Price TBD';
}

function formatCurrency(usd) {
    if (typeof window.Currency !== 'undefined') {
        return window.Currency.format(Number(usd));
    }

    return '$' + Number(usd).toLocaleString();
}

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
        trigger.tabIndex = 0;
        trigger.setAttribute('role', 'button');
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        dropdown.setAttribute('role', 'listbox');

        const closeMood = () => {
            wrapper.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
        };
        const openMood = () => {
            document.querySelectorAll('.custom-select-wrapper.open').forEach(w => {
                if (w !== wrapper) {
                    w.classList.remove('open');
                    w.querySelector('.custom-select-trigger')?.setAttribute('aria-expanded', 'false');
                }
            });
            wrapper.classList.add('open');
            trigger.setAttribute('aria-expanded', 'true');
        };
        const toggleMood = () => wrapper.classList.contains('open') ? closeMood() : openMood();

        trigger.addEventListener('click', e => { e.stopPropagation(); toggleMood(); });
        trigger.addEventListener('keydown', e => {
            if (['Enter', ' ', 'ArrowDown'].includes(e.key)) {
                e.preventDefault();
                openMood();
            } else if (e.key === 'Escape') {
                e.preventDefault();
                closeMood();
            }
        });
        dropdown.querySelectorAll('.custom-select-option').forEach(opt => {
            opt.setAttribute('role', 'option');
            opt.setAttribute('aria-selected', opt.classList.contains('selected') ? 'true' : 'false');
            opt.addEventListener('click', function (e) {
                e.stopPropagation();
                dropdown.querySelectorAll('.custom-select-option').forEach(o => {
                    o.classList.remove('selected');
                    o.setAttribute('aria-selected', 'false');
                });
                this.classList.add('selected');
                this.setAttribute('aria-selected', 'true');
                const iconTag = this.querySelector('i');
                iconEl.innerHTML = iconTag ? iconTag.outerHTML : '';
                textEl.textContent = this.textContent.trim();
                hidden.value = this.dataset.value;
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
                closeMood();
            });
        });
        dropdown.addEventListener('click', e => e.stopPropagation());
        document.addEventListener('click', closeMood);
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeMood();
        });
    }

    
    const filterContainer = document.querySelector('.filter-tags');
    if (filterContainer) {
        filterContainer.addEventListener('click', function (e) {
            const tag = e.target.closest('.filter-tag');
            if (!tag) return;
            e.stopPropagation();
            window.applyDestinationFilter(tag.dataset.filter, tag);
        });
    }

    
    const generateBtn = document.getElementById('generatePlanBtn');
    if (generateBtn) {
        generateBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            window.generateQuickPlan(e);
        });
    }

    document.querySelectorAll('.category-card[data-trip-kind]').forEach(card => {
        card.addEventListener('click', function (e) {
            e.stopPropagation();
            window.filterByStyle(this.dataset.tripKind, this);
        });
    });

    
    document.addEventListener('click', function (e) {
        const nextBtn = e.target.closest('.qb-next-btn');
        const backBtn = e.target.closest('.qb-back-btn');
        if (nextBtn) {
            e.stopPropagation();
            window.showStep(parseInt(nextBtn.getAttribute('data-next')));
        }
        if (backBtn) {
            e.stopPropagation();
            window.showStep(parseInt(backBtn.getAttribute('data-back')));
        }
    });

    fetchDestinations();
    updateLandingBudgetDropdown();
});

function updateLandingBudgetDropdown() {
    var sel = document.getElementById('budgetSelect');
    if (!sel) return;
    var fmt = typeof window.Currency !== 'undefined' ? window.Currency.format : function(n) { return '$' + n.toLocaleString(); };
    var currentVal = sel.value;
    var opts = [
        { value: 'backpacker', text: 'Backpacker (under ' + fmt(500) + ')' },
        { value: 'budget',     text: 'Budget (' + fmt(500) + ' to ' + fmt(1500) + ')' },
        { value: 'mid',        text: 'Mid range (' + fmt(1500) + ' to ' + fmt(4000) + ')' },
        { value: 'premium',    text: 'Premium (' + fmt(4000) + ' to ' + fmt(8000) + ')' },
        { value: 'luxury',     text: 'Luxury (' + fmt(8000) + '+)' },
    ];
    opts.forEach(function(o) {
        var el = sel.querySelector('option[value="' + o.value + '"]');
        if (el) el.textContent = o.text;
    });
    sel.value = currentVal;
}
document.addEventListener('currency:changed', function() {
    updateLandingBudgetDropdown();
    if (window._allDestinations) {
        renderGrid(window._allDestinations.slice(0, 8));
    }
});

ready(function () {
    ['moodSelect', 'budgetSelect', 'companionSelect'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', fetchDestinations);
    });
});

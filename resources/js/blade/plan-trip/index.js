import { jsPDF as JsPdfModule } from 'jspdf';

let selectedMood = '';
let lastResults = [];
let lastPayload = {};
let selectedDest = null;
let prefillApplied = false;

// Get locked country and airport from global modules
function getLockedData() {
    const lockedCountry = window.countryLock ? window.countryLock.getLockedCountry() : null;
    const departureAirport = window.locationDetector ? window.locationDetector.departureAirport : null;
    
    return {
        destination: lockedCountry ? (lockedCountry.destination || lockedCountry.country) : '',
        country: lockedCountry ? lockedCountry.country : '',
        origin: departureAirport ? (departureAirport.city || departureAirport.name) : '',
        originCode: departureAirport ? departureAirport.code : ''
    };
}

// Expose to window immediately — data-action attributes in blade need these
// before the module's init() runs. Function declarations are hoisted so
// this works even though the function bodies appear later in the file.
window.selectMood         = function(el) { return selectMood(el); };
window.submitCustomMood   = function()   { return submitCustomMood(); };
window.goStep             = function(n)  { return goStep(n); };
window.generateSuggestions= function()  { return generateSuggestions(); };
window.selectDestination  = function(i) { return selectDestination(i); };
window.openReceipt        = function()  { return openReceipt(); };
window.closeReceipt       = function()  { return closeReceipt(); };
window.printReceipt       = function()  { return printReceipt(); };
window.downloadReceiptPdf = function()  { return downloadReceiptPdf(); };
window.esc                = function(s) { return esc(s); };
window.surpriseMe         = function()  { /* defined below */ };
window.acceptSurprise     = function()  { /* defined below */ };
window.clearSurprise      = function()  { /* defined below */ };
window.syncDuration       = function(value) { 
    document.getElementById('duration').value = value; 
};

const COST_MULTIPLIERS = {
    budget: { backpacker: 0.7, budget: 1.0, mid: 1.5, premium: 2.5, luxury: 4.0 },
    duration: { weekend: 0.4, week: 1.0, two_weeks: 1.8, month: 3.5, flexible: 1.0 },
    companion: { solo: 1.0, couple: 0.9, family_young: 1.2, family_teens: 1.3, friends_small: 0.95, friends_large: 0.9, business: 1.4 }
};

const REGION_BASE_COSTS = {
    europe: 2500,
    southeast_asia: 1500,
    east_asia: 2200,
    south_asia: 1400,
    middle_east: 2000,
    africa: 1800,
    north_america: 2800,
    latin_america: 1600,
    oceania: 3000,
    caribbean: 2400,
    any: 2000
};

const REGION_TAX_RATES = {
    europe: 19,
    southeast_asia: 10,
    east_asia: 13,
    south_asia: 12,
    middle_east: 5,
    africa: 15,
    north_america: 11,
    latin_america: 16,
    oceania: 15,
    caribbean: 18,
    any: 12
};

const BUDGET_TIERS = [
    { value: 'backpacker', label: 'Backpacker',       low: null, high: 500  },
    { value: 'budget',     label: 'Budget Friendly',  low: 500,  high: 1500 },
    { value: 'mid',        label: 'Mid Range',        low: 1500, high: 4000 },
    { value: 'premium',    label: 'Premium',          low: 4000, high: 8000 },
    { value: 'luxury',     label: 'Luxury',           low: 8000, high: null },
];

function getBudgetLabel(value) {
    const fmt = typeof window.Currency !== 'undefined'
        ? n => window.Currency.format(n)
        : n => '$' + Number(n).toLocaleString();
    const tier = BUDGET_TIERS.find(t => t.value === value);
    if (!tier) return value || 'Not set';
    if (tier.low === null) return `${tier.label} (under ${fmt(tier.high)})`;
    if (tier.high === null) return `${tier.label} (${fmt(tier.low)}+)`;
    return `${tier.label} (${fmt(tier.low)} to ${fmt(tier.high)})`;
}

const budgetLabels = new Proxy({}, { get: (_, k) => getBudgetLabel(k) });

const durLabels = {
    weekend: 'Long Weekend (3 to 4 days)',
    week: 'One Week (7 days)',
    two_weeks: 'Two Weeks (10 to 14 days)',
    month: 'One Month or more',
    flexible: 'Flexible / Open-ended'
};

const compLabels = {
    solo: 'Solo Traveller',
    couple: 'Couple',
    family_young: 'Family with Young Children',
    family_teens: 'Family with Teenagers',
    friends_small: 'Small Group of Friends (2 to 4)',
    friends_large: 'Large Group of Friends (5+)',
    business: 'Business Traveller'
};

const regionLabels = {
    europe: 'Europe',
    southeast_asia: 'Southeast Asia',
    east_asia: 'East Asia',
    south_asia: 'South Asia',
    middle_east: 'Middle East',
    africa: 'Africa',
    north_america: 'North America',
    latin_america: 'Latin America',
    oceania: 'Oceania',
    caribbean: 'Caribbean',
    any: 'No preference'
};

const accommodationLabels = {
    hostel: 'Hostel / Dorm',
    budget_hotel: 'Budget Hotel',
    boutique: 'Boutique Hotel',
    resort: 'Resort',
    villa: 'Private Villa',
    airbnb: 'Apartment / Airbnb',
    glamping: 'Glamping / Eco Lodge',
    any: 'No preference'
};

const MOOD_ICON_MAP = [
    ['adventur', 'fa-hiking'],
    ['hike', 'fa-mountain'],
    ['relax', 'fa-spa'],
    ['calm', 'fa-water'],
    ['peace', 'fa-dove'],
    ['cultur', 'fa-landmark'],
    ['histor', 'fa-scroll'],
    ['art', 'fa-palette'],
    ['romant', 'fa-heart'],
    ['love', 'fa-heart'],
    ['food', 'fa-utensils'],
    ['cuisin', 'fa-drumstick-bite'],
    ['eat', 'fa-utensils'],
    ['eco', 'fa-leaf'],
    ['natur', 'fa-tree'],
    ['green', 'fa-seedling'],
    ['party', 'fa-music'],
    ['fun', 'fa-laugh'],
    ['spirit', 'fa-praying-hands'],
    ['mindful', 'fa-peace'],
    ['winter', 'fa-snowflake'],
    ['ski', 'fa-skiing'],
    ['beach', 'fa-umbrella-beach'],
    ['sun', 'fa-sun'],
    ['tropic', 'fa-umbrella-beach'],
    ['city', 'fa-city'],
    ['urban', 'fa-subway'],
    ['desert', 'fa-sun'],
    ['mountain', 'fa-mountain'],
    ['island', 'fa-water'],
    ['ocean', 'fa-water'],
    ['tired', 'fa-bed'],
    ['burnt', 'fa-fire'],
    ['disconnect', 'fa-wifi'],
    ['excited', 'fa-bolt'],
    ['curious', 'fa-search'],
    ['wander', 'fa-plane'],
];

const FALLBACK_ICONS = [
    'fa-globe-americas', 'fa-map-marked-alt', 'fa-compass', 'fa-route',
    'fa-suitcase', 'fa-passport', 'fa-binoculars', 'fa-star',
];
let _fallbackIdx = 0;

function pickIcon(label) {
    const lower = (label || '').toLowerCase();
    for (const [key, icon] of MOOD_ICON_MAP) {
        if (lower.includes(key)) return icon;
    }
    return FALLBACK_ICONS[_fallbackIdx++ % FALLBACK_ICONS.length];
}

let communityMoods = [];

function setSelectedMood(value, label) {
    selectedMood = value;
    const hiddenInput = document.getElementById('selectedMoodValue');
    if (hiddenInput) hiddenInput.value = value;
    window.__planTripMood = value;

    const display = document.getElementById('selectedMoodDisplay');
    const labelEl = document.getElementById('selectedMoodLabel');
    if (!display || !labelEl) return;

    if (value) {
        const icon = pickIcon(label || value);
        labelEl.innerHTML = `<i class="fas ${icon} selected-mood-icon"></i>${escHtml(label)}`;
        display.classList.add('visible');
    } else {
        display.classList.remove('visible');
    }
}

function selectMood(el) {
    document.querySelectorAll('.mood-card').forEach(c => c.classList.remove('selected'));
    document.querySelectorAll('.mood-pill').forEach(p => p.classList.remove('selected'));
    el.classList.add('selected');
    const mood = el.dataset.mood;
    const h4 = el.querySelector('h4');
    setSelectedMood(mood, h4 ? h4.textContent.trim() : mood);
}

function setFieldValue(id, value) {
    const el = document.getElementById(id);
    if (!el || value === undefined || value === null || value === '') return false;
    el.value = value;
    el.dispatchEvent(new Event('change', { bubbles: true }));
    return true;
}

function applyPlanTripPrefill() {
    if (prefillApplied) return;
    prefillApplied = true;

    const query = Object.fromEntries(new URLSearchParams(window.location.search).entries());
    const stored = window.PlanTripContext?.load?.() || {};
    const data = { ...stored, ...query };
    const hasPrefill = Object.keys(data).some(key => data[key] && key !== 'prefill');
    if (!hasPrefill) return;

    const mood = data.mood || data.category || '';
    if (mood) {
        const matchingMood = document.querySelector(`.mood-card[data-mood="${CSS.escape(mood)}"]`);
        if (matchingMood) selectMood(matchingMood);
        else setSelectedMood(mood, mood.replace(/[_-]/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));
    } else {
        const defaultMood = document.querySelector('.mood-card[data-mood="relaxed"]');
        if (defaultMood) selectMood(defaultMood);
    }

    setFieldValue('budget', data.budget);
    setFieldValue('durationDays', data.duration);
    setFieldValue('duration', data.duration);
    setFieldValue('companion', data.companion);
    setFieldValue('month', data.month);
    setFieldValue('region', data.region);
    setFieldValue('accommodation', data.accommodation);
    setFieldValue('origin', data.origin || data.from);
    setFieldValue('experience', data.experience);
    setFieldValue('destinationInterest', data.destination || data.to || data.q);

    if (data.destination && !document.getElementById('feelingNote')?.value.trim()) {
        setFieldValue('feelingNote', `I want to plan around ${data.destination}.`);
    }

    window.PlanTripContext?.clear?.();
    if (window.location.search) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    goStep(data.destination || data.origin || data.region || data.accommodation ? 3 : 2);
}

function selectCommunityMood(label, pillEl) {
    document.querySelectorAll('.mood-card').forEach(c => c.classList.remove('selected'));
    const wasSelected = pillEl.classList.contains('selected');
    document.querySelectorAll('.mood-pill').forEach(p => p.classList.remove('selected'));
    if (wasSelected) {
        setSelectedMood('', '');
    } else {
        pillEl.classList.add('selected');
        setSelectedMood(label, label);
        bumpMoodUsage(pillEl.dataset.moodId);
    }
}

async function loadCommunityMoods() {
    try {
        const res = await fetch('/api/trip-moods', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        communityMoods = data.moods || [];
        renderCommunityMoods();
    } catch (e) {
        const loading = document.getElementById('communityMoodsLoading');
        if (loading) loading.innerHTML = '<span class="community-moods-message">Could not load community moods.</span>';
    }
}

function renderCommunityMoods() {
    const container = document.getElementById('communityMoodPills');
    const countEl = document.getElementById('communityMoodCount');
    const loading = document.getElementById('communityMoodsLoading');
    if (loading) loading.remove();
    if (!container) return;

    if (!communityMoods.length) {
        container.innerHTML = '<span class="community-moods-message muted">No community moods yet — be the first!</span>';
        return;
    }

    if (countEl) countEl.textContent = `(${communityMoods.length})`;
    container.innerHTML = '';
    communityMoods.forEach(mood => {
        const pill = document.createElement('button');
        pill.type = 'button';
        pill.className = 'mood-pill' + (selectedMood === mood.label ? ' selected' : '');
        pill.dataset.moodId = mood.id;
        pill.dataset.moodLabel = mood.label;
        const icon = pickIcon(mood.label);
        const countText = mood.use_count > 1 ? `<span class="pill-count">×${mood.use_count}</span>` : '';
        pill.innerHTML = `<i class="fas ${icon} pill-icon"></i>${escHtml(mood.label)}${countText}`;
        pill.addEventListener('click', () => selectCommunityMood(mood.label, pill));
        container.appendChild(pill);
    });
}

async function submitCustomMood() {
    const input = document.getElementById('customMoodInput');
    const feedback = document.getElementById('moodSubmitFeedback');
    const btn = document.getElementById('btnAddMood');
    const raw = input.value.trim();

    if (!raw) { showFeedback(feedback, 'error', 'Please type a feeling first.'); return; }
    if (raw.length < 3) { showFeedback(feedback, 'error', 'Too short — write at least a few characters.'); return; }

    document.querySelectorAll('.mood-card.selected, .mood-pill.selected').forEach(el => el.classList.remove('selected'));
    setSelectedMood(raw, raw);

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
    clearFeedback(feedback);

    try {
        const res = await fetch('/api/trip-moods', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ label: raw }),
        });
        const data = await res.json();

        if (!res.ok) {
            showFeedback(feedback, 'error', data.message || 'Could not save mood. Please try again.');
            return;
        }

        const exists = communityMoods.find(m => m.label.toLowerCase() === data.mood.label.toLowerCase());
        if (!exists) {
            communityMoods.unshift(data.mood);
        } else {
            const idx = communityMoods.findIndex(m => m.id === data.mood.id);
            if (idx > -1) communityMoods[idx] = data.mood;
        }

        renderCommunityMoods();
        setSelectedMood(data.mood.label, data.mood.label);
        document.querySelectorAll('.mood-pill').forEach(p => {
            if (p.dataset.moodLabel && p.dataset.moodLabel.toLowerCase() === data.mood.label.toLowerCase()) {
                p.classList.add('selected');
            }
        });

        input.value = '';
        const icon = pickIcon(data.mood.label);
        showFeedback(feedback, 'success', `<i class="fas fa-check"></i> <i class="fas ${icon}"></i> "${escHtml(data.mood.label)}" saved and selected!`);
    } catch (e) {
        showFeedback(feedback, 'error', 'Network error. Please try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus"></i> Add &amp; Use';
    }
}

async function bumpMoodUsage(id) {
    try {
        await fetch(`/api/trip-moods/${id}/use`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
    } catch (_) {}
}

function showFeedback(el, type, html) {
    if (!el) return;
    el.className = 'mood-submit-feedback ' + type;
    el.innerHTML = html;
}

function clearFeedback(el) {
    if (!el) return;
    el.className = 'mood-submit-feedback';
    el.innerHTML = '';
}

function escHtml(str) {
    return String(str || '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function goStep(n) {
    [1, 2, 3, 4].forEach(i => {
        const step = document.getElementById('step' + i);
        const si = document.getElementById('si' + i);
        if (step) step.classList.add('hidden');
        if (si) si.classList.remove('active', 'done');
    });
    for (let i = 1; i < n; i++) {
        const si = document.getElementById('si' + i);
        if (si) si.classList.add('done');
    }
    const currentSi = document.getElementById('si' + n);
    if (currentSi) currentSi.classList.add('active');
    const currentStep = document.getElementById('step' + n);
    if (currentStep) currentStep.classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function generateSuggestions() {
    const hiddenInput = document.getElementById('selectedMoodValue');
    selectedMood = (hiddenInput && hiddenInput.value.trim()) ? hiddenInput.value.trim() : selectedMood;

    if (!selectedMood) {
        alert('Please select or add a mood first.');
        goStep(1);
        return;
    }

    selectedDest = null;

    const receiptBtn = document.getElementById('receiptBtn');
    if (receiptBtn) receiptBtn.classList.add('hidden');
    const quickPrintBtn = document.getElementById('quickPrintBtn');
    if (quickPrintBtn) quickPrintBtn.classList.add('hidden');
    const quickPdfBtn = document.getElementById('quickPdfBtn');
    if (quickPdfBtn) quickPdfBtn.classList.add('hidden');
    const saveBtn = document.getElementById('saveBtn');
    if (saveBtn) saveBtn.classList.add('hidden');

    goStep(4);

    const loadingState = document.getElementById('loadingState');
    const errorState = document.getElementById('errorState');
    const resultsState = document.getElementById('resultsState');

    if (loadingState) { loadingState.style.display = ''; loadingState.classList.remove('hidden'); }
    if (errorState) errorState.classList.add('hidden');
    if (resultsState) resultsState.classList.add('hidden');

    lastPayload = {
        mood: selectedMood,
        feeling_note: document.getElementById('feelingNote')?.value.trim() || null,
        budget: document.getElementById('budget')?.value || null,
        duration: document.getElementById('duration')?.value || document.getElementById('durationDays')?.value || null,
        companion: document.getElementById('companion')?.value || null,
        month: document.getElementById('month')?.value || null,
        region: document.getElementById('region')?.value || null,
        accommodation: document.getElementById('accommodation')?.value || null,
        destination_interest: document.getElementById('destinationInterest')?.value.trim() || null,
        origin: document.getElementById('origin')?.value.trim() || null,
        experience: document.getElementById('experience')?.value || null,
        currency: typeof window.Currency !== 'undefined' ? window.Currency.active : 'USD',
    };

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        const res = await fetch('/ai/suggest', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
            },
            body: JSON.stringify(lastPayload),
        });

        const json = await res.json();
        if (loadingState) loadingState.classList.add('hidden');

        if (!json.success) {
            if (errorState) { errorState.textContent = json.message || 'Something went wrong.';
                errorState.classList.remove('hidden'); }
            return;
        }

        lastResults = json.data.map(dest => ({...dest, costBreakdown: calculateCostBreakdown(dest, lastPayload) }));
        renderResults(lastResults);
    } catch (err) {
        if (loadingState) loadingState.classList.add('hidden');
        if (errorState) { errorState.textContent = 'Network error: ' + err.message;
            errorState.classList.remove('hidden'); }
    }
}

function calculateCostBreakdown(destination, payload) {
    // Use AI-provided realistic prices instead of hard-coded regional costs
    const costMin = destination.cost_min_usd || 0;
    const costMax = destination.cost_max_usd || 0;
    
    // If AI provided realistic prices, use them
    if (costMin > 0 && costMax > 0) {
        const avgTotal = Math.round((costMin + costMax) / 2);
        const nights = getNightsFromDuration(payload.duration);
        
        // Break down the AI total without inflating it. AI prices are already full-trip estimates.
        const flights = Math.round(avgTotal * 0.42); // Flights typically 40-45% of total
        const accommodation_c = Math.round(avgTotal * 0.28); // Accommodation 25-30%
        const food = Math.round(avgTotal * 0.18); // Food 15-20%
        const activities = Math.round(avgTotal * 0.08); // Activities 5-10%
        const transportation = Math.max(0, avgTotal - flights - accommodation_c - food - activities);
        
        const subtotal = flights + accommodation_c + activities + food + transportation;
        const taxes = 0;
        const serviceFee = 0;
        const total = avgTotal;
        
        return {
            breakdown: {
                flights: { amount: flights, description: 'Round-trip flights (economy)', details: getFlightDetails(payload.origin, destination.destination) },
                accommodation: { amount: accommodation_c, description: getAccommodationDescription(payload.accommodation), nights },
                activities: { amount: activities, description: 'Guided tours & activities', items: getActivityCount(destination.top_activities) },
                food: { amount: food, description: 'Meals & dining experiences', perDay: Math.round(food / (nights || 7)) },
                transportation: { amount: transportation, description: 'Local transportation', includes: ['Airport transfers', 'Public transport', 'Inter-city travel'] }
            },
            subtotal,
            taxes: { amount: taxes, rate: 0, type: 'Included in AI estimate' },
            serviceFee: { amount: serviceFee, description: 'Included in AI estimate' },
            total,
            range: {
                low: costMin, // Use AI-provided min
                high: costMax, // Use AI-provided max
                display: `${fmtCurrency(costMin)} to ${fmtCurrency(costMax)}`
            },
            savings: {
                earlyBird: Math.round(total * 0.10),
                groupDiscount: (payload.companion || '').includes('family') || (payload.companion || '').includes('friends') ? Math.round(total * 0.08) : 0,
                packageDeal: Math.round(total * 0.05)
            }
        };
    }
    
    // Fallback to old calculation if AI didn't provide prices
    const region = payload.region || 'any';
    const baseCost = REGION_BASE_COSTS[region] || 2000;
    const budgetMult = COST_MULTIPLIERS.budget[payload.budget] || 1.0;
    // Support numeric duration (days) as well as legacy string keys
    const durationMult = (!isNaN(Number(payload.duration)) && Number(payload.duration) > 0)
        ? Math.max(0.3, Number(payload.duration) / 7)
        : (COST_MULTIPLIERS.duration[payload.duration] || 1.0);
    const companionMult = COST_MULTIPLIERS.companion[payload.companion] || 1.0;

    const flights = Math.round(baseCost * 0.40 * budgetMult);
    const accommodation_c = Math.round(baseCost * 0.35 * durationMult * budgetMult);
    const activities = Math.round(baseCost * 0.15 * companionMult);
    const food = Math.round(baseCost * 0.20 * durationMult);
    const transportation = Math.round(baseCost * 0.10 * budgetMult);
    const subtotal = flights + accommodation_c + activities + food + transportation;
    const taxRate = REGION_TAX_RATES[region] || 12;
    const taxes = Math.round(subtotal * (taxRate / 100));
    const serviceFee = Math.round(subtotal * 0.05);
    const total = subtotal + taxes + serviceFee;
    const nights = getNightsFromDuration(payload.duration);

    return {
        breakdown: {
            flights: { amount: flights, description: 'Round-trip flights (economy)', details: getFlightDetails(payload.origin, destination.destination) },
            accommodation: { amount: accommodation_c, description: getAccommodationDescription(payload.accommodation), nights },
            activities: { amount: activities, description: 'Guided tours & activities', items: getActivityCount(destination.top_activities) },
            food: { amount: food, description: 'Meals & dining experiences', perDay: Math.round(food / (nights || 7)) },
            transportation: { amount: transportation, description: 'Local transportation', includes: ['Airport transfers', 'Public transport', 'Inter-city travel'] }
        },
        subtotal,
        taxes: { amount: taxes, rate: taxRate, type: 'VAT/GST' },
        serviceFee: { amount: serviceFee, description: 'Booking & service fee' },
        total,
        range: {
            low: Math.round(total * 0.85),
            high: Math.round(total * 1.15),
            display: `${fmtCurrency(Math.round(total * 0.85))} – ${fmtCurrency(Math.round(total * 1.15))}`
        },
        savings: {
            earlyBird: Math.round(total * 0.10),
            groupDiscount: (payload.companion || '').includes('family') || (payload.companion || '').includes('friends') ? Math.round(total * 0.08) : 0,
            packageDeal: Math.round(total * 0.05)
        }
    };
}

function fmt(n) { return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

function fmtCurrency(n) {
    if (typeof window.Currency !== 'undefined') {
        return window.Currency.format(Number(n));
    }
    return '$' + fmt(Math.round(n));
}

// PDF-specific currency formatter that respects user's currency
function fmtCurrencyForPDF(n) {
    if (typeof window.Currency !== 'undefined') {
        return window.Currency.format(Number(n));
    }
    // Fallback without currency symbol if Currency helper not available
    return fmt(Math.round(n));
}

function getFlightDetails(origin, dest) {
    if (!origin) return 'Based on average prices from major hubs';
    const airlines = ['Emirates', 'Qatar Airways', 'Singapore Airlines', 'British Airways', 'Lufthansa', 'Delta', 'United'];
    const airline = airlines[Math.floor(Math.random() * airlines.length)];
    const hrs = Math.floor(Math.random() * 6) + 8;
    return `${airline} · ~${hrs}h from ${origin}`;
}

function getAccommodationDescription(p) {
    const d = {
        hostel: 'Shared dormitory in central location',
        budget_hotel: '2–3 star hotel with breakfast',
        boutique: 'Boutique hotel with local character',
        resort: 'All-inclusive resort',
        villa: 'Private villa with pool',
        airbnb: 'Private apartment with kitchen',
        glamping: 'Eco-lodge with unique experience',
        any: 'Mix of comfortable accommodations'
    };
    return d[p] || d.any;
}

function getNightsFromDuration(d) {
    if (d && !isNaN(Number(d))) return Math.max(1, Number(d));
    return { weekend: 3, week: 7, two_weeks: 12, month: 28, flexible: 7 }[d] || 7;
}

function getActivityCount(a) {
    if (!a) return 'Multiple activities';
    return `${a.split(',').length} included activities`;
}

function generateAirportCode(dest) {
    const w = dest.split(' ');
    return (w.length === 1 ? dest.substring(0, 3) : w.map(x => x[0]).join('').substring(0, 3)).toUpperCase();
}

function renderResults(destinations) {
    const grid = document.getElementById('resultsGrid');
    if (!grid) return;

    grid.innerHTML = '';
    destinations.forEach((d, idx) => {
        const card = document.createElement('div');
        card.className = 'dest-card';
        card.dataset.idx = idx;
        card.innerHTML = `
            <div class="select-badge"><i class="fas fa-check"></i></div>
            <div class="dest-card-header">
                <h3>${esc(d.destination)}</h3>
                <div class="country"><i class="fas fa-globe country-icon"></i>${esc(d.country)}</div>
            </div>
            <p>${esc(d.description)}</p>
            <div class="dest-cost">
                <i class="fas fa-wallet dest-cost-icon"></i>
                <span data-price-usd="${d.costBreakdown.range.low}">${d.costBreakdown.range.display}</span>
                <span class="dest-cost-note">per person · <em>${d.estimated_cost || ''}</em></span>
            </div>
            <div class="dest-meta">
                <div class="dest-meta-row"><i class="fas fa-calendar-check"></i><span><strong>Best time:</strong> ${esc(d.best_time_to_visit)}</span></div>
                <div class="dest-meta-row"><i class="fas fa-star"></i><span><strong>Activities:</strong> ${esc(d.top_activities)}</span></div>
                <div class="dest-meta-row"><i class="fas fa-passport"></i><span><strong>Visa:</strong> ${esc(d.visa_info)}</span></div>
                <div class="dest-meta-row"><i class="fas fa-plane"></i><span><strong>Flights:</strong> ${esc(d.flight_info)}</span></div>
                <div class="dest-meta-row"><i class="fas fa-lightbulb"></i><span><strong>Tip:</strong> ${esc(d.travel_tip)}</span></div>
            </div>`;
        card.addEventListener('click', () => selectDestination(idx));
        grid.appendChild(card);
    });

    const resultsState = document.getElementById('resultsState');
    if (resultsState) resultsState.classList.remove('hidden');

    if (destinations.length) {
        selectDestination(0, false);
    }
}

function selectDestination(idx, shouldScroll = true) {
    document.querySelectorAll('.dest-card').forEach(c => c.classList.remove('selected'));
    const cards = document.querySelectorAll('.dest-card');
    if (cards[idx]) cards[idx].classList.add('selected');
    selectedDest = lastResults[idx];

    const receiptBtn = document.getElementById('receiptBtn');
    if (receiptBtn) receiptBtn.classList.remove('hidden');

    const quickPrintBtn = document.getElementById('quickPrintBtn');
    if (quickPrintBtn) quickPrintBtn.classList.remove('hidden');

    const quickPdfBtn = document.getElementById('quickPdfBtn');
    if (quickPdfBtn) quickPdfBtn.classList.remove('hidden');

    const saveBtn = document.getElementById('saveBtn');
    if (saveBtn) {
        saveBtn.classList.remove('hidden');
        saveBtn.innerHTML = '<i class="fas fa-bookmark"></i> Save to Dashboard';
        saveBtn.disabled = false;
    }

    if (shouldScroll && receiptBtn) receiptBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    // Load travel advisory for selected destination
    if (typeof window.renderTravelAdvisory === 'function') {
        window.renderTravelAdvisory(
            'travelAdvisoryContainer',
            selectedDest.destination,
            selectedDest.country
        );
    }
}

async function saveTripToDashboard() {
    if (!selectedDest) {
        showActionError('Select a destination before saving.');
        return;
    }

    const btn = document.getElementById('saveBtn');
    if (btn) { btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…'; }

    const payload = {
        destination: selectedDest.destination,
        country: selectedDest.country || null,
        mood: lastPayload.mood || null,
        feeling_note: lastPayload.feeling_note || null,
        budget: lastPayload.budget || null,
        duration: lastPayload.duration || null,
        companion: lastPayload.companion || null,
        region: lastPayload.region || null,
        accommodation: lastPayload.accommodation || null,
        origin: lastPayload.origin || null,
        month: lastPayload.month || null,
        estimated_cost: selectedDest.costBreakdown?.total || null,
        // Save AI suggestion data
        description: selectedDest.description || null,
        travel_tip: selectedDest.travel_tip || null,
        visa_info: selectedDest.visa_info || null,
        flight_info: selectedDest.flight_info || null,
        best_time_to_visit: selectedDest.best_time_to_visit || null,
        is_good_right_now: selectedDest.is_good_right_now || false,
        // Save pricing breakdown
        flight_cost: selectedDest.costBreakdown?.breakdown?.flights?.amount || null,
        accommodation_cost: selectedDest.costBreakdown?.breakdown?.accommodation?.amount || null,
        activities_cost: selectedDest.costBreakdown?.breakdown?.activities?.amount || null,
        food_cost: selectedDest.costBreakdown?.breakdown?.food?.amount || null,
        transport_cost: selectedDest.costBreakdown?.breakdown?.transportation?.amount || null,
        cost_breakdown: selectedDest.costBreakdown || null,
        // Save activities as array
        activities: selectedDest.top_activities ? selectedDest.top_activities.split(',').map(a => a.trim()) : null,
        // Save validation data
        validation_data: selectedDest.validation || null,
        weather_data: selectedDest.weather || null,
        safety_data: selectedDest.safety || null,
    };

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        const res = await fetch('/api/trips', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
            },
            body: JSON.stringify(payload),
        });
        if (res.status === 401 || res.redirected) {
            window.location.href = '/login';
            return;
        }

        const data = await res.json().catch(() => ({}));

        if (res.status === 409) {
            if (btn) { btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-bookmark"></i> Already Saved'; }
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Already Saved',
                    text: selectedDest.destination + ' is already on your dashboard.',
                    icon: 'info',
                    confirmButtonColor: '#c9a96e',
                    confirmButtonText: 'View Dashboard',
                    showCancelButton: true,
                    cancelButtonText: 'Stay Here',
                }).then(result => { if (result.isConfirmed) window.location.href = '/dashboard'; });
            }
            return;
        }

        if (res.ok && data.success) {
            if (btn) btn.innerHTML = '<i class="fas fa-check"></i> Saved!';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Trip Saved!',
                    text: selectedDest.destination + ' has been added to your dashboard.',
                    icon: 'success',
                    confirmButtonColor: '#c9a96e',
                    confirmButtonText: 'View Dashboard',
                    showCancelButton: true,
                    cancelButtonText: 'Stay Here',
                }).then(result => { if (result.isConfirmed) window.location.href = '/dashboard'; });
            }
        } else {
            throw new Error(data.message || 'Failed to save');
        }
    } catch (err) {
        if (btn) { btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-bookmark"></i> Save to Dashboard'; }
        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: 'Error', text: err.message || 'Could not save trip.', icon: 'error', confirmButtonColor: '#c9a96e' });
        }
    }
}

function showActionError(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({ title: 'Almost there', text: message, icon: 'info', confirmButtonColor: '#c9a96e' });
    } else {
        alert(message);
    }
}

function openReceipt() {
    if (!selectedDest) return;
    const receiptContent = document.getElementById('receiptContent');
    if (receiptContent) receiptContent.innerHTML = buildReceiptHTML(selectedDest);
    const receiptModal = document.getElementById('receiptModal');
    if (receiptModal) { receiptModal.classList.add('open');
        document.body.style.overflow = 'hidden'; }
}

function closeReceipt() {
    const receiptModal = document.getElementById('receiptModal');
    if (receiptModal) { receiptModal.classList.remove('open');
        document.body.style.overflow = ''; }
}

function generateReferenceNumber() {
    const ts = Date.now().toString().slice(-8);
    const rnd = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
    return `SBP-${ts}-${rnd}`;
}

function getFormattedDates() {
    const now = new Date();
    const valid = new Date(now);
    valid.setMonth(valid.getMonth() + 3);
    return {
        issueDate: now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
        issueTime: now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true }),
        validUntil: valid.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }),
        bookingRef: generateReferenceNumber()
    };
}

function generateActivityTags(activities) {
    if (!activities) return '';
    return activities.split(',').map(a => a.trim()).map(a =>
        `<span class="activity-tag">
            <i class="fas fa-tag"></i>${esc(a)}
        </span>`
    ).join('');
}

function buildReceiptHTML(d) {
    const dates = getFormattedDates();
    const cost = d.costBreakdown;
    const bk = cost.breakdown;
    const mood = (lastPayload.mood || '').charAt(0).toUpperCase() + (lastPayload.mood || '').slice(1);
    const bud = budgetLabels[lastPayload.budget] || lastPayload.budget || 'Not set';
    const dur = (lastPayload.duration && !isNaN(Number(lastPayload.duration)))
        ? `${lastPayload.duration} days`
        : (durLabels[lastPayload.duration] || lastPayload.duration || 'Not set');
    const comp = compLabels[lastPayload.companion] || lastPayload.companion || 'Not set';

    return `
    <div style="font-family:'Georgia',serif;color:#2c2c2c;">
        <div style="background:#3b1f2b;padding:28px 30px;">
            <div style="display:flex;align-items:center;gap:15px;margin-bottom:18px;">
                <div style="width:72px;height:72px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><img src="/img/travel-icon.png" alt="Smart Booking" style="width:100%;height:100%;object-fit:contain;filter:brightness(0) invert(1);"></div>
                <div>
                    <div style="font-size:22px;font-weight:bold;color:#f5e6d3;letter-spacing:2px;font-variant:small-caps;">Smart Booking</div>
                    <div style="font-size:11px;color:#d4c4b0;">AI-Powered Travel Planning</div>
                </div>
            </div>
            <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;background:rgba(255,255,255,0.07);padding:14px 16px;border-radius:6px;">
                <div>
                    <div style="font-size:10px;color:#d4c4b0;text-transform:uppercase;letter-spacing:1px;">Booking Reference</div>
                    <div style="font-size:17px;font-weight:bold;color:#c9a96e;font-family:monospace;">${dates.bookingRef}</div>
                </div>
                <div>
                    <div style="font-size:10px;color:#d4c4b0;text-transform:uppercase;letter-spacing:1px;">Issued</div>
                    <div style="font-size:13px;color:#f5e6d3;">${dates.issueDate}</div>
                    <div style="font-size:11px;color:#d4c4b0;">${dates.issueTime}</div>
                </div>
                <div>
                    <div style="font-size:10px;color:#d4c4b0;text-transform:uppercase;letter-spacing:1px;">Valid Until</div>
                    <div style="font-size:13px;color:#f5e6d3;">${dates.validUntil}</div>
                </div>
            </div>
        </div>
        <div style="background:linear-gradient(135deg,#c9a96e,#b8955a);padding:18px 30px;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div style="font-size:26px;font-weight:bold;color:#3b1f2b;">${esc(d.destination)}</div>
                <div style="font-size:13px;color:#3b1f2b;opacity:0.75;margin-top:4px;"><i class="fas fa-map-pin" style="margin-right:5px;"></i>${esc(d.country)}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:10px;color:#3b1f2b;opacity:0.7;text-transform:uppercase;">Code</div>
                <div style="font-size:26px;font-weight:bold;color:#3b1f2b;font-family:monospace;">${generateAirportCode(d.destination)}</div>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;padding:20px 30px;background:#fff;">
            ${[['fas fa-smile','Mood',mood],['fas fa-clock','Duration',dur+` (${bk.accommodation.nights}n)`],['fas fa-users','Companion',comp],['fas fa-wallet','Budget',bud]].map(([icon,label,val])=>`
                <div style="text-align:center;padding:14px 10px;background:#f8f4f0;border-radius:8px;">
                    <div style="font-size:22px;color:#c9a96e;margin-bottom:6px;"><i class="${icon}"></i></div>
                    <div style="font-size:10px;color:#6b5b4f;text-transform:uppercase;letter-spacing:0.5px;">${label}</div>
                    <div style="font-size:13px;font-weight:bold;color:#3b1f2b;margin-top:4px;">${val}</div>
                </div>`).join('')}
        </div>
        <div style="padding:20px 30px;background:#fff;border-top:1px solid #e2d5c7;">
            <h3 style="color:#3b1f2b;font-size:16px;font-weight:normal;border-bottom:2px solid #c9a96e;padding-bottom:10px;margin:0 0 18px;">
                <i class="fas fa-calculator" style="color:#c9a96e;margin-right:8px;"></i>Cost Estimation Breakdown
            </h3>
            ${[['fas fa-plane','Flights',fmtCurrency(bk.flights.amount),bk.flights.description,bk.flights.details],['fas fa-hotel','Accommodation',fmtCurrency(bk.accommodation.amount),bk.accommodation.description,`${bk.accommodation.nights} nights`],['fas fa-ticket-alt','Activities',fmtCurrency(bk.activities.amount),bk.activities.description,bk.activities.items],['fas fa-utensils','Food & Dining',fmtCurrency(bk.food.amount),bk.food.description,`~${fmtCurrency(bk.food.perDay)}/day`],['fas fa-bus','Local Transport',fmtCurrency(bk.transportation.amount),bk.transportation.description,bk.transportation.includes.join(' · ')]].map(([icon,label,amt,desc,detail])=>`
                <div style="background:#f8f4f0;border-radius:8px;padding:13px 15px;margin-bottom:10px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <i class="${icon}" style="color:#c9a96e;width:18px;text-align:center;"></i>
                            <span style="font-weight:bold;color:#3b1f2b;">${label}</span>
                        </div>
                        <span style="font-weight:bold;color:#3b1f2b;">${amt}</span>
                    </div>
                    <div style="font-size:11px;color:#6b5b4f;margin-left:28px;">${desc}<br><span style="color:#c9a96e;">${detail}</span></div>
                </div>`).join('')}
            <div style="margin-top:16px;border-top:2px solid #e2d5c7;padding-top:14px;">
                ${[['Subtotal',fmtCurrency(cost.subtotal)],[`Taxes (${cost.taxes.rate}% ${cost.taxes.type})`,fmtCurrency(cost.taxes.amount)],['Service Fee',fmtCurrency(cost.serviceFee.amount)]].map(([l,v])=>`
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:14px;">
                        <span style="color:#6b5b4f;">${l}</span><span style="color:#3b1f2b;">${v}</span>
                    </div>`).join('')}
            </div>
            <div style="background:linear-gradient(135deg,#3b1f2b,#4d2a3a);border-radius:8px;padding:18px 20px;margin-top:14px;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="color:#c9a96e;font-size:13px;text-transform:uppercase;letter-spacing:1px;">Total per person</div>
                    <div style="color:#d4c4b0;font-size:11px;margin-top:3px;">Range: ${cost.range.display}</div>
                </div>
                <div style="color:#c9a96e;font-size:30px;font-weight:bold;">${fmtCurrency(cost.total)}</div>
            </div>
            <div style="margin-top:16px;background:#e8f4e8;border-radius:8px;padding:14px 16px;border-left:4px solid #4CAF50;">
                <div style="font-weight:bold;color:#2c5e2c;margin-bottom:10px;font-size:13px;"><i class="fas fa-tag" style="margin-right:6px;color:#4CAF50;"></i>Available Discounts</div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;text-align:center;">
                    ${[['Early Bird',cost.savings.earlyBird],['Group Discount',cost.savings.groupDiscount],['Package Deal',cost.savings.packageDeal]].map(([l,v])=>`
                        <div>
                            <div style="font-size:16px;font-weight:bold;color:#2c5e2c;">${fmtCurrency(v)}</div>
                            <div style="font-size:10px;color:#6b5b4f;">${l}</div>
                        </div>`).join('')}
                </div>
                <div style="text-align:center;font-size:11px;color:#6b5b4f;margin-top:10px;">
                    Total potential savings: <strong style="color:#2c5e2c;">${fmtCurrency(cost.savings.earlyBird+cost.savings.groupDiscount+cost.savings.packageDeal)}</strong>
                </div>
            </div>
        </div>
        <div style="padding:0 30px 20px;background:#fff;">
            <div style="background:#f8f4f0;padding:14px 16px;border-radius:6px;border-left:4px solid #c9a96e;">
                <div style="font-weight:bold;color:#3b1f2b;margin-bottom:6px;font-size:13px;"><i class="fas fa-plane-departure" style="color:#c9a96e;margin-right:6px;"></i>Flight Information</div>
                <div style="color:#6b5b4f;font-size:13px;line-height:1.6;">${esc(d.flight_info)}</div>
            </div>
        </div>
        <div style="padding:0 30px 20px;background:#fff;">
            <div style="font-weight:bold;color:#3b1f2b;border-bottom:2px solid #c9a96e;padding-bottom:8px;margin-bottom:12px;font-size:13px;"><i class="fas fa-star" style="color:#c9a96e;margin-right:6px;"></i>Recommended Activities</div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">${generateActivityTags(d.top_activities)}</div>
        </div>
        <div style="padding:0 30px 20px;background:#fff;">
            <div style="background:linear-gradient(135deg,#fdf0dc,#fff8f2);padding:16px;border-radius:6px;border:1px dashed #c9a96e;display:flex;gap:14px;align-items:flex-start;">
                <i class="fas fa-lightbulb" style="font-size:22px;color:#c9a96e;flex-shrink:0;margin-top:2px;"></i>
                <div>
                    <div style="font-weight:bold;color:#3b1f2b;margin-bottom:5px;font-size:13px;">Travel Tip</div>
                    <div style="color:#6b5b4f;font-style:italic;font-size:13px;line-height:1.6;">${esc(d.travel_tip)}</div>
                </div>
            </div>
        </div>
        <div style="padding:14px 30px;background:#f8f4f0;font-size:10px;color:#6b5b4f;line-height:1.6;border-top:1px solid #e2d5c7;">
            <div style="display:flex;flex-wrap:wrap;gap:16px;margin-bottom:8px;">
                <span><i class="fas fa-check-circle" style="color:#c9a96e;"></i> 24/7 Customer Support</span>
                <span><i class="fas fa-check-circle" style="color:#c9a96e;"></i> Price Match Guarantee</span>
                <span><i class="fas fa-check-circle" style="color:#c9a96e;"></i> Free Cancellation*</span>
                <span><i class="fas fa-check-circle" style="color:#c9a96e;"></i> AI-Powered Recommendations</span>
            </div>
            All prices are estimates and subject to change. Taxes are approximate. *Cancellation policy varies by provider.
        </div>
        <div style="padding:10px 30px;background:#3b1f2b;display:flex;justify-content:space-between;font-size:9px;color:#d4c4b0;">
            <span>Smart Booking AI · smartbooking.com</span>
            <span>Ref: ${dates.bookingRef}</span>
            <span>Page 1 of 1</span>
        </div>
    </div>`;
}

function printReceipt() {
    const receiptContent = document.getElementById('receiptContent');
    if (!receiptContent) return;
    const html = receiptContent.innerHTML;
    const win = window.open('', '_blank', 'width=820,height=1050');
    if (win) {
        win.document.write(`<!DOCTYPE html><html><head>
            <title>Trip Receipt — Smart Booking</title>
            <link rel="stylesheet" href="https:
            <style>*{box-sizing:border-box;margin:0;padding:0;}body{font-family:'Georgia',serif;background:#f5f0eb;padding:24px;color:#2c2c2c;}@media print{body{background:#fff;padding:0;}}</style>
        </head><body>${html}</body></html>`);
        win.document.close();
        win.focus();
        setTimeout(() => { win.print(); win.close(); }, 500);
    }
}

function printSelectedReceipt() {
    if (!selectedDest) {
        showActionError('Select a destination before printing.');
        return;
    }

    openReceipt();
    setTimeout(() => printReceipt(), 100);
}

async function downloadReceiptPdf() {
    if (!selectedDest) {
        showActionError('Select a destination before saving a PDF.');
        return;
    }
    const jsPDF = window.jspdf?.jsPDF || JsPdfModule;
    if (!jsPDF) { printReceipt(); return; }
    const d = selectedDest;
    const cost = d.costBreakdown || calculateCostBreakdown(d, lastPayload || {});
    const bk = cost.breakdown;
    const dates = getFormattedDates();
    const doc = new jsPDF({ unit: 'mm', format: 'a4' });
    const pW = doc.internal.pageSize.getWidth();
    const pH = doc.internal.pageSize.getHeight();
    const mg = 18;
    const cW = pW - mg * 2;
    const deep = [59, 31, 43], gold = [201, 169, 110], muted = [107, 91, 79];
    const cream = [248, 244, 240], green = [44, 94, 44], lightGreen = [232, 244, 232];

    let logoBase64 = null;
    try {
        const resp = await fetch('/img/travel-icon.png');
        const blob = await resp.blob();
        const raw = await new Promise(res => { const r = new FileReader(); r.onloadend = () => res(r.result); r.readAsDataURL(blob); });
        const img = await new Promise((res, rej) => { const el = new Image(); el.onload = () => res(el); el.onerror = rej; el.src = raw; });
        const canvas = document.createElement('canvas');
        canvas.width = img.naturalWidth || 256; canvas.height = img.naturalHeight || 256;
        const ctx = canvas.getContext('2d'); ctx.drawImage(img, 0, 0);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;
        for (let i = 0; i < data.length; i += 4) { if (data[i+3] > 0) data[i] = data[i+1] = data[i+2] = 255; }
        ctx.putImageData(imageData, 0, 0);
        logoBase64 = canvas.toDataURL('image/png');
    } catch (_) {}

    let y = 0;
    function wrap(text, maxW, size) { doc.setFontSize(size); return doc.splitTextToSize(String(text || ''), maxW); }
    function newPageIfNeeded(needed) { if (y + needed > pH - 16) { doc.addPage(); addPageFooter(); y = 14; } }
    function addPageFooter() {
        doc.setFillColor(...deep); doc.rect(0, pH - 10, pW, 10, 'F');
        doc.setFontSize(7); doc.setTextColor(...gold);
        doc.text(`Smart Booking AI  ·  Ref: ${dates.bookingRef}`, pW / 2, pH - 3, { align: 'center' });
    }

    doc.setFillColor(...deep); doc.rect(0, 0, pW, 34, 'F');
    if (logoBase64) doc.addImage(logoBase64, 'PNG', mg, 4, 26, 26);
    doc.setFont('helvetica','bold'); doc.setFontSize(18); doc.setTextColor(...gold);
    doc.text('SMART BOOKING', mg + 30, 16);
    doc.setFont('helvetica','normal'); doc.setFontSize(8); doc.setTextColor(212, 196, 176);
    doc.text('AI-Powered Travel Planning  ·  smartbooking.com', mg + 30, 23);
    y = 34;

    doc.setFillColor(70, 40, 55); doc.rect(0, y, pW, 14, 'F');
    doc.setFont('helvetica','bold'); doc.setFontSize(9); doc.setTextColor(...gold);
    doc.text(`Ref: ${dates.bookingRef}`, mg, y + 9);
    doc.setFont('helvetica','normal'); doc.setFontSize(8); doc.setTextColor(212, 196, 176);
    doc.text(`Issued: ${dates.issueDate}  ·  ${dates.issueTime}`, pW - mg, y + 9, { align: 'right' });
    y += 14;

    doc.setFillColor(...gold); doc.rect(0, y, pW, 20, 'F');
    doc.setFont('helvetica','bold'); doc.setFontSize(18); doc.setTextColor(...deep);
    doc.text(String(d.destination), mg, y + 13);
    doc.setFont('helvetica','normal'); doc.setFontSize(9);
    doc.text(String(d.country).toUpperCase() + '  ·  ' + generateAirportCode(d.destination), pW - mg, y + 13, { align: 'right' });
    y += 20;

    const mood = (lastPayload.mood || '').charAt(0).toUpperCase() + (lastPayload.mood || '').slice(1);
    const cards2 = [['Mood',mood],['Duration',((lastPayload.duration && !isNaN(Number(lastPayload.duration))) ? `${lastPayload.duration} days` : (durLabels[lastPayload.duration]||lastPayload.duration||'Not set'))+` (${bk.accommodation.nights}n)`],['Companion',compLabels[lastPayload.companion]||lastPayload.companion||'Not set'],['Budget',budgetLabels[lastPayload.budget]||lastPayload.budget||'Not set']];
    const cardW = cW / 4 - 2;
    y += 5;
    cards2.forEach(([label, val], i) => {
        const cx = mg + i * (cardW + 2.7);
        doc.setFillColor(...cream); doc.roundedRect(cx, y, cardW, 18, 2, 2, 'F');
        doc.setFont('helvetica','bold'); doc.setFontSize(7); doc.setTextColor(...gold);
        doc.text(label.toUpperCase(), cx + cardW/2, y+6, { align: 'center' });
        doc.setFont('helvetica','normal'); doc.setFontSize(8); doc.setTextColor(...deep);
        const vlines = wrap(val, cardW - 4, 8);
        if (vlines[0]) doc.text(vlines[0], cx + cardW/2, y+13, { align: 'center' });
    });
    y += 24;

    function sectionTitle(title) {
        newPageIfNeeded(12);
        doc.setFillColor(...gold); doc.rect(mg, y, 3, 7, 'F');
        doc.setFont('helvetica','bold'); doc.setFontSize(10); doc.setTextColor(...deep);
        doc.text(title, mg + 6, y + 5.5); y += 10;
    }

    function costRow(label, amount, desc, detail) {
        newPageIfNeeded(20);
        doc.setFillColor(...cream); doc.roundedRect(mg, y, cW, 18, 2, 2, 'F');
        doc.setFont('helvetica','bold'); doc.setFontSize(9); doc.setTextColor(...deep);
        doc.text(label, mg + 6, y + 7); doc.text(String(amount), mg + cW - 2, y + 7, { align: 'right' });
        doc.setFont('helvetica','normal'); doc.setFontSize(7.5); doc.setTextColor(...muted);
        doc.text(String(desc), mg + 6, y + 12.5);
        doc.setTextColor(...gold);
        const detailLines = wrap(detail, cW - 14, 7.5);
        if (detailLines[0]) doc.text(detailLines[0], mg + 6, y + 16.5);
        y += 21;
    }

    sectionTitle('Cost Estimation Breakdown');
    costRow('Flights', fmtCurrencyForPDF(bk.flights.amount), bk.flights.description, bk.flights.details);
    costRow('Accommodation', fmtCurrencyForPDF(bk.accommodation.amount), bk.accommodation.description, `${bk.accommodation.nights} nights`);
    costRow('Activities & Tours', fmtCurrencyForPDF(bk.activities.amount), bk.activities.description, bk.activities.items);
    costRow('Food & Dining', fmtCurrencyForPDF(bk.food.amount), bk.food.description, `~${fmtCurrencyForPDF(bk.food.perDay)}/day`);
    costRow('Local Transport', fmtCurrencyForPDF(bk.transportation.amount), bk.transportation.description, bk.transportation.includes.join(' · '));

    y += 2; doc.setDrawColor(...gold); doc.setLineWidth(0.4); doc.line(mg, y, mg + cW, y); y += 5;
    [['Subtotal',fmtCurrencyForPDF(cost.subtotal)],[`Taxes (${cost.taxes.rate}% ${cost.taxes.type})`,fmtCurrencyForPDF(cost.taxes.amount)],['Service Fee',fmtCurrencyForPDF(cost.serviceFee.amount)]].forEach(([l,v]) => {
        newPageIfNeeded(7);
        doc.setFont('helvetica','normal'); doc.setFontSize(9); doc.setTextColor(...muted);
        doc.text(l, mg + 4, y); doc.setTextColor(...deep); doc.text(v, mg + cW - 2, y, { align: 'right' }); y += 7;
    });

    newPageIfNeeded(22); y += 3;
    doc.setFillColor(...deep); doc.roundedRect(mg, y, cW, 20, 3, 3, 'F');
    doc.setFont('helvetica','normal'); doc.setFontSize(9); doc.setTextColor(...gold);
    doc.text('TOTAL PER PERSON (ESTIMATED)', mg + 5, y + 8);
    doc.setFontSize(8); doc.setTextColor(212, 196, 176);
    // Create range display with correct currency for PDF
    const rangeDisplay = `${fmtCurrencyForPDF(cost.range.low)} to ${fmtCurrencyForPDF(cost.range.high)}`;
    doc.text(`Range: ${rangeDisplay}`, mg + 5, y + 14);
    doc.setFont('helvetica','bold'); doc.setFontSize(18); doc.setTextColor(...gold);
    doc.text(fmtCurrencyForPDF(cost.total), mg + cW - 3, y + 14, { align: 'right' });
    y += 25;

    newPageIfNeeded(28);
    doc.setFillColor(...lightGreen); doc.roundedRect(mg, y, cW, 24, 2, 2, 'F');
    doc.setFont('helvetica','bold'); doc.setFontSize(9); doc.setTextColor(...green);
    doc.text('Available Discounts', mg + 5, y + 8);
    [['Early Bird',cost.savings.earlyBird],['Group Discount',cost.savings.groupDiscount],['Package Deal',cost.savings.packageDeal]].forEach(([l,v],i) => {
        const sx = mg + i * (cW/3) + (cW/3)/2;
        doc.setFont('helvetica','bold'); doc.setFontSize(10); doc.setTextColor(...green);
        doc.text(fmtCurrencyForPDF(v), sx, y+16, { align: 'center' });
        doc.setFont('helvetica','normal'); doc.setFontSize(7); doc.setTextColor(...muted);
        doc.text(l, sx, y+21, { align: 'center' });
    });
    y += 28;

    sectionTitle('Flight Information');
    newPageIfNeeded(18);
    doc.setFillColor(...cream); doc.roundedRect(mg, y, cW, 16, 2, 2, 'F');
    doc.setFillColor(...gold); doc.rect(mg, y, 3, 16, 'F');
    doc.setFont('helvetica','normal'); doc.setFontSize(9); doc.setTextColor(...deep);
    doc.text(wrap(d.flight_info, cW - 10, 9), mg + 7, y + 7);
    y += 22;

    sectionTitle('Recommended Activities');
    if (d.top_activities) {
        const acts = d.top_activities.split(',').map(a => a.trim());
        let ax = mg, lineY = y;
        acts.forEach(act => {
            const tw = doc.getTextWidth(act) + 10;
            if (ax + tw > mg + cW) { ax = mg; lineY += 10; }
            newPageIfNeeded(10);
            doc.setFillColor(...cream); doc.roundedRect(ax, lineY-4, tw, 8, 2, 2, 'F');
            doc.setDrawColor(...gold); doc.setLineWidth(0.3); doc.roundedRect(ax, lineY-4, tw, 8, 2, 2, 'S');
            doc.setFont('helvetica','normal'); doc.setFontSize(8); doc.setTextColor(...deep);
            doc.text(act, ax + tw/2, lineY+1, { align: 'center' });
            ax += tw + 4;
        });
        y = lineY + 12;
    }

    sectionTitle('Travel Tip');
    newPageIfNeeded(22);
    doc.setFillColor(253, 240, 220);
    const tipLines = wrap(d.travel_tip, cW - 14, 9);
    const tipH = tipLines.length * 5 + 10;
    doc.roundedRect(mg, y, cW, tipH, 2, 2, 'F');
    doc.setDrawColor(...gold); doc.setLineWidth(0.4); doc.roundedRect(mg, y, cW, tipH, 2, 2, 'S');
    doc.setFont('helvetica','italic'); doc.setFontSize(9); doc.setTextColor(...muted);
    doc.text(tipLines, mg + 7, y + 7);
    y += tipH + 6;

    newPageIfNeeded(16);
    doc.setFillColor(...cream); doc.rect(0, y, pW, 14, 'F');
    doc.setFont('helvetica','normal'); doc.setFontSize(7); doc.setTextColor(...muted);
    doc.text('All prices are estimates and subject to change based on seasonality, availability, and booking dates. *Cancellation policy varies by provider.', pW/2, y+5, { align: 'center', maxWidth: cW });
    doc.text('Valid Until: ' + dates.validUntil, pW/2, y+11, { align: 'center' });
    y += 14;

    addPageFooter();
    doc.save(`trip-receipt-${String(d.destination).toLowerCase().replace(/\s+/g, '-')}.pdf`);
}

function esc(str) {
    return String(str || '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function init() {
    // ── Step navigation ───────────────────────────────────────────────────────
    document.getElementById('step1NextBtn')?.addEventListener('click', () => goStep(2));
    document.getElementById('step2BackBtn')?.addEventListener('click', () => goStep(1));
    document.getElementById('step2NextBtn')?.addEventListener('click', () => goStep(3));
    document.getElementById('step3BackBtn')?.addEventListener('click', () => goStep(2));
    document.getElementById('step3FindBtn')?.addEventListener('click', () => generateSuggestions());
    document.getElementById('step4BackBtn')?.addEventListener('click', () => goStep(3));
    document.getElementById('step4RegenerateBtn')?.addEventListener('click', () => generateSuggestions());

    // ── Mood cards ────────────────────────────────────────────────────────────
    document.querySelectorAll('.mood-card').forEach(card => {
        card.addEventListener('click', () => selectMood(card));
    });

    // ── Custom mood ───────────────────────────────────────────────────────────
    document.getElementById('btnAddMood')?.addEventListener('click', () => submitCustomMood());
    document.getElementById('customMoodInput')?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); submitCustomMood(); }
    });

    // ── Surprise Me ───────────────────────────────────────────────────────────
    document.getElementById('surpriseMeBtn')?.addEventListener('click', () => window.surpriseMe());

    // ── Duration sync ─────────────────────────────────────────────────────────
    document.getElementById('durationDays')?.addEventListener('input', function() {
        const hidden = document.getElementById('duration');
        if (hidden) hidden.value = this.value;
    });

    // ── Receipt modal ─────────────────────────────────────────────────────────
    document.getElementById('receiptBtn')?.addEventListener('click', () => openReceipt());
    document.getElementById('quickPrintBtn')?.addEventListener('click', () => printSelectedReceipt());
    document.getElementById('quickPdfBtn')?.addEventListener('click', () => downloadReceiptPdf());
    document.getElementById('closeReceiptBtn')?.addEventListener('click', () => closeReceipt());
    document.getElementById('closeReceiptBtn2')?.addEventListener('click', () => closeReceipt());
    document.getElementById('printReceiptBtn')?.addEventListener('click', () => printReceipt());
    document.getElementById('downloadPdfBtn')?.addEventListener('click', () => downloadReceiptPdf());

    const receiptModal = document.getElementById('receiptModal');
    if (receiptModal) {
        receiptModal.addEventListener('click', function(e) { if (e.target === this) closeReceipt(); });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('receiptModal');
            if (modal && modal.classList.contains('open')) closeReceipt();
        }
    });

    // ── Save to dashboard ─────────────────────────────────────────────────────
    document.getElementById('saveBtn')?.addEventListener('click', () => saveTripToDashboard());

    // ── Hero tagline rotation ─────────────────────────────────────────────────
    const tagline = document.getElementById('heroTagline');
    if (tagline) {
        tagline.textContent = HERO_TAGLINES[Math.floor(Math.random() * HERO_TAGLINES.length)];
    }

    // ── Load community moods ──────────────────────────────────────────────────
    loadCommunityMoods();
    applyPlanTripPrefill();
}

if (document.readyState !== 'loading') init();
else document.addEventListener('DOMContentLoaded', init);

window.surpriseMe = function() {
    const moods = ['adventurous', 'relaxed', 'cultural', 'romantic', 'foodie', 'eco-travel'];
    const budgets = ['backpacker', 'budget', 'mid', 'premium'];
    const durations = ['weekend', 'week', 'two_weeks'];
    const companions = ['solo', 'couple', 'friends_small'];
    const regions = ['europe', 'southeast_asia', 'africa', 'latin_america', 'east_asia', 'middle_east'];

    // Step 1 — pick and visually select a random mood
    const randomMood = moods[Math.floor(Math.random() * moods.length)];
    const moodCard = document.querySelector('.mood-card[data-mood="' + randomMood + '"]');
    if (moodCard) {
        // Make sure we're on step 1 first
        goStep(1);
        // Scroll to mood grid
        moodCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        // Flash highlight then select
        moodCard.style.transform = 'scale(1.08)';
        moodCard.style.boxShadow = '0 0 0 3px var(--gold)';
        setTimeout(function() {
            moodCard.style.transform = '';
            moodCard.style.boxShadow = '';
            selectMood(moodCard);
        }, 400);
    }

    // Set random values for steps 2 & 3
    const chosenBudget   = budgets[Math.floor(Math.random() * budgets.length)];
    const chosenDuration = durations[Math.floor(Math.random() * durations.length)];
    const chosenCompanion = companions[Math.floor(Math.random() * companions.length)];
    const chosenRegion   = regions[Math.floor(Math.random() * regions.length)];

    const budgetEl    = document.getElementById('budget');
    const durationEl  = document.getElementById('duration');
    const companionEl = document.getElementById('companion');
    const regionEl    = document.getElementById('region');

    if (budgetEl) {
        budgetEl.value = chosenBudget;
        budgetEl.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (durationEl) {
        durationEl.value = chosenDuration;
        durationEl.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (companionEl) {
        companionEl.value = chosenCompanion;
        companionEl.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (regionEl) {
        regionEl.value = chosenRegion;
        regionEl.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Build a readable summary of what was chosen
    const moodLabels = {
        adventurous: 'Adventurous', relaxed: 'Relaxed', cultural: 'Cultural',
        romantic: 'Romantic', foodie: 'Foodie', 'eco-travel': 'Eco-Travel'
    };
    // use module-level getBudgetLabel so currency is respected
    const durationLabels = {
        weekend: 'Long Weekend (3 to 4 days)', week: 'One Week', two_weeks: 'Two Weeks'
    };
    const companionLabels = {
        solo: 'Solo', couple: 'Couple', friends_small: 'Small Group of Friends'
    };
    const regionLabels = {
        europe: 'Europe', southeast_asia: 'Southeast Asia', africa: 'Africa',
        latin_america: 'Latin America', east_asia: 'East Asia', middle_east: 'Middle East'
    };

    // Show a summary panel on step 1 before moving on
    let summaryEl = document.getElementById('surpriseSummary');
    if (!summaryEl) {
        summaryEl = document.createElement('div');
        summaryEl.id = 'surpriseSummary';
        summaryEl.className = 'surprise-summary';
        const btnRow = document.querySelector('#step1 .btn-row');
        if (btnRow) btnRow.parentNode.insertBefore(summaryEl, btnRow);
    }

    summaryEl.innerHTML =
        '<div class="surprise-summary-title"><i class="fas fa-dice"></i> Your Surprise Trip</div>'
        + '<div class="surprise-summary-grid">'
        + '<div class="surprise-item"><i class="fas fa-heart"></i><span><strong>Mood</strong>' + (moodLabels[randomMood] || randomMood) + '</span></div>'
        + '<div class="surprise-item"><i class="fas fa-wallet"></i><span><strong>Budget</strong>' + (budgetLabels[chosenBudget] || chosenBudget) + '</span></div>'
        + '<div class="surprise-item"><i class="fas fa-clock"></i><span><strong>Duration</strong>' + (durationLabels[chosenDuration] || chosenDuration) + '</span></div>'
        + '<div class="surprise-item"><i class="fas fa-users"></i><span><strong>Travelling as</strong>' + (companionLabels[chosenCompanion] || chosenCompanion) + '</span></div>'
        + '<div class="surprise-item"><i class="fas fa-globe"></i><span><strong>Region</strong>' + (regionLabels[chosenRegion] || chosenRegion) + '</span></div>'
        + '</div>'
        + '<div class="surprise-summary-actions">'
        + '<button class="secondary-button" id="surpriseChangeBtn"><i class="fas fa-redo"></i> Change</button>'
        + '<button class="primary-button" id="surpriseAcceptBtn"><i class="fas fa-magic"></i> Find My Destinations</button>'
        + '</div>';

    document.getElementById('surpriseChangeBtn')?.addEventListener('click', () => window.clearSurprise());
    document.getElementById('surpriseAcceptBtn')?.addEventListener('click', () => window.acceptSurprise());

    summaryEl.style.display = 'block';
    summaryEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
};

window.acceptSurprise = function() {
    generateSuggestions();
};

window.clearSurprise = function() {
    const summaryEl = document.getElementById('surpriseSummary');
    if (summaryEl) summaryEl.style.display = 'none';
    // Deselect mood cards
    document.querySelectorAll('.mood-card.selected').forEach(function(c) { c.classList.remove('selected'); });
    document.querySelectorAll('.mood-pill.selected').forEach(function(p) { p.classList.remove('selected'); });
    setSelectedMood('', '');
    // Reset selects
    ['budget', 'duration', 'companion', 'region'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) el.selectedIndex = 0;
    });
};

// Animated loading messages
const LOADING_MESSAGES = [
    'Scanning 195 countries for your perfect match...',
    'Consulting the travel gods...',
    'Checking visa requirements and flight routes...',
    'Asking locals for their secret spots...',
    'Calculating the vibe-to-budget ratio...',
    'Filtering out tourist traps...',
    'Finding hidden gems just for you...',
    'Almost there — good things take a moment...',
];

const TRAVEL_FACTS = [
    '🌍 Did you know? The shortest commercial flight is just 57 seconds long (Scotland).',
    '✈️ Fun fact: Airlines lose about 25 million bags per year. Always pack essentials in carry-on.',
    '🏨 Pro tip: Booking Tuesday–Wednesday is usually 15% cheaper than weekends.',
    '🌊 The Maldives has the lowest elevation of any country — just 1.5m above sea level.',
    '🎒 Packing tip: Roll your clothes instead of folding — fits 30% more in your bag.',
    '🗺️ The world\'s longest road trip is from Cape Town to Magadan — 22,000km.',
    '🌅 Golden hour is 30 minutes after sunrise and before sunset — best photos of your life.',
    '🍜 Street food is often the safest bet — high turnover means fresh ingredients.',
    '🏔️ Altitude sickness can hit at just 2,500m. Acclimatise before big hikes.',
    '💡 Offline maps save lives. Download Google Maps for your destination before you go.',
];

let loadingMsgInterval = null;
let loadingMsgIndex = 0;

function startLoadingAnimation() {
    const msgEl = document.getElementById('loadingMsg');
    const factEl = document.getElementById('loadingFunFact');

    loadingMsgIndex = 0;
    if (msgEl) msgEl.textContent = LOADING_MESSAGES[0];
    if (factEl) {
        const fact = TRAVEL_FACTS[Math.floor(Math.random() * TRAVEL_FACTS.length)];
        factEl.innerHTML = fact;
        factEl.style.opacity = '0';
        setTimeout(function() { factEl.style.opacity = '1'; }, 500);
    }

    loadingMsgInterval = setInterval(function() {
        loadingMsgIndex = (loadingMsgIndex + 1) % LOADING_MESSAGES.length;
        if (msgEl) {
            msgEl.style.opacity = '0';
            setTimeout(function() {
                msgEl.textContent = LOADING_MESSAGES[loadingMsgIndex];
                msgEl.style.opacity = '1';
            }, 200);
        }
    }, 2000);
}

function stopLoadingAnimation() {
    if (loadingMsgInterval) {
        clearInterval(loadingMsgInterval);
        loadingMsgInterval = null;
    }
}

// Rotating hero taglines
const HERO_TAGLINES = [
    'Let AI build the perfect itinerary tailored to your mood, budget, and style.',
    'From weekend escapes to month-long adventures — we\'ve got you covered.',
    'Tell us how you feel. We\'ll tell you where to go.',
    'Your next great story starts with a single search.',
    'Forget the spreadsheets. Just tell us your vibe.',
];

(function initPlanTrip() {
    // Rotate hero tagline
    const tagline = document.getElementById('heroTagline');
    if (tagline) {
        tagline.textContent = HERO_TAGLINES[Math.floor(Math.random() * HERO_TAGLINES.length)];
    }
    // saveBtn listener is already added in init() — do not add it again here
})();

// Patch generateSuggestions to use animated loading
const _origGenerate = window.generateSuggestions;
window.generateSuggestions = async function() {
    startLoadingAnimation();
    try {
        await _origGenerate();
    } finally {
        stopLoadingAnimation();
    }
};

// Update budget dropdown labels when currency changes
function updateBudgetDropdowns() {
    var selects = document.querySelectorAll('select#budget, select#budgetSelect');
    selects.forEach(function(sel) {
        var currentVal = sel.value;
        BUDGET_TIERS.forEach(function(tier) {
            var el = sel.querySelector('option[value="' + tier.value + '"]');
            if (el) el.textContent = getBudgetLabel(tier.value);
        });
        sel.value = currentVal;
    });
}

// Run on load and on currency change — poll until Currency is ready
(function() {
    function tryRegister() {
        if (typeof window.Currency === 'undefined') { setTimeout(tryRegister, 100); return; }
        // Update immediately with current currency
        updateBudgetDropdowns();
        // Update whenever currency changes
        window.Currency.onCurrencyChange(function() {
            updateBudgetDropdowns();
            // Re-render results if visible
            if (lastResults && lastResults.length) {
                lastResults = lastResults.map(function(dest) {
                    return Object.assign({}, dest, { costBreakdown: calculateCostBreakdown(dest, lastPayload) });
                });
                renderResults(lastResults);
            }
        });
        // Also update when rates are fetched (currency module fires refreshAllPrices after fetch)
        var _origRefresh = window.Currency.refreshAllPrices;
        if (_origRefresh) {
            window.Currency.refreshAllPrices = function() {
                _origRefresh();
                updateBudgetDropdowns();
            };
        }
    }
    if (document.readyState !== 'loading') tryRegister();
    else document.addEventListener('DOMContentLoaded', tryRegister);
})();

document.addEventListener('currency:changed', function() { if (window.Currency) window.Currency.refresh(); });

const { jsPDF } = window.jspdf;

let selectedMood = '';
let lastResults = [];
let lastPayload = {};
let selectedDest = null;

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

const budgetLabels = {
    backpacker: 'Backpacker (Under $500)',
    budget: 'Budget-Friendly ($500–$1,500)',
    mid: 'Mid-Range ($1,500–$4,000)',
    premium: 'Premium ($4,000–$8,000)',
    luxury: 'Luxury ($8,000+)'
};

const durLabels = {
    weekend: 'Long Weekend (3–4 days)',
    week: 'One Week (7 days)',
    two_weeks: 'Two Weeks (10–14 days)',
    month: 'One Month or more',
    flexible: 'Flexible / Open-ended'
};

const compLabels = {
    solo: 'Solo Traveller',
    couple: 'Couple',
    family_young: 'Family with Young Children',
    family_teens: 'Family with Teenagers',
    friends_small: 'Small Group of Friends (2–4)',
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
    glamping: 'Glamping / Eco-Lodge',
    any: 'No preference'
};

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.mood-card').forEach(card => {
        card.addEventListener('click', function() { selectMood(this); });
    });

    const receiptModal = document.getElementById('receiptModal');
    if (receiptModal) {
        receiptModal.addEventListener('click', function(e) {
            if (e.target === this) closeReceipt();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('receiptModal');
            if (modal && modal.classList.contains('open')) {
                closeReceipt();
            }
        }
    });

    const saveBtn = document.getElementById('saveBtn');
    if (saveBtn) saveBtn.addEventListener('click', saveTripToDashboard);
});

function selectMood(el) {
    document.querySelectorAll('.mood-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    selectedMood = el.dataset.mood;
    /* Keep hidden input + global bridge in sync so community/custom moods also work */
    const hiddenInput = document.getElementById('selectedMoodValue');
    if (hiddenInput) hiddenInput.value = selectedMood;
    window.__planTripMood = selectedMood;
}

function goStep(n) {
    [1, 2, 3, 4].forEach(i => {
        const step = document.getElementById('step' + i);
        const si = document.getElementById('si' + i);
        if (step) step.style.display = 'none';
        if (si) si.classList.remove('active', 'done');
    });
    for (let i = 1; i < n; i++) {
        const si = document.getElementById('si' + i);
        if (si) si.classList.add('done');
    }
    const currentSi = document.getElementById('si' + n);
    if (currentSi) currentSi.classList.add('active');
    const currentStep = document.getElementById('step' + n);
    if (currentStep) currentStep.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function generateSuggestions() {
    /* Hidden input is the single source of truth — written by card clicks,
       community pill clicks, and custom mood add (all three paths). */
    const hiddenInput = document.getElementById('selectedMoodValue');
    selectedMood = (hiddenInput && hiddenInput.value.trim()) ? hiddenInput.value.trim() : selectedMood;

    if (!selectedMood) {
        alert('Please select or add a mood first.');
        goStep(1);
        return;
    }

    selectedDest = null;

    const receiptBtn = document.getElementById('receiptBtn');
    if (receiptBtn) receiptBtn.style.display = 'none';

    const saveBtn = document.getElementById('saveBtn');
    if (saveBtn) saveBtn.style.display = 'none';

    goStep(4);

    const loadingState = document.getElementById('loadingState');
    const errorState = document.getElementById('errorState');
    const resultsState = document.getElementById('resultsState');

    if (loadingState) loadingState.style.display = 'block';
    if (errorState) errorState.style.display = 'none';
    if (resultsState) resultsState.style.display = 'none';

    const feelingNoteElement = document.getElementById('feelingNote');
    const budgetElement = document.getElementById('budget');
    const durationElement = document.getElementById('duration');
    const companionElement = document.getElementById('companion');
    const monthElement = document.getElementById('month');
    const regionElement = document.getElementById('region');
    const accommodationElement = document.getElementById('accommodation');
    const originElement = document.getElementById('origin');
    const experienceElement = document.getElementById('experience');

    lastPayload = {
        mood: selectedMood,
        feeling_note: (feelingNoteElement && feelingNoteElement.value) ? feelingNoteElement.value.trim() : null,
        budget: budgetElement ? budgetElement.value : null,
        duration: durationElement ? durationElement.value : null,
        companion: companionElement ? companionElement.value : null,
        month: (monthElement && monthElement.value) ? monthElement.value : null,
        region: (regionElement && regionElement.value) ? regionElement.value : null,
        accommodation: (accommodationElement && accommodationElement.value) ? accommodationElement.value : null,
        origin: (originElement && originElement.value.trim()) ? originElement.value.trim() : null,
        experience: (experienceElement && experienceElement.value) ? experienceElement.value : null,
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
        if (loadingState) loadingState.style.display = 'none';

        if (!json.success) {
            if (errorState) {
                errorState.textContent = json.message || 'Something went wrong.';
                errorState.style.display = 'block';
            }
            return;
        }

        lastResults = json.data.map(dest => ({
            ...dest,
            costBreakdown: calculateCostBreakdown(dest, lastPayload)
        }));
        renderResults(lastResults);

    } catch (err) {
        if (loadingState) loadingState.style.display = 'none';
        if (errorState) {
            errorState.textContent = 'Network error: ' + err.message;
            errorState.style.display = 'block';
        }
    }
}

function calculateCostBreakdown(destination, payload) {
    const region = payload.region || 'any';
    const baseCost = REGION_BASE_COSTS[region] || 2000;

    const budgetMult = COST_MULTIPLIERS.budget[payload.budget] || 1.0;
    const durationMult = COST_MULTIPLIERS.duration[payload.duration] || 1.0;
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
            display: `$${fmt(Math.round(total * 0.85))} – $${fmt(Math.round(total * 1.15))}`
        },
        savings: {
            earlyBird: Math.round(total * 0.10),
            groupDiscount: (payload.companion || '').includes('family') || (payload.companion || '').includes('friends') ? Math.round(total * 0.08) : 0,
            packageDeal: Math.round(total * 0.05)
        }
    };
}

function fmt(n) { return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

function getFlightDetails(origin, dest) {
    if (!origin) return 'Based on average prices from major hubs';
    const airlines = ['Emirates', 'Qatar Airways', 'Singapore Airlines', 'British Airways', 'Lufthansa', 'Delta', 'United'];
    const airline = airlines[Math.floor(Math.random() * airlines.length)];
    const hrs = Math.floor(Math.random() * 6) + 8;
    return `${airline} · ~${hrs}h from ${origin}`;
}

function getAccommodationDescription(p) {
    const d = { hostel: 'Shared dormitory in central location', budget_hotel: '2–3 star hotel with breakfast', boutique: 'Boutique hotel with local character', resort: 'All-inclusive resort', villa: 'Private villa with pool', airbnb: 'Private apartment with kitchen', glamping: 'Eco-lodge with unique experience', any: 'Mix of comfortable accommodations' };
    return d[p] || d.any;
}

function getNightsFromDuration(d) { return { weekend: 3, week: 7, two_weeks: 12, month: 28, flexible: 7 }[d] || 7; }

function getActivityCount(a) { if (!a) return 'Multiple activities'; return `${a.split(',').length} included activities`; }

function generateAirportCode(dest) { const w = dest.split(' '); return (w.length === 1 ? dest.substring(0, 3) : w.map(x => x[0]).join('').substring(0, 3)).toUpperCase(); }

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
                <div class="country"><i class="fas fa-globe" style="margin-right:4px;"></i>${esc(d.country)}</div>
            </div>
            <p>${esc(d.description)}</p>
            <div class="dest-cost">
                <i class="fas fa-wallet" style="color:var(--gold);margin-right:6px;"></i>
                <span>${d.costBreakdown.range.display}</span>
                <span style="font-size:12px;color:var(--text-muted);display:block;margin-top:4px;">per person</span>
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
    if (resultsState) resultsState.style.display = 'block';
}

function selectDestination(idx) {
    document.querySelectorAll('.dest-card').forEach(c => c.classList.remove('selected'));
    const cards = document.querySelectorAll('.dest-card');
    if (cards[idx]) cards[idx].classList.add('selected');
    selectedDest = lastResults[idx];

    const receiptBtn = document.getElementById('receiptBtn');
    if (receiptBtn) receiptBtn.style.display = 'inline-flex';

    const saveBtn = document.getElementById('saveBtn');
    if (saveBtn) {
        saveBtn.style.display = 'inline-flex';
        saveBtn.innerHTML = '<i class="fas fa-bookmark"></i> Save to Dashboard';
        saveBtn.disabled = false;
    }

    if (receiptBtn) receiptBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

async function saveTripToDashboard() {
    if (!selectedDest) return;

    const btn = document.getElementById('saveBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
    }

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
        estimated_cost: (selectedDest.costBreakdown && selectedDest.costBreakdown.total) ? selectedDest.costBreakdown.total : null,
    };
    console.log('[plan-trip] POST /api/trips payload:', payload);

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
        const data = await res.json();
        console.log('[plan-trip] POST /api/trips response:', res.status, data);

        if (res.status === 409) {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-bookmark"></i> Already Saved';
            }
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
            if (btn) { btn.innerHTML = '<i class="fas fa-check"></i> Saved!'; }
            try {
                localStorage.setItem('smartBookingTripSaved', JSON.stringify({
                    ts: Date.now(),
                    destination: selectedDest.destination,
                    country: selectedDest.country || null,
                }));
                localStorage.setItem('smartBookingTripProfile', JSON.stringify({
                    mood: lastPayload.mood || null,
                    budget: lastPayload.budget || null,
                    accommodation: lastPayload.accommodation || null,
                    region: lastPayload.region || null,
                    feeling_note: lastPayload.feeling_note || null,
                }));
            } catch (_) {}
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
        console.error('[plan-trip] saveTripToDashboard error:', err);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-bookmark"></i> Save to Dashboard';
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: 'Error', text: err.message || 'Could not save trip. Please try again.', icon: 'error', confirmButtonColor: '#c9a96e' });
        }
    }
}

function openReceipt() {
    if (!selectedDest) return;
    const receiptContent = document.getElementById('receiptContent');
    if (receiptContent) {
        receiptContent.innerHTML = buildReceiptHTML(selectedDest);
    }
    const receiptModal = document.getElementById('receiptModal');
    if (receiptModal) {
        receiptModal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closeReceipt() {
    const receiptModal = document.getElementById('receiptModal');
    if (receiptModal) {
        receiptModal.classList.remove('open');
        document.body.style.overflow = '';
    }
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
        `<span style="background:#f8f4f0;padding:6px 12px;border-radius:20px;font-size:12px;color:#3b1f2b;border:1px solid #c9a96e;display:inline-flex;align-items:center;gap:5px;">
            <i class="fas fa-tag" style="color:#c9a96e;font-size:10px;"></i>${esc(a)}
        </span>`
    ).join('');
}

function buildReceiptHTML(d) {
    const dates = getFormattedDates();
    const cost = d.costBreakdown;
    const bk = cost.breakdown;

    const mood = (lastPayload.mood || '').charAt(0).toUpperCase() + (lastPayload.mood || '').slice(1);
    const bud = budgetLabels[lastPayload.budget] || lastPayload.budget || '—';
    const dur = durLabels[lastPayload.duration] || lastPayload.duration || '—';
    const comp = compLabels[lastPayload.companion] || lastPayload.companion || '—';

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
            ${[
                ['fas fa-smile',   'Mood',         mood],
                ['fas fa-clock',   'Duration',     dur + ` (${bk.accommodation.nights}n)`],
                ['fas fa-users',   'Companion',    comp],
                ['fas fa-wallet',  'Budget',       bud]
            ].map(([icon,label,val]) => `
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
            ${[
                ['fas fa-plane',   'Flights',           fmt(bk.flights.amount),        bk.flights.description,        bk.flights.details],
                ['fas fa-hotel',   'Accommodation',     fmt(bk.accommodation.amount),  bk.accommodation.description,  `${bk.accommodation.nights} nights`],
                ['fas fa-ticket-alt','Activities',      fmt(bk.activities.amount),     bk.activities.description,     bk.activities.items],
                ['fas fa-utensils','Food & Dining',     fmt(bk.food.amount),           bk.food.description,           `~$${bk.food.perDay}/day`],
                ['fas fa-bus',     'Local Transport',   fmt(bk.transportation.amount), bk.transportation.description, bk.transportation.includes.join(' · ')]
            ].map(([icon,label,amt,desc,detail]) => `
                <div style="background:#f8f4f0;border-radius:8px;padding:13px 15px;margin-bottom:10px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <i class="${icon}" style="color:#c9a96e;width:18px;text-align:center;"></i>
                            <span style="font-weight:bold;color:#3b1f2b;">${label}</span>
                        </div>
                        <span style="font-weight:bold;color:#3b1f2b;">$${amt}</span>
                    </div>
                    <div style="font-size:11px;color:#6b5b4f;margin-left:28px;">${desc}<br><span style="color:#c9a96e;">${detail}</span></div>
                </div>`).join('')}
            <div style="margin-top:16px;border-top:2px solid #e2d5c7;padding-top:14px;">
                ${[
                    ['Subtotal',                                `$${fmt(cost.subtotal)}`],
                    [`Taxes (${cost.taxes.rate}% ${cost.taxes.type})`, `$${fmt(cost.taxes.amount)}`],
                    ['Service Fee',                             `$${fmt(cost.serviceFee.amount)}`]
                ].map(([l,v]) => `
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:14px;">
                        <span style="color:#6b5b4f;">${l}</span><span style="color:#3b1f2b;">${v}</span>
                    </div>`).join('')}
            </div>
            <div style="background:linear-gradient(135deg,#3b1f2b,#4d2a3a);border-radius:8px;padding:18px 20px;margin-top:14px;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="color:#c9a96e;font-size:13px;text-transform:uppercase;letter-spacing:1px;">Total per person</div>
                    <div style="color:#d4c4b0;font-size:11px;margin-top:3px;">Range: ${cost.range.display}</div>
                </div>
                <div style="color:#c9a96e;font-size:30px;font-weight:bold;">$${fmt(cost.total)}</div>
            </div>
            <div style="margin-top:16px;background:#e8f4e8;border-radius:8px;padding:14px 16px;border-left:4px solid #4CAF50;">
                <div style="font-weight:bold;color:#2c5e2c;margin-bottom:10px;font-size:13px;"><i class="fas fa-tag" style="margin-right:6px;color:#4CAF50;"></i>Available Discounts</div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;text-align:center;">
                    ${[
                        ['Early Bird',     cost.savings.earlyBird],
                        ['Group Discount', cost.savings.groupDiscount],
                        ['Package Deal',   cost.savings.packageDeal]
                    ].map(([l,v]) => `
                        <div>
                            <div style="font-size:16px;font-weight:bold;color:#2c5e2c;">$${fmt(v)}</div>
                            <div style="font-size:10px;color:#6b5b4f;">${l}</div>
                        </div>`).join('')}
                </div>
                <div style="text-align:center;font-size:11px;color:#6b5b4f;margin-top:10px;">
                    Total potential savings: <strong style="color:#2c5e2c;">$${fmt(cost.savings.earlyBird + cost.savings.groupDiscount + cost.savings.packageDeal)}</strong>
                </div>
            </div>
        </div>
        <div style="padding:0 30px 20px;background:#fff;">
            <div style="background:#f8f4f0;padding:14px 16px;border-radius:6px;border-left:4px solid #c9a96e;">
                <div style="font-weight:bold;color:#3b1f2b;margin-bottom:6px;font-size:13px;">
                    <i class="fas fa-plane-departure" style="color:#c9a96e;margin-right:6px;"></i>Flight Information
                </div>
                <div style="color:#6b5b4f;font-size:13px;line-height:1.6;">${esc(d.flight_info)}</div>
            </div>
        </div>
        <div style="padding:0 30px 20px;background:#fff;">
            <div style="font-weight:bold;color:#3b1f2b;border-bottom:2px solid #c9a96e;padding-bottom:8px;margin-bottom:12px;font-size:13px;">
                <i class="fas fa-star" style="color:#c9a96e;margin-right:6px;"></i>Recommended Activities
            </div>
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
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                *{box-sizing:border-box;margin:0;padding:0;}
                body{font-family:'Georgia',serif;background:#f5f0eb;padding:24px;color:#2c2c2c;}
                @media print{body{background:#fff;padding:0;}}
            </style>
        </head><body>${html}</body></html>`);
        win.document.close();
        win.focus();
        setTimeout(() => { win.print(); win.close(); }, 500);
    }
}

async function downloadReceiptPdf() {
    const { jsPDF } = window.jspdf;
    if (!selectedDest) return;

    const d = selectedDest;
    const cost = d.costBreakdown;
    const bk = cost.breakdown;
    const dates = getFormattedDates();

    const doc = new jsPDF({ unit: 'mm', format: 'a4' });
    const pW = doc.internal.pageSize.getWidth();
    const pH = doc.internal.pageSize.getHeight();
    const mg = 18;
    const cW = pW - mg * 2;

    const deep = [59, 31, 43];
    const gold = [201, 169, 110];
    const muted = [107, 91, 79];
    const cream = [248, 244, 240];
    const green = [44, 94, 44];
    const lightGreen = [232, 244, 232];

    let logoBase64 = null;
    try {
        const resp = await fetch('/img/travel-icon.png');
        const blob = await resp.blob();
        const raw = await new Promise(res => {
            const reader = new FileReader();
            reader.onloadend = () => res(reader.result);
            reader.readAsDataURL(blob);
        });
        const img = await new Promise((res, rej) => {
            const el = new Image();
            el.onload = () => res(el);
            el.onerror = rej;
            el.src = raw;
        });
        const canvas = document.createElement('canvas');
        canvas.width = img.naturalWidth || 256;
        canvas.height = img.naturalHeight || 256;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;
        for (let i = 0; i < data.length; i += 4) {
            if (data[i + 3] > 0) {
                data[i] = data[i + 1] = data[i + 2] = 255;
            }
        }
        ctx.putImageData(imageData, 0, 0);
        logoBase64 = canvas.toDataURL('image/png');
    } catch (_) {}

    let y = 0;

    function wrap(text, maxW, size) {
        doc.setFontSize(size);
        return doc.splitTextToSize(String(text || ''), maxW);
    }

    function newPageIfNeeded(needed) {
        if (y + needed > pH - 16) {
            doc.addPage();
            addPageFooter();
            y = 14;
        }
    }

    function addPageFooter() {
        doc.setFillColor(...deep);
        doc.rect(0, pH - 10, pW, 10, 'F');
        doc.setFontSize(7);
        doc.setTextColor(...gold);
        doc.text(`Smart Booking AI  ·  Ref: ${dates.bookingRef}`, pW / 2, pH - 3, { align: 'center' });
    }

    doc.setFillColor(...deep);
    doc.rect(0, 0, pW, 34, 'F');
    if (logoBase64) {
        doc.addImage(logoBase64, 'PNG', mg, 4, 26, 26);
    }
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(18);
    doc.setTextColor(...gold);
    doc.text('SMART BOOKING', mg + 30, 16);
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    doc.setTextColor(212, 196, 176);
    doc.text('AI-Powered Travel Planning  ·  smartbooking.com', mg + 30, 23);
    y = 34;

    doc.setFillColor(70, 40, 55);
    doc.rect(0, y, pW, 14, 'F');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(9);
    doc.setTextColor(...gold);
    doc.text(`Ref: ${dates.bookingRef}`, mg, y + 9);
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    doc.setTextColor(212, 196, 176);
    doc.text(`Issued: ${dates.issueDate}  ·  ${dates.issueTime}`, pW - mg, y + 9, { align: 'right' });
    y += 14;

    doc.setFillColor(...gold);
    doc.rect(0, y, pW, 20, 'F');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(18);
    doc.setTextColor(...deep);
    doc.text(String(d.destination), mg, y + 13);
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(9);
    doc.text(String(d.country).toUpperCase() + '  ·  ' + generateAirportCode(d.destination), pW - mg, y + 13, { align: 'right' });
    y += 20;

    const mood = (lastPayload.mood || '').charAt(0).toUpperCase() + (lastPayload.mood || '').slice(1);
    const cards = [
        ['Mood',      mood],
        ['Duration',  (durLabels[lastPayload.duration] || lastPayload.duration || '—') + ` (${bk.accommodation.nights}n)`],
        ['Companion', compLabels[lastPayload.companion] || lastPayload.companion || '—'],
        ['Budget',    budgetLabels[lastPayload.budget] || lastPayload.budget || '—'],
    ];
    const cardW = cW / 4 - 2;
    y += 5;
    cards.forEach(([label, val], i) => {
        const cx = mg + i * (cardW + 2.7);
        doc.setFillColor(...cream);
        doc.roundedRect(cx, y, cardW, 18, 2, 2, 'F');
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(7);
        doc.setTextColor(...gold);
        doc.text(label.toUpperCase(), cx + cardW / 2, y + 6, { align: 'center' });
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(8);
        doc.setTextColor(...deep);
        const vlines = wrap(val, cardW - 4, 8);
        if (vlines[0]) doc.text(vlines[0], cx + cardW / 2, y + 13, { align: 'center' });
    });
    y += 24;

    function sectionTitle(title) {
        newPageIfNeeded(12);
        doc.setFillColor(...gold);
        doc.rect(mg, y, 3, 7, 'F');
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(10);
        doc.setTextColor(...deep);
        doc.text(title, mg + 6, y + 5.5);
        y += 10;
    }

    function costRow(icon, label, amount, desc, detail, bgOverride) {
        newPageIfNeeded(20);
        const rH = 18;
        doc.setFillColor(...(bgOverride || cream));
        doc.roundedRect(mg, y, cW, rH, 2, 2, 'F');
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(9);
        doc.setTextColor(...deep);
        doc.text(label, mg + 6, y + 7);
        doc.text(`$${amount}`, mg + cW - 2, y + 7, { align: 'right' });
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(7.5);
        doc.setTextColor(...muted);
        doc.text(String(desc), mg + 6, y + 12.5);
        doc.setTextColor(...gold);
        const detailLines = wrap(detail, cW - 14, 7.5);
        if (detailLines[0]) doc.text(detailLines[0], mg + 6, y + 16.5);
        y += rH + 3;
    }

    sectionTitle('Cost Estimation Breakdown');
    costRow('', 'Flights',           fmt(bk.flights.amount),        bk.flights.description,        bk.flights.details);
    costRow('', 'Accommodation',     fmt(bk.accommodation.amount),  bk.accommodation.description,  `${bk.accommodation.nights} nights`);
    costRow('', 'Activities & Tours',fmt(bk.activities.amount),     bk.activities.description,     bk.activities.items);
    costRow('', 'Food & Dining',     fmt(bk.food.amount),           bk.food.description,           `~$${bk.food.perDay}/day`);
    costRow('', 'Local Transport',   fmt(bk.transportation.amount), bk.transportation.description, bk.transportation.includes.join(' · '));

    y += 2;
    doc.setDrawColor(...gold);
    doc.setLineWidth(0.4);
    doc.line(mg, y, mg + cW, y);
    y += 5;

    [
        ['Subtotal',                                         `$${fmt(cost.subtotal)}`],
        [`Taxes (${cost.taxes.rate}% ${cost.taxes.type})`,  `$${fmt(cost.taxes.amount)}`],
        ['Service Fee',                                      `$${fmt(cost.serviceFee.amount)}`],
    ].forEach(([l, v]) => {
        newPageIfNeeded(7);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9);
        doc.setTextColor(...muted);
        doc.text(l, mg + 4, y);
        doc.setTextColor(...deep);
        doc.text(v, mg + cW - 2, y, { align: 'right' });
        y += 7;
    });

    newPageIfNeeded(22);
    y += 3;
    doc.setFillColor(...deep);
    doc.roundedRect(mg, y, cW, 20, 3, 3, 'F');
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(9);
    doc.setTextColor(...gold);
    doc.text('TOTAL PER PERSON (ESTIMATED)', mg + 5, y + 8);
    doc.setFontSize(8);
    doc.setTextColor(212, 196, 176);
    doc.text(`Range: ${cost.range.display}`, mg + 5, y + 14);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(18);
    doc.setTextColor(...gold);
    doc.text(`$${fmt(cost.total)}`, mg + cW - 3, y + 14, { align: 'right' });
    y += 25;

    newPageIfNeeded(28);
    doc.setFillColor(...lightGreen);
    doc.roundedRect(mg, y, cW, 24, 2, 2, 'F');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(9);
    doc.setTextColor(...green);
    doc.text('Available Discounts', mg + 5, y + 8);
    const savings = [
        ['Early Bird',     cost.savings.earlyBird],
        ['Group Discount', cost.savings.groupDiscount],
        ['Package Deal',   cost.savings.packageDeal],
    ];
    const sW = cW / 3;
    savings.forEach(([l, v], i) => {
        const sx = mg + i * sW + sW / 2;
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(10);
        doc.setTextColor(...green);
        doc.text(`$${fmt(v)}`, sx, y + 16, { align: 'center' });
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(7);
        doc.setTextColor(...muted);
        doc.text(l, sx, y + 21, { align: 'center' });
    });
    y += 28;

    sectionTitle('Flight Information');
    newPageIfNeeded(18);
    doc.setFillColor(...cream);
    doc.roundedRect(mg, y, cW, 16, 2, 2, 'F');
    doc.setFillColor(...gold);
    doc.rect(mg, y, 3, 16, 'F');
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(9);
    doc.setTextColor(...deep);
    const flightLines = wrap(d.flight_info, cW - 10, 9);
    doc.text(flightLines, mg + 7, y + 7);
    y += 18 + 4;

    sectionTitle('Recommended Activities');
    if (d.top_activities) {
        const acts = d.top_activities.split(',').map(a => a.trim());
        let ax = mg, lineY = y;
        acts.forEach(act => {
            const tw = doc.getTextWidth(act) + 10;
            if (ax + tw > mg + cW) { ax = mg; lineY += 10; }
            newPageIfNeeded(10);
            doc.setFillColor(...cream);
            doc.roundedRect(ax, lineY - 4, tw, 8, 2, 2, 'F');
            doc.setDrawColor(...gold);
            doc.setLineWidth(0.3);
            doc.roundedRect(ax, lineY - 4, tw, 8, 2, 2, 'S');
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8);
            doc.setTextColor(...deep);
            doc.text(act, ax + tw / 2, lineY + 1, { align: 'center' });
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
    doc.setDrawColor(...gold);
    doc.setLineWidth(0.4);
    doc.roundedRect(mg, y, cW, tipH, 2, 2, 'S');
    doc.setFont('helvetica', 'italic');
    doc.setFontSize(9);
    doc.setTextColor(...muted);
    doc.text(tipLines, mg + 7, y + 7);
    y += tipH + 6;

    newPageIfNeeded(16);
    doc.setFillColor(...cream);
    doc.rect(0, y, pW, 14, 'F');
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(7);
    doc.setTextColor(...muted);
    doc.text(
        'All prices are estimates and subject to change based on seasonality, availability, and booking dates. *Cancellation policy varies by provider.',
        pW / 2, y + 5, { align: 'center', maxWidth: cW }
    );
    doc.text('Valid Until: ' + dates.validUntil, pW / 2, y + 11, { align: 'center' });
    y += 14;

    addPageFooter();

    doc.save(`trip-receipt-${String(d.destination).toLowerCase().replace(/\s+/g, '-')}.pdf`);
}

function esc(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

window.selectMood = selectMood;
window.goStep = goStep;
window.generateSuggestions = generateSuggestions;
window.openReceipt = openReceipt;
window.closeReceipt = closeReceipt;
window.printReceipt = printReceipt;
window.downloadReceiptPdf = downloadReceiptPdf;

@extends('layouts.public')

@section('title', $destination->name . ' — Smart Booking')

@section('content')

<section class="dest-show-hero" style="background: linear-gradient(160deg, rgba(10,20,30,0.62) 0%, rgba(59,31,43,0.45) 100%), url('{{ $destination->image_url ?: 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=1800&q=80' }}') center/cover no-repeat;">
    <div class="dest-show-hero-inner">
        <a href="{{ route('destinations') }}" class="dest-back-link">
            <i class="fas fa-arrow-left"></i> All Destinations
        </a>
        <div class="dest-show-badges">
            @if($destination->badge)
                <span class="dest-show-badge">{{ $destination->badge }}</span>
            @endif
            @if($destination->is_hidden_gem)
                <span class="dest-show-badge dest-show-badge--gem"><i class="fas fa-gem"></i> Hidden Gem</span>
            @endif
        </div>
        <h1>{{ $destination->name }}</h1>
        <p class="dest-show-location">
            <i class="fas fa-map-marker-alt"></i>
            {{ $destination->country }}
            @if($destination->region)
                &nbsp;·&nbsp; {{ ucwords(str_replace('_', ' ', $destination->region)) }}
            @endif
        </p>
        <div class="dest-show-meta-row">
            @if($destination->mood)
                <span class="dest-show-pill"><i class="fas fa-heart"></i> {{ ucfirst($destination->mood) }}</span>
            @endif
            @if($destination->category)
                <span class="dest-show-pill"><i class="fas fa-tag"></i> {{ ucwords(str_replace('_', ' ', $destination->category)) }}</span>
            @endif
            @if($destination->match_score)
                <span class="dest-show-pill dest-show-pill--gold"><i class="fas fa-star"></i> {{ $destination->match_score }}% match</span>
            @endif
        </div>
    </div>
</section>

<div class="dest-show-wrap">

    
    <div class="dest-show-main">

        <div class="dest-show-card">
            <h2><i class="fas fa-info-circle"></i> About {{ $destination->name }}</h2>
            <p class="dest-show-description">
                {{ $destination->description ?: 'No description available yet for this destination.' }}
            </p>
        </div>

        {{-- AI Cost Breakdown --}}
        <div class="dest-show-card" id="costBreakdownCard">
            <div class="cost-breakdown-header">
                <h2><i class="fas fa-calculator"></i> Cost Breakdown</h2>
                <div class="cost-duration-selector">
                    <label><i class="fas fa-calendar"></i> Duration:</label>
                    <input type="number" id="costDuration" min="1" max="365" value="7"
                        style="width:65px;padding:6px 8px;border:1.5px solid var(--border);border-radius:6px;font-size:13px;font-family:'Georgia',serif;background:#fff;color:var(--deep);"
                        onchange="loadCostBreakdown()">
                    <span style="font-size:13px;color:var(--text-muted);">days</span>
                </div>
            </div>
            <div id="costBreakdownContent">
                <div class="cost-loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading cost data from AI…
                </div>
            </div>
        </div>

        {{-- Activities Manager --}}
        <div class="dest-show-card" id="activitiesCard" style="display:none;">
            <div class="activities-header">
                <h2><i class="fas fa-map-marked-alt"></i> Activities</h2>
                <span class="activities-subtitle">Toggle activities to update your estimated cost</span>
            </div>
            <div id="activitiesContent"></div>
            <div class="activities-total-row" id="activitiesTotalRow" style="display:none;">
                <span><i class="fas fa-tag"></i> Selected activities total:</span>
                <strong id="activitiesTotal">$0</strong>
            </div>
        </div>

        <div class="dest-show-card">
            <h2><i class="fas fa-list-ul"></i> Key Facts</h2>
            <div class="dest-show-facts">
                <div class="dest-fact">
                    <i class="fas fa-globe"></i>
                    <div>
                        <span class="fact-label">Country</span>
                        <span class="fact-value">{{ $destination->country ?: '—' }}</span>
                    </div>
                </div>
                <div class="dest-fact">
                    <i class="fas fa-map"></i>
                    <div>
                        <span class="fact-label">Region</span>
                        <span class="fact-value">{{ $destination->region ? ucwords(str_replace('_', ' ', $destination->region)) : '—' }}</span>
                    </div>
                </div>
                <div class="dest-fact">
                    <i class="fas fa-tag"></i>
                    <div>
                        <span class="fact-label">Category</span>
                        <span class="fact-value">{{ $destination->category ? ucwords(str_replace('_', ' ', $destination->category)) : '—' }}</span>
                    </div>
                </div>
                <div class="dest-fact">
                    <i class="fas fa-heart"></i>
                    <div>
                        <span class="fact-label">Travel Mood</span>
                        <span class="fact-value">{{ $destination->mood ? ucfirst($destination->mood) : '—' }}</span>
                    </div>
                </div>
                <div class="dest-fact">
                    <i class="fas fa-dollar-sign"></i>
                    <div>
                        <span class="fact-label">Starting From</span>
                        <span class="fact-value" data-price-usd="{{ $destination->price_from }}">${{ number_format($destination->price_from) }} <small>per person</small></span>
                    </div>
                </div>
                @if($destination->match_score)
                <div class="dest-fact">
                    <i class="fas fa-star"></i>
                    <div>
                        <span class="fact-label">Match Score</span>
                        <span class="fact-value">{{ $destination->match_score }}%</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        
        @if($related->count())
        <div class="dest-show-card">
            <h2><i class="fas fa-compass"></i> Similar Destinations</h2>
            <div class="dest-show-related">
                @foreach($related as $r)
                <a href="{{ route('destinations.show', $r->id) }}" class="dest-related-card">
                    <div class="dest-related-img" style="background-image:url('{{ $r->image_url ?: 'https://picsum.photos/seed/'.urlencode($r->name).'/400/260' }}')"></div>
                    <div class="dest-related-body">
                        <h4>{{ $r->name }}</h4>
                        <p>{{ $r->country }}</p>
                        @if($r->price_from)
                            <span class="dest-related-price" data-price-usd="{{ $r->price_from }}">From ${{ number_format($r->price_from) }}</span>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    
    <aside class="dest-show-sidebar">

        
        <div class="dest-show-card dest-show-cta-card">
            <div class="dest-cta-price">
                <span class="dest-cta-from">From</span>
                <span class="dest-cta-amount" data-price-usd="{{ $destination->price_from }}">${{ number_format($destination->price_from) }}</span>
                <span class="dest-cta-per">per person</span>
            </div>

            <a href="{{ route('plan-trip') }}?destination={{ urlencode($destination->name) }}&mood={{ $destination->mood }}&region={{ $destination->region }}"
               class="primary-button dest-cta-btn">
                <i class="fas fa-route"></i> Plan This Trip
            </a>

            <a href="{{ route('flights.index') }}?destination={{ urlencode($destination->name) }}"
               class="secondary-button dest-cta-btn">
                <i class="fas fa-plane"></i> Search Flights
            </a>

            @auth
            <button class="secondary-button dest-cta-btn" id="wishlistBtn"
                    onclick="toggleWishlist({{ $destination->id }})">
                <i class="fas fa-heart"></i> Save to Wishlist
            </button>
            @else
            <a href="{{ route('login') }}" class="secondary-button dest-cta-btn">
                <i class="fas fa-heart"></i> Save to Wishlist
            </a>
            @endauth
        </div>

        
        <div class="dest-show-card">
            <h3><i class="fas fa-info-circle"></i> Quick Info</h3>
            <ul class="dest-quick-list">
                <li><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> {{ $destination->country }}</li>
                @if($destination->region)
                <li><i class="fas fa-globe"></i> <strong>Region:</strong> {{ ucwords(str_replace('_', ' ', $destination->region)) }}</li>
                @endif
                @if($destination->mood)
                <li><i class="fas fa-heart"></i> <strong>Best for:</strong> {{ ucfirst($destination->mood) }} travellers</li>
                @endif
                @if($destination->is_hidden_gem)
                <li><i class="fas fa-gem" style="color:var(--gold)"></i> <strong>Hidden Gem</strong> destination</li>
                @endif
            </ul>
        </div>

    </aside>

</div>

@endsection

@push('scripts')
<script>
// ── Destination Cost Calculator ───────────────────────────────────────────
(function () {
    var DEST     = @json($destination->name);
    var COUNTRY  = @json($destination->country);
    var costData = null;
    var selectedActivities = new Set();
    var currentTier = 'mid';

    // ── Currency helper ───────────────────────────────────────────────────
    function fmt(usd) {
        if (typeof window.Currency !== 'undefined') return window.Currency.format(Number(usd));
        return '$' + Number(usd).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    // ── Load cost breakdown from Groq API ─────────────────────────────────
    window.loadCostBreakdown = function () {
        var duration = document.getElementById('costDuration');
        var days = duration ? parseInt(duration.value) : 7;
        var content = document.getElementById('costBreakdownContent');
        if (!content) return;

        content.innerHTML = '<div class="cost-loading"><i class="fas fa-spinner fa-spin"></i> Fetching real cost data for ' + DEST + '…</div>';

        fetch('/api/destination-cost?destination=' + encodeURIComponent(DEST) + '&country=' + encodeURIComponent(COUNTRY) + '&duration=' + days, {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res.success) throw new Error(res.message || 'Failed');
            costData = res.data;
            // Default all activities as selected
            selectedActivities = new Set();
            (costData.activities || []).forEach(function (a, i) {
                if (a.included !== false) selectedActivities.add(i);
            });
            renderCostBreakdown();
            renderActivities();
        })
        .catch(function (err) {
            content.innerHTML = '<div class="cost-error"><i class="fas fa-exclamation-circle"></i> Could not load cost data. Please try again.</div>';
        });
    };

    // ── Render the cost breakdown ─────────────────────────────────────────
    function renderCostBreakdown() {
        var content = document.getElementById('costBreakdownContent');
        if (!content || !costData) return;

        var dc = costData.daily_costs || {};
        var days = costData.duration_days || 7;

        // Calculate dynamic total based on selected tier + selected activities
        var activitiesCost = calcActivitiesCost();
        var tierAccom = { budget: dc.budget_hotel || 40, mid: dc.mid_hotel || 100, luxury: dc.luxury_hotel || 250 };
        var tierFood  = { budget: dc.street_food || 8, mid: dc.mid_restaurant || 25, luxury: dc.fine_dining || 70 };
        var tierTrans = { budget: dc.local_transport || 5, mid: dc.taxi_rideshare || 15, luxury: (dc.car_rental || 50) };

        var accomTotal = tierAccom[currentTier] * days;
        var foodTotal  = tierFood[currentTier] * days;
        var transTotal = tierTrans[currentTier] * days;
        var miscTotal  = (costData.misc_daily_usd || 15) * days;
        var visaCost   = costData.visa_cost_usd || 0;
        var insurance  = costData.travel_insurance_usd || 35;
        var grandTotal = accomTotal + foodTotal + transTotal + miscTotal + activitiesCost + visaCost + insurance;

        // Update sidebar CTA price
        var ctaAmount = document.querySelector('.dest-cta-amount');
        if (ctaAmount) {
            ctaAmount.textContent = fmt(grandTotal);
            ctaAmount.removeAttribute('data-price-usd');
        }

        content.innerHTML =
            // Tier selector
            '<div class="cost-tier-selector">' +
                '<button class="cost-tier-btn' + (currentTier === 'budget' ? ' active' : '') + '" onclick="window.setCostTier(\'budget\')">' +
                    '<i class="fas fa-backpack"></i> Budget' +
                '</button>' +
                '<button class="cost-tier-btn' + (currentTier === 'mid' ? ' active' : '') + '" onclick="window.setCostTier(\'mid\')">' +
                    '<i class="fas fa-star-half-alt"></i> Mid-Range' +
                '</button>' +
                '<button class="cost-tier-btn' + (currentTier === 'luxury' ? ' active' : '') + '" onclick="window.setCostTier(\'luxury\')">' +
                    '<i class="fas fa-gem"></i> Luxury' +
                '</button>' +
            '</div>' +

            // Grand total banner
            '<div class="cost-grand-total">' +
                '<div class="cost-grand-label">Estimated Total (' + days + ' days, 1 person)</div>' +
                '<div class="cost-grand-amount" id="grandTotalDisplay">' + fmt(grandTotal) + '</div>' +
                '<div class="cost-grand-note">Based on your selected activities &amp; travel style</div>' +
            '</div>' +

            // Breakdown rows
            '<div class="cost-rows">' +
                costRow('fa-bed',        'Accommodation',  accomTotal,    fmt(tierAccom[currentTier]) + '/night × ' + days + ' nights') +
                costRow('fa-utensils',   'Food & Dining',  foodTotal,     fmt(tierFood[currentTier]) + '/day × ' + days + ' days') +
                costRow('fa-bus',        'Transport',      transTotal,    fmt(tierTrans[currentTier]) + '/day × ' + days + ' days') +
                costRow('fa-map-marked-alt', 'Activities', activitiesCost, selectedActivities.size + ' activities selected') +
                costRow('fa-shopping-bag', 'Misc & Shopping', miscTotal,  fmt(costData.misc_daily_usd || 15) + '/day') +
                (visaCost > 0 ? costRow('fa-passport', 'Visa', visaCost, 'One-time fee') : '') +
                costRow('fa-shield-alt', 'Travel Insurance', insurance,  'Recommended') +
            '</div>' +

            // Daily cost reference
            '<div class="cost-daily-ref">' +
                '<div class="cost-daily-title"><i class="fas fa-info-circle"></i> Daily Cost Reference</div>' +
                '<div class="cost-daily-grid">' +
                    dailyRef('fa-bed',      'Budget Hotel',   dc.budget_hotel) +
                    dailyRef('fa-hotel',    'Mid Hotel',      dc.mid_hotel) +
                    dailyRef('fa-concierge-bell', 'Luxury Hotel', dc.luxury_hotel) +
                    dailyRef('fa-utensils', 'Street Food',    dc.street_food) +
                    dailyRef('fa-coffee',   'Restaurant',     dc.mid_restaurant) +
                    dailyRef('fa-bus',      'Local Transport',dc.local_transport) +
                    dailyRef('fa-car',      'Taxi/Rideshare', dc.taxi_rideshare) +
                '</div>' +
            '</div>' +

            // Tips
            (costData.tips && costData.tips.length ?
                '<div class="cost-tips">' +
                    '<div class="cost-tips-title"><i class="fas fa-lightbulb"></i> Money-Saving Tips</div>' +
                    costData.tips.map(function (t) {
                        return '<div class="cost-tip"><i class="fas fa-check-circle"></i> ' + esc(t) + '</div>';
                    }).join('') +
                '</div>'
            : '');
    }

    function costRow(icon, label, amount, note) {
        return '<div class="cost-row">' +
            '<div class="cost-row-left">' +
                '<i class="fas ' + icon + '"></i>' +
                '<div>' +
                    '<div class="cost-row-label">' + label + '</div>' +
                    '<div class="cost-row-note">' + note + '</div>' +
                '</div>' +
            '</div>' +
            '<div class="cost-row-amount">' + fmt(amount) + '</div>' +
        '</div>';
    }

    function dailyRef(icon, label, amount) {
        if (!amount) return '';
        return '<div class="cost-daily-item">' +
            '<i class="fas ' + icon + '"></i>' +
            '<span>' + label + '</span>' +
            '<strong>' + fmt(amount) + '</strong>' +
        '</div>';
    }

    // ── Render activities ─────────────────────────────────────────────────
    function renderActivities() {
        var card = document.getElementById('activitiesCard');
        var content = document.getElementById('activitiesContent');
        if (!card || !content || !costData) return;

        var activities = costData.activities || [];
        if (!activities.length) return;

        card.style.display = 'block';

        var CATEGORY_ICONS = {
            culture: 'fa-landmark', nature: 'fa-tree', adventure: 'fa-hiking',
            food: 'fa-utensils', relaxation: 'fa-spa', shopping: 'fa-shopping-bag',
            nightlife: 'fa-music', sport: 'fa-running'
        };

        content.innerHTML = '<div class="activities-grid">' +
            activities.map(function (a, i) {
                var isSelected = selectedActivities.has(i);
                var icon = CATEGORY_ICONS[a.category] || 'fa-map-marker-alt';
                return '<div class="activity-item' + (isSelected ? ' selected' : '') + '" id="activity-' + i + '" onclick="window.toggleActivity(' + i + ')">' +
                    '<div class="activity-toggle">' +
                        '<i class="fas ' + (isSelected ? 'fa-check-circle' : 'fa-circle') + '"></i>' +
                    '</div>' +
                    '<div class="activity-icon"><i class="fas ' + icon + '"></i></div>' +
                    '<div class="activity-info">' +
                        '<div class="activity-name">' + esc(a.name) + '</div>' +
                        '<div class="activity-meta">' +
                            '<span><i class="fas fa-clock"></i> ' + (a.duration_hours || 2) + 'h</span>' +
                            '<span class="activity-category">' + (a.category || 'activity') + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="activity-cost">' +
                        (a.cost_usd > 0 ? fmt(a.cost_usd) : '<span class="free-badge">Free</span>') +
                    '</div>' +
                '</div>';
            }).join('') +
        '</div>';

        updateActivitiesTotal();
    }

    // ── Toggle activity ───────────────────────────────────────────────────
    window.toggleActivity = function (index) {
        if (selectedActivities.has(index)) {
            selectedActivities.delete(index);
        } else {
            selectedActivities.add(index);
        }

        var el = document.getElementById('activity-' + index);
        if (el) {
            el.classList.toggle('selected', selectedActivities.has(index));
            var icon = el.querySelector('.activity-toggle i');
            if (icon) icon.className = 'fas ' + (selectedActivities.has(index) ? 'fa-check-circle' : 'fa-circle');
        }

        updateActivitiesTotal();
        renderCostBreakdown(); // Re-render full breakdown with new total
    };

    function calcActivitiesCost() {
        if (!costData || !costData.activities) return 0;
        var total = 0;
        costData.activities.forEach(function (a, i) {
            if (selectedActivities.has(i)) total += (a.cost_usd || 0);
        });
        return total;
    }

    function updateActivitiesTotal() {
        var totalRow = document.getElementById('activitiesTotalRow');
        var totalEl  = document.getElementById('activitiesTotal');
        if (!totalRow || !totalEl) return;
        var total = calcActivitiesCost();
        totalEl.textContent = fmt(total);
        totalRow.style.display = 'flex';
    }

    // ── Set cost tier ─────────────────────────────────────────────────────
    window.setCostTier = function (tier) {
        currentTier = tier;
        renderCostBreakdown();
    };

    // ── Currency change ───────────────────────────────────────────────────
    function tryRegister() {
        if (typeof window.Currency !== 'undefined') {
            window.Currency.onCurrencyChange(function () {
                window.Currency.refreshAllPrices();
                if (costData) { renderCostBreakdown(); renderActivities(); }
            });
            window.Currency.refreshAllPrices();
        } else {
            setTimeout(tryRegister, 100);
        }
    }
    tryRegister();

    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // ── Auto-load on page ready ───────────────────────────────────────────
    if (document.readyState !== 'loading') window.loadCostBreakdown();
    else document.addEventListener('DOMContentLoaded', window.loadCostBreakdown);

}());

// ── Wishlist ──────────────────────────────────────────────────────────────
@auth
(function () {
    const destId = {{ $destination->id }};
    const csrf   = document.querySelector('meta[name="csrf-token"]').content;
    const btn    = document.getElementById('wishlistBtn');
    let isSaved  = false;

    fetch('/api/wishlist/count', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.ids && data.ids.includes(destId)) {
                isSaved = true;
                if (btn) btn.innerHTML = '<i class="fas fa-heart" style="color:var(--danger)"></i> Saved';
            }
        }).catch(() => {});

    window.toggleWishlist = function (id) {
        if (!btn) return;
        if (isSaved) {
            fetch('/wishlist/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf } })
                .then(() => { isSaved = false; btn.innerHTML = '<i class="fas fa-heart"></i> Save to Wishlist'; })
                .catch(() => {});
        } else {
            fetch('/wishlist', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ destination_id: id }) })
                .then(() => { isSaved = true; btn.innerHTML = '<i class="fas fa-heart" style="color:var(--danger)"></i> Saved'; })
                .catch(() => {});
        }
    };
}());
@endauth
</script>
@endpush
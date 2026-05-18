@extends('layouts.public')

@section('title', 'Discover — Smart Booking')

@section('content')

{{-- Hero --}}
<section class="hero hero-with-image hero-pattern"
         style="background-image: url('{{ $heroImage ?? 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=1920&q=80' }}');">
    <div class="hero-content">
        <h1 class="hero-title">
            <i class="fas fa-compass"></i> Discover Your Next Adventure
        </h1>
        <p class="hero-subtitle">
            Search any city, country, or travel mood — results come from live global APIs and are cached for speed.
        </p>

        <div class="hero-search-bar">
            <form id="discoverSearchForm" class="discover-search-form" autocomplete="off">
                <div class="search-input-group">
                    <i class="fas fa-search search-icon"></i>
                    <input
                        type="text"
                        id="discoverSearchInput"
                        name="search"
                        placeholder="Search destinations, countries, or regions…"
                        autocomplete="off"
                    >
                </div>

                <div class="search-filters">
                    <select id="discoverRegionFilter" name="region" class="filter-select">
                        <option value="">All Countries</option>
                        @foreach($countries as $country)
                            <option value="{{ $country['code'] }}">{{ $country['name'] }}</option>
                        @endforeach
                    </select>

                    <select id="discoverMoodFilter" name="mood" class="filter-select">
                        <option value="">All Moods</option>
                        @foreach($moodCategories as $cat)
                            <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary search-btn" id="discoverSearchBtn">
                        <span class="btn-text"><i class="fas fa-search"></i> Search</span>
                        <span class="btn-spinner hidden"><i class="fas fa-spinner fa-spin"></i></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

{{-- Main Content --}}
<div class="section">
    <div class="section-container">

        {{-- Results info bar --}}
        <div id="discoverResultsInfo" class="search-results-info hidden"></div>

        {{-- Section header --}}
        <div id="discoverSectionHeader" class="section-header">
            <span class="section-badge">
                <i class="fas fa-star"></i> Featured Destinations
            </span>
            <h2 class="section-title">Handpicked destinations to inspire you</h2>
            <p class="section-description">
                Use the search above to explore any destination, country, or travel mood.
            </p>
        </div>

        {{-- Destinations grid --}}
        <div id="discoverGrid" class="grid grid-auto-fit">
            <div class="discover-loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading destinations…</p>
            </div>
        </div>

        {{-- Empty state --}}
        <div id="discoverEmpty" class="empty-state hidden">
            <i class="fas fa-compass empty-state-icon"></i>
            <h3 class="empty-state-title">No destinations found</h3>
            <p class="empty-state-text" id="discoverEmptyText">
                Try a different search term, country, or mood.
            </p>
            <button class="btn btn-primary" id="discoverClearBtn">
                <i class="fas fa-times"></i> Clear search
            </button>
        </div>

        {{-- Browse by Mood --}}
        @if($moodCategories->isNotEmpty())
        <div class="mood-section" id="discoverMoodSection">
            <div class="section-header">
                <h2 class="section-title">Browse by Mood</h2>
                <p class="section-description">Find destinations that match your travel style</p>
            </div>
            <div class="grid grid-cols-3">
                @foreach($moodCategories as $mood)
                <button class="card mood-category-card"
                        data-mood="{{ $mood->name }}"
                        id="moodBtn_{{ $loop->index }}">
                    <div class="card-content mood-card-content">
                        <div class="mood-icon-wrap"
                             style="background: {{ $mood->gradient }}; color: {{ $mood->color }};">
                            <i class="fas fa-{{ $mood->icon }}"></i>
                        </div>
                        <h3 class="card-title">{{ $mood->name }}</h3>
                        <p class="card-description">{{ $mood->description }}</p>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
        @endif

        {{-- CTA --}}
        <div class="discover-cta">
            <i class="fas fa-magic discover-cta-icon"></i>
            <h2 class="discover-cta-title">Not Sure Where to Go?</h2>
            <p class="discover-cta-text">
                Let our AI suggest the perfect destination based on your mood and preferences.
            </p>
            <a href="{{ route('plan-trip') }}" class="btn btn-lg btn-cta-white">
                <i class="fas fa-route"></i> Start Planning Your Trip
            </a>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ── DOM refs ──────────────────────────────────────────────────────────────
    const form          = document.getElementById('discoverSearchForm');
    const searchInput   = document.getElementById('discoverSearchInput');
    const regionFilter  = document.getElementById('discoverRegionFilter');
    const moodFilter    = document.getElementById('discoverMoodFilter');
    const searchBtn     = document.getElementById('discoverSearchBtn');
    const grid          = document.getElementById('discoverGrid');
    const emptyState    = document.getElementById('discoverEmpty');
    const emptyText     = document.getElementById('discoverEmptyText');
    const clearBtn      = document.getElementById('discoverClearBtn');
    const resultsInfo   = document.getElementById('discoverResultsInfo');
    const sectionHeader = document.getElementById('discoverSectionHeader');
    const moodSection   = document.getElementById('discoverMoodSection');

    // ── Tag icons map ─────────────────────────────────────────────────────────
    const TAG_ICONS = {
        'Cultural':    'landmark',
        'Foodie':      'utensils',
        'Beach':       'umbrella-beach',
        'Nature':      'leaf',
        'Photography': 'camera',
        'Romantic':    'heart',
        'Relaxed':     'spa',
        'Eco-Travel':  'leaf',
        'Adventurous': 'hiking',
    };

    // ── Helpers ───────────────────────────────────────────────────────────────
    function esc(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function setLoading(on) {
        if (!searchBtn) return;
        searchBtn.querySelector('.btn-text').classList.toggle('hidden', on);
        searchBtn.querySelector('.btn-spinner').classList.toggle('hidden', !on);
        searchBtn.disabled = on;
    }

    function showGrid() {
        grid.classList.remove('hidden');
        emptyState.classList.add('hidden');
    }

    function showEmpty(msg) {
        grid.innerHTML = '';
        grid.classList.add('hidden');
        emptyState.classList.remove('hidden');
        if (msg && emptyText) emptyText.textContent = msg;
    }

    function setResultsInfo(q, region, mood, count) {
        if (!q && !region && !mood) {
            resultsInfo.classList.add('hidden');
            sectionHeader.classList.remove('hidden');
            if (moodSection) moodSection.classList.remove('hidden');
            return;
        }

        sectionHeader.classList.add('hidden');
        if (moodSection) moodSection.classList.add('hidden');
        resultsInfo.classList.remove('hidden');

        const parts = [];
        if (q)      parts.push(`"${esc(q)}"`);
        if (region) parts.push(`in <strong>${esc(regionFilter.options[regionFilter.selectedIndex]?.text || region)}</strong>`);
        if (mood)   parts.push(`mood: <strong>${esc(mood)}</strong>`);

        resultsInfo.innerHTML = `
            <h2><i class="fas fa-search"></i> Search Results
                <span class="results-count">(${count} found)</span>
            </h2>
            <p class="search-query">${parts.join(' ')}</p>`;
    }

    // ── Render a single destination card ─────────────────────────────────────
    function renderCard(d) {
        const tags = (d.tags || []).slice(0, 3).map(function(tag) {
            const icon = TAG_ICONS[tag] || 'compass';
            return `<span class="tag"><i class="fas fa-${esc(icon)}"></i> ${esc(tag)}</span>`;
        }).join('');

        const price = d.price_from > 0
            ? `<span class="card-price-label">From</span>
               <span class="card-price-amount">${typeof window.Currency !== 'undefined' ? window.Currency.format(d.price_from) : '$' + d.price_from}</span>`
            : `<span class="card-price-label">Type</span>
               <span class="card-price-amount">${esc(d.region || 'Global')}</span>`;

        const location = [d.city, d.region || d.country].filter(Boolean).join(' · ') || d.country || 'Global';

        return `
        <article class="card">
            <div class="card-image">
                <img src="${esc(d.image_url)}"
                     alt="${esc(d.name)} ${esc(d.country || '')}"
                     loading="lazy"
                     onerror="this.src='https://source.unsplash.com/800x600/?${encodeURIComponent((d.name||'travel') + ' ' + (d.country||''))}'"
                >
                ${d.is_featured ? '<span class="card-badge"><i class="fas fa-star"></i> Featured</span>' : ''}
            </div>
            <div class="card-content">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${esc(d.name)}</h3>
                        <p class="card-subtitle">
                            <i class="fas fa-map-marker-alt"></i>
                            ${esc(location)}
                        </p>
                    </div>
                    <div class="card-price">${price}</div>
                </div>
                <p class="card-description">${esc(d.description || 'A destination worth exploring.')}</p>
                ${tags ? `<div class="card-tags">${tags}</div>` : ''}
                <div class="card-footer">
                    <a href="${esc(d.detail_url || '#')}" class="btn btn-primary">
                        <i class="fas fa-info-circle"></i> See details
                    </a>
                </div>
            </div>
        </article>`;
    }

    // ── Main search function ──────────────────────────────────────────────────
    async function doSearch(q, region, mood) {
        setLoading(true);
        grid.innerHTML = '<div class="discover-loading"><i class="fas fa-spinner fa-spin"></i><p>Searching destinations…</p></div>';
        showGrid();

        try {
            const params = new URLSearchParams();
            if (q)      params.set('q', q);
            if (region) params.set('region', region);
            if (mood)   params.set('mood', mood);

            const res  = await fetch('/api/discover?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json();
            const list = data.destinations || [];

            setResultsInfo(q, region, mood, list.length);

            // If the server resolved a nickname, show what it found
            if (data.resolved_query && data.resolved_query !== q) {
                const info = document.getElementById('discoverResultsInfo');
                if (info) {
                    const note = document.createElement('p');
                    note.className = 'search-query';
                    note.innerHTML = `<i class="fas fa-lightbulb"></i> Showing results for <strong>${esc(data.resolved_query)}</strong>`;
                    info.appendChild(note);
                }
            }

            if (!list.length) {
                showEmpty(
                    q || region || mood
                        ? 'No destinations found for your search. Try a different term, country, or mood.'
                        : 'No destinations loaded yet. Try searching for a city or country.'
                );
                return;
            }

            showGrid();
            grid.innerHTML = list.map(renderCard).join('');

            // Refresh currency prices
            if (typeof window.Currency !== 'undefined') {
                window.Currency.refresh();
            }
        } catch (err) {
            grid.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle empty-state-icon"></i>
                    <h3 class="empty-state-title">Something went wrong</h3>
                    <p class="empty-state-text">Could not load destinations. Please try again.</p>
                </div>`;
        } finally {
            setLoading(false);
        }
    }

    // ── Event listeners ───────────────────────────────────────────────────────
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const q      = searchInput ? searchInput.value.trim() : '';
            const region = regionFilter ? regionFilter.value : '';
            const mood   = moodFilter ? moodFilter.value : '';
            doSearch(q, region, mood);
        });
    }

    // Mood category cards
    document.querySelectorAll('.mood-category-card').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const mood = this.dataset.mood;
            if (moodFilter) moodFilter.value = mood;
            if (searchInput) searchInput.value = '';
            if (regionFilter) regionFilter.value = '';
            doSearch('', '', mood);
            // Scroll to results
            if (grid) grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Clear button
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (searchInput)  searchInput.value  = '';
            if (regionFilter) regionFilter.value = '';
            if (moodFilter)   moodFilter.value   = '';
            doSearch('', '', '');
        });
    }

    // Currency change
    document.addEventListener('currency:changed', function () {
        if (window.Currency) window.Currency.refresh();
    });

    // ── Initial load ──────────────────────────────────────────────────────────
    doSearch('', '', '');

})();
</script>
@endpush

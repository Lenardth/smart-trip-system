(function () {
    'use strict';

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
    let suppressFilterChange = false;

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

    function setActiveMood(mood) {
        document.querySelectorAll('.mood-category-card').forEach(function (btn) {
            btn.classList.toggle('mood-active', btn.dataset.mood === mood);
        });
    }

    function setResultsInfo(q, region, mood, count) {
        if (!q && !region && !mood) {
            resultsInfo.classList.add('hidden');
            sectionHeader.classList.remove('hidden');
            setActiveMood(null);
            return;
        }

        sectionHeader.classList.add('hidden');
        resultsInfo.classList.remove('hidden');

        const parts = [];
        if (q)      parts.push(`"${esc(q)}"`);
        if (region) parts.push(`in <strong>${esc(regionFilter.options[regionFilter.selectedIndex]?.text || region)}</strong>`);
        if (mood)   parts.push(`Mood: <strong>${esc(mood)}</strong>`);

        resultsInfo.innerHTML = `
            <div class="results-info-row">
                <div>
                    <h2><i class="fas fa-search"></i> Search Results
                        <span class="results-count">(${count} found)</span>
                    </h2>
                    <p class="search-query">${parts.join(' · ')}</p>
                </div>
                <button type="button" class="btn btn-outline-sm results-clear-btn" id="resultsClearBtn">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>`;

        const inlineClr = document.getElementById('resultsClearBtn');
        if (inlineClr) {
            inlineClr.addEventListener('click', function () {
                clearFilters();
                doSearch('', '', '');
            });
        }
    }

    function syncSelect(select) {
        if (!select) return;
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function currentSearch() {
        return {
            q: searchInput ? searchInput.value.trim() : '',
            region: regionFilter ? regionFilter.value : '',
            mood: moodFilter ? moodFilter.value : '',
        };
    }

    function clearFilters() {
        if (searchInput)  searchInput.value  = '';
        if (regionFilter) regionFilter.value = '';
        if (moodFilter)   moodFilter.value   = '';
        suppressFilterChange = true;
        syncSelect(regionFilter);
        syncSelect(moodFilter);
        suppressFilterChange = false;
        setActiveMood(null);
    }

    function renderCard(d) {
        const tags = (d.tags || []).slice(0, 3).map(function(tag) {
            const icon = TAG_ICONS[tag] || 'compass';
            return `<span class="tag"><i class="fas fa-${esc(icon)}"></i> ${esc(tag)}</span>`;
        }).join('');

        const priceTag = d.price_from > 0
            ? `<span class="tag tag-price"><i class="fas fa-tag"></i> ${typeof window.Currency !== 'undefined' ? window.Currency.format(d.price_from) : '$' + d.price_from}</span>`
            : '';

        const location = esc(d.country || d.region || 'Global');

        const fallbackSrc = 'https://picsum.photos/seed/' + encodeURIComponent(d.name || 'travel') + '/800/560';

        return `
        <article class="card destination-card">
            <div class="card-image">
                <img src="${esc(d.image_url)}"
                     alt="${esc(d.name)}"
                     loading="lazy"
                     data-fallback="${esc(fallbackSrc)}"
                     class="card-img-fallback"
                >
                ${d.is_featured ? '<span class="card-badge"><i class="fas fa-star"></i> Featured</span>' : ''}
                <div class="card-location-pill">
                    <i class="fas fa-map-marker-alt"></i> ${location}
                </div>
            </div>
            <div class="card-content">
                <h3 class="card-title">${esc(d.name)}</h3>
                <p class="card-description">${esc(d.description || 'A destination worth exploring.')}</p>
                ${(tags || priceTag) ? `<div class="card-tags">${priceTag}${tags}</div>` : ''}
                <div class="card-footer destination-card-footer">
                    <a href="${esc(d.detail_url || '#')}" class="btn btn-outline-sm btn-card-action">
                        <i class="fas fa-info-circle"></i> Details
                    </a>
                    <a href="${esc(d.plan_url || '#')}" class="btn btn-primary btn-card-action">
                        <i class="fas fa-route"></i> Plan Trip
                    </a>
                </div>
            </div>
        </article>`;
    }

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

            if (typeof window.Currency !== 'undefined') window.Currency.refresh();
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

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const filters = currentSearch();
            doSearch(filters.q, filters.region, filters.mood);
        });
    }

    [regionFilter, moodFilter].forEach(function (filter) {
        if (!filter) return;
        filter.addEventListener('change', function () {
            if (suppressFilterChange) return;
            const filters = currentSearch();
            setActiveMood(filters.mood);
            doSearch(filters.q, filters.region, filters.mood);
        });
    });

    document.querySelectorAll('.mood-category-card').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const mood = this.dataset.mood;
            if (moodFilter)   moodFilter.value   = mood;
            if (searchInput)  searchInput.value  = '';
            if (regionFilter) regionFilter.value = '';
            suppressFilterChange = true;
            syncSelect(moodFilter);
            syncSelect(regionFilter);
            suppressFilterChange = false;
            setActiveMood(mood);
            doSearch('', '', mood);
            if (grid) grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    document.querySelectorAll('.mood-icon-wrap[data-mood-bg]').forEach(function (icon) {
        icon.style.setProperty('--mood-bg', icon.dataset.moodBg || '');
        icon.style.setProperty('--mood-color', icon.dataset.moodColor || '');
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            clearFilters();
            doSearch('', '', '');
        });
    }

    document.addEventListener('currency:changed', function () {
        if (window.Currency) window.Currency.refresh();
    });

    doSearch('', '', '');

})();

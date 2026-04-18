(function () {
    var allDest = [];
    var searchQuery = '';

    var REGION_MAP = {
        europe:      ['europe'],
        asia:        ['asia', 'southeast_asia', 'east_asia', 'south_asia'],
        america:     ['america', 'north_america', 'south_america', 'central_america', 'latin_america', 'caribbean'],
        africa:      ['africa'],
        middle_east: ['middle_east'],
        oceania:     ['oceania'],
    };

    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function fmt(n) {
        return Number(n).toLocaleString();
    }

    function buildCard(d) {
        var img   = d.image_url || 'https://picsum.photos/seed/' + encodeURIComponent(d.name) + '/600/400';
        var priceUsd = d.price_from ? Number(d.price_from) : 0;
        var price = priceUsd > 0
            ? (typeof window.Currency !== 'undefined' ? window.Currency.format(priceUsd) : '$' + fmt(priceUsd))
            : '';
        var badge = d.badge ? '<span class="card-badge">' + esc(d.badge) + '</span>' : '';
        var gem   = d.is_hidden_gem
            ? '<span class="card-badge" style="background:rgba(138,43,226,.85);color:#fff;left:14px;right:auto;"><i class="fas fa-gem"></i> Hidden Gem</span>'
            : '';
        var tags = [];
        if (d.mood)     tags.push('<span class="dest-tag">' + esc(d.mood.replace(/_/g,' ').replace(/\b\w/g, function(c){return c.toUpperCase();})) + '</span>');
        if (d.category) tags.push('<span class="dest-tag">' + esc(d.category.replace(/_/g,' ').replace(/\b\w/g, function(c){return c.toUpperCase();})) + '</span>');

        return '<div class="dest-card">' +
            '<div class="dest-card-img" style="background-image:url(\'' + esc(img) + '\')">' +
                badge + gem +
                (d.match_score ? '<span class="card-rating"><i class="fas fa-star"></i> ' + d.match_score + '% match</span>' : '') +
            '</div>' +
            '<div class="dest-card-body">' +
                '<h3>' + esc(d.name) + '</h3>' +
                '<div class="dest-location"><i class="fas fa-map-marker-alt"></i> ' + esc(d.country || '') +
                    (d.region ? ' &nbsp;&middot;&nbsp; ' + esc(d.region.replace(/_/g,' ').replace(/\b\w/g, function(c){return c.toUpperCase();})) : '') +
                '</div>' +
                (tags.length ? '<div class="dest-tags">' + tags.join('') + '</div>' : '') +
                '<p>' + esc((d.description || '').substring(0, 120)) + (d.description && d.description.length > 120 ? '&hellip;' : '') + '</p>' +
                '<div class="dest-card-footer">' +
                    (priceUsd > 0 ? '<div class="dest-price" data-price-usd="' + priceUsd + '">' + price + ' <span>/ person</span></div>' : '<div></div>') +
                '</div>' +
                '<a href="/destinations/' + d.id + '" class="primary-button" style="margin-top:12px;display:block;text-align:center;text-decoration:none;">Explore <i class="fas fa-arrow-right"></i></a>' +
            '</div>' +
        '</div>';
    }

    function render(list) {
        var grid    = document.getElementById('destGrid');
        var loading = document.getElementById('destLoading');
        var empty   = document.getElementById('destEmpty');
        if (loading) loading.style.display = 'none';
        if (!list || !list.length) {
            grid.style.display = 'none';
            if (empty) empty.style.display = 'block';
            return;
        }
        if (empty) empty.style.display = 'none';
        grid.style.display = 'grid';
        grid.innerHTML = list.map(buildCard).join('');
    }

    function doSearch() {
        var input = document.getElementById('destSearchInput');
        searchQuery = input ? input.value.trim().toLowerCase() : '';
        
        if (searchQuery.length < 2) {
            var activeTab = document.querySelector('.cont-tab.active');
            var filter = activeTab ? activeTab.dataset.filter : 'all';
            filterDest(filter, activeTab);
            return;
        }

        var filtered = allDest.filter(function(d) {
            var name = (d.name || '').toLowerCase();
            var country = (d.country || '').toLowerCase();
            var desc = (d.description || '').toLowerCase();
            return name.indexOf(searchQuery) !== -1 || 
                   country.indexOf(searchQuery) !== -1 || 
                   desc.indexOf(searchQuery) !== -1;
        });

        if (filtered.length < 5) {
            var loading = document.getElementById('destLoading');
            if (loading) loading.style.display = 'grid';
            
            fetch('/api/discover/search?q=' + encodeURIComponent(searchQuery), {
                headers: { 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(results) {
                if (loading) loading.style.display = 'none';
                
                var searchDests = (results || []).map(function(r) {
                    return {
                        id: r.id,
                        name: r.name,
                        country: r.country,
                        description: r.description || '',
                        image_url: r.image_url || '',
                        price_from: 0,
                        badge: r.type === 'new' ? 'New' : null
                    };
                });
                
                var ids = new Set(filtered.map(function(d) { return d.id; }));
                searchDests.forEach(function(d) {
                    if (!ids.has(d.id)) {
                        filtered.push(d);
                        allDest.push(d);
                    }
                });
                
                render(filtered);
            })
            .catch(function() {
                if (loading) loading.style.display = 'none';
                render(filtered);
            });
        } else {
            render(filtered);
        }
    }

    window.filterDest = function (filter, el) {
        document.querySelectorAll('.cont-tab').forEach(function (t) { t.classList.remove('active'); });
        if (el) el.classList.add('active');
        
        var input = document.getElementById('destSearchInput');
        if (input) input.value = '';
        searchQuery = '';
        
        if (filter === 'all') { render(allDest); return; }
        var regions  = REGION_MAP[filter] || [filter];
        var filtered = allDest.filter(function (d) {
            return regions.some(function (r) {
                return (d.region || '').toLowerCase().indexOf(r) !== -1;
            });
        });
        render(filtered);
    };

    var searchInput = document.getElementById('destSearchInput');
    var searchBtn = document.getElementById('destSearchBtn');
    if (searchInput && searchBtn) {
        searchBtn.addEventListener('click', doSearch);
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') doSearch();
        });
        searchInput.addEventListener('input', function() {
            clearTimeout(window.destSearchTimer);
            window.destSearchTimer = setTimeout(doSearch, 450);
        });
    }

    fetch('/api/discover/destinations?active=1', { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            allDest = Array.isArray(data) ? data : (data.data || data.destinations || []);
            render(allDest);
            if (typeof window.Currency !== 'undefined') {
                window.Currency.onCurrencyChange(function() { render(allDest); });
            }
        })
        .catch(function () {
            var loading = document.getElementById('destLoading');
            var empty   = document.getElementById('destEmpty');
            if (loading) loading.style.display = 'none';
            if (empty)   empty.style.display   = 'block';
        });
}());

@extends('layouts.public')

@section('title', 'Destinations — Smart Booking')

@section('content')

<section class="page-hero" style="background: linear-gradient(160deg, rgba(5,20,40,0.75) 0%, rgba(59,31,43,0.50) 100%), url('https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=1920&q=90') center/cover no-repeat;">
    <div>
        <h1><i class="fas fa-map-marked-alt"></i> All Destinations</h1>
        <p>Browse our full catalogue of curated travel destinations around the world.</p>
    </div>
</section>

<div class="dest-wrap">

    <div class="continent-tabs" id="contTabs">
        <div class="cont-tab active" data-filter="all"        onclick="filterDest('all',this)"><i class="fas fa-globe"></i> All</div>
        <div class="cont-tab" data-filter="asia"              onclick="filterDest('asia',this)"><i class="fas fa-globe-asia"></i> Asia</div>
        <div class="cont-tab" data-filter="europe"            onclick="filterDest('europe',this)"><i class="fas fa-globe-europe"></i> Europe</div>
        <div class="cont-tab" data-filter="america"           onclick="filterDest('america',this)"><i class="fas fa-globe-americas"></i> Americas</div>
        <div class="cont-tab" data-filter="africa"            onclick="filterDest('africa',this)"><i class="fas fa-globe-africa"></i> Africa</div>
        <div class="cont-tab" data-filter="middle_east"       onclick="filterDest('middle_east',this)"><i class="fas fa-mosque"></i> Middle East</div>
        <div class="cont-tab" data-filter="oceania"           onclick="filterDest('oceania',this)"><i class="fas fa-water"></i> Oceania</div>
    </div>

    <div id="destLoading" class="dest-grid">
        @for($i = 0; $i < 6; $i++)
        <div class="dest-card">
            <div class="dest-card-img skeleton"></div>
            <div class="dest-card-body">
                <div class="sk-line medium skeleton" style="height:20px;margin-bottom:10px;"></div>
                <div class="sk-line short skeleton" style="height:14px;margin-bottom:14px;"></div>
                <div class="sk-line full skeleton"></div>
                <div class="sk-line full skeleton"></div>
                <div class="sk-line medium skeleton"></div>
            </div>
        </div>
        @endfor
    </div>

    <div class="dest-grid" id="destGrid" style="display:none;"></div>

    <div id="destEmpty" style="display:none;text-align:center;padding:60px 20px;">
        <i class="fas fa-map-marked-alt" style="font-size:40px;opacity:.3;"></i>
        <p style="margin-top:14px;color:var(--text-muted);">No destinations found for this filter.</p>
    </div>

</div>

@endsection

@push('scripts')
<script>
(function () {
    var allDest = [];

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
        var price = d.price_from ? '$' + fmt(d.price_from) : '';
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
                    (price ? '<div class="dest-price">' + price + ' <span>/ person</span></div>' : '<div></div>') +
                    '<a href="/destinations/' + d.id + '" class="primary-button" style="padding:9px 18px;font-size:13px;text-decoration:none;">Explore <i class="fas fa-arrow-right"></i></a>' +
                '</div>' +
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

    window.filterDest = function (filter, el) {
        document.querySelectorAll('.cont-tab').forEach(function (t) { t.classList.remove('active'); });
        if (el) el.classList.add('active');
        if (filter === 'all') { render(allDest); return; }
        var regions  = REGION_MAP[filter] || [filter];
        var filtered = allDest.filter(function (d) {
            return regions.some(function (r) {
                return (d.region || '').toLowerCase().indexOf(r) !== -1;
            });
        });
        render(filtered);
    };

    fetch('/api/discover/destinations?active=1', { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            allDest = Array.isArray(data) ? data : (data.data || data.destinations || []);
            render(allDest);
        })
        .catch(function () {
            var loading = document.getElementById('destLoading');
            var empty   = document.getElementById('destEmpty');
            if (loading) loading.style.display = 'none';
            if (empty)   empty.style.display   = 'block';
        });
}());
</script>
@endpush
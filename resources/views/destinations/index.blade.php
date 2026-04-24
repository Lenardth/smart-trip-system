@extends('layouts.public')

@section('title', 'Destinations — Smart Booking')

@section('content')

<section class="page-hero" style="background: linear-gradient(160deg, rgba(5,20,40,0.75) 0%, rgba(59,31,43,0.50) 100%), url('https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=1920&q=90') center/cover no-repeat; min-height: 450px; display: flex; align-items: center;">
    <div>
        <h1 style="margin-bottom: 16px;"><i class="fas fa-map-marked-alt"></i> All Destinations</h1>
        <p style="font-size: 15px; max-width: 600px; margin: 0 auto;">Browse our full catalogue of curated travel destinations around the world.</p>
        <div class="hero-search" style="margin-top:24px;">
            <input type="text" id="destSearchInput" placeholder="Search destinations, countries..." autocomplete="off" style="flex:1;min-width:240px;padding:14px 20px;border:none;border-radius:4px;font-size:15px;font-family:'Georgia',serif;color:var(--deep);background:var(--card-bg);">
            <button id="destSearchBtn" style="background:var(--gold);color:var(--deep);border:none;padding:14px 28px;border-radius:4px;font-weight:bold;font-size:15px;cursor:pointer;font-family:'Georgia',serif;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
    </div>
</section>

<div class="dest-wrap">

    <div class="continent-tabs" id="contTabs">
        <div class="cont-tab active" data-filter="all"        onclick="filterDest('all',this)"><i class="fas fa-globe"></i> All</div>
        <div class="cont-tab" data-filter="asia"              onclick="filterDest('asia',this)"><i class="fas fa-globe-asia"></i> Asia</div>
        <div class="cont-tab" data-filter="europe"            onclick="filterDest('europe',this)"><i class="fas fa-globe-europe"></i> Europe</div>
        <div class="cont-tab" data-filter="america"           onclick="filterDest('america',this)"><i class="fas fa-globe-america"></i> America</div>
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
<script src="/js/destinations-search.js?v=2"></script>
@endpush

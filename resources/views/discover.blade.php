<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Discover — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @vite(['resources/css/pages/discover.css', 'resources/js/pages/discover.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="main-header">
    <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo">
    <span class="logo-text">Smart Booking</span>
</header>

<nav>
    <div class="nav-container">
        <a href="/"><i class="fas fa-home"></i> Home</a>
        <a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/plan-trip"><i class="fas fa-route"></i> Plan Trip</a>
        <a href="/flights"><i class="fas fa-plane"></i> Book Flights</a>
        <a href="/discover" class="active"><i class="fas fa-compass"></i> Discover</a>
        <a href="/destinations"><i class="fas fa-map-marked-alt"></i> Destinations</a>
        <a href="/community"><i class="fas fa-users"></i> Community</a>
        <a href="/login"><i class="fas fa-sign-in-alt"></i> Login</a>
    </div>
</nav>

<section class="page-hero">
    <div>
        <h1><i class="fas fa-compass"></i> Discover</h1>
        <p>Explore trending destinations, hidden gems, and AI-curated picks.</p>
        <div class="hero-search">
            <input type="text" id="searchInput" placeholder="Search destinations, countries, experiences…" autocomplete="off">
            <button id="searchBtn"><i class="fas fa-search"></i> Search</button>
        </div>
    </div>
</section>

<div class="discover-wrap">

    {{-- Filter Tabs --}}
    <div class="filter-tabs" id="filterTabs">
        <span class="filter-tab active" data-category="all">All</span>
        <span class="filter-tab" data-category="trending">Trending</span>
        <span class="filter-tab" data-category="ai_pick">AI Picks</span>
        <span class="filter-tab" data-category="beach">Beach</span>
        <span class="filter-tab" data-category="mountain">Mountain</span>
        <span class="filter-tab" data-category="historical">Historical</span>
        <span class="filter-tab" data-category="food_culture">Food &amp; Culture</span>
        <span class="filter-tab" data-category="eco_tourism">Eco-Tourism</span>
    </div>

    {{-- Region Pills --}}
    <div class="region-row" id="regionRow">
        <div class="region-pill active" data-region="all"><i class="fas fa-globe-americas"></i> Worldwide</div>
        <div class="region-pill" data-region="asia"><i class="fas fa-globe-asia"></i> Asia</div>
        <div class="region-pill" data-region="europe"><i class="fas fa-globe-europe"></i> Europe</div>
        <div class="region-pill" data-region="america"><i class="fas fa-globe-americas"></i> America</div>
        <div class="region-pill" data-region="africa"><i class="fas fa-globe-africa"></i> Africa</div>
        <div class="region-pill" data-region="oceania"><i class="fas fa-globe-asia"></i> Oceania</div>
    </div>

    {{-- Results info --}}
    <div class="results-info" id="resultsInfo"></div>

    {{-- Destinations Grid --}}
    <div class="destinations-grid" id="destinationsGrid">
        @for ($i = 0; $i < 6; $i++)
        <div class="destination-card">
            <div class="destination-image skeleton"></div>
            <div class="destination-content">
                <div class="sk-line medium skeleton"></div>
                <div class="sk-line short skeleton"></div>
                <div class="sk-line full skeleton"></div>
                <div class="sk-line full skeleton"></div>
                <div class="sk-line medium skeleton" style="margin-top:10px;height:36px;border-radius:4px;"></div>
            </div>
        </div>
        @endfor
    </div>

    {{-- Hidden Gems --}}
    <div class="featured-section">
        <h2 class="section-title">Hidden Gems</h2>
        <p style="color:var(--text-sub);font-size:15px;margin-top:0;">
            Destinations our AI found that most travelers overlook — but love once they visit.
        </p>
        <div class="featured-grid" id="hiddenGemsGrid">
            @for ($i = 0; $i < 3; $i++)
            <div class="featured-card">
                <div class="feat-img skeleton"></div>
                <div class="feat-body">
                    <div class="sk-line medium skeleton" style="background:rgba(255,255,255,.15);"></div>
                    <div class="sk-line full skeleton"   style="background:rgba(255,255,255,.1); margin-top:6px;"></div>
                </div>
            </div>
            @endfor
        </div>
    </div>

</div>

{{-- Toast --}}
<div class="toast" id="toast">
    <i class="fas fa-info-circle"></i>
    <span id="toastMsg"></span>
</div>

<footer class="footer">
    <div style="max-width:1200px;margin:0 auto;">
        <p>© 2026 Smart Trip Planner | Laravel Web Application Project</p>
        <div style="margin-top:15px;">
            <a href="#"><i class="fab fa-github"></i></a>
            <a href="#"><i class="fab fa-laravel"></i></a>
            <a href="#"><i class="fas fa-graduation-cap"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
    </div>
</footer>

<script>
    window.__DISCOVER__ = { csrfToken: "{{ csrf_token() }}" };
</script>


</body>
</html>

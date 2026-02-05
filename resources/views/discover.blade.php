<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Discover — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--deep:#3b1f2b;--deep-alt:#4d2a3a;--gold:#c9a96e;--gold-hover:#b8955a;--cream:#f5f0eb;--card-bg:#fff8f2;--border:#e2d5c7;--border-soft:#d4c4b0;--text-light:#f5e6d3;--text-muted:#6b5b4f;--text-sub:#d4c4b0;}
        body{font-family:'Georgia',serif;margin:0;padding:0;background:var(--cream);color:#2c2c2c;text-align:center;}
        .main-header{display:flex;align-items:center;justify-content:flex-start;gap:15px;padding:20px 40px 20px 60px;background-color:var(--deep);}
        .logo{height:100px;width:auto;min-width:100px;object-fit:contain;filter:brightness(0) invert(1);}
        .logo-text{font-size:32px;font-weight:700;color:var(--text-light);letter-spacing:2px;text-shadow:1px 1px 3px rgba(0,0,0,0.4);font-variant:small-caps;}
        .nav-container{display:flex;justify-content:center;background:var(--gold);padding:15px;flex-wrap:wrap;border-bottom:2px solid var(--gold-hover);}
        .nav-container a{text-decoration:none;color:var(--deep);font-size:15px;font-weight:bold;padding:10px 15px;border-radius:4px;transition:all 0.3s ease;display:flex;align-items:center;gap:8px;letter-spacing:0.5px;font-family:'Georgia',serif;}
        .nav-container a:hover,.nav-container a.active{background:rgba(59,31,43,0.18);transform:translateY(-2px);}
        .page-hero{background:linear-gradient(rgba(30,15,20,0.6),rgba(30,15,20,0.6)),url('/img/pexels-mikegles-30931569.jpg');background-size:cover;background-position:center;height:260px;display:flex;align-items:center;justify-content:center;text-align:center;color:var(--text-light);}
        .page-hero h1{font-size:36px;font-weight:normal;letter-spacing:1px;margin:0 0 10px;color:var(--text-light);}
        .page-hero p{font-size:16px;color:var(--text-sub);margin:0 0 20px;}

        /* Search Bar in Hero */
        .hero-search{display:flex;gap:10px;justify-content:center;max-width:580px;margin:0 auto;flex-wrap:wrap;}
        .hero-search input{flex:1;min-width:240px;padding:14px 20px;border:none;border-radius:4px;font-size:15px;font-family:'Georgia',serif;color:var(--deep);background:var(--card-bg);}
        .hero-search input:focus{outline:none;box-shadow:0 0 0 3px rgba(201,169,110,0.3);}
        .hero-search button{background:var(--gold);color:var(--deep);border:none;padding:14px 28px;border-radius:4px;font-weight:bold;font-size:15px;cursor:pointer;font-family:'Georgia',serif;transition:background 0.3s;display:flex;align-items:center;gap:8px;}
        .hero-search button:hover{background:var(--gold-hover);}

        .section-title{color:var(--deep);font-size:28px;margin-bottom:10px;position:relative;padding-bottom:15px;font-weight:normal;letter-spacing:1px;}
        .section-title:after{content:'';position:absolute;bottom:0;left:50%;transform:translateX(-50%);width:60px;height:2px;background:var(--gold);}
        .section-subtitle{color:var(--text-muted);font-size:16px;margin-bottom:30px;max-width:800px;margin-left:auto;margin-right:auto;}
        .primary-button{background:var(--gold);color:var(--deep);border:none;padding:12px 30px;border-radius:4px;cursor:pointer;font-weight:bold;font-size:15px;transition:background 0.3s ease,box-shadow 0.3s ease;display:inline-flex;align-items:center;justify-content:center;gap:10px;font-family:'Georgia',serif;letter-spacing:0.5px;box-shadow:0 2px 6px rgba(0,0,0,0.15);text-decoration:none;}
        .primary-button:hover{background:var(--gold-hover);box-shadow:0 3px 10px rgba(0,0,0,0.22);}

        .discover-wrap{max-width:1200px;margin:40px auto;padding:0 20px;}

        /* Filter Tabs */
        .filter-tabs{display:flex;gap:10px;flex-wrap:wrap;justify-content:center;margin-bottom:30px;}
        .filter-tab{padding:10px 24px;background:var(--card-bg);border:1px solid var(--deep);border-radius:4px;cursor:pointer;transition:all 0.3s ease;font-weight:600;color:var(--deep);font-size:14px;font-family:'Georgia',serif;}
        .filter-tab:hover,.filter-tab.active{background:var(--deep);color:var(--text-light);}

        /* Destinations Grid */
        .destinations-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:25px;margin-bottom:50px;}
        .destination-card{background:var(--card-bg);border-radius:6px;overflow:hidden;box-shadow:0 3px 10px rgba(59,31,43,0.08);transition:transform 0.3s ease,box-shadow 0.3s ease;cursor:pointer;border:1px solid var(--border);display:flex;flex-direction:column;}
        .destination-card:hover{transform:translateY(-5px);box-shadow:0 8px 22px rgba(59,31,43,0.15);}
        .destination-image{height:190px;width:100%;background-size:cover;background-position:center;flex-shrink:0;position:relative;}
        .dest-badge{position:absolute;top:12px;left:12px;background:var(--deep);color:var(--text-light);font-size:11px;font-weight:bold;padding:4px 10px;border-radius:3px;letter-spacing:0.5px;}
        .destination-content{padding:20px;display:flex;flex-direction:column;flex-grow:1;}
        .destination-content h3{margin:0 0 8px;color:var(--deep);font-weight:normal;font-size:19px;letter-spacing:0.5px;}
        .destination-meta{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;font-size:14px;}
        .price-tag{background:var(--gold);color:var(--deep);padding:5px 14px;border-radius:3px;font-weight:bold;font-size:13px;}
        .mood-indicator{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:#f5efe8;border-radius:3px;font-size:13px;color:var(--deep);border:1px solid var(--border);}
        .destination-content p{color:var(--text-muted);font-size:14px;line-height:1.5;flex-grow:1;margin:0 0 14px;}
        .destination-content .primary-button{margin-top:auto;width:100%;padding:10px;font-size:14px;}

        /* Featured / Hidden Gems */
        .featured-section{background:linear-gradient(135deg,var(--deep),var(--deep-alt));border-radius:6px;padding:50px 40px;margin-bottom:50px;color:var(--text-light);box-shadow:0 8px 28px rgba(59,31,43,0.25);border:1px solid rgba(201,169,110,0.2);}
        .featured-section h2{color:var(--text-light);font-weight:normal;font-size:28px;margin-top:0;letter-spacing:1px;}
        .featured-section .section-title:after{background:var(--gold);}
        .featured-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:22px;margin-top:28px;}
        .featured-card{background:rgba(255,248,242,0.08);border:1px solid rgba(201,169,110,0.2);border-radius:6px;overflow:hidden;transition:transform 0.3s ease;}
        .featured-card:hover{transform:translateY(-4px);}
        .featured-card .feat-img{height:150px;background-size:cover;background-position:center;}
        .featured-card .feat-body{padding:18px;text-align:left;}
        .featured-card h4{color:var(--text-light);font-weight:normal;font-size:17px;margin:0 0 6px;}
        .featured-card p{color:var(--text-sub);font-size:13px;margin:0;line-height:1.5;}
        .featured-card .feat-tag{display:inline-block;background:var(--gold);color:var(--deep);font-size:11px;font-weight:bold;padding:3px 8px;border-radius:3px;margin-top:8px;}

        /* Region Filter Row */
        .region-row{display:flex;gap:16px;flex-wrap:wrap;justify-content:center;margin-bottom:28px;}
        .region-pill{display:flex;align-items:center;gap:8px;padding:8px 18px;background:var(--card-bg);border:1px solid var(--border);border-radius:4px;cursor:pointer;transition:all 0.3s;font-size:13px;color:var(--deep);font-family:'Georgia',serif;font-weight:600;}
        .region-pill:hover,.region-pill.active{border-color:var(--gold);background:#fdf0dc;}

        .footer{background:var(--deep);color:var(--text-sub);text-align:center;padding:30px 20px;margin-top:60px;}
        .footer a{color:var(--gold);margin:0 10px;transition:color 0.3s ease;text-decoration:none;}
        .footer a:hover{color:var(--text-light);}

        @media(max-width:768px){
            .main-header{justify-content:center;padding:15px 20px;}.logo{height:60px;min-width:60px;}.logo-text{font-size:24px;}
            .nav-container{flex-direction:column;align-items:center;}.nav-container a{font-size:14px;padding:8px 10px;}
            .featured-section{padding:30px 20px;}
        }
    </style>
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
        <a href="/flights"><i class="fas fa-plane"></i> Book Flights</a> <!-- Flight Booking Added -->
        <a href="/discover"><i class="fas fa-compass"></i> Discover</a>
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
            <input type="text" placeholder="Search destinations, countries, experiences…">
            <button><i class="fas fa-search"></i> Search</button>
        </div>
    </div>
</section>

<div class="discover-wrap">

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <span class="filter-tab active">All</span>
        <span class="filter-tab">Trending</span>
        <span class="filter-tab">AI Picks</span>
        <span class="filter-tab">Beach</span>
        <span class="filter-tab">Mountain</span>
        <span class="filter-tab">Historical</span>
        <span class="filter-tab">Food & Culture</span>
        <span class="filter-tab">Eco-Tourism</span>
    </div>

    <!-- Region Pills -->
    <div class="region-row">
        <div class="region-pill active"><i class="fas fa-globe-americas"></i> Worldwide</div>
        <div class="region-pill"><i class="fas fa-globe-asia"></i> Asia</div>
        <div class="region-pill"><i class="fas fa-globe-europe"></i> Europe</div>
        <div class="region-pill"><i class="fas fa-globe-americas"></i> America</div>
        <div class="region-pill"><i class="fas fa-globe-africa"></i> Africa</div>
        <div class="region-pill"><i class="fas fa-globe-asia"></i> Oceania</div>
    </div>

    <!-- Destinations Grid -->
    <div class="destinations-grid">
        <div class="destination-card">
            <div class="destination-image" style="background-image:url('https://images.unsplash.com/photo-1516483638261-f4dbaf036963');"><span class="dest-badge">🔥 Trending</span></div>
            <div class="destination-content">
                <h3>Bali, Indonesia</h3>
                <div class="destination-meta"><span class="price-tag">$1,200+</span><span class="mood-indicator"><i class="fas fa-spa"></i> Relaxed</span></div>
                <p>Yoga retreats, stunning temples, and lush rice terraces await in this tropical paradise.</p>
                <button class="primary-button">Explore <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>
        <div class="destination-card">
            <div class="destination-image" style="background-image:url('https://images.unsplash.com/photo-1511739001486-6bfe10ce785f');"><span class="dest-badge">⭐ AI Pick</span></div>
            <div class="destination-content">
                <h3>Kyoto, Japan</h3>
                <div class="destination-meta"><span class="price-tag">$2,100+</span><span class="mood-indicator"><i class="fas fa-landmark"></i> Cultural</span></div>
                <p>Ancient temples, serene gardens, and traditional tea ceremonies in Japan's cultural heartland.</p>
                <button class="primary-button">Explore <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>
        <div class="destination-card">
            <div class="destination-image" style="background-image:url('https://images.unsplash.com/photo-1579530190412-b35a65e17c8d');"></div>
            <div class="destination-content">
                <h3>Swiss Alps</h3>
                <div class="destination-meta"><span class="price-tag">$2,800+</span><span class="mood-indicator"><i class="fas fa-mountain"></i> Adventurous</span></div>
                <p>Breathtaking peaks, world-class skiing, and luxury mountain chalets surrounded by pristine nature.</p>
                <button class="primary-button">Explore <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>
        <div class="destination-card">
            <div class="destination-image" style="background-image:url('https://images.unsplash.com/photo-1578662996442-48f60103fc96');"><span class="dest-badge">💝 Romantic</span></div>
            <div class="destination-content">
                <h3>Santorini, Greece</h3>
                <div class="destination-meta"><span class="price-tag">$1,800+</span><span class="mood-indicator"><i class="fas fa-heart"></i> Romantic</span></div>
                <p>White-washed villages, legendary sunsets, and crystal-clear Aegean waters.</p>
                <button class="primary-button">Explore <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>
        <div class="destination-card">
            <div class="destination-image" style="background-image:url('https://images.unsplash.com/photo-1477959858617-67f85cf4f1df');"></div>
            <div class="destination-content">
                <h3>Paris, France</h3>
                <div class="destination-meta"><span class="price-tag">$2,400+</span><span class="mood-indicator"><i class="fas fa-landmark"></i> Cultural</span></div>
                <p>Museums, haute cuisine, and iconic landmarks make Paris the ultimate city for culture lovers.</p>
                <button class="primary-button">Explore <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>
        <div class="destination-card">
            <div class="destination-image" style="background-image:url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4');"><span class="dest-badge">🌿 Eco</span></div>
            <div class="destination-content">
                <h3>New Zealand</h3>
                <div class="destination-meta"><span class="price-tag">$3,000+</span><span class="mood-indicator"><i class="fas fa-leaf"></i> Eco-Travel</span></div>
                <p>Dramatic fjords, volcanic landscapes, and some of the world's best hiking trails.</p>
                <button class="primary-button">Explore <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>
    </div>

    <!-- Hidden Gems -->
    <div class="featured-section">
        <h2 class="section-title" style="color:var(--text-light);">Hidden Gems</h2>
        <p style="color:var(--text-sub);font-size:15px;margin-top:0;">Destinations our AI found that most travelers overlook — but love once they visit.</p>
        <div class="featured-grid">
            <div class="featured-card">
                <div class="feat-img" style="background-image:url('https://images.unsplash.com/photo-1552160554-bdfd817add7b');"></div>
                <div class="feat-body">
                    <h4>Kotor, Montenegro</h4>
                    <p>A stunning medieval walled city nestled between mountains and the Adriatic Sea.</p>
                    <span class="feat-tag">94% Match</span>
                </div>
            </div>
            <div class="featured-card">
                <div class="feat-img" style="background-image:url('https://images.unsplash.com/photo-1585386959984-a4155224a1ad');"></div>
                <div class="feat-body">
                    <h4>Chiang Mai, Thailand</h4>
                    <p>Ancient temples, night bazaars, and ethical elephant sanctuaries in northern Thailand.</p>
                    <span class="feat-tag">91% Match</span>
                </div>
            </div>
            <div class="featured-card">
                <div class="feat-img" style="background-image:url('https://images.unsplash.com/photo-1477959858617-67f85cf4f1df');"></div>
                <div class="feat-body">
                    <h4>Azores, Portugal</h4>
                    <p>Volcanic islands with hot springs, whale watching, and dramatic coastal cliffs.</p>
                    <span class="feat-tag">89% Match</span>
                </div>
            </div>
        </div>
    </div>
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
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
    });
});
document.querySelectorAll('.region-pill').forEach(pill => {
    pill.addEventListener('click', function() {
        document.querySelectorAll('.region-pill').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>
</body>
</html>

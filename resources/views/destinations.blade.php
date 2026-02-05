<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Destinations — Smart Booking</title>
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
        .page-hero{background:linear-gradient(rgba(30,15,20,0.6),rgba(30,15,20,0.6)),url('/img/pexels-mikegles-30931569.jpg');background-size:cover;background-position:center;height:240px;display:flex;align-items:center;justify-content:center;text-align:center;color:var(--text-light);}
        .page-hero h1{font-size:36px;font-weight:normal;letter-spacing:1px;margin:0 0 8px;color:var(--text-light);}
        .page-hero p{font-size:16px;color:var(--text-sub);margin:0;}
        .section-title{color:var(--deep);font-size:28px;margin-bottom:10px;position:relative;padding-bottom:15px;font-weight:normal;letter-spacing:1px;}
        .section-title:after{content:'';position:absolute;bottom:0;left:50%;transform:translateX(-50%);width:60px;height:2px;background:var(--gold);}
        .section-subtitle{color:var(--text-muted);font-size:16px;margin-bottom:30px;max-width:800px;margin-left:auto;margin-right:auto;}
        .primary-button{background:var(--gold);color:var(--deep);border:none;padding:12px 30px;border-radius:4px;cursor:pointer;font-weight:bold;font-size:15px;transition:background 0.3s ease,box-shadow 0.3s ease;display:inline-flex;align-items:center;justify-content:center;gap:10px;font-family:'Georgia',serif;letter-spacing:0.5px;box-shadow:0 2px 6px rgba(0,0,0,0.15);text-decoration:none;}
        .primary-button:hover{background:var(--gold-hover);box-shadow:0 3px 10px rgba(0,0,0,0.22);}

        .dest-wrap{max-width:1200px;margin:40px auto;padding:0 20px;}

        /* Continent Tabs */
        .continent-tabs{display:flex;gap:8px;flex-wrap:wrap;justify-content:center;margin-bottom:32px;}
        .cont-tab{padding:10px 22px;background:var(--card-bg);border:1px solid var(--border);border-radius:4px;cursor:pointer;transition:all 0.3s;font-size:14px;font-weight:600;color:var(--deep);font-family:'Georgia',serif;display:flex;align-items:center;gap:7px;}
        .cont-tab:hover,.cont-tab.active{background:var(--deep);color:var(--text-light);border-color:var(--deep);}

        /* Destination Grid (larger cards) */
        .dest-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:28px;margin-bottom:50px;}
        .dest-card{background:var(--card-bg);border-radius:6px;overflow:hidden;box-shadow:0 3px 10px rgba(59,31,43,0.08);border:1px solid var(--border);transition:transform 0.3s,box-shadow 0.3s;cursor:pointer;display:flex;flex-direction:column;}
        .dest-card:hover{transform:translateY(-5px);box-shadow:0 8px 22px rgba(59,31,43,0.15);}
        .dest-card-img{height:210px;background-size:cover;background-position:center;position:relative;flex-shrink:0;}
        .dest-card-img .card-badge{position:absolute;top:14px;right:14px;background:var(--deep);color:var(--text-light);font-size:12px;font-weight:bold;padding:5px 12px;border-radius:3px;}
        .dest-card-img .card-rating{position:absolute;bottom:14px;left:14px;background:rgba(0,0,0,0.55);color:#fff;font-size:13px;padding:5px 10px;border-radius:3px;display:flex;align-items:center;gap:5px;}
        .dest-card-img .card-rating i{color:#f0c040;}
        .dest-card-body{padding:22px;text-align:left;flex-grow:1;display:flex;flex-direction:column;}
        .dest-card-body h3{color:var(--deep);font-weight:normal;font-size:20px;margin:0 0 6px;}
        .dest-card-body .dest-location{color:var(--text-muted);font-size:13px;margin:0 0 12px;display:flex;align-items:center;gap:6px;}
        .dest-card-body p{color:var(--text-muted);font-size:14px;line-height:1.5;flex-grow:1;margin:0 0 16px;}
        .dest-card-footer{display:flex;justify-content:space-between;align-items:center;margin-top:auto;}
        .dest-price{font-size:18px;color:var(--deep);font-weight:bold;}<br>
        .dest-price span{font-size:13px;color:var(--text-muted);font-weight:normal;}
        .dest-tags{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px;}
        .dest-tag{font-size:11px;background:#f0ece6;color:var(--deep);padding:3px 9px;border-radius:3px;font-weight:600;}

        /* Compare Section */
        .compare-section{background:var(--card-bg);border-radius:6px;padding:44px 40px;border:1px solid var(--border);box-shadow:0 3px 10px rgba(59,31,43,0.08);margin-bottom:50px;}
        .compare-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-top:28px;}
        .compare-card{border:2px dashed var(--border);border-radius:6px;padding:30px 18px;cursor:pointer;transition:all 0.3s;text-align:center;min-height:140px;display:flex;align-items:center;justify-content:center;flex-direction:column;}
        .compare-card:hover{border-color:var(--gold);background:#fdf8f2;}
        .compare-card .compare-icon{font-size:2em;color:var(--border-soft);margin-bottom:10px;}
        .compare-card p{color:var(--text-muted);font-size:14px;margin:0;}
        .compare-card.filled{border:1px solid var(--border);background:var(--card-bg);flex-direction:row;gap:14px;padding:16px;text-align:left;}
        .compare-card.filled .compare-thumb{width:52px;height:52px;border-radius:5px;background-size:cover;background-position:center;flex-shrink:0;}
        .compare-card.filled .compare-info h4{color:var(--deep);font-weight:normal;font-size:15px;margin:0;}
        .compare-card.filled .compare-info p{color:var(--text-muted);font-size:12px;margin:2px 0 0;}

        .footer{background:var(--deep);color:var(--text-sub);text-align:center;padding:30px 20px;margin-top:60px;}
        .footer a{color:var(--gold);margin:0 10px;transition:color 0.3s ease;text-decoration:none;}
        .footer a:hover{color:var(--text-light);}

        @media(max-width:768px){
            .main-header{justify-content:center;padding:15px 20px;}.logo{height:60px;min-width:60px;}.logo-text{font-size:24px;}
            .nav-container{flex-direction:column;align-items:center;}.nav-container a{font-size:14px;padding:8px 10px;}
            .dest-grid{grid-template-columns:1fr;}.compare-section{padding:28px 18px;}
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
        <h1><i class="fas fa-map-marked-alt"></i> All Destinations</h1>
        <p>Browse our full catalogue of curated travel destinations around the world.</p>
    </div>
</section>

<div class="dest-wrap">

    <!-- Continent Tabs -->
    <div class="continent-tabs">
        <div class="cont-tab active"><i class="fas fa-globe"></i> All</div>
        <div class="cont-tab"><i class="fas fa-globe-asia"></i> Asia</div>
        <div class="cont-tab"><i class="fas fa-globe-europe"></i> Europe</div>
        <div class="cont-tab"><i class="fas fa-globe-americas"></i> Americas</div>
        <div class="cont-tab"><i class="fas fa-globe-africa"></i> Africa</div>
        <div class="cont-tab"><i class="fas fa-globe-asia"></i> Oceania</div>
    </div>

    <!-- Destination Grid -->
    <div class="dest-grid">
        <div class="dest-card">
            <div class="dest-card-img" style="background-image:url('https://images.unsplash.com/photo-1516483638261-f4dbaf036963');"><span class="card-badge">Popular</span><span class="card-rating"><i class="fas fa-star"></i> 4.8</span></div>
            <div class="dest-card-body">
                <h3>Bali, Indonesia</h3>
                <div class="dest-location"><i class="fas fa-map-marker-alt"></i> Southeast Asia</div>
                <div class="dest-tags"><span class="dest-tag">Beach</span><span class="dest-tag">Spiritual</span><span class="dest-tag">Relaxation</span></div>
                <p>A tropical paradise known for its stunning temples, rice terraces, and vibrant arts scene. Perfect for yoga, surfing, and cultural exploration.</p>
                <div class="dest-card-footer"><div class="dest-price">$1,200 <span>/ person</span></div><button class="primary-button" style="padding:9px 18px;font-size:13px;">View Details</button></div>
            </div>
        </div>
        <div class="dest-card">
            <div class="dest-card-img" style="background-image:url('https://images.unsplash.com/photo-1511739001486-6bfe10ce785f');"><span class="card-badge">Top Rated</span><span class="card-rating"><i class="fas fa-star"></i> 4.9</span></div>
            <div class="dest-card-body">
                <h3>Kyoto, Japan</h3>
                <div class="dest-location"><i class="fas fa-map-marker-alt"></i> East Asia</div>
                <div class="dest-tags"><span class="dest-tag">History</span><span class="dest-tag">Temples</span><span class="dest-tag">Culture</span></div>
                <p>Japan's cultural capital is home to over 1,600 Buddhist temples, stunning bamboo groves, and traditional geisha districts. Best visited during cherry blossom season.</p>
                <div class="dest-card-footer"><div class="dest-price">$2,100 <span>/ person</span></div><button class="primary-button" style="padding:9px 18px;font-size:13px;">View Details</button></div>
            </div>
        </div>
        <div class="dest-card">
            <div class="dest-card-img" style="background-image:url('https://images.unsplash.com/photo-1579530190412-b35a65e17c8d');"><span class="card-rating"><i class="fas fa-star"></i> 4.7</span></div>
            <div class="dest-card-body">
                <h3>Swiss Alps</h3>
                <div class="dest-location"><i class="fas fa-map-marker-alt"></i> Western Europe</div>
                <div class="dest-tags"><span class="dest-tag">Skiing</span><span class="dest-tag">Hiking</span><span class="dest-tag">Luxury</span></div>
                <p>World-class skiing, dramatic mountain vistas, and charming alpine villages. The Matterhorn and Jungfrau peaks offer unforgettable views year-round.</p>
                <div class="dest-card-footer"><div class="dest-price">$2,800 <span>/ person</span></div><button class="primary-button" style="padding:9px 18px;font-size:13px;">View Details</button></div>
            </div>
        </div>
        <div class="dest-card">
            <div class="dest-card-img" style="background-image:url('https://images.unsplash.com/photo-1578662996442-48f60103fc96');"><span class="card-badge">Romantic</span><span class="card-rating"><i class="fas fa-star"></i> 4.8</span></div>
            <div class="dest-card-body">
                <h3>Santorini, Greece</h3>
                <div class="dest-location"><i class="fas fa-map-marker-alt"></i> Southern Europe</div>
                <div class="dest-tags"><span class="dest-tag">Romance</span><span class="dest-tag">Sunset</span><span class="dest-tag">Island</span></div>
                <p>Iconic white-washed buildings with blue domes perched on volcanic cliffs. The sunsets here are legendary, and the local cuisine is exceptional.</p>
                <div class="dest-card-footer"><div class="dest-price">$1,800 <span>/ person</span></div><button class="primary-button" style="padding:9px 18px;font-size:13px;">View Details</button></div>
            </div>
        </div>
        <div class="dest-card">
            <div class="dest-card-img" style="background-image:url('https://images.unsplash.com/photo-1477959858617-67f85cf4f1df');"><span class="card-rating"><i class="fas fa-star"></i> 4.6</span></div>
            <div class="dest-card-body">
                <h3>Paris, France</h3>
                <div class="dest-location"><i class="fas fa-map-marker-alt"></i> Western Europe</div>
                <div class="dest-tags"><span class="dest-tag">Art</span><span class="dest-tag">Cuisine</span><span class="dest-tag">Fashion</span></div>
                <p>The Eiffel Tower, Louvre Museum, and legendary café culture make Paris an eternal favourite. Best explored on foot through its charming arrondissements.</p>
                <div class="dest-card-footer"><div class="dest-price">$2,400 <span>/ person</span></div><button class="primary-button" style="padding:9px 18px;font-size:13px;">View Details</button></div>
            </div>
        </div>
        <div class="dest-card">
            <div class="dest-card-img" style="background-image:url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4');"><span class="card-badge">Eco</span><span class="card-rating"><i class="fas fa-star"></i> 4.9</span></div>
            <div class="dest-card-body">
                <h3>New Zealand</h3>
                <div class="dest-location"><i class="fas fa-map-marker-alt"></i> Oceania</div>
                <div class="dest-tags"><span class="dest-tag">Hiking</span><span class="dest-tag">Nature</span><span class="dest-tag">Adventure</span></div>
                <p>From the fjords of Milford Sound to the volcanic landscapes of the North Island, New Zealand offers some of the world's most dramatic scenery.</p>
                <div class="dest-card-footer"><div class="dest-price">$3,000 <span>/ person</span></div><button class="primary-button" style="padding:9px 18px;font-size:13px;">View Details</button></div>
            </div>
        </div>
    </div>

    <!-- Compare Section -->
    <div class="compare-section">
        <h2 class="section-title">Compare Destinations</h2>
        <p class="section-subtitle">Select up to 3 destinations to compare prices, ratings, and activities side by side.</p>
        <div class="compare-grid">
            <div class="compare-card filled">
                <div class="compare-thumb" style="background-image:url('https://images.unsplash.com/photo-1516483638261-f4dbaf036963');"></div>
                <div class="compare-info"><h4>Bali</h4><p>$1,200 · 4.8★</p></div>
            </div>
            <div class="compare-card filled">
                <div class="compare-thumb" style="background-image:url('https://images.unsplash.com/photo-1578662996442-48f60103fc96');"></div>
                <div class="compare-info"><h4>Santorini</h4><p>$1,800 · 4.8★</p></div>
            </div>
            <div class="compare-card">
                <div class="compare-icon"><i class="fas fa-plus-circle"></i></div>
                <p>Add destination</p>
            </div>
        </div>
        <div style="text-align:center;margin-top:24px;">
            <button class="primary-button"><i class="fas fa-columns"></i> Compare Now</button>
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
document.querySelectorAll('.cont-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.cont-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>
</body>
</html>

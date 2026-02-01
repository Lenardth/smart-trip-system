<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --deep:#3b1f2b;--deep-alt:#4d2a3a;--gold:#c9a96e;--gold-hover:#b8955a;
            --cream:#f5f0eb;--card-bg:#fff8f2;--border:#e2d5c7;--border-soft:#d4c4b0;
            --text-light:#f5e6d3;--text-muted:#6b5b4f;--text-sub:#d4c4b0;
        }
        body { font-family:'Georgia',serif; margin:0; padding:0; background:var(--cream); color:#2c2c2c; text-align:center; }

        /* Header */
        .main-header { display:flex; align-items:center; justify-content:flex-start; gap:15px; padding:20px 40px 20px 60px; background-color:var(--deep); }
        .logo { height:100px; width:auto; min-width:100px; object-fit:contain; filter:brightness(0) invert(1); }
        .logo-text { font-size:32px; font-weight:700; color:var(--text-light); letter-spacing:2px; text-shadow:1px 1px 3px rgba(0,0,0,0.4); font-variant:small-caps; }

        /* Nav */
        .nav-container { display:flex; justify-content:center; background:var(--gold); padding:15px; flex-wrap:wrap; border-bottom:2px solid var(--gold-hover); }
        .nav-container a { text-decoration:none; color:var(--deep); font-size:15px; font-weight:bold; padding:10px 15px; border-radius:4px; transition:all 0.3s ease; display:flex; align-items:center; gap:8px; letter-spacing:0.5px; font-family:'Georgia',serif; }
        .nav-container a:hover, .nav-container a.active { background:rgba(59,31,43,0.18); transform:translateY(-2px); }

        /* Page Hero (shorter) */
        .page-hero { background:linear-gradient(rgba(30,15,20,0.6),rgba(30,15,20,0.6)), url('/img/pexels-mikegles-30931569.jpg'); background-size:cover; background-position:center; height:220px; display:flex; align-items:center; justify-content:center; text-align:center; color:var(--text-light); }
        .page-hero h1 { font-size:34px; font-weight:normal; letter-spacing:1px; margin:0 0 8px; color:var(--text-light); }
        .page-hero p { font-size:16px; color:var(--text-sub); margin:0; }

        /* Section Titles */
        .section-title { color:var(--deep); font-size:28px; margin-bottom:10px; position:relative; padding-bottom:15px; font-weight:normal; letter-spacing:1px; }
        .section-title:after { content:''; position:absolute; bottom:0; left:50%; transform:translateX(-50%); width:60px; height:2px; background:var(--gold); }
        .section-subtitle { color:var(--text-muted); font-size:16px; margin-bottom:30px; max-width:800px; margin-left:auto; margin-right:auto; }

        /* Buttons */
        .primary-button { background:var(--gold); color:var(--deep); border:none; padding:12px 30px; border-radius:4px; cursor:pointer; font-weight:bold; font-size:15px; transition:background 0.3s ease,box-shadow 0.3s ease; display:inline-flex; align-items:center; justify-content:center; gap:10px; font-family:'Georgia',serif; letter-spacing:0.5px; box-shadow:0 2px 6px rgba(0,0,0,0.15); text-decoration:none; }
        .primary-button:hover { background:var(--gold-hover); box-shadow:0 3px 10px rgba(0,0,0,0.22); }

        /* Dashboard Layout */
        .dashboard-wrap { max-width:1200px; margin:40px auto; padding:0 20px; }

        /* Stats Row */
        .stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px; margin-bottom:40px; }
        .stat-card { background:var(--card-bg); border-radius:6px; padding:28px 20px; border:1px solid var(--border); box-shadow:0 3px 10px rgba(59,31,43,0.08); text-align:center; }
        .stat-card .stat-icon { font-size:2em; color:var(--gold); margin-bottom:12px; }
        .stat-card .stat-number { font-size:2.4em; font-weight:normal; color:var(--deep); letter-spacing:1px; margin:0; }
        .stat-card .stat-label { color:var(--text-muted); font-size:14px; margin-top:6px; }

        /* Two-column grid */
        .dash-grid { display:grid; grid-template-columns:1.4fr 1fr; gap:30px; }

        /* Upcoming Trips */
        .upcoming-trips { background:var(--card-bg); border-radius:6px; padding:30px; border:1px solid var(--border); box-shadow:0 3px 10px rgba(59,31,43,0.08); text-align:left; }
        .upcoming-trips h3 { color:var(--deep); font-weight:normal; font-size:20px; margin-top:0; border-bottom:1px solid var(--border); padding-bottom:12px; }
        .trip-item { display:flex; align-items:center; gap:18px; padding:16px 0; border-bottom:1px solid var(--border); }
        .trip-item:last-child { border-bottom:none; }
        .trip-thumb { width:72px; height:72px; border-radius:6px; background-size:cover; background-position:center; flex-shrink:0; }
        .trip-info h4 { color:var(--deep); font-weight:normal; font-size:17px; margin:0 0 4px; }
        .trip-info .trip-date { color:var(--text-muted); font-size:13px; }
        .trip-status { font-size:12px; font-weight:bold; padding:4px 10px; border-radius:3px; }
        .trip-status.upcoming { background:#e8f5e9; color:#2e7d32; }
        .trip-status.planning { background:#fff3e0; color:#e65100; }

        /* AI Insights */
        .ai-insights { background:linear-gradient(135deg,var(--deep) 0%,var(--deep-alt) 100%); border-radius:6px; padding:30px; color:var(--text-light); text-align:left; box-shadow:0 8px 28px rgba(59,31,43,0.25); border:1px solid rgba(201,169,110,0.2); }
        .ai-insights h3 { color:var(--text-light); font-weight:normal; font-size:20px; margin-top:0; border-bottom:1px solid rgba(201,169,110,0.25); padding-bottom:12px; }
        .insight-item { display:flex; gap:14px; align-items:flex-start; padding:14px 0; border-bottom:1px solid rgba(201,169,110,0.15); }
        .insight-item:last-child { border-bottom:none; }
        .insight-icon { width:38px; height:38px; border-radius:50%; background:rgba(201,169,110,0.18); display:flex; align-items:center; justify-content:center; color:var(--gold); flex-shrink:0; }
        .insight-item p { margin:0; color:var(--text-sub); font-size:14px; line-height:1.5; }
        .insight-item strong { color:var(--text-light); }

        /* Recent Activity */
        .activity-section { margin-top:35px; }
        .activity-list { background:var(--card-bg); border-radius:6px; border:1px solid var(--border); box-shadow:0 3px 10px rgba(59,31,43,0.08); overflow:hidden; }
        .activity-row { display:flex; align-items:center; gap:16px; padding:16px 22px; border-bottom:1px solid var(--border); text-align:left; }
        .activity-row:last-child { border-bottom:none; }
        .activity-row:hover { background:#f9f5f0; }
        .activity-dot { width:36px; height:36px; border-radius:50%; background:var(--border); display:flex; align-items:center; justify-content:center; color:var(--deep); flex-shrink:0; }
        .activity-row .act-text { flex:1; }
        .activity-row .act-text strong { color:var(--deep); font-size:14px; }
        .activity-row .act-text span { color:var(--text-muted); font-size:13px; display:block; margin-top:2px; }
        .activity-row .act-time { color:var(--text-muted); font-size:12px; white-space:nowrap; }

        /* Footer */
        .footer { background:var(--deep); color:var(--text-sub); text-align:center; padding:30px 20px; margin-top:60px; }
        .footer a { color:var(--gold); margin:0 10px; transition:color 0.3s ease; text-decoration:none; }
        .footer a:hover { color:var(--text-light); }

        @media(max-width:768px){
            .main-header{justify-content:center;padding:15px 20px;} .logo{height:60px;min-width:60px;} .logo-text{font-size:24px;}
            .nav-container{flex-direction:column;align-items:center;} .nav-container a{font-size:14px;padding:8px 10px;}
            .dash-grid{grid-template-columns:1fr;} .stats-row{grid-template-columns:repeat(2,1fr);}
        }
    </style>
</head>
<body>

<header class="main-header">
    <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo">
    <span class="logo-text">Smart Booking</span>
</header>
<nav><div class="nav-container">
    <a href="/"><i class="fas fa-home"></i> Home</a>
    <a href="/dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="/plan-trip"><i class="fas fa-route"></i> Plan Trip</a>
    <a href="/discover"><i class="fas fa-compass"></i> Discover</a>
    <a href="/destinations"><i class="fas fa-map-marked-alt"></i> Destinations</a>
    <a href="/community"><i class="fas fa-users"></i> Community</a>
    <a href="/login"><i class="fas fa-sign-in-alt"></i> Login</a>
    <a href="/register"><i class="fas fa-user-plus"></i> Register</a>
</div></nav>

<section class="page-hero">
    <div>
        <h1><i class="fas fa-tachometer-alt"></i> My Dashboard</h1>
        <p>Welcome back! Here's an overview of your travel journey.</p>
    </div>
</section>

<div class="dashboard-wrap">

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-plane-departure"></i></div>
            <div class="stat-number">7</div>
            <div class="stat-label">Trips Completed</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-number">3</div>
            <div class="stat-label">Upcoming Trips</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div class="stat-number">12</div>
            <div class="stat-label">Countries Visited</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-star"></i></div>
            <div class="stat-number">4.8</div>
            <div class="stat-label">Avg. Trip Rating</div>
        </div>
    </div>

    <!-- Two Column: Upcoming + AI Insights -->
    <div class="dash-grid">
        <div class="upcoming-trips">
            <h3><i class="fas fa-suitcase-rolling" style="color:var(--gold);margin-right:8px;"></i>Upcoming Trips</h3>
            <div class="trip-item">
                <div class="trip-thumb" style="background-image:url('https://images.unsplash.com/photo-1516483638261-f4dbaf036963');"></div>
                <div class="trip-info">
                    <h4>Bali, Indonesia</h4>
                    <div class="trip-date"><i class="fas fa-calendar"></i> Feb 15 – Feb 24, 2026</div>
                </div>
                <span class="trip-status upcoming">Upcoming</span>
            </div>
            <div class="trip-item">
                <div class="trip-thumb" style="background-image:url('https://images.unsplash.com/photo-1511739001486-6bfe10ce785f');"></div>
                <div class="trip-info">
                    <h4>Kyoto, Japan</h4>
                    <div class="trip-date"><i class="fas fa-calendar"></i> Mar 20 – Mar 28, 2026</div>
                </div>
                <span class="trip-status planning">Planning</span>
            </div>
            <div class="trip-item">
                <div class="trip-thumb" style="background-image:url('https://images.unsplash.com/photo-1578662996442-48f60103fc96');"></div>
                <div class="trip-info">
                    <h4>Santorini, Greece</h4>
                    <div class="trip-date"><i class="fas fa-calendar"></i> Jun 10 – Jun 18, 2026</div>
                </div>
                <span class="trip-status planning">Planning</span>
            </div>
            <div style="text-align:center;margin-top:18px;">
                <a href="/plan-trip" class="primary-button" style="font-size:14px;padding:10px 22px;"><i class="fas fa-plus"></i> Plan New Trip</a>
            </div>
        </div>

        <div class="ai-insights">
            <h3><i class="fas fa-robot" style="color:var(--gold);margin-right:8px;"></i>AI Insights</h3>
            <div class="insight-item">
                <div class="insight-icon"><i class="fas fa-lightbulb"></i></div>
                <p><strong>Best time for Bali:</strong> Mid-February has ideal weather — dry season with fewer crowds. Pack light layers for evening temple visits.</p>
            </div>
            <div class="insight-item">
                <div class="insight-icon"><i class="fas fa-yen-sign"></i></div>
                <p><strong>Budget alert:</strong> Flight prices to Kyoto are 18% lower if you book before Feb 28. We found 3 deals matching your budget.</p>
            </div>
            <div class="insight-item">
                <div class="insight-icon"><i class="fas fa-heart"></i></div>
                <p><strong>Mood match:</strong> Based on your past trips, you enjoy cultural experiences. Consider adding a cooking class in Santorini.</p>
            </div>
            <div class="insight-item">
                <div class="insight-icon"><i class="fas fa-map"></i></div>
                <p><strong>New destination:</strong> You haven't visited Southeast Asia much. Thailand scores 94% match with your travel profile.</p>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="activity-section">
        <h2 class="section-title">Recent Activity</h2>
        <div class="activity-list">
            <div class="activity-row">
                <div class="activity-dot"><i class="fas fa-check"></i></div>
                <div class="act-text"><strong>Bali hotel confirmed</strong><span>Amanusa Resort — 9 nights</span></div>
                <div class="act-time">2 hours ago</div>
            </div>
            <div class="activity-row">
                <div class="activity-dot"><i class="fas fa-star" style="color:var(--gold);"></i></div>
                <div class="act-text"><strong>Left a review</strong><span>Rated Swiss Alps trip 5 stars</span></div>
                <div class="act-time">1 day ago</div>
            </div>
            <div class="activity-row">
                <div class="activity-dot"><i class="fas fa-users" style="color:var(--deep);"></i></div>
                <div class="act-text"><strong>Invited to group trip</strong><span>Anna Chen invited you to a Portugal trip</span></div>
                <div class="act-time">2 days ago</div>
            </div>
            <div class="activity-row">
                <div class="activity-dot"><i class="fas fa-robot" style="color:var(--gold);"></i></div>
                <div class="act-text"><strong>AI generated itinerary</strong><span>New itinerary for Kyoto ready for review</span></div>
                <div class="act-time">3 days ago</div>
            </div>
            <div class="activity-row">
                <div class="activity-dot"><i class="fas fa-plane"></i></div>
                <div class="act-text"><strong>Flight booked</strong><span>Budapest → Bali, Feb 15 — Emirates</span></div>
                <div class="act-time">5 days ago</div>
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
</body>
</html>

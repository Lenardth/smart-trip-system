<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Community — Smart Booking</title>
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

        .community-wrap{max-width:1200px;margin:40px auto;padding:0 20px;}

        /* Stats Banner */
        .community-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:44px;}
        .comm-stat{background:var(--card-bg);border-radius:6px;padding:22px;border:1px solid var(--border);box-shadow:0 3px 10px rgba(59,31,43,0.08);text-align:center;}
        .comm-stat .cs-num{font-size:2em;color:var(--deep);font-weight:normal;margin:0;}
        .comm-stat .cs-label{color:var(--text-muted);font-size:13px;margin-top:4px;}

        /* Two-column layout */
        .comm-grid{display:grid;grid-template-columns:1.6fr 1fr;gap:30px;margin-bottom:50px;}

        /* Forum Topics */
        .forum-section{background:var(--card-bg);border-radius:6px;padding:28px;border:1px solid var(--border);box-shadow:0 3px 10px rgba(59,31,43,0.08);text-align:left;}
        .forum-section h3{color:var(--deep);font-weight:normal;font-size:20px;margin-top:0;border-bottom:1px solid var(--border);padding-bottom:12px;display:flex;justify-content:space-between;align-items:center;}
        .forum-topic{display:flex;gap:16px;padding:16px 0;border-bottom:1px solid var(--border);align-items:flex-start;}
        .forum-topic:last-child{border-bottom:none;}
        .forum-avatar{width:44px;height:44px;border-radius:50%;background:var(--deep);display:flex;align-items:center;justify-content:center;color:var(--text-light);font-weight:bold;font-size:15px;flex-shrink:0;}
        .forum-topic .ft-body{flex:1;}
        .forum-topic .ft-body h4{color:var(--deep);font-weight:normal;font-size:15px;margin:0 0 4px;cursor:pointer;}
        .forum-topic .ft-body h4:hover{color:var(--gold);}
        .forum-topic .ft-body .ft-meta{color:var(--text-muted);font-size:12px;}
        .forum-topic .ft-stats{text-align:right;flex-shrink:0;}
        .forum-topic .ft-stats .fs-num{color:var(--deep);font-size:15px;font-weight:bold;}
        .forum-topic .ft-stats .fs-label{color:var(--text-muted);font-size:11px;}
        .forum-topic .ft-tag{display:inline-block;font-size:10px;background:var(--border);color:var(--deep);padding:2px 7px;border-radius:3px;font-weight:600;margin-right:4px;}

        /* Sidebar: Group Trips */
        .sidebar-section{background:var(--card-bg);border-radius:6px;padding:24px;border:1px solid var(--border);box-shadow:0 3px 10px rgba(59,31,43,0.08);text-align:left;margin-bottom:24px;}
        .sidebar-section h3{color:var(--deep);font-weight:normal;font-size:18px;margin-top:0;border-bottom:1px solid var(--border);padding-bottom:10px;}
        .group-trip{display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid var(--border);}
        .group-trip:last-child{border-bottom:none;}
        .group-trip .gt-icon{width:40px;height:40px;border-radius:6px;background:linear-gradient(135deg,var(--deep),var(--deep-alt));display:flex;align-items:center;justify-content:center;color:var(--gold);flex-shrink:0;}
        .group-trip .gt-info h4{color:var(--deep);font-weight:normal;font-size:14px;margin:0;}
        .group-trip .gt-info p{color:var(--text-muted);font-size:12px;margin:2px 0 0;}
        .group-trip .gt-badge{font-size:11px;background:#e8f5e9;color:#2e7d32;padding:3px 8px;border-radius:3px;font-weight:bold;white-space:nowrap;}

        /* Travel Stories */
        .stories-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px;margin-bottom:50px;}
        .story-card{background:var(--card-bg);border-radius:6px;overflow:hidden;border:1px solid var(--border);box-shadow:0 3px 10px rgba(59,31,43,0.08);transition:transform 0.3s,box-shadow 0.3s;cursor:pointer;display:flex;flex-direction:column;}
        .story-card:hover{transform:translateY(-4px);box-shadow:0 6px 18px rgba(59,31,43,0.13);}
        .story-img{height:180px;background-size:cover;background-position:center;flex-shrink:0;}
        .story-body{padding:20px;text-align:left;flex-grow:1;display:flex;flex-direction:column;}
        .story-author{display:flex;align-items:center;gap:10px;margin-bottom:12px;}
        .story-author .sa-avatar{width:34px;height:34px;border-radius:50%;background:var(--deep);display:flex;align-items:center;justify-content:center;color:var(--text-light);font-size:13px;font-weight:bold;}
        .story-author .sa-info{font-size:12px;color:var(--text-muted);}
        .story-author .sa-info strong{color:var(--deep);display:block;font-size:13px;}
        .story-body h4{color:var(--deep);font-weight:normal;font-size:17px;margin:0 0 8px;}
        .story-body p{color:var(--text-muted);font-size:13px;line-height:1.55;flex-grow:1;margin:0;}
        .story-body .story-footer{display:flex;justify-content:space-between;align-items:center;margin-top:14px;font-size:12px;color:var(--text-muted);}

        /* Top Travelers */
        .travelers-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin-bottom:50px;}
        .traveler-card{background:var(--card-bg);border-radius:6px;padding:28px 20px;border:1px solid var(--border);box-shadow:0 3px 10px rgba(59,31,43,0.08);text-align:center;transition:transform 0.3s;}
        .traveler-card:hover{transform:translateY(-3px);}
        .traveler-avatar{width:70px;height:70px;border-radius:50%;background:var(--deep);display:flex;align-items:center;justify-content:center;color:var(--text-light);font-size:24px;font-weight:bold;margin:0 auto 14px;}
        .traveler-card h4{color:var(--deep);font-weight:normal;font-size:17px;margin:0 0 4px;}
        .traveler-card .tc-sub{color:var(--text-muted);font-size:13px;margin:0 0 12px;}
        .traveler-card .tc-stats{display:flex;justify-content:center;gap:20px;}
        .tc-stat{text-align:center;}
        .tc-stat .ts-num{color:var(--deep);font-weight:bold;font-size:16px;}
        .tc-stat .ts-label{color:var(--text-muted);font-size:11px;}
        .traveler-card .tc-badge{display:inline-block;background:var(--gold);color:var(--deep);font-size:11px;font-weight:bold;padding:3px 9px;border-radius:3px;margin-top:10px;}

        .footer{background:var(--deep);color:var(--text-sub);text-align:center;padding:30px 20px;margin-top:60px;}
        .footer a{color:var(--gold);margin:0 10px;transition:color 0.3s ease;text-decoration:none;}
        .footer a:hover{color:var(--text-light);}

        @media(max-width:768px){
            .main-header{justify-content:center;padding:15px 20px;}.logo{height:60px;min-width:60px;}.logo-text{font-size:24px;}
            .nav-container{flex-direction:column;align-items:center;}.nav-container a{font-size:14px;padding:8px 10px;}
            .comm-grid{grid-template-columns:1fr;}
        }
    </style>
</head>
<body>

<header class="main-header">
    <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo">
    <span class="logo-text">Smart Booking</span>
</header>
<nav>
    <nav>
    <div class="nav-container">
        <a href="/"><i class="fas fa-home"></i> Home</a>
        <a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/plan-trip"><i class="fas fa-route"></i> Plan Trip</a>
        <a href="/flights"><i class="fas fa-plane"></i> Book Flights</a>
        <a href="/discover" class="active"><i class="fas fa-compass"></i> Discover</a>
        <a href="/destinations"><i class="fas fa-map-marked-alt"></i> Destinations</a>
        <a href="/community"><i class="fas fa-users"></i> Community</a>
        <a href="/wishlist" class="wishlist-counter">
            <i class="fas fa-heart"></i> Wishlist
            <span class="wishlist-count" id="wishlistCount">0</span>
        </a>
        <a href="/login"><i class="fas fa-sign-in-alt"></i> Login</a>
    </div>
</nav>
<section class="page-hero">
    <div>
        <h1><i class="fas fa-users"></i> Community</h1>
        <p>Connect with fellow travelers, share stories, and join group adventures.</p>
    </div>
</section>

<div class="community-wrap">

    <!-- Community Stats -->
    <div class="community-stats">
        <div class="comm-stat"><div class="cs-num">52,400</div><div class="cs-label">Active Members</div></div>
        <div class="comm-stat"><div class="cs-num">8,200</div><div class="cs-label">Travel Stories</div></div>
        <div class="comm-stat"><div class="cs-num">340</div><div class="cs-label">Active Groups</div></div>
        <div class="comm-stat"><div class="cs-num">1,180</div><div class="cs-label">Forum Topics</div></div>
    </div>

    <!-- Forum + Sidebar -->
    <div class="comm-grid">
        <!-- Forum -->
        <div class="forum-section">
            <h3><i class="fas fa-comments" style="color:var(--gold);margin-right:8px;"></i>Forum Topics <a href="#" style="font-size:13px;color:var(--gold);text-decoration:none;font-weight:normal;">+ New Topic</a></h3>
            <div class="forum-topic">
                <div class="forum-avatar">SJ</div>
                <div class="ft-body">
                    <span class="ft-tag">Bali</span><span class="ft-tag">Solo</span>
                    <h4>Best solo-travel tips for Bali in February?</h4>
                    <div class="ft-meta">Posted by <strong>Sarah Johnson</strong> · 2 hours ago</div>
                </div>
                <div class="ft-stats"><div class="fs-num">24</div><div class="fs-label">Replies</div></div>
            </div>
            <div class="forum-topic">
                <div class="forum-avatar" style="background:#4d2a3a;">MR</div>
                <div class="ft-body">
                    <span class="ft-tag">Japan</span><span class="ft-tag">Budget</span>
                    <h4>How to travel Japan on a $2,000 budget for 2 weeks</h4>
                    <div class="ft-meta">Posted by <strong>Michael Roberts</strong> · 5 hours ago</div>
                </div>
                <div class="ft-stats"><div class="fs-num">41</div><div class="fs-label">Replies</div></div>
            </div>
            <div class="forum-topic">
                <div class="forum-avatar" style="background:#5a3040;">AC</div>
                <div class="ft-body">
                    <span class="ft-tag">Family</span><span class="ft-tag">Europe</span>
                    <h4>Family-friendly itinerary for Southern Europe — any ideas?</h4>
                    <div class="ft-meta">Posted by <strong>Anna Chen</strong> · 1 day ago</div>
                </div>
                <div class="ft-stats"><div class="fs-num">18</div><div class="fs-label">Replies</div></div>
            </div>
            <div class="forum-topic">
                <div class="forum-avatar" style="background:#3b2535;">LK</div>
                <div class="ft-body">
                    <span class="ft-tag">Adventure</span>
                    <h4>Anyone done the Milford Track in winter? Worth it?</h4>
                    <div class="ft-meta">Posted by <strong>Laura Kim</strong> · 2 days ago</div>
                </div>
                <div class="ft-stats"><div class="fs-num">12</div><div class="fs-label">Replies</div></div>
            </div>
            <div class="forum-topic">
                <div class="forum-avatar" style="background:#4a2838;">DP</div>
                <div class="ft-body">
                    <span class="ft-tag">Romantic</span><span class="ft-tag">Greece</span>
                    <h4>Planning a honeymoon in Santorini — villa vs hotel?</h4>
                    <div class="ft-meta">Posted by <strong>David Park</strong> · 3 days ago</div>
                </div>
                <div class="ft-stats"><div class="fs-num">29</div><div class="fs-label">Replies</div></div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="sidebar-section">
                <h3><i class="fas fa-users" style="color:var(--gold);margin-right:6px;"></i>Group Trips</h3>
                <div class="group-trip">
                    <div class="gt-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="gt-info"><h4>Portugal Road Trip</h4><p>Lisbon → Porto · Mar 2026 · 4 spots left</p></div>
                    <span class="gt-badge">Open</span>
                </div>
                <div class="group-trip">
                    <div class="gt-icon"><i class="fas fa-mountain"></i></div>
                    <div class="gt-info"><h4>Himalayan Trekking</h4><p>Nepal · Apr 2026 · 2 spots left</p></div>
                    <span class="gt-badge">Open</span>
                </div>
                <div class="group-trip">
                    <div class="gt-icon"><i class="fas fa-umbrella-beach"></i></div>
                    <div class="gt-info"><h4>Maldives Escape</h4><p>Jun 2026 · Full</p></div>
                    <span class="gt-badge" style="background:#fff3e0;color:#e65100;">Full</span>
                </div>
                <div style="text-align:center;margin-top:16px;">
                    <button class="primary-button" style="font-size:13px;padding:9px 18px;"><i class="fas fa-plus"></i> Create Group</button>
                </div>
            </div>

            <div class="sidebar-section">
                <h3><i class="fas fa-fire" style="color:var(--gold);margin-right:6px;"></i>Trending Tags</h3>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;">
                    <span style="background:var(--border);color:var(--deep);padding:5px 12px;border-radius:3px;font-size:13px;cursor:pointer;">#Bali2026</span>
                    <span style="background:var(--border);color:var(--deep);padding:5px 12px;border-radius:3px;font-size:13px;cursor:pointer;">#SoloTravel</span>
                    <span style="background:var(--border);color:var(--deep);padding:5px 12px;border-radius:3px;font-size:13px;cursor:pointer;">#BudgetTrips</span>
                    <span style="background:var(--border);color:var(--deep);padding:5px 12px;border-radius:3px;font-size:13px;cursor:pointer;">#Japan</span>
                    <span style="background:var(--border);color:var(--deep);padding:5px 12px;border-radius:3px;font-size:13px;cursor:pointer;">#FamilyTravel</span>
                    <span style="background:var(--border);color:var(--deep);padding:5px 12px;border-radius:3px;font-size:13px;cursor:pointer;">#EcoTourism</span>
                    <span style="background:var(--border);color:var(--deep);padding:5px 12px;border-radius:3px;font-size:13px;cursor:pointer;">#HiddenGems</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Travel Stories -->
    <h2 class="section-title">Travel Stories</h2>
    <p class="section-subtitle">Real experiences from our community — inspiring tales from around the globe.</p>
    <div class="stories-grid">
        <div class="story-card">
            <div class="story-img" style="background-image:url('https://images.unsplash.com/photo-1516483638261-f4dbaf036963');"></div>
            <div class="story-body">
                <div class="story-author"><div class="sa-avatar">SJ</div><div class="sa-info"><strong>Sarah Johnson</strong>Feb 2026</div></div>
                <h4>Finding Peace in Bali: A Solo Traveler's Tale</h4>
                <p>I didn't expect a solo trip to change my perspective on life. The quiet mornings in Ubud and conversations with locals taught me more than any book could.</p>
                <div class="story-footer"><span><i class="fas fa-heart"></i> 142 likes</span><span><i class="fas fa-comment"></i> 28 comments</span></div>
            </div>
        </div>
        <div class="story-card">
            <div class="story-img" style="background-image:url('https://images.unsplash.com/photo-1511739001486-6bfe10ce785f');"></div>
            <div class="story-body">
                <div class="story-author"><div class="sa-avatar" style="background:#4d2a3a;">MR</div><div class="sa-info"><strong>Michael Roberts</strong>Jan 2026</div></div>
                <h4>Two Weeks in Japan on a Shoestring Budget</h4>
                <p>Japan doesn't have to break the bank. I discovered ryokans, temple stays, and incredible street food that kept my wallet surprisingly full.</p>
                <div class="story-footer"><span><i class="fas fa-heart"></i> 218 likes</span><span><i class="fas fa-comment"></i> 45 comments</span></div>
            </div>
        </div>
        <div class="story-card">
            <div class="story-img" style="background-image:url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4');"></div>
            <div class="story-body">
                <div class="story-author"><div class="sa-avatar" style="background:#5a3040;">LK</div><div class="sa-info"><strong>Laura Kim</strong>Jan 2026</div></div>
                <h4>The Milford Track: New Zealand's Greatest Walk</h4>
                <p>Four days of pure wilderness magic. Rain, mud, and the most stunning landscapes I've ever seen. My legs hurt for a week — worth every step.</p>
                <div class="story-footer"><span><i class="fas fa-heart"></i> 97 likes</span><span><i class="fas fa-comment"></i> 19 comments</span></div>
            </div>
        </div>
    </div>

    <!-- Top Travelers -->
    <h2 class="section-title">Top Travelers</h2>
    <p class="section-subtitle">Our most active and inspiring community members.</p>
    <div class="travelers-grid">
        <div class="traveler-card">
            <div class="traveler-avatar">SJ</div>
            <h4>Sarah Johnson</h4>
            <div class="tc-sub">Solo Adventurer</div>
            <div class="tc-stats"><div class="tc-stat"><div class="ts-num">23</div><div class="ts-label">Trips</div></div><div class="tc-stat"><div class="ts-num">15</div><div class="ts-label">Countries</div></div><div class="tc-stat"><div class="ts-num">42</div><div class="ts-label">Posts</div></div></div>
            <div class="tc-badge">⭐ Top Contributor</div>
        </div>
        <div class="traveler-card">
            <div class="traveler-avatar" style="background:#4d2a3a;">MR</div>
            <h4>Michael Roberts</h4>
            <div class="tc-sub">Budget Travel Expert</div>
            <div class="tc-stats"><div class="tc-stat"><div class="ts-num">31</div><div class="ts-label">Trips</div></div><div class="tc-stat"><div class="ts-num">22</div><div class="ts-label">Countries</div></div><div class="tc-stat"><div class="ts-num">68</div><div class="ts-label">Posts</div></div></div>
            <div class="tc-badge">🌍 World Explorer</div>
        </div>
        <div class="traveler-card">
            <div class="traveler-avatar" style="background:#5a3040;">AC</div>
            <h4>Anna Chen</h4>
            <div class="tc-sub">Family Travel Guru</div>
            <div class="tc-stats"><div class="tc-stat"><div class="ts-num">12</div><div class="ts-label">Trips</div></div><div class="tc-stat"><div class="ts-num">9</div><div class="ts-label">Countries</div></div><div class="tc-stat"><div class="ts-num">24</div><div class="ts-label">Posts</div></div></div>
            <div class="tc-badge">👨‍👩‍👧 Family Fave</div>
        </div>
        <div class="traveler-card">
            <div class="traveler-avatar" style="background:#3b2535;">LK</div>
            <h4>Laura Kim</h4>
            <div class="tc-sub">Adventure Seeker</div>
            <div class="tc-stats"><div class="tc-stat"><div class="ts-num">18</div><div class="ts-label">Trips</div></div><div class="tc-stat"><div class="ts-num">14</div><div class="ts-label">Countries</div></div><div class="tc-stat"><div class="ts-num">35</div><div class="ts-label">Posts</div></div></div>
            <div class="tc-badge">🏔️ Trailblazer</div>
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

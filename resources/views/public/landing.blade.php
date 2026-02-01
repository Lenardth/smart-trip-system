<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── Color Tokens ── */
        :root {
            --deep:     #3b1f2b;
            --deep-alt: #4d2a3a;
            --gold:     #c9a96e;
            --gold-hover: #b8955a;
            --cream:    #f5f0eb;
            --card-bg:  #fff8f2;
            --border:   #e2d5c7;
            --border-soft: #d4c4b0;
            --text-light: #f5e6d3;
            --text-muted: #6b5b4f;
            --text-sub:  #d4c4b0;
        }

        /* ── Base ── */
        body {
            font-family: 'Georgia', serif;
            margin: 0;
            padding: 0;
            background: var(--cream);
            color: #2c2c2c;
            text-align: center;
        }

        /* ── Header ── */
        .main-header {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 15px;
            padding: 20px 40px 20px 60px;
            background-color: var(--deep);
        }

        .logo {
            height: 100px;
            width: auto;
            min-width: 100px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .logo-text {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-light);
            letter-spacing: 2px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.4);
            font-variant: small-caps;
        }

        /* ── Nav ── */
        .nav-container {
            display: flex;
            justify-content: center;
            background: var(--gold);
            padding: 15px;
            flex-wrap: wrap;
            border-bottom: 2px solid var(--gold-hover);
        }

        .nav-container a {
            text-decoration: none;
            color: var(--deep);
            font-size: 15px;
            font-weight: bold;
            padding: 10px 15px;
            border-radius: 4px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: 0.5px;
            font-family: 'Georgia', serif;
        }

        .nav-container a:hover {
            background: rgba(59,31,43,0.18);
            transform: translateY(-2px);
        }

        /* ── Hero ── */
        .hero {
            background: linear-gradient(rgba(30,15,20,0.55), rgba(30,15,20,0.55)),
                        url('/img/pexels-mikegles-30931569.jpg');
            background-size: cover;
            background-position: center;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--text-light);
        }

        .hero-content {
            background: rgba(40,20,28,0.65);
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            border: 1px solid rgba(201,169,110,0.3);
        }

        .hero-content h1 {
            font-size: 36px;
            margin-bottom: 10px;
            color: var(--text-light);
            font-weight: normal;
            letter-spacing: 1px;
        }

        .hero-content p {
            font-size: 17px;
            margin-bottom: 20px;
            color: var(--text-sub);
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        /* ── Buttons ── */
        .primary-button {
            background: var(--gold);
            color: var(--deep);
            border: none;
            padding: 12px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            transition: background 0.3s ease, box-shadow 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-family: 'Georgia', serif;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            text-decoration: none;
        }

        .primary-button:hover {
            background: var(--gold-hover);
            box-shadow: 0 3px 10px rgba(0,0,0,0.22);
        }

        .secondary-button {
            background: transparent;
            color: var(--text-light);
            border: 1px solid rgba(201,169,110,0.6);
            padding: 10px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: 'Georgia', serif;
            letter-spacing: 0.5px;
        }

        .secondary-button:hover {
            background: var(--gold);
            color: var(--deep);
            border-color: var(--gold);
        }

        /* ── Tiles ── */
        .tile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .tile {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 6px;
            box-shadow: 0 3px 10px rgba(59,31,43,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid var(--border);
        }

        .tile:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 18px rgba(59,31,43,0.13);
        }

        .tile h3 {
            color: var(--deep);
            margin-top: 0;
            font-weight: normal;
            font-size: 18px;
            letter-spacing: 0.5px;
        }

        .tile p {
            color: var(--text-muted);
        }

        /* ── Discover ── */
        .discover-section {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }

        .discover-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .discover-header .secondary-button {
            color: var(--deep);
            border-color: var(--deep);
        }

        .discover-header .secondary-button:hover {
            background: var(--deep);
            color: var(--text-light);
        }

        /* ── Filter Tags ── */
        .filter-tags {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 30px;
            justify-content: center;
        }

        .filter-tag {
            padding: 10px 25px;
            background: var(--card-bg);
            border: 1px solid var(--deep);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            color: var(--deep);
            font-size: 14px;
            font-family: 'Georgia', serif;
        }

        .filter-tag:hover,
        .filter-tag.active {
            background: var(--deep);
            color: var(--text-light);
        }

        /* ── Destination Cards ── */
        .destinations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .destination-card {
            background: var(--card-bg);
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(59,31,43,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            border: 1px solid var(--border);
            display: flex;
            flex-direction: column;
        }

        .destination-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 22px rgba(59,31,43,0.15);
        }

        .destination-image {
            height: 180px;
            width: 100%;
            background-size: cover;
            background-position: center;
            flex-shrink: 0;
        }

        .destination-content {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .destination-content h3 {
            margin-top: 0;
            margin-bottom: 10px;
            color: var(--deep);
            font-weight: normal;
            font-size: 19px;
            letter-spacing: 0.5px;
        }

        .destination-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .destination-content p {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.5;
            flex-grow: 1;
            margin-top: 0;
        }

        .destination-content .primary-button {
            margin-top: auto;
            width: 100%;
            padding: 10px;
        }

        .price-tag {
            background: var(--gold);
            color: var(--deep);
            padding: 5px 15px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 13px;
            letter-spacing: 0.3px;
        }

        .mood-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 15px;
            background: #f5efe8;
            border-radius: 3px;
            font-size: 13px;
            color: var(--deep);
            border: 1px solid var(--border);
        }

        /* ── Testimonials ── */
        .testimonials {
            background: #efe8df;
            padding: 60px 20px;
            margin: 60px 0;
            border-top: 1px solid var(--border-soft);
            border-bottom: 1px solid var(--border-soft);
        }

        .testimonial-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .testimonial-card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 6px;
            box-shadow: 0 3px 10px rgba(59,31,43,0.08);
            position: relative;
            border: 1px solid var(--border);
        }

        .testimonial-card:before {
            content: '\201C';
            font-size: 70px;
            color: var(--gold);
            opacity: 0.35;
            position: absolute;
            top: 5px;
            left: 18px;
            font-family: 'Georgia', serif;
            line-height: 1;
        }

        .testimonial-card p {
            color: var(--text-muted);
            line-height: 1.6;
            font-style: italic;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 20px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--deep);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            font-weight: bold;
            font-size: 17px;
            letter-spacing: 1px;
            flex-shrink: 0;
        }

        .user-name {
            font-weight: bold;
            color: var(--deep);
            text-align: left;
        }

        .user-trip {
            color: var(--text-muted);
            font-size: 14px;
            text-align: left;
        }

        /* ── AI Banner ── */
        .ai-features-banner {
            background: linear-gradient(135deg, var(--deep) 0%, var(--deep-alt) 100%);
            color: var(--text-light);
            padding: 50px 40px;
            border-radius: 6px;
            margin: 60px auto;
            max-width: 1200px;
            box-shadow: 0 8px 28px rgba(59,31,43,0.25);
            border: 1px solid rgba(201,169,110,0.2);
        }

        .ai-features-banner h2 {
            font-size: 32px;
            margin-bottom: 20px;
            color: var(--text-light);
            font-weight: normal;
            letter-spacing: 1px;
        }

        .ai-features-banner > div:first-child p {
            font-size: 18px;
            opacity: 0.85;
            max-width: 800px;
            margin: 0 auto;
            color: var(--text-sub);
        }

        .stats-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 30px;
            text-align: center;
            margin-top: 40px;
        }

        .stat-item {
            flex: 1;
            min-width: 200px;
            color: var(--text-sub);
        }

        .stat-number {
            font-size: 2.8em;
            font-weight: normal;
            margin-bottom: 10px;
            color: var(--gold);
            letter-spacing: 1px;
        }

        /* ── Newsletter ── */
        .newsletter {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 6px;
            text-align: center;
            max-width: 800px;
            margin: 60px auto;
            box-shadow: 0 3px 10px rgba(59,31,43,0.08);
            border: 1px solid var(--border);
        }

        .newsletter-input {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .newsletter-input input {
            flex: 1;
            padding: 15px;
            border: 1px solid var(--deep);
            border-radius: 4px;
            font-size: 16px;
            color: var(--deep);
            background: var(--card-bg);
            font-family: 'Georgia', serif;
        }

        .newsletter-input input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 2px rgba(201,169,110,0.2);
        }

        .newsletter-input button {
            background: var(--gold);
            color: var(--deep);
            border: none;
            padding: 15px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            transition: background 0.3s ease;
            font-family: 'Georgia', serif;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .newsletter-input button:hover {
            background: var(--gold-hover);
        }

        .newsletter p.privacy {
            color: #8a7e74;
            font-size: 14px;
            margin-top: 15px;
        }

        /* ── Quick Plan ── */
        .quick-plan {
            background: var(--card-bg);
            border-radius: 6px;
            padding: 40px;
            margin: 60px auto;
            max-width: 1200px;
            box-shadow: 0 3px 10px rgba(59,31,43,0.08);
            border: 1px solid var(--border);
        }

        .quick-plan-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .form-group label {
            font-weight: bold;
            color: var(--deep);
            font-size: 14px;
            text-align: left;
            letter-spacing: 0.5px;
        }

        .form-group select {
            padding: 12px;
            border: 1px solid var(--border-soft);
            border-radius: 4px;
            font-size: 15px;
            color: var(--deep);
            background: var(--card-bg);
            transition: border-color 0.3s ease;
            font-family: 'Georgia', serif;
        }

        .form-group select:focus {
            border-color: var(--gold);
            outline: none;
            box-shadow: 0 0 0 2px rgba(201,169,110,0.2);
        }

        /* ── Categories ── */
        .explore-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 40px;
        }

        .category-card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 6px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(59,31,43,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            border: 1px solid var(--border);
            border-top: 3px solid var(--gold);
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 22px rgba(59,31,43,0.15);
        }

        .category-card h3 {
            color: var(--deep);
            font-weight: normal;
            font-size: 18px;
        }

        .category-card p {
            color: var(--text-muted);
        }

        .category-icon {
            font-size: 2.5em;
            color: var(--deep);
            margin-bottom: 20px;
        }

        /* ── How It Works ── */
        .how-it-works-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .how-step {
            text-align: center;
            padding: 20px;
            background: var(--card-bg);
            border-radius: 6px;
            box-shadow: 0 3px 10px rgba(59,31,43,0.08);
            border: 1px solid var(--border);
        }

        .how-step-number {
            background: var(--deep);
            color: var(--text-light);
            width: 60px;
            height: 60px;
            line-height: 60px;
            border-radius: 50%;
            margin: 0 auto 15px;
            font-size: 24px;
            font-weight: normal;
            letter-spacing: 0;
        }

        .how-step h3 {
            color: var(--deep);
            font-weight: normal;
            font-size: 18px;
        }

        .how-step p {
            color: var(--text-muted);
        }

        /* ── Smart Features ── */
        .smart-features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .smart-feature-card {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 6px;
            box-shadow: 0 3px 10px rgba(59,31,43,0.08);
            border: 1px solid var(--border);
            text-align: left;
        }

        .smart-feature-card .feature-icon {
            font-size: 2em;
            color: var(--deep);
            margin-bottom: 15px;
            display: block;
        }

        .smart-feature-card h3 {
            color: var(--deep);
            font-weight: normal;
            font-size: 18px;
            margin-top: 0;
        }

        .smart-feature-card p {
            color: var(--text-muted);
        }

        /* ── Section Titles ── */
        .section-title {
            color: var(--deep);
            font-size: 28px;
            margin-bottom: 10px;
            position: relative;
            padding-bottom: 15px;
            font-weight: normal;
            letter-spacing: 1px;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 2px;
            background: var(--gold);
        }

        .section-subtitle {
            color: var(--text-muted);
            font-size: 16px;
            margin-bottom: 30px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        /* ── Footer ── */
        .footer {
            background: var(--deep);
            color: var(--text-sub);
            text-align: center;
            padding: 30px 20px;
            margin-top: 40px;
        }

        .footer a {
            color: var(--gold);
            margin: 0 10px;
            transition: color 0.3s ease;
            text-decoration: none;
        }

        .footer a:hover {
            color: var(--text-light);
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .hero-content h1 { font-size: 28px; }
            .tile-grid { grid-template-columns: 1fr; }
            .nav-container { flex-direction: column; align-items: center; }
            .discover-header { flex-direction: column; align-items: center; gap: 15px; text-align: center; }
            .filter-tags { justify-content: center; }
            .newsletter-input { flex-direction: column; align-items: center; }
            .newsletter-input input { width: 100%; box-sizing: border-box; }
            .quick-plan-form { grid-template-columns: 1fr; }
            .hero-buttons { flex-direction: column; align-items: center; }
            .main-header { justify-content: center; padding: 15px 20px; }
            .logo { height: 60px; min-width: 60px; }
            .logo-text { font-size: 24px; }
            .nav-container a { font-size: 14px; padding: 8px 10px; }
        }
    </style>
</head>
<body>

<!-- Header -->
<header class="main-header">
    <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo">
    <span class="logo-text">Smart Booking</span>
</header>

<!-- Nav -->
<nav>
    <div class="nav-container">
        <a href="/"><i class="fas fa-home"></i> Home</a>
        <a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/plan-trip"><i class="fas fa-route"></i> Plan Trip</a>
        <a href="/discover"><i class="fas fa-compass"></i> Discover</a>
        <a href="/destinations"><i class="fas fa-map-marked-alt"></i> Destinations</a>
        <a href="/community"><i class="fas fa-users"></i> Community</a>
        <a href="/login"><i class="fas fa-sign-in-alt"></i> Login</a>
        <a href="/register"><i class="fas fa-user-plus"></i> Register</a>
    </div>
</nav>

<!-- Hero -->
<section class="hero">
    <div class="hero-content">
        <h1>Plan Your Perfect Journey with AI</h1>
        <p>Personalized travel recommendations based on your mood, preferences, and budget. Discover destinations you'll love.</p>
        <div class="hero-buttons">
            <button class="primary-button" onclick="window.location.href='/plan-trip'">
                <i class="fas fa-magic"></i> Start Planning Now
            </button>
            <button class="secondary-button" onclick="window.location.href='/discover'">
                <i class="fas fa-compass"></i> Explore Destinations
            </button>
        </div>
    </div>
</section>

<!-- Quick Plan Form -->
<div class="quick-plan">
    <h2 class="section-title">Quick Trip Builder</h2>
    <p class="section-subtitle">Get instant recommendations by filling these simple preferences</p>
    <div class="quick-plan-form">
        <div class="form-group">
            <label>Current Mood</label>
            <select id="moodSelect">
                <option value="adventurous">Adventurous</option>
                <option value="relaxed">Relaxed</option>
                <option value="cultural">Cultural</option>
                <option value="romantic">Romantic</option>
                <option value="foodie">Foodie</option>
            </select>
        </div>
        <div class="form-group">
            <label>Budget Range</label>
            <select id="budgetSelect">
                <option value="budget">Budget Friendly</option>
                <option value="mid">Mid Range</option>
                <option value="luxury">Luxury</option>
            </select>
        </div>
        <div class="form-group">
            <label>Travel Duration</label>
            <select id="durationSelect">
                <option value="weekend">Weekend Getaway</option>
                <option value="week">One Week</option>
                <option value="long">Extended Trip</option>
            </select>
        </div>
        <div class="form-group">
            <label>Companion</label>
            <select id="companionSelect">
                <option value="solo">Solo Travel</option>
                <option value="couple">Couple</option>
                <option value="family">Family</option>
                <option value="friends">Friends</option>
            </select>
        </div>
    </div>
    <div style="text-align: center; margin-top: 30px;">
        <button class="primary-button" onclick="generateQuickPlan()">
            <i class="fas fa-robot"></i> Generate AI Suggestions
        </button>
    </div>
</div>

<!-- Feature Tiles -->
<div class="tile-grid">
    <div class="tile">
        <h3><i class="fas fa-brain"></i> AI Mood-Based Suggestions</h3>
        <p>Tell us how you feel—adventurous, relaxed, cultural—and get personalized destination recommendations.</p>
    </div>
    <div class="tile">
        <h3><i class="fas fa-sliders-h"></i> Smart Budget Optimization</h3>
        <p>Set your budget and let our algorithm find the best flights, accommodations, and activities within your range.</p>
    </div>
    <div class="tile">
        <h3><i class="fas fa-users"></i> Collaborative Planning</h3>
        <p>Invite friends, vote on destinations, and create shared itineraries that work for everyone.</p>
    </div>
</div>

<!-- Discover Section -->
<section class="discover-section">
    <div class="discover-header">
        <h2 class="section-title">Discover Trending Destinations</h2>
        <button class="secondary-button" onclick="window.location.href='/discover'">
            View All <i class="fas fa-arrow-right"></i>
        </button>
    </div>

    <div class="filter-tags">
        <span class="filter-tag active">All</span>
        <span class="filter-tag">Tropical</span>
        <span class="filter-tag">Mountain</span>
        <span class="filter-tag">Historical</span>
        <span class="filter-tag">Beach</span>
        <span class="filter-tag">Food</span>
        <span class="filter-tag">Art &amp; Culture</span>
        <span class="filter-tag">Eco-Tourism</span>
    </div>

    <div class="destinations-grid">
        <div class="destination-card">
            <div class="destination-image" style="background-image: url('https://images.unsplash.com/photo-1516483638261-f4dbaf036963');"></div>
            <div class="destination-content">
                <h3>Bali, Indonesia</h3>
                <div class="destination-meta">
                    <span class="price-tag">Premium</span>
                    <span class="mood-indicator"><i class="fas fa-spa"></i> Relaxed</span>
                </div>
                <p>Perfect for yoga retreats and beach relaxation with stunning temples.</p>
                <button class="primary-button" onclick="window.location.href='/destination/bali'">
                    Explore <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <div class="destination-card">
            <div class="destination-image" style="background-image: url('https://images.unsplash.com/photo-1511739001486-6bfe10ce785f');"></div>
            <div class="destination-content">
                <h3>Kyoto, Japan</h3>
                <div class="destination-meta">
                    <span class="price-tag">Luxury</span>
                    <span class="mood-indicator"><i class="fas fa-landmark"></i> Cultural</span>
                </div>
                <p>Ancient temples, traditional tea houses, and beautiful cherry blossoms.</p>
                <button class="primary-button" onclick="window.location.href='/destination/kyoto'">
                    Explore <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <div class="destination-card">
            <div class="destination-image" style="background-image: url('https://images.unsplash.com/photo-1579530190412-b35a65e17c8d');"></div>
            <div class="destination-content">
                <h3>Swiss Alps</h3>
                <div class="destination-meta">
                    <span class="price-tag">Luxury</span>
                    <span class="mood-indicator"><i class="fas fa-mountain"></i> Adventurous</span>
                </div>
                <p>Breathtaking mountain views, skiing, and luxury mountain resorts.</p>
                <button class="primary-button" onclick="window.location.href='/destination/alps'">
                    Explore <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <div class="destination-card">
            <div class="destination-image" style="background-image: url('https://images.unsplash.com/photo-1578662996442-48f60103fc96');"></div>
            <div class="destination-content">
                <h3>Santorini, Greece</h3>
                <div class="destination-meta">
                    <span class="price-tag">Premium</span>
                    <span class="mood-indicator"><i class="fas fa-heart"></i> Romantic</span>
                </div>
                <p>White-washed buildings, stunning sunsets, and crystal clear waters.</p>
                <button class="primary-button" onclick="window.location.href='/destination/santorini'">
                    Explore <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Explore Categories -->
<div style="max-width: 1200px; margin: 60px auto; padding: 0 20px;">
    <h2 class="section-title">Explore By Travel Style</h2>
    <p class="section-subtitle">Find destinations that match your preferred travel experience</p>

    <div class="explore-categories">
        <div class="category-card">
            <div class="category-icon"><i class="fas fa-hiking"></i></div>
            <h3>Adventure Travel</h3>
            <p>Hiking, trekking, and extreme sports destinations</p>
        </div>
        <div class="category-card">
            <div class="category-icon"><i class="fas fa-umbrella-beach"></i></div>
            <h3>Beach &amp; Relaxation</h3>
            <p>Perfect spots for sunbathing and unwinding</p>
        </div>
        <div class="category-card">
            <div class="category-icon"><i class="fas fa-landmark"></i></div>
            <h3>Cultural Immersion</h3>
            <p>Historical sites and cultural experiences</p>
        </div>
        <div class="category-card">
            <div class="category-icon"><i class="fas fa-utensils"></i></div>
            <h3>Culinary Tours</h3>
            <p>Foodie paradises and cooking experiences</p>
        </div>
    </div>
</div>

<!-- How It Works -->
<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <h2 class="section-title">How It Works</h2>
    <div class="how-it-works-grid">
        <div class="how-step">
            <div class="how-step-number">1</div>
            <h3>Set Your Preferences</h3>
            <p>Choose your mood, travel dates, budget, and interests using our intuitive preference selector.</p>
        </div>
        <div class="how-step">
            <div class="how-step-number">2</div>
            <h3>Get AI Recommendations</h3>
            <p>Our algorithm analyzes thousands of destinations to suggest perfect matches for your trip.</p>
        </div>
        <div class="how-step">
            <div class="how-step-number">3</div>
            <h3>Build &amp; Book Itinerary</h3>
            <p>Customize your trip plan, add activities, and book directly through our integrated partners.</p>
        </div>
    </div>
</div>

<!-- AI Features Banner -->
<div class="ai-features-banner">
    <div style="text-align: center;">
        <h2>Powered by Advanced AI</h2>
        <p>Our intelligent algorithms analyze millions of data points to create your perfect trip</p>
    </div>
    <div class="stats-container">
        <div class="stat-item">
            <div class="stat-number">10K+</div>
            <div>Destinations Analyzed</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">95%</div>
            <div>User Satisfaction Rate</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">24/7</div>
            <div>Real-Time Updates</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">50K+</div>
            <div>Happy Travelers</div>
        </div>
    </div>
</div>

<!-- Advanced Smart Features -->
<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <h2 class="section-title">Advanced Smart Features</h2>
    <p class="section-subtitle">Experience the future of travel planning with our AI-powered tools</p>

    <div class="smart-features-grid">
        <div class="smart-feature-card">
            <i class="fas fa-robot feature-icon"></i>
            <h3>Predictive Weather Planning</h3>
            <p>AI predicts optimal travel dates based on historical weather patterns and seasonal trends at your chosen destinations.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-chart-pie feature-icon"></i>
            <h3>Real-Time Cost Analysis</h3>
            <p>Live price tracking for flights, hotels, and activities with alerts for price drops and special deals.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-heartbeat feature-icon"></i>
            <h3>Mood &amp; Interest Matching</h3>
            <p>Advanced personality profiling to match destinations with your emotional state and personal interests.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-sync-alt feature-icon"></i>
            <h3>Dynamic Itinerary Adjuster</h3>
            <p>Automatically suggests itinerary changes based on real-time factors like traffic, closures, or weather changes.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-user-friends feature-icon"></i>
            <h3>Group Compatibility Scoring</h3>
            <p>For group trips, analyzes preferences of all travelers to find destinations that satisfy everyone optimally.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-leaf feature-icon"></i>
            <h3>Sustainable Travel Options</h3>
            <p>Highlights eco-friendly accommodations, low-carbon transportation, and sustainable tourism activities.</p>
        </div>
    </div>
</div>

<!-- Testimonials -->
<section class="testimonials">
    <div class="testimonial-container">
        <h2 class="section-title">What Travelers Say</h2>
        <p class="section-subtitle">Join thousands of satisfied travelers who discovered their perfect trips</p>

        <div class="testimonial-grid">
            <div class="testimonial-card">
                <p>"The AI suggestions were spot on! I told the system I wanted a relaxing cultural trip, and it suggested Kyoto during cherry blossom season. Best trip ever!"</p>
                <div class="user-info">
                    <div class="user-avatar">SJ</div>
                    <div>
                        <div class="user-name">Sarah Johnson</div>
                        <div class="user-trip">Traveled to Japan, March 2024</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <p>"As a solo traveler, safety was my concern. The app recommended destinations with great solo traveler infrastructure and connected me with local guides."</p>
                <div class="user-info">
                    <div class="user-avatar">MR</div>
                    <div>
                        <div class="user-name">Michael Roberts</div>
                        <div class="user-trip">Solo Traveler, Multiple Trips</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <p>"Planning a family vacation with different ages was challenging. The AI created an itinerary that kept everyone happy. The kids loved it!"</p>
                <div class="user-info">
                    <div class="user-avatar">AC</div>
                    <div>
                        <div class="user-name">Anna Chen</div>
                        <div class="user-trip">Family Trip to Bali, 2024</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<div class="newsletter">
    <h2 class="section-title">Get Travel Inspiration</h2>
    <p class="section-subtitle">Subscribe to receive weekly destination ideas, travel tips, and exclusive deals</p>
    <div class="newsletter-input">
        <input type="email" placeholder="Enter your email address">
        <button onclick="subscribeNewsletter()">
            <i class="fas fa-paper-plane"></i> Subscribe
        </button>
    </div>
    <p class="privacy">We respect your privacy. Unsubscribe at any time.</p>
</div>

<!-- Footer -->
<footer class="footer">
    <div style="max-width: 1200px; margin: 0 auto;">
        <p>© 2026 Smart Trip Planner | Laravel Web Application Project | Created for Web Application Development Course</p>
        <div style="margin-top: 15px;">
            <a href="#"><i class="fab fa-github"></i></a>
            <a href="#"><i class="fab fa-laravel"></i></a>
            <a href="#"><i class="fas fa-graduation-cap"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
    </div>
</footer>

<script>
    function generateQuickPlan() {
        const mood = document.getElementById('moodSelect').value;
        const budget = document.getElementById('budgetSelect').value;
        const duration = document.getElementById('durationSelect').value;
        const companion = document.getElementById('companionSelect').value;

        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyzing...';
        button.disabled = true;

        setTimeout(() => {
            window.location.href = `/plan-trip?mood=${mood}&budget=${budget}&duration=${duration}&companion=${companion}`;
            button.innerHTML = originalText;
            button.disabled = false;
        }, 1500);
    }

    function subscribeNewsletter() {
        const emailInput = document.querySelector('.newsletter-input input');
        const email = emailInput.value;

        if (!email || !email.includes('@')) {
            alert('Please enter a valid email address');
            return;
        }

        const button = document.querySelector('.newsletter-input button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Subscribed!';
        button.style.background = '#6b8f6b';

        setTimeout(() => {
            button.innerHTML = originalText;
            button.style.background = '';
            emailInput.value = '';
        }, 3000);

        console.log('Newsletter subscription:', email);
    }

    // Filter tags
    document.querySelectorAll('.filter-tag').forEach(tag => {
        tag.addEventListener('click', function() {
            document.querySelectorAll('.filter-tag').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const grid = document.querySelector('.destinations-grid');
            grid.style.opacity = '0.5';
            setTimeout(() => { grid.style.opacity = '1'; }, 300);
        });
    });

    // Destination card hover
    document.querySelectorAll('.destination-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 22px rgba(59,31,43,0.15)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 3px 10px rgba(59,31,43,0.08)';
        });
    });

    // Category card hover
    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 22px rgba(59,31,43,0.15)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 3px 10px rgba(59,31,43,0.08)';
        });
    });

    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({ top: targetElement.offsetTop - 80, behavior: 'smooth' });
            }
        });
    });
</script>

</body>
</html>

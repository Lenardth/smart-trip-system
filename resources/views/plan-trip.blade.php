<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Plan Trip — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --deep: #3b1f2b; --deep-alt: #4d2a3a; --gold: #c9a96e; --gold-hover: #b8955a;
            --cream: #f5f0eb; --card-bg: #fff8f2; --border: #e2d5c7; --border-soft: #d4c4b0;
            --text-light: #f5e6d3; --text-muted: #6b5b4f; --text-sub: #d4c4b0;
            --success: #2ecc71; --error: #e74c3c; --info: #3498db;
        }
        body {
            font-family: 'Georgia', serif;
            margin: 0;
            padding: 0;
            background: var(--cream);
            color: #2c2c2c;
        }
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
        .user-display {
            margin-left: auto;
            color: var(--text-light);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
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
        .nav-container a:hover,
        .nav-container a.active {
            background: rgba(59, 31, 43, 0.18);
            transform: translateY(-2px);
        }
        .nav-container form {
            display: inline;
        }
        .nav-container button {
            background: none;
            border: none;
            color: var(--deep);
            font-size: 15px;
            font-weight: bold;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Georgia', serif;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .nav-container button:hover {
            background: rgba(59, 31, 43, 0.18);
            transform: translateY(-2px);
        }
        .wishlist-counter {
            position: relative;
        }
        .wishlist-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--deep);
            color: var(--text-light);
            font-size: 11px;
            font-weight: bold;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .page-hero {
            background: linear-gradient(rgba(30, 15, 20, 0.6), rgba(30, 15, 20, 0.6)),
                        url('/img/pexels-mikegles-30931569.jpg');
            background-size: cover;
            background-position: center;
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--text-light);
        }
        .page-hero h1 {
            font-size: 34px;
            font-weight: normal;
            letter-spacing: 1px;
            margin: 0 0 8px;
            color: var(--text-light);
        }
        .page-hero p {
            font-size: 16px;
            color: var(--text-sub);
            margin: 0;
        }

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
        .primary-button:disabled {
            background: var(--border-soft);
            color: #8a7e74;
            cursor: not-allowed;
            box-shadow: none;
        }

        /* Planner Wrap */
        .planner-wrap {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Step Indicators */
        .steps {
            display: flex;
            justify-content: center;
            gap: 0;
            margin-bottom: 40px;
            position: relative;
        }
        .steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 70%;
            height: 2px;
            background: var(--border);
            z-index: 0;
        }
        .step {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            max-width: 180px;
        }
        .step-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--card-bg);
            border: 2px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .step.active .step-circle,
        .step.done .step-circle {
            background: var(--gold);
            border-color: var(--gold);
            color: var(--deep);
        }
        .step-label {
            margin-top: 8px;
            font-size: 13px;
            color: var(--text-muted);
            font-weight: bold;
        }
        .step.active .step-label {
            color: var(--deep);
        }

        /* Mood Cards */
        .mood-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin: 20px 0 30px;
        }
        .mood-card {
            background: var(--card-bg);
            border: 2px solid var(--border);
            border-radius: 8px;
            padding: 24px 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        .mood-card:hover {
            border-color: var(--gold);
            transform: translateY(-3px);
            box-shadow: 0 4px 14px rgba(59, 31, 43, 0.12);
        }
        .mood-card.selected {
            border-color: var(--gold);
            background: linear-gradient(135deg, #fff8f2, #fdf0dc);
            box-shadow: 0 4px 14px rgba(201, 169, 110, 0.25);
        }
        .mood-card .mood-icon {
            font-size: 2em;
            color: var(--deep);
            margin-bottom: 10px;
        }
        .mood-card h4 {
            color: var(--deep);
            font-weight: normal;
            font-size: 15px;
            margin: 0;
        }
        .mood-card p {
            color: var(--text-muted);
            font-size: 12px;
            margin: 6px 0 0;
        }

        /* Form Groups */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            text-align: left;
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: bold;
            color: var(--deep);
            font-size: 14px;
            letter-spacing: 0.5px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 14px;
            border: 1px solid var(--border-soft);
            border-radius: 4px;
            font-size: 15px;
            color: var(--deep);
            background: var(--card-bg);
            font-family: 'Georgia', serif;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 2px rgba(201, 169, 110, 0.2);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 90px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Budget Slider */
        .budget-display {
            text-align: center;
            font-size: 24px;
            font-weight: normal;
            color: var(--deep);
            margin: 10px 0;
        }
        .budget-display span {
            color: var(--gold);
            font-weight: bold;
        }
        input[type="range"] {
            -webkit-appearance: none;
            width: 100%;
            height: 6px;
            background: var(--border);
            border-radius: 3px;
            outline: none;
        }
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--gold);
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
        .budget-labels {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        /* Planner Card */
        .planner-card {
            background: var(--card-bg);
            border-radius: 6px;
            padding: 36px;
            border: 1px solid var(--border);
            box-shadow: 0 3px 10px rgba(59, 31, 43, 0.08);
            margin-bottom: 30px;
            display: none;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .planner-card.active {
            display: block;
        }
        .planner-card h3 {
            color: var(--deep);
            font-weight: normal;
            font-size: 20px;
            margin-top: 0;
            text-align: left;
            border-bottom: 1px solid var(--border);
            padding-bottom: 12px;
        }

        /* Itinerary Preview */
        .itinerary-preview {
            background: linear-gradient(135deg, var(--deep), var(--deep-alt));
            border-radius: 6px;
            padding: 36px;
            color: var(--text-light);
            border: 1px solid rgba(201, 169, 110, 0.2);
            box-shadow: 0 8px 28px rgba(59, 31, 43, 0.25);
            display: none;
            animation: fadeIn 0.5s ease;
        }
        .itinerary-preview.active {
            display: block;
        }
        .itinerary-preview h3 {
            color: var(--text-light);
            font-weight: normal;
            font-size: 22px;
            margin-top: 0;
            border-bottom: 1px solid rgba(201, 169, 110, 0.25);
            padding-bottom: 12px;
        }
        .itin-day {
            display: flex;
            gap: 18px;
            padding: 16px 0;
            border-bottom: 1px solid rgba(201, 169, 110, 0.15);
            align-items: flex-start;
        }
        .itin-day:last-child {
            border-bottom: none;
        }
        .itin-day-num {
            background: var(--gold);
            color: var(--deep);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 15px;
            flex-shrink: 0;
        }
        .itin-day h4 {
            color: var(--text-light);
            font-weight: normal;
            font-size: 16px;
            margin: 0 0 4px;
        }
        .itin-day p {
            color: var(--text-sub);
            font-size: 13px;
            margin: 0;
            line-height: 1.5;
        }

        .btn-row {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .secondary-button {
            background: transparent;
            color: var(--deep);
            border: 1px solid var(--deep);
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
            background: var(--deep);
            color: var(--text-light);
        }
        .secondary-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Summary Card */
        .summary-card {
            background: var(--card-bg);
            border-radius: 6px;
            padding: 20px;
            border: 1px solid var(--border);
            margin-bottom: 20px;
            text-align: left;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--border-soft);
        }
        .summary-item:last-child {
            border-bottom: none;
        }
        .summary-item .label {
            color: var(--text-muted);
        }
        .summary-item .value {
            color: var(--deep);
            font-weight: bold;
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--deep);
            color: var(--text-light);
            padding: 15px 20px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 9999;
            animation: slideIn 0.3s ease;
            max-width: 400px;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .notification.success { border-left: 4px solid var(--success); }
        .notification.info { border-left: 4px solid var(--info); }
        .notification.error { border-left: 4px solid var(--error); }
        .notification button {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            padding: 0;
            margin-left: 10px;
        }

        /* Loading Spinner */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: var(--text-light);
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .footer {
            background: var(--deep);
            color: var(--text-sub);
            text-align: center;
            padding: 30px 20px;
            margin-top: 60px;
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

        .login-prompt {
            background: var(--card-bg);
            border: 2px solid var(--gold);
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            max-width: 500px;
            margin: 40px auto;
        }
        .login-prompt i {
            font-size: 48px;
            color: var(--gold);
            margin-bottom: 20px;
        }
        .login-prompt h2 {
            color: var(--deep);
            margin-bottom: 15px;
        }
        .login-prompt p {
            color: var(--text-muted);
            margin-bottom: 25px;
            line-height: 1.6;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-header {
                justify-content: center;
                padding: 15px 20px;
            }
            .logo {
                height: 60px;
                min-width: 60px;
            }
            .logo-text {
                font-size: 24px;
            }
            .user-display {
                position: absolute;
                top: 10px;
                right: 20px;
                font-size: 12px;
            }
            .nav-container {
                flex-direction: column;
                align-items: center;
            }
            .nav-container a {
                font-size: 14px;
                padding: 8px 10px;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .steps::before {
                width: 80%;
            }
            .mood-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .planner-card,
            .itinerary-preview {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<header class="main-header">
    <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo">
    <span class="logo-text">Smart Booking</span>
    <div class="user-display">
        @auth
            <i class="fas fa-user-circle"></i>
            <span>{{ Auth::user()->name }}</span>
        @else
            <i class="fas fa-user-circle"></i>
            <span>Guest</span>
        @endauth
    </div>
</header>

<nav>
    <div class="nav-container">
        <a href="/"><i class="fas fa-home"></i> Home</a>
        <a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/plan-trip" class="active"><i class="fas fa-route"></i> Plan Trip</a>
        <a href="/flights"><i class="fas fa-plane"></i> Book Flights</a>
        <a href="/discover"><i class="fas fa-compass"></i> Discover</a>
        <a href="/destinations"><i class="fas fa-map-marked-alt"></i> Destinations</a>
        <a href="/community"><i class="fas fa-users"></i> Community</a>
        <a href="/wishlist" class="wishlist-counter">
            <i class="fas fa-heart"></i> Wishlist
            <span class="wishlist-count" id="wishlistCount">0</span>
        </a>
        @guest
            <a href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i> Login</a>
            <a href="{{ route('register') }}"><i class="fas fa-user-plus"></i> Register</a>
        @else
            <a href="/profile"><i class="fas fa-user-circle"></i> Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        @endguest
    </div>
</nav>

<section class="page-hero">
    <div>
        <h1><i class="fas fa-route"></i> Plan Your Trip</h1>
        <p id="welcomeMessage">
            @auth
                Welcome back, {{ explode(' ', Auth::user()->name)[0] }}! Let's plan your perfect trip.
            @else
                Let AI build the perfect itinerary tailored to your mood, budget, and style.
            @endauth
        </p>
    </div>
</section>

<div class="planner-wrap">
    @guest
        <!-- Login Prompt for Guests -->
        <div class="login-prompt">
            <i class="fas fa-lock"></i>
            <h2>Login Required</h2>
            <p>To use our AI-powered trip planner and save your itineraries, please log in or create a free account.</p>
            <a href="{{ route('login') }}" class="primary-button" style="margin-right: 10px;">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
            <a href="{{ route('register') }}" class="primary-button">
                <i class="fas fa-user-plus"></i> Register
            </a>
        </div>
    @else
        <!-- Steps -->
        <div class="steps">
            <div class="step active" id="stepIndicator1">
                <div class="step-circle">1</div>
                <div class="step-label">Mood & Style</div>
            </div>
            <div class="step" id="stepIndicator2">
                <div class="step-circle">2</div>
                <div class="step-label">Destination</div>
            </div>
            <div class="step" id="stepIndicator3">
                <div class="step-circle">3</div>
                <div class="step-label">Dates & Budget</div>
            </div>
            <div class="step" id="stepIndicator4">
                <div class="step-circle">4</div>
                <div class="step-label">Itinerary</div>
            </div>
        </div>

        <!-- Step 1: Mood -->
        <div class="planner-card active" id="step1">
            <h3><i class="fas fa-heart" style="color:var(--gold);margin-right:8px;"></i>How are you feeling?</h3>
            <p style="color:var(--text-muted);text-align:left;margin-top:0;">Choose the mood that best describes what kind of trip you're looking for.</p>
            <div class="mood-grid">
                <div class="mood-card selected" data-mood="adventurous" onclick="selectMood(this)">
                    <div class="mood-icon"><i class="fas fa-hiking"></i></div>
                    <h4>Adventurous</h4>
                    <p>Thrills & exploration</p>
                </div>
                <div class="mood-card" data-mood="relaxed" onclick="selectMood(this)">
                    <div class="mood-icon"><i class="fas fa-spa"></i></div>
                    <h4>Relaxed</h4>
                    <p>Peace & tranquility</p>
                </div>
                <div class="mood-card" data-mood="cultural" onclick="selectMood(this)">
                    <div class="mood-icon"><i class="fas fa-landmark"></i></div>
                    <h4>Cultural</h4>
                    <p>History & art</p>
                </div>
                <div class="mood-card" data-mood="romantic" onclick="selectMood(this)">
                    <div class="mood-icon"><i class="fas fa-heart"></i></div>
                    <h4>Romantic</h4>
                    <p>Love & escape</p>
                </div>
                <div class="mood-card" data-mood="foodie" onclick="selectMood(this)">
                    <div class="mood-icon"><i class="fas fa-utensils"></i></div>
                    <h4>Foodie</h4>
                    <p>Cuisine & flavor</p>
                </div>
                <div class="mood-card" data-mood="eco" onclick="selectMood(this)">
                    <div class="mood-icon"><i class="fas fa-leaf"></i></div>
                    <h4>Eco-Travel</h4>
                    <p>Nature & sustainability</p>
                </div>
            </div>
            <div class="btn-row">
                <button class="primary-button" onclick="nextStep(2)">Next <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- Step 2: Destination -->
        <div class="planner-card" id="step2">
            <h3><i class="fas fa-map-marker-alt" style="color:var(--gold);margin-right:8px;"></i>Pick a Destination</h3>
            <div class="form-group">
                <label>Where do you want to go?</label>
                <select id="destinationSelect">
                    <option value="">— Let AI choose for me —</option>
                    <option value="bali">Bali, Indonesia</option>
                    <option value="kyoto">Kyoto, Japan</option>
                    <option value="swiss">Swiss Alps, Switzerland</option>
                    <option value="santorini">Santorini, Greece</option>
                    <option value="paris">Paris, France</option>
                    <option value="lisbon">Lisbon, Portugal</option>
                    <option value="bangkok">Bangkok, Thailand</option>
                    <option value="amalfi">Amalfi Coast, Italy</option>
                    <option value="nz">New Zealand</option>
                    <option value="morocco">Morocco</option>
                </select>
            </div>
            <div class="form-group">
                <label>Travel Companion</label>
                <select id="companionSelect">
                    <option value="solo">Solo Travel</option>
                    <option value="couple">Couple</option>
                    <option value="family">Family</option>
                    <option value="friends">Friends Group</option>
                </select>
            </div>
            <div class="form-group">
                <label>Travelers</label>
                <input type="number" id="travelersCount" min="1" max="20" value="1" onchange="updateTravelers()">
            </div>
            <div class="btn-row">
                <button class="secondary-button" onclick="prevStep(1)"><i class="fas fa-arrow-left"></i> Back</button>
                <button class="primary-button" onclick="nextStep(3)">Next <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- Step 3: Dates & Budget -->
        <div class="planner-card" id="step3">
            <h3><i class="fas fa-calendar-alt" style="color:var(--gold);margin-right:8px;"></i>Dates & Budget</h3>
            
            <!-- Trip Summary -->
            <div class="summary-card" id="tripSummary">
                <h4 style="color:var(--deep);margin-top:0;">Trip Summary</h4>
                <div class="summary-item">
                    <span class="label">Mood:</span>
                    <span class="value" id="summaryMood">Adventurous</span>
                </div>
                <div class="summary-item">
                    <span class="label">Destination:</span>
                    <span class="value" id="summaryDestination">Let AI choose</span>
                </div>
                <div class="summary-item">
                    <span class="label">Travelers:</span>
                    <span class="value" id="summaryTravelers">1 person (Solo)</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Departure Date</label>
                    <input type="date" id="departureDate" onchange="validateDates()">
                </div>
                <div class="form-group">
                    <label>Return Date</label>
                    <input type="date" id="returnDate" onchange="validateDates()">
                </div>
            </div>
            <div class="form-group">
                <label>Total Budget (per person)</label>
                <div class="budget-display">$<span id="budgetVal">2,500</span></div>
                <input type="range" id="budgetSlider" min="500" max="10000" step="100" value="2500" oninput="updateBudget()">
                <div class="budget-labels"><span>$500</span><span>$10,000+</span></div>
            </div>
            <div class="form-group">
                <label>Special Requirements</label>
                <textarea id="specialRequirements" placeholder="Dietary needs, accessibility requirements, interests…" oninput="updateRequirements()"></textarea>
            </div>
            <div class="btn-row">
                <button class="secondary-button" onclick="prevStep(2)"><i class="fas fa-arrow-left"></i> Back</button>
                <button class="primary-button" onclick="nextStep(4)">Generate Itinerary <i class="fas fa-robot"></i></button>
            </div>
        </div>

        <!-- Step 4: AI Itinerary Preview -->
        <div class="itinerary-preview" id="step4">
            <h3><i class="fas fa-robot" style="color:var(--gold);margin-right:8px;"></i>AI-Generated Itinerary</h3>
            
            <!-- Final Summary -->
            <div class="summary-card" style="background:rgba(255,255,255,0.1);border-color:rgba(201,169,110,0.3);color:var(--text-light);">
                <h4 style="color:var(--text-light);margin-top:0;">Your Trip Details</h4>
                <div class="summary-item" style="border-color:rgba(201,169,110,0.2);">
                    <span class="label" style="color:var(--text-sub);">Mood:</span>
                    <span class="value" id="finalMood">Adventurous</span>
                </div>
                <div class="summary-item" style="border-color:rgba(201,169,110,0.2);">
                    <span class="label" style="color:var(--text-sub);">Destination:</span>
                    <span class="value" id="finalDestination">Bali, Indonesia</span>
                </div>
                <div class="summary-item" style="border-color:rgba(201,169,110,0.2);">
                    <span class="label" style="color:var(--text-sub);">Duration:</span>
                    <span class="value" id="finalDuration">7 days</span>
                </div>
                <div class="summary-item" style="border-color:rgba(201,169,110,0.2);">
                    <span class="label" style="color:var(--text-sub);">Budget:</span>
                    <span class="value" id="finalBudget">$2,500 per person</span>
                </div>
            </div>

            <p style="color:var(--text-sub);margin-top:0;font-size:14px;text-align:left;">Based on your selections, here is your personalized itinerary:</p>
            <div id="itineraryDays">
                <!-- Dynamic itinerary will be inserted here -->
            </div>

            <div class="btn-row">
                <button class="secondary-button" onclick="prevStep(3)"><i class="fas fa-arrow-left"></i> Back</button>
                <button class="primary-button" onclick="saveItinerary()"><i class="fas fa-save"></i> Save Itinerary</button>
                <button class="primary-button" style="background:var(--info);" onclick="exportItinerary()"><i class="fas fa-download"></i> Export PDF</button>
            </div>
        </div>
    @endguest
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

@auth
<script>
// Trip Planner Data
let tripData = {
    mood: 'adventurous',
    destination: '',
    companion: 'solo',
    travelers: 1,
    departureDate: '',
    returnDate: '',
    budget: 2500,
    requirements: '',
    user_id: {{ Auth::id() }}
};

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initDates();
    setupEventListeners();
    updateStepIndicators(1);
    
    // Load saved trip data if exists
    const savedTrip = localStorage.getItem('currentTrip_{{ Auth::id() }}');
    if (savedTrip) {
        tripData = JSON.parse(savedTrip);
        loadSavedData();
    }
});

function initDates() {
    // Set min date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('departureDate').min = today;
    document.getElementById('returnDate').min = today;
    
    // Set default departure to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('departureDate').value = tomorrow.toISOString().split('T')[0];
    
    // Set default return to 7 days later
    const nextWeek = new Date();
    nextWeek.setDate(nextWeek.getDate() + 7);
    document.getElementById('returnDate').value = nextWeek.toISOString().split('T')[0];
    
    tripData.departureDate = tomorrow.toISOString().split('T')[0];
    tripData.returnDate = nextWeek.toISOString().split('T')[0];
}

function setupEventListeners() {
    // Destination select
    document.getElementById('destinationSelect').addEventListener('change', function() {
        tripData.destination = this.value;
        updateSummary();
        saveTripData();
    });
    
    // Companion select
    document.getElementById('companionSelect').addEventListener('change', function() {
        tripData.companion = this.value;
        updateSummary();
        saveTripData();
    });
    
    // Departure date
    document.getElementById('departureDate').addEventListener('change', function() {
        tripData.departureDate = this.value;
        document.getElementById('returnDate').min = this.value;
        updateSummary();
        saveTripData();
    });
    
    // Return date
    document.getElementById('returnDate').addEventListener('change', function() {
        tripData.returnDate = this.value;
        updateSummary();
        saveTripData();
    });
    
    // Budget slider
    document.getElementById('budgetSlider').addEventListener('input', function() {
        updateBudget();
    });
    
    // Requirements textarea
    document.getElementById('specialRequirements').addEventListener('input', function() {
        tripData.requirements = this.value;
        saveTripData();
    });
}

function loadSavedData() {
    // Load mood
    const moodCard = document.querySelector(`.mood-card[data-mood="${tripData.mood}"]`);
    if (moodCard) {
        selectMood(moodCard);
    }
    
    // Load destination
    if (tripData.destination) {
        document.getElementById('destinationSelect').value = tripData.destination;
    }
    
    // Load companion
    document.getElementById('companionSelect').value = tripData.companion;
    
    // Load travelers
    document.getElementById('travelersCount').value = tripData.travelers;
    
    // Load dates
    if (tripData.departureDate) {
        document.getElementById('departureDate').value = tripData.departureDate;
    }
    if (tripData.returnDate) {
        document.getElementById('returnDate').value = tripData.returnDate;
    }
    
    // Load budget
    document.getElementById('budgetSlider').value = tripData.budget;
    document.getElementById('budgetVal').textContent = tripData.budget.toLocaleString();
    
    // Load requirements
    if (tripData.requirements) {
        document.getElementById('specialRequirements').value = tripData.requirements;
    }
    
    updateSummary();
}

function saveTripData() {
    localStorage.setItem('currentTrip_{{ Auth::id() }}', JSON.stringify(tripData));
}

// Mood selection
function selectMood(el) {
    document.querySelectorAll('.mood-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    tripData.mood = el.getAttribute('data-mood');
    updateSummary();
    saveTripData();
}

// Travelers update
function updateTravelers() {
    const count = parseInt(document.getElementById('travelersCount').value);
    if (count >= 1 && count <= 20) {
        tripData.travelers = count;
        updateSummary();
        saveTripData();
    }
}

// Budget update
function updateBudget() {
    const budget = parseInt(document.getElementById('budgetSlider').value);
    document.getElementById('budgetVal').textContent = budget.toLocaleString();
    tripData.budget = budget;
    saveTripData();
}

// Requirements update
function updateRequirements() {
    tripData.requirements = document.getElementById('specialRequirements').value;
    saveTripData();
}

// Date validation
function validateDates() {
    const departure = document.getElementById('departureDate').value;
    const returnDate = document.getElementById('returnDate').value;
    
    if (departure && returnDate) {
        const depDate = new Date(departure);
        const retDate = new Date(returnDate);
        
        if (retDate < depDate) {
            showNotification('Return date must be after departure date', 'error');
            document.getElementById('returnDate').value = '';
            tripData.returnDate = '';
        }
    }
}

// Update trip summary
function updateSummary() {
    // Mood
    document.getElementById('summaryMood').textContent = 
        tripData.mood.charAt(0).toUpperCase() + tripData.mood.slice(1);
    
    // Destination
    const destinationSelect = document.getElementById('destinationSelect');
    const selectedOption = destinationSelect.options[destinationSelect.selectedIndex];
    document.getElementById('summaryDestination').textContent = 
        selectedOption.text || 'Let AI choose';
    
    // Travelers
    const companionText = {
        'solo': 'Solo',
        'couple': 'Couple',
        'family': 'Family',
        'friends': 'Friends'
    }[tripData.companion];
    
    const plural = tripData.travelers > 1 ? 'people' : 'person';
    document.getElementById('summaryTravelers').textContent = 
        `${tripData.travelers} ${plural} (${companionText})`;
}

// Step navigation
let currentStep = 1;

function updateStepIndicators(step) {
    currentStep = step;
    
    // Update step indicators
    for (let i = 1; i <= 4; i++) {
        const indicator = document.getElementById(`stepIndicator${i}`);
        const stepElement = document.getElementById(`step${i}`);
        
        if (i === step) {
            indicator.classList.add('active');
            indicator.classList.remove('done');
            stepElement.classList.add('active');
        } else if (i < step) {
            indicator.classList.remove('active');
            indicator.classList.add('done');
            stepElement.classList.remove('active');
        } else {
            indicator.classList.remove('active');
            indicator.classList.remove('done');
            stepElement.classList.remove('active');
        }
    }
}

function nextStep(step) {
    // Validate current step before proceeding
    if (!validateStep(currentStep)) {
        return;
    }
    
    updateStepIndicators(step);
    
    // If going to step 4, generate itinerary
    if (step === 4) {
        generateItinerary();
    }
    
    // Smooth scroll to top of planner
    document.querySelector('.planner-wrap').scrollIntoView({ behavior: 'smooth' });
}

function prevStep(step) {
    updateStepIndicators(step);
    document.querySelector('.planner-wrap').scrollIntoView({ behavior: 'smooth' });
}

function validateStep(step) {
    switch(step) {
        case 1:
            if (!tripData.mood) {
                showNotification('Please select a mood for your trip', 'error');
                return false;
            }
            return true;
            
        case 2:
            if (!tripData.destination) {
                // If AI choose option is selected, assign a destination
                const destinations = ['bali', 'kyoto', 'santorini', 'paris', 'lisbon'];
                tripData.destination = destinations[Math.floor(Math.random() * destinations.length)];
                document.getElementById('destinationSelect').value = tripData.destination;
                updateSummary();
            }
            return true;
            
        case 3:
            if (!tripData.departureDate || !tripData.returnDate) {
                showNotification('Please select both departure and return dates', 'error');
                return false;
            }
            return true;
            
        default:
            return true;
    }
}

// Generate itinerary based on selections
function generateItinerary() {
    const itineraryDays = document.getElementById('itineraryDays');
    itineraryDays.innerHTML = '<div class="itin-day"><div class="itin-day-num"><div class="spinner"></div></div><div><h4>Generating your itinerary...</h4><p>AI is crafting the perfect trip plan for you</p></div></div>';
    
    // Simulate AI processing
    setTimeout(() => {
        // Update final summary
        document.getElementById('finalMood').textContent = 
            tripData.mood.charAt(0).toUpperCase() + tripData.mood.slice(1);
        
        const destinationSelect = document.getElementById('destinationSelect');
        const selectedOption = destinationSelect.options[destinationSelect.selectedIndex];
        document.getElementById('finalDestination').textContent = selectedOption.text;
        
        // Calculate duration
        const depDate = new Date(tripData.departureDate);
        const retDate = new Date(tripData.returnDate);
        const duration = Math.ceil((retDate - depDate) / (1000 * 60 * 60 * 24));
        document.getElementById('finalDuration').textContent = `${duration} days`;
        
        document.getElementById('finalBudget').textContent = `$${tripData.budget.toLocaleString()} per person`;
        
        // Generate itinerary based on mood and destination
        const itinerary = generateItineraryContent();
        itineraryDays.innerHTML = itinerary;
        
        showNotification('Itinerary generated successfully!', 'success');
    }, 1500);
}

function generateItineraryContent() {
    const destination = tripData.destination;
    const mood = tripData.mood;
    const budget = tripData.budget;
    
    // Base itineraries for different destinations
    const itineraries = {
        'bali': [
            { day: 1, title: 'Arrival in Ubud', desc: 'Arrive at Ngurah Rai Airport. Transfer to your villa in Ubud. Traditional Balinese welcome ceremony.' },
            { day: 2, title: 'Rice Terraces & Temples', desc: 'Morning at Tegallalang Rice Terraces. Visit Tirta Empul temple for purification.' },
            { day: 3, title: 'Adventure Day', desc: 'White-water rafting on Ayung River. Evening Kecak dance performance.' },
            { day: 4, title: 'Cooking & Culture', desc: 'Balinese cooking class. Explore Ubud art market and local crafts.' },
            { day: 5, title: 'Beach Time', desc: 'Transfer to Seminyak. Relax at the beach, enjoy sunset cocktails.' },
            { day: 6, title: 'Island Exploration', desc: 'Day trip to Nusa Penida for snorkeling and cliff views.' },
            { day: 7, title: 'Spa & Departure', desc: 'Morning spa treatment. Last-minute shopping. Departure transfer.' }
        ],
        'kyoto': [
            { day: 1, title: 'Arrival in Kyoto', desc: 'Arrive at Kansai Airport. Check into traditional ryokan. Evening Gion district walk.' },
            { day: 2, title: 'Golden Temples', desc: 'Visit Kinkaku-ji (Golden Pavilion) and Ryoan-ji temple with zen garden.' },
            { day: 3, title: 'Bamboo Forest & Shrines', desc: 'Walk through Arashiyama Bamboo Grove. Visit Tenryu-ji temple.' },
            { day: 4, title: 'Geisha Culture', desc: 'Morning at Fushimi Inari Shrine. Evening maiko performance in Gion.' },
            { day: 5, title: 'Traditional Arts', desc: 'Tea ceremony experience. Kimono fitting and photoshoot.' },
            { day: 6, title: 'Day Trip to Nara', desc: 'Visit Todai-ji temple with giant Buddha. Feed deer in Nara Park.' },
            { day: 7, title: 'Last Day in Kyoto', desc: 'Visit Nishiki Market. Traditional kaiseki dinner. Departure.' }
        ],
        'santorini': [
            { day: 1, title: 'Arrival in Santorini', desc: 'Arrive and check into caldera-view hotel. Sunset dinner in Oia.' },
            { day: 2, title: 'Caldera Cruise', desc: 'Full-day catamaran cruise. Visit hot springs, Red Beach, and White Beach.' },
            { day: 3, title: 'Wine Tasting', desc: 'Tour of traditional wineries. Wine tasting with sunset views.' },
            { day: 4, title: 'Ancient Thera', desc: 'Visit archaeological site of Akrotiri. Explore ancient ruins.' },
            { day: 5, title: 'Beach Day', desc: 'Relax at Perissa Black Sand Beach. Water sports activities.' },
            { day: 6, title: 'Cooking Class', desc: 'Traditional Greek cooking class. Learn to make moussaka and baklava.' },
            { day: 7, title: 'Farewell Santorini', desc: 'Last photos at blue-domed churches. Souvenir shopping. Departure.' }
        ],
        'paris': [
            { day: 1, title: 'Arrival in Paris', desc: 'Arrive at CDG. Check into hotel near Louvre. Evening Seine River walk.' },
            { day: 2, title: 'Eiffel Tower & Louvre', desc: 'Morning at Eiffel Tower. Afternoon at Louvre Museum.' },
            { day: 3, title: 'Notre-Dame & Montmartre', desc: 'Visit Notre-Dame Cathedral. Explore Montmartre and Sacré-Cœur.' },
            { day: 4, title: 'Versailles Day Trip', desc: 'Full-day trip to Palace of Versailles. Hall of Mirrors tour.' },
            { day: 5, title: 'Art & Fashion', desc: 'Visit Musée d\'Orsay. Shopping in Le Marais district.' },
            { day: 6, title: 'Food Tour', desc: 'French cooking class. Cheese and wine tasting in Latin Quarter.' },
            { day: 7, title: 'Au Revoir Paris', desc: 'Morning at Luxembourg Gardens. Final croissants. Departure.' }
        ]
    };
    
    // Default itinerary if destination not in list
    const selectedItinerary = itineraries[destination] || itineraries['bali'];
    
    // Adjust itinerary based on mood
    let adjustedItinerary = [...selectedItinerary];
    
    if (mood === 'adventurous') {
        adjustedItinerary[2].desc += ' Add volcano hiking.';
        adjustedItinerary[4].desc = 'Water sports and scuba diving.';
    } else if (mood === 'relaxed') {
        adjustedItinerary[2].desc = 'Full-day spa retreat. Meditation and yoga sessions.';
        adjustedItinerary[4].desc = 'Beach relaxation and massage therapy.';
    } else if (mood === 'foodie') {
        adjustedItinerary[3].desc = 'Food market tour and cooking masterclass.';
        adjustedItinerary[5].desc = 'Wine tasting tour and gourmet dinner.';
    } else if (mood === 'cultural') {
        adjustedItinerary[2].desc = 'Museum tour and cultural workshops.';
        adjustedItinerary[4].desc = 'Traditional craft workshops and local performances.';
    }
    
    // Adjust based on budget
    if (budget > 5000) {
        adjustedItinerary.forEach(day => {
            day.desc += ' Luxury accommodations and private tours included.';
        });
    } else if (budget < 1500) {
        adjustedItinerary.forEach(day => {
            day.desc = day.desc.replace('private', 'group').replace('luxury', 'comfortable');
        });
    }
    
    // Generate HTML
    let html = '';
    adjustedItinerary.forEach((item, index) => {
        html += `
            <div class="itin-day">
                <div class="itin-day-num">${item.day}</div>
                <div>
                    <h4>${item.title}</h4>
                    <p>${item.desc}</p>
                    ${generateActivities(item.day, mood, destination)}
                </div>
            </div>
        `;
    });
    
    return html;
}

function generateActivities(day, mood, destination) {
    const activities = {
        'adventurous': ['Hiking', 'Zip-lining', 'Scuba Diving', 'Rock Climbing', 'Paragliding'],
        'relaxed': ['Spa Treatment', 'Yoga Session', 'Beach Relaxation', 'Meditation', 'Reading'],
        'foodie': ['Cooking Class', 'Food Tour', 'Wine Tasting', 'Market Visit', 'Fine Dining'],
        'cultural': ['Museum Visit', 'Historical Tour', 'Art Workshop', 'Cultural Show', 'Local Market'],
        'romantic': ['Sunset Cruise', 'Private Dinner', 'Couples Spa', 'Photography Tour', 'Stargazing'],
        'eco': ['Nature Walk', 'Wildlife Watching', 'Eco Tour', 'Sustainable Workshop', 'Farm Visit']
    };
    
    const moodActivities = activities[mood] || activities['adventurous'];
    const randomActivity = moodActivities[Math.floor(Math.random() * moodActivities.length)];
    
    return `<div style="margin-top: 8px; padding: 6px 10px; background: rgba(201, 169, 110, 0.2); border-radius: 4px; display: inline-block;">
                <small><i class="fas fa-star"></i> Featured Activity: ${randomActivity}</small>
            </div>`;
}

// Save itinerary to database
function saveItinerary() {
    const itinerary = {
        ...tripData,
        generatedAt: new Date().toISOString(),
        itineraryId: 'TRIP-' + Date.now()
    };
    
    // Send to server
    fetch('/api/itineraries', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(itinerary)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Itinerary saved to your dashboard!', 'success');
            // Clear local storage after successful save
            localStorage.removeItem('currentTrip_{{ Auth::id() }}');
        } else {
            showNotification('Failed to save itinerary. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}

// Export itinerary as PDF
function exportItinerary() {
    showNotification('Preparing your itinerary for download...', 'info');
    
    // Call server endpoint to generate PDF
    window.location.href = '/itineraries/export?' + new URLSearchParams(tripData);
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    document.querySelectorAll('.notification').forEach(n => n.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    `;
    
    document.body.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 3000);
}
</script>
@endauth
</body>
</html>

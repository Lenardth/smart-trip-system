<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite([
        'resources/css/app.css',
        'resources/css/pages/base.css',
        'resources/css/pages/landing.css',
        'resources/js/pages/base.js',
        'resources/js/pages/landing.js'
    ])
    <style>
        #originInput {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--card-bg);
            color: var(--text);
            font-size: 15px;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            appearance: none;
            -webkit-appearance: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        #originInput:focus {
            border-color: var(--deep);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.15);
        }

        #originInput::placeholder {
            color: var(--text-muted);
            opacity: 0.7;
        }

        .input-hint {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: var(--text-muted);
            opacity: 0.8;
        }

        .input-hint i {
            margin-right: 4px;
        }

        .custom-select-wrapper {
            position: relative;
            user-select: none;
        }

        .custom-select-trigger {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--card-bg);
            color: var(--text);
            font-size: 15px;
            font-family: inherit;
            cursor: pointer;
            box-sizing: border-box;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .custom-select-trigger:hover,
        .custom-select-wrapper.open .custom-select-trigger {
            border-color: var(--deep);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.15);
        }

        .custom-select-icon {
            color: var(--deep);
            width: 18px;
            text-align: center;
            flex-shrink: 0;
        }

        .custom-select-text {
            flex: 1;
        }

        .custom-select-arrow {
            color: var(--text-muted);
            font-size: 12px;
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }

        .custom-select-wrapper.open .custom-select-arrow {
            transform: rotate(180deg);
        }

        .custom-select-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 6px;
            z-index: 999;
            max-height: 280px;
            overflow-y: auto;
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
        }

        .custom-select-wrapper.open .custom-select-dropdown {
            display: block !important;
        }

        .custom-select-group {
            padding: 8px 14px 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--deep);
            opacity: 0.8;
            border-top: 1px solid var(--border);
            margin-top: 4px;
        }

        .custom-select-group:first-child {
            border-top: none;
            margin-top: 0;
        }

        .custom-select-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            font-size: 14px;
            color: var(--text);
            cursor: pointer;
            transition: background 0.15s;
        }

        .custom-select-option i {
            color: var(--deep);
            width: 16px;
            text-align: center;
            flex-shrink: 0;
        }

        .custom-select-option:hover {
            background: rgba(201, 169, 110, 0.1);
        }

        .custom-select-option.selected {
            background: rgba(201, 169, 110, 0.15);
            font-weight: 600;
        }

        .category-card.active-style {
            border-color: var(--deep) !important;
            background: rgba(201, 169, 110, 0.12) !important;
        }

        .category-card.active-style .category-icon i {
            color: var(--deep);
        }

        #destinationsGrid,
        #styleDestinationsGrid {
            align-items: stretch;
        }

        #destinationsGrid .destination-card,
        #styleDestinationsGrid .destination-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        #destinationsGrid .destination-card .destination-info,
        #styleDestinationsGrid .destination-card .destination-info {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        #destinationsGrid .destination-card .destination-info a.primary-button,
        #styleDestinationsGrid .destination-card .destination-info a.primary-button {
            margin-top: auto !important;
            display: block !important;
            text-align: center !important;
        }
    </style>
</head>
<body>

<header class="main-header">
    <a href="/" style="display:flex;align-items:center;gap:14px;text-decoration:none;">
        <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo">
        <span class="logo-text">Smart Booking</span>
    </a>
    @auth
    <div class="user-display">
        <i class="fas fa-user-circle"></i>
        <span>{{ Auth::user()->name }}</span>
    </div>
    @endauth
</header>

<nav class="nav-container">
    <a href="/" class="active"><i class="fas fa-home"></i> Home</a>
    @auth
    <a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    @endauth
    <a href="/plan-trip"><i class="fas fa-route"></i> Plan Trip</a>
    <a href="/flights"><i class="fas fa-plane"></i> Book Flights</a>
    <a href="/discover"><i class="fas fa-compass"></i> Discover</a>
    <a href="/destinations"><i class="fas fa-map-marked-alt"></i> Destinations</a>
    <a href="/community"><i class="fas fa-users"></i> Community</a>
    @auth
    <a href="/wishlist"><i class="fas fa-heart"></i> Wishlist <span class="nav-badge" id="wishlistCount">0</span></a>
    @endauth
    @guest
    <a href="/login"><i class="fas fa-sign-in-alt"></i> Login</a>
    @endguest
    @auth
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </form>
    @endauth
</nav>

<section class="hero">
    <div class="hero-content">
        <h1>Plan Your Perfect Journey with AI</h1>
        <p>Personalized travel recommendations based on your mood, preferences, and budget. Discover destinations you'll love.</p>
        <div class="hero-buttons">
            <button class="primary-button" onclick="window.location.href='/plan-trip'">
                <i class="fas fa-magic"></i> Start Planning Now
            </button>
            <button class="secondary-button" onclick="window.location.href='/flights'">
                <i class="fas fa-plane"></i> Book Flights
            </button>
        </div>
    </div>
</section>

<div class="quick-plan">
    <h2 class="section-title">Quick Trip Builder</h2>
    <p class="section-subtitle">Tell us about your ideal trip and our AI will find the perfect destinations for you</p>

    <div class="qb-steps">
        <div class="qb-step active" data-step="1">
            <span class="qb-step-num">1</span>
            <span class="qb-step-label">Who & Mood</span>
        </div>
        <div class="qb-step-line"></div>
        <div class="qb-step" data-step="2">
            <span class="qb-step-num">2</span>
            <span class="qb-step-label">Trip Details</span>
        </div>
        <div class="qb-step-line"></div>
        <div class="qb-step" data-step="3">
            <span class="qb-step-num">3</span>
            <span class="qb-step-label">Preferences</span>
        </div>
    </div>

    <div class="qb-panel" id="qbPanel1">
        <div class="quick-plan-form">
            <div class="form-group">
                <label><i class="fas fa-users"></i> Travelling With</label>
                <select id="companionSelect" name="companion">
                    <option value="solo">Solo — Just me</option>
                    <option value="couple">Couple — Partner & I</option>
                    <option value="family_young">Family — Young children (under 12)</option>
                    <option value="family_teens">Family — Teenagers</option>
                    <option value="friends_small">Friends — Small group (2–4)</option>
                    <option value="friends_large">Friends — Large group (5+)</option>
                    <option value="business">Business Travel</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-heart"></i> Travel Mood</label>
                <div class="custom-select-wrapper" id="moodSelectWrapper">
                    <div class="custom-select-trigger" id="moodSelectTrigger">
                        <span class="custom-select-icon"><i class="fas fa-hiking"></i></span>
                        <span class="custom-select-text">Adventurous — Thrills & outdoors</span>
                        <i class="fas fa-chevron-down custom-select-arrow"></i>
                    </div>
                    <div class="custom-select-dropdown" id="moodDropdown" style="display:none;">
                        <div class="custom-select-group">Popular Moods</div>
                        <div class="custom-select-option selected" data-value="adventurous"><i class="fas fa-hiking"></i> Adventurous — Thrills &amp; outdoors</div>
                        <div class="custom-select-option" data-value="relaxed"><i class="fas fa-umbrella-beach"></i> Relaxed — Rest &amp; recharge</div>
                        <div class="custom-select-option" data-value="romantic"><i class="fas fa-heart"></i> Romantic — Intimate &amp; scenic</div>
                        <div class="custom-select-option" data-value="cultural"><i class="fas fa-landmark"></i> Cultural — History &amp; arts</div>
                        <div class="custom-select-option" data-value="foodie"><i class="fas fa-utensils"></i> Foodie — Cuisine &amp; markets</div>
                        <div class="custom-select-option" data-value="nature"><i class="fas fa-tree"></i> Nature — Wildlife &amp; eco-tourism</div>

                        <div class="custom-select-group">Mindset &amp; Wellness</div>
                        <div class="custom-select-option" data-value="wellness"><i class="fas fa-spa"></i> Wellness — Spa &amp; mindfulness</div>
                        <div class="custom-select-option" data-value="spiritual"><i class="fas fa-place-of-worship"></i> Spiritual — Temples &amp; retreats</div>
                        <div class="custom-select-option" data-value="digital_detox"><i class="fas fa-power-off"></i> Digital Detox — Unplug &amp; reconnect</div>
                        <div class="custom-select-option" data-value="slow_travel"><i class="fas fa-feather"></i> Slow Travel — Immerse, don't rush</div>
                        <div class="custom-select-option" data-value="healing"><i class="fas fa-hand-holding-heart"></i> Healing — Recovery &amp; self-care</div>
                        <div class="custom-select-option" data-value="solo_discovery"><i class="fas fa-user"></i> Solo Discovery — Find yourself</div>

                        <div class="custom-select-group">Social &amp; Entertainment</div>
                        <div class="custom-select-option" data-value="nightlife"><i class="fas fa-music"></i> Nightlife — Music &amp; entertainment</div>
                        <div class="custom-select-option" data-value="festival"><i class="fas fa-star"></i> Festival Chaser — Events &amp; carnivals</div>
                        <div class="custom-select-option" data-value="sports"><i class="fas fa-futbol"></i> Sports Fan — Matches &amp; stadiums</div>
                        <div class="custom-select-option" data-value="party"><i class="fas fa-glass-cheers"></i> Party Mode — Celebrate &amp; socialise</div>
                        <div class="custom-select-option" data-value="lgbtq"><i class="fas fa-rainbow"></i> LGBTQ+ Friendly — Inclusive destinations</div>
                        <div class="custom-select-option" data-value="social"><i class="fas fa-users"></i> Social Butterfly — Meet new people</div>

                        <div class="custom-select-group">Exploration Styles</div>
                        <div class="custom-select-option" data-value="road_trip"><i class="fas fa-car"></i> Road Trip — Open roads &amp; freedom</div>
                        <div class="custom-select-option" data-value="backpacking"><i class="fas fa-suitcase"></i> Backpacking — Rough it &amp; explore</div>
                        <div class="custom-select-option" data-value="island_hopping"><i class="fas fa-water"></i> Island Hopping — Sea &amp; archipelagos</div>
                        <div class="custom-select-option" data-value="city_break"><i class="fas fa-city"></i> City Break — Urban buzz &amp; skylines</div>
                        <div class="custom-select-option" data-value="off_beaten"><i class="fas fa-map"></i> Off the Beaten Path — Hidden gems</div>
                        <div class="custom-select-option" data-value="luxury_escape"><i class="fas fa-gem"></i> Luxury Escape — Five-star everything</div>
                        <div class="custom-select-option" data-value="safari"><i class="fas fa-paw"></i> Safari &amp; Wildlife — Raw nature up close</div>
                        <div class="custom-select-option" data-value="cruise"><i class="fas fa-ship"></i> Cruise — Sea views &amp; port hopping</div>

                        <div class="custom-select-group">Seasonal &amp; Weather</div>
                        <div class="custom-select-option" data-value="sun_seeker"><i class="fas fa-sun"></i> Sun Seeker — Heat &amp; beaches</div>
                        <div class="custom-select-option" data-value="snow_lover"><i class="fas fa-snowflake"></i> Snow Lover — Skiing &amp; winter wonderlands</div>
                        <div class="custom-select-option" data-value="autumn_vibes"><i class="fas fa-leaf"></i> Autumn Vibes — Foliage &amp; cosy escapes</div>
                        <div class="custom-select-option" data-value="monsoon_magic"><i class="fas fa-cloud-rain"></i> Monsoon Magic — Lush greens &amp; rain</div>
                        <div class="custom-select-option" data-value="northern_lights"><i class="fas fa-moon"></i> Aurora Chaser — Northern lights</div>

                        <div class="custom-select-group">Learning &amp; Growth</div>
                        <div class="custom-select-option" data-value="language"><i class="fas fa-language"></i> Language Immersion — Learn while travelling</div>
                        <div class="custom-select-option" data-value="photography"><i class="fas fa-camera"></i> Photography Trip — Capture stunning moments</div>
                        <div class="custom-select-option" data-value="volunteer"><i class="fas fa-hands-helping"></i> Volunteer Travel — Give back while exploring</div>
                        <div class="custom-select-option" data-value="study_abroad"><i class="fas fa-graduation-cap"></i> Study Abroad — Education &amp; experience</div>
                        <div class="custom-select-option" data-value="cooking_class"><i class="fas fa-utensils"></i> Culinary School — Cook like a local</div>
                        <div class="custom-select-option" data-value="art_retreat"><i class="fas fa-paint-brush"></i> Art Retreat — Paint, sculpt &amp; create</div>

                        <div class="custom-select-group">Family &amp; Groups</div>
                        <div class="custom-select-option" data-value="family_fun"><i class="fas fa-child"></i> Family Fun — Kids activities &amp; theme parks</div>
                        <div class="custom-select-option" data-value="multigenerational"><i class="fas fa-users"></i> Multi-Generational — Everyone included</div>
                        <div class="custom-select-option" data-value="honeymoon"><i class="fas fa-ring"></i> Honeymoon — Perfect romantic start</div>
                        <div class="custom-select-option" data-value="girls_trip"><i class="fas fa-female"></i> Girls Trip — Friendship &amp; fun</div>
                        <div class="custom-select-option" data-value="boys_trip"><i class="fas fa-male"></i> Boys Trip — Adventure &amp; bonding</div>
                        <div class="custom-select-option" data-value="group_adventure"><i class="fas fa-mountain"></i> Group Adventure — Shared thrills</div>
                    </div>
                    <input type="hidden" id="moodSelect" name="mood" value="adventurous">
                </div>
            </div>
            <div class="form-group">
                <label><i class="fas fa-star"></i> Experience Level</label>
                <select id="experienceSelect" name="experience">
                    <option value="first_time">First-time traveller</option>
                    <option value="occasional">Occasional traveller (1–2 trips/year)</option>
                    <option value="regular">Regular traveller (3–5 trips/year)</option>
                    <option value="frequent">Frequent traveller (6+ trips/year)</option>
                </select>
            </div>
        </div>
        <div class="qb-nav">
            <button class="primary-button qb-next-btn" data-next="2">
                Next <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <div class="qb-panel" id="qbPanel2" style="display:none;">
        <div class="quick-plan-form">
            <div class="form-group">
                <label><i class="fas fa-calendar-alt"></i> Departure Month</label>
                <select id="monthSelect" name="month">
                    <option value="january">January</option>
                    <option value="february">February</option>
                    <option value="march">March</option>
                    <option value="april">April</option>
                    <option value="may">May</option>
                    <option value="june">June</option>
                    <option value="july">July</option>
                    <option value="august">August</option>
                    <option value="september">September</option>
                    <option value="october">October</option>
                    <option value="november">November</option>
                    <option value="december">December</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-clock"></i> Trip Duration</label>
                <select id="durationSelect" name="duration">
                    <option value="weekend">Long Weekend (3–4 days)</option>
                    <option value="week">One Week (5–7 days)</option>
                    <option value="two_weeks">Two Weeks (10–14 days)</option>
                    <option value="month">One Month+</option>
                    <option value="flexible">Flexible / Open-ended</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-dollar-sign"></i> Budget Per Person</label>
                <select id="budgetSelect" name="budget">
                    <option value="backpacker">Backpacker — Under $500</option>
                    <option value="budget">Budget — $500–$1,500</option>
                    <option value="mid">Mid-range — $1,500–$4,000</option>
                    <option value="premium">Premium — $4,000–$8,000</option>
                    <option value="luxury">Luxury — $8,000+</option>
                </select>
            </div>
        </div>
        <div class="qb-nav">
            <button class="secondary-button qb-back-btn" data-back="1">
                <i class="fas fa-arrow-left"></i> Back
            </button>
            <button class="primary-button qb-next-btn" data-next="3">
                Next <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <div class="qb-panel" id="qbPanel3" style="display:none;">
        <div class="quick-plan-form">
            <div class="form-group">
                <label><i class="fas fa-globe"></i> Preferred Region</label>
                <select id="regionSelect" name="region">
                    <option value="any">Anywhere — Surprise me!</option>
                    <option value="europe">Europe</option>
                    <option value="southeast_asia">Southeast Asia</option>
                    <option value="east_asia">East Asia</option>
                    <option value="south_asia">South Asia</option>
                    <option value="middle_east">Middle East</option>
                    <option value="africa">Africa</option>
                    <option value="north_america">North America</option>
                    <option value="central_america">Central America &amp; Caribbean</option>
                    <option value="south_america">South America</option>
                    <option value="oceania">Oceania &amp; Pacific</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-bed"></i> Accommodation Style</label>
                <select id="accommodationSelect" name="accommodation">
                    <option value="any">No preference</option>
                    <option value="hostel">Hostel / Budget guesthouse</option>
                    <option value="bnb">B&amp;B / Boutique hotel</option>
                    <option value="hotel">3–4 star hotel</option>
                    <option value="resort">5-star resort</option>
                    <option value="villa">Private villa / Airbnb</option>
                    <option value="glamping">Glamping / Eco-lodge</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-plane-departure"></i> Flying From</label>
                <input
                    type="text"
                    id="originInput"
                    name="origin"
                    placeholder="e.g. Johannesburg, London, New York, Dubai..."
                    autocomplete="off"
                >
                <small class="input-hint"><i class="fas fa-info-circle"></i> Enter any city or airport name</small>
            </div>
        </div>
        <div class="qb-nav">
            <button class="secondary-button qb-back-btn" data-back="2">
                <i class="fas fa-arrow-left"></i> Back
            </button>
            <button class="primary-button" id="generatePlanBtn">
                <i class="fas fa-robot"></i> Generate AI Suggestions
            </button>
        </div>
    </div>
</div>

<!-- AI Recommendations Container -->
<div id="aiRecommendations" class="ai-recommendations" style="display: none;">
    <div class="loading-spinner" id="loadingSpinner" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i> AI is analyzing your preferences...
    </div>
    <div id="recommendationsContent"></div>
</div>

<div class="tile-grid">
    <div class="tile">
        <h3><i class="fas fa-plane"></i> Easy Flight Booking</h3>
        <p>Search and book flights worldwide with our integrated flight booking system. Get the best deals on airfare.</p>
    </div>
    <div class="tile">
        <h3><i class="fas fa-brain"></i> AI Mood-Based Suggestions</h3>
        <p>Tell us how you feel—adventurous, relaxed, cultural—and get personalized destination recommendations.</p>
    </div>
    <div class="tile">
        <h3><i class="fas fa-sliders-h"></i> Smart Budget Optimization</h3>
        <p>Set your budget and let our algorithm find the best flights, accommodations, and activities within your range.</p>
    </div>
</div>

<section class="slideshow-section">
    <h2 class="section-title">Featured Destinations</h2>
    <p class="section-subtitle">Discover handpicked destinations curated by our travel experts</p>

    <div class="slideshow-container">
        <div class="slides">
            <div class="slide active" style="background-image: url('https://images.unsplash.com/photo-1516496636080-14fb876e029d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                <div class="slide-content">
                    <h3>Amalfi Coast, Italy</h3>
                    <p>Experience the breathtaking beauty of Italy's coastline with its colorful cliffside villages, delicious cuisine, and Mediterranean charm.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1516483638261-f4dbaf036963?ixlib=rb-4.0.3&auto=format&fit=crop&w=2067&q=80');">
                <div class="slide-content">
                    <h3>Bali, Indonesia</h3>
                    <p>Find your inner peace in Bali's spiritual retreats, lush rice terraces, and pristine beaches. Perfect for relaxation and adventure.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                <div class="slide-content">
                    <h3>Santorini, Greece</h3>
                    <p>Marvel at the iconic white-washed buildings, stunning sunsets, and crystal-clear waters of this romantic Greek island paradise.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1511739001486-6bfe10ce785f?ixlib=rb-4.0.3&auto=format&fit=crop&w=2064&q=80');">
                <div class="slide-content">
                    <h3>Kyoto, Japan</h3>
                    <p>Step back in time with ancient temples, traditional tea houses, and the magical beauty of cherry blossom season in Kyoto.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1579530190412-b35a65e17c8d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2066&q=80');">
                <div class="slide-content">
                    <h3>Swiss Alps</h3>
                    <p>Embrace adventure in the majestic Swiss Alps with breathtaking mountain views, skiing, and luxury mountain resorts.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1513326738677-b964603b136d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                <div class="slide-content">
                    <h3>Marrakech, Morocco</h3>
                    <p>Discover vibrant souks, stunning palaces, and rich cultural heritage in this enchanting North African city.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1544551763-46a013bb70d5?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                <div class="slide-content">
                    <h3>Maldives Overwater Bungalows</h3>
                    <p>Experience ultimate luxury in crystal-clear turquoise waters with private villas and world-class diving.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1543832923-44667a44c804?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                <div class="slide-content">
                    <h3>New York City, USA</h3>
                    <p>Explore the city that never sleeps with iconic landmarks, Broadway shows, and diverse culinary experiences.</p>
                </div>
            </div>
        </div>

        <div class="slide-number">1 / 8</div>

        <div class="slide-controls">
            <button class="slide-btn prev-btn">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="slide-btn next-btn">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <div class="slide-indicators">
            <span class="indicator active" data-slide="0"></span>
            <span class="indicator" data-slide="1"></span>
            <span class="indicator" data-slide="2"></span>
            <span class="indicator" data-slide="3"></span>
            <span class="indicator" data-slide="4"></span>
            <span class="indicator" data-slide="5"></span>
            <span class="indicator" data-slide="6"></span>
            <span class="indicator" data-slide="7"></span>
        </div>
    </div>
</section>

<section class="discover-section">
    <div class="discover-header">
        <h2 class="section-title">Discover Trending Destinations</h2>
        <button class="secondary-button" onclick="window.location.href='/discover'">
            View All <i class="fas fa-arrow-right"></i>
        </button>
    </div>

    <div class="filter-tags">
        <span class="filter-tag active" data-filter="all"><i class="fas fa-globe"></i> All</span>
        <span class="filter-tag" data-filter="trending"><i class="fas fa-fire"></i> Trending</span>
        <span class="filter-tag" data-filter="beach"><i class="fas fa-umbrella-beach"></i> Beach</span>
        <span class="filter-tag" data-filter="mountain"><i class="fas fa-mountain"></i> Mountain</span>
        <span class="filter-tag" data-filter="historical"><i class="fas fa-landmark"></i> Historical</span>
        <span class="filter-tag" data-filter="food_culture"><i class="fas fa-utensils"></i> Food & Culture</span>
        <span class="filter-tag" data-filter="eco_tourism"><i class="fas fa-leaf"></i> Eco-Tourism</span>
        <span class="filter-tag" data-filter="romantic"><i class="fas fa-heart"></i> Romantic</span>
        <span class="filter-tag" data-filter="adventurous"><i class="fas fa-hiking"></i> Adventure</span>
        <span class="filter-tag" data-filter="hidden_gem"><i class="fas fa-gem"></i> Hidden Gems</span>
    </div>

    <div id="destinationsLoading" style="text-align:center; padding: 60px 20px; color: var(--text-muted);">
        <i class="fas fa-spinner fa-spin" style="font-size:28px; color:var(--deep);"></i>
        <p style="margin-top:12px;">Loading destinations...</p>
    </div>

    <div class="destinations-grid" id="destinationsGrid" style="display:none;"></div>

    <div id="destinationsEmpty" style="display:none; text-align:center; padding:60px 20px; color:var(--text-muted);">
        <i class="fas fa-map-marked-alt" style="font-size:40px; opacity:0.3;"></i>
        <p style="margin-top:12px;">No destinations found for this filter.</p>
    </div>
</section>

<div style="max-width: 1200px; margin: 60px auto; padding: 40px; background: var(--card-bg); border-radius: 6px; border: 1px solid var(--border);">
    <h2 class="section-title">Ready to Fly?</h2>
    <p class="section-subtitle">Find and book flights to your dream destinations</p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-top: 40px;">
        <div style="text-align: center; padding: 30px; background: rgba(201,169,110,0.1); border-radius: 6px; border: 1px solid var(--border); cursor: pointer;" onclick="window.location.href='/flights'">
            <div style="font-size: 48px; color: var(--deep); margin-bottom: 20px;">
                <i class="fas fa-search"></i>
            </div>
            <h3 style="color: var(--deep); margin-bottom: 10px;">Search Flights</h3>
            <p style="color: var(--text-muted);">Find flights worldwide with flexible dates</p>
        </div>

        <div style="text-align: center; padding: 30px; background: rgba(201,169,110,0.1); border-radius: 6px; border: 1px solid var(--border); cursor: pointer;" onclick="window.location.href='/flights/create'">
            <div style="font-size: 48px; color: var(--deep); margin-bottom: 20px;">
                <i class="fas fa-plus-circle"></i>
            </div>
            <h3 style="color: var(--deep); margin-bottom: 10px;">Create Flight</h3>
            <p style="color: var(--text-muted);">Add custom flight options</p>
        </div>

        <div style="text-align: center; padding: 30px; background: rgba(201,169,110,0.1); border-radius: 6px; border: 1px solid var(--border); cursor: pointer;" onclick="window.location.href='/bookings'">
            <div style="font-size: 48px; color: var(--deep); margin-bottom: 20px;">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <h3 style="color: var(--deep); margin-bottom: 10px;">My Bookings</h3>
            <p style="color: var(--text-muted);">View and manage your bookings</p>
        </div>
    </div>

    <div style="text-align: center; margin-top: 40px;">
        <button class="primary-button" onclick="window.location.href='/flights'" style="padding: 15px 40px; font-size: 16px;">
            <i class="fas fa-plane"></i> Start Booking Flights Now
        </button>
    </div>
</div>

<div style="max-width: 1200px; margin: 60px auto; padding: 0 20px;">
    <h2 class="section-title">Explore By Travel Style</h2>
    <p class="section-subtitle">Find destinations that match your preferred travel experience</p>

    <div class="explore-categories">
        <div class="category-card active-style" data-style="adventure" onclick="filterByStyle('adventure', this)">
            <div class="category-icon"><i class="fas fa-hiking"></i></div>
            <h3>Adventure Travel</h3>
            <p>Hiking, trekking, and extreme sports destinations</p>
        </div>
        <div class="category-card" data-style="beach" onclick="filterByStyle('beach', this)">
            <div class="category-icon"><i class="fas fa-umbrella-beach"></i></div>
            <h3>Beach &amp; Relaxation</h3>
            <p>Perfect spots for sunbathing and unwinding</p>
        </div>
        <div class="category-card" data-style="cultural" onclick="filterByStyle('cultural', this)">
            <div class="category-icon"><i class="fas fa-landmark"></i></div>
            <h3>Cultural Immersion</h3>
            <p>Historical sites and cultural experiences</p>
        </div>
        <div class="category-card" data-style="food" onclick="filterByStyle('food', this)">
            <div class="category-icon"><i class="fas fa-utensils"></i></div>
            <h3>Culinary Tours</h3>
            <p>Foodie paradises and cooking experiences</p>
        </div>
    </div>

    <div id="styleResultsHeader" style="display:none; margin: 30px 0 16px; display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h3 id="styleResultsTitle" style="margin:0; color:var(--text); font-size:18px;"></h3>
            <span id="styleResultsCount" style="font-size:13px; color:var(--text-muted);"></span>
        </div>
    </div>

    <div class="destinations-grid" id="styleDestinationsGrid" style="display:none; margin-top:24px;"></div>

    <div id="styleEmpty" style="display:none; text-align:center; padding:40px; color:var(--text-muted);">
        <i class="fas fa-map-marked-alt" style="font-size:36px; opacity:0.3;"></i>
        <p style="margin-top:12px;">No destinations found for this style.</p>
    </div>

    <div id="styleViewAll" style="display:none; text-align:center; margin-top:28px;">
        <a href="/destinations" class="secondary-button" style="text-decoration:none; padding:10px 28px;">
            <i class="fas fa-compass"></i> View All Destinations
        </a>
    </div>
</div>

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
            <p>Customize your trip plan, add activities, and book flights directly through our system.</p>
        </div>
    </div>
</div>

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

<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <h2 class="section-title">Advanced Smart Features</h2>
    <p class="section-subtitle">Experience the future of travel planning with our AI-powered tools</p>

    <div class="smart-features-grid">
        <div class="smart-feature-card">
            <i class="fas fa-plane feature-icon"></i>
            <h3>Smart Flight Booking</h3>
            <p>AI-powered flight search finds the best deals, optimal routes, and perfect timing for your travels.</p>
        </div>
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
            <i class="fas fa-leaf feature-icon"></i>
            <h3>Sustainable Travel Options</h3>
            <p>Highlights eco-friendly accommodations, low-carbon transportation, and sustainable tourism activities.</p>
        </div>
    </div>
</div>

<section class="testimonials">
    <div class="testimonial-container">
        <h2 class="section-title">What Travelers Say</h2>
        <p class="section-subtitle">Join thousands of satisfied travelers who discovered their perfect trips</p>

        <div class="testimonial-grid" id="testimonialsGrid">
        </div>
    </div>
</section>

<div class="newsletter">
    <h2 class="section-title">Get Travel Inspiration</h2>
    <p class="section-subtitle">Subscribe to receive weekly destination ideas, travel tips, and exclusive deals</p>
    <div class="newsletter-input">
        <input type="email" id="newsletterEmail" placeholder="Enter your email address">
        <button onclick="subscribeNewsletter()">
            <i class="fas fa-paper-plane"></i> Subscribe
        </button>
    </div>
    <p class="privacy">We respect your privacy. Unsubscribe at any time.</p>
</div>

<footer class="footer">
    <div style="max-width: 1200px; margin: 0 auto;">
        <p>© 2026 Smart Trip Planner | Laravel Web Application Project | Created By Lenard Tivanani Hlabangwana</p>
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
document.addEventListener('DOMContentLoaded', function () {
    const wrapper  = document.getElementById('moodSelectWrapper');
    const trigger  = document.getElementById('moodSelectTrigger');
    const dropdown = document.getElementById('moodDropdown');
    const hidden   = document.getElementById('moodSelect');

    if (!wrapper || !trigger || !dropdown || !hidden) return;

    const iconEl = trigger.querySelector('.custom-select-icon');
    const textEl = trigger.querySelector('.custom-select-text');

    trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        wrapper.classList.toggle('open');
    });

    dropdown.querySelectorAll('.custom-select-option').forEach(function (opt) {
        opt.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdown.querySelectorAll('.custom-select-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');

            const iconTag = this.querySelector('i');
            iconEl.innerHTML = iconTag ? iconTag.outerHTML : '';
            textEl.textContent = this.textContent.trim();
            hidden.value = this.dataset.value;

            wrapper.classList.remove('open');
        });
    });

    document.addEventListener('click', function () {
        wrapper.classList.remove('open');
    });
});
</script>

<script>
(function () {
    const grid       = document.getElementById('destinationsGrid');
    const loading    = document.getElementById('destinationsLoading');
    const empty      = document.getElementById('destinationsEmpty');
    const filterTags = document.querySelectorAll('.filter-tag');

    let allDestinations = [];
    let activeFilter    = 'all';

    async function fetchDestinations() {
        loading.style.display = 'block';
        grid.style.display    = 'none';
        empty.style.display   = 'none';

        try {
            const res = await fetch('/api/discover/destinations?active=1', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!res.ok) {
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }

            const data = await res.json();
            console.log('[Destinations] Raw API response:', data);

            if (Array.isArray(data)) {
                allDestinations = data;
            } else if (data && Array.isArray(data.data)) {
                allDestinations = data.data;
            } else if (data && Array.isArray(data.destinations)) {
                allDestinations = data.destinations;
            } else {
                allDestinations = [];
                console.warn('[Destinations] Unexpected response shape:', data);
            }

            console.log('[Destinations] Loaded:', allDestinations.length, 'destinations');
            window._allDestinations = allDestinations;
            renderGrid(allDestinations.slice(0, 8));

        } catch (err) {
            console.error('[Destinations] Fetch failed:', err);
            loading.style.display = 'block';
            loading.innerHTML = `
                <div style="padding:40px;text-align:center;">
                    <i class="fas fa-exclamation-triangle" style="font-size:36px;color:var(--deep);opacity:0.7;"></i>
                    <p style="margin:12px 0 4px;font-size:15px;color:var(--text);">Could not load destinations</p>
                    <p style="font-size:12px;color:var(--text-muted);margin:0 0 16px;">${err.message}</p>
                    <button class="secondary-button" onclick="initDestinations()" style="font-size:13px;">
                        <i class="fas fa-redo"></i> Retry
                    </button>
                </div>`;
        }
    }

    function applyFilter(filter) {
        activeFilter = filter;
        let results;
        if (filter === 'all') {
            results = allDestinations;
        } else if (filter === 'hidden_gem') {
            results = allDestinations.filter(d => Number(d.is_hidden_gem) === 1);
        } else if (filter === 'romantic') {
            results = allDestinations.filter(d => d.mood === 'romantic' || (d.badge && d.badge.toLowerCase().includes('romantic')));
        } else if (filter === 'adventurous') {
            results = allDestinations.filter(d => d.mood === 'adventurous' || d.category === 'adventurous');
        } else {
            results = allDestinations.filter(d => d.category === filter || d.mood === filter);
        }
        renderGrid(results.slice(0, 8));
    }

    function renderGrid(destinations) {
        loading.style.display = 'none';

        if (!destinations || destinations.length === 0) {
            grid.style.display  = 'none';
            empty.style.display = 'block';
            return;
        }

        empty.style.display = 'none';
        grid.style.display  = 'grid';
        grid.innerHTML      = destinations.map(d => buildCard(d)).join('');
    }

    function buildCard(d) {
        const image       = d.image_url || `https://picsum.photos/seed/${encodeURIComponent(d.name)}/600/400`;
        const price       = d.price_from ? `From $${Number(d.price_from).toLocaleString()}` : '';
        const badge       = d.badge ? `<span class="destination-badge">${d.badge}</span>` : '';
        const hiddenGem   = Number(d.is_hidden_gem) === 1
            ? `<span class="destination-badge" style="background:rgba(138,43,226,0.85);"><i class="fas fa-gem"></i> Hidden Gem</span>` : '';
        const matchScore  = d.match_score
            ? `<div class="match-score"><i class="fas fa-star"></i> ${d.match_score}% match</div>` : '';
        const description = d.description
            ? (d.description.length > 110 ? d.description.substring(0, 110) + '…' : d.description)
            : '';
        const regionLabel = formatRegion(d.region);
        const moodIcon    = moodIconMap(d.mood);

        return `
        <div class="destination-card" data-category="${d.category}" data-mood="${d.mood}" data-hidden="${d.is_hidden_gem}">
            <div class="destination-image" style="background-image:url('${image}');background-size:cover;background-position:center;height:200px;position:relative;border-radius:6px 6px 0 0;overflow:hidden;">
                <div style="position:absolute;inset:0;background:linear-gradient(to bottom,transparent 40%,rgba(0,0,0,0.6));"></div>
                <div style="position:absolute;top:12px;left:12px;display:flex;gap:6px;flex-wrap:wrap;">
                    ${badge}${hiddenGem}
                </div>
                ${matchScore ? `<div style="position:absolute;top:12px;right:12px;">${matchScore}</div>` : ''}
                <div style="position:absolute;bottom:12px;left:14px;right:14px;">
                    <h3 style="color:#fff;margin:0;font-size:17px;font-weight:700;text-shadow:0 1px 4px rgba(0,0,0,0.7);">${d.name}</h3>
                    <p style="color:rgba(255,255,255,0.85);margin:3px 0 0;font-size:13px;">
                        <i class="fas fa-map-marker-alt" style="margin-right:4px;"></i>${d.country} &nbsp;·&nbsp; ${regionLabel}
                    </p>
                </div>
            </div>
            <div class="destination-info">
                <p style="color:var(--text-muted);font-size:13px;margin:0 0 12px;line-height:1.5;">${description}</p>
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span style="font-size:12px;color:var(--text-muted);background:rgba(201,169,110,0.12);border:1px solid var(--border);border-radius:20px;padding:3px 10px;">${moodIcon} ${formatLabel(d.mood)}</span>
                        <span style="font-size:12px;color:var(--text-muted);background:rgba(201,169,110,0.12);border:1px solid var(--border);border-radius:20px;padding:3px 10px;"><i class="fas fa-tag"></i> ${formatLabel(d.category)}</span>
                    </div>
                    <div style="text-align:right;">
                        ${price ? `<div style="font-size:15px;font-weight:700;color:var(--deep);">${price}</div><div style="font-size:11px;color:var(--text-muted);">per person</div>` : ''}
                    </div>
                </div>
                <a href="/destinations/${d.id ?? ''}" class="primary-button" style="text-decoration:none;padding:9px;font-size:13px;">
                    <i class="fas fa-compass"></i> Explore
                </a>
            </div>
        </div>`;
    }

    function formatRegion(region) {
        const map = {
            europe: 'Europe', asia: 'Asia', middle_east: 'Middle East',
            africa: 'Africa', america: 'Americas', oceania: 'Oceania', general: 'World'
        };
        return map[region] || (region ? region.replace(/_/g, ' ') : 'World');
    }

    function formatLabel(str) {
        if (!str) return '';
        return str.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }

    function moodIconMap(mood) {
        const icons = {
            adventurous: '<i class="fas fa-hiking"></i>',
            relaxed:     '<i class="fas fa-umbrella-beach"></i>',
            cultural:    '<i class="fas fa-landmark"></i>',
            romantic:    '<i class="fas fa-heart"></i>',
            foodie:      '<i class="fas fa-utensils"></i>',
            wellness:    '<i class="fas fa-spa"></i>',
            eco:         '<i class="fas fa-leaf"></i>',
            eco_tourism: '<i class="fas fa-leaf"></i>',
            nature:      '<i class="fas fa-tree"></i>',
            general:     '<i class="fas fa-globe"></i>',
            beach:       '<i class="fas fa-umbrella-beach"></i>',
            mountain:    '<i class="fas fa-mountain"></i>',
            nightlife:   '<i class="fas fa-music"></i>',
            spiritual:   '<i class="fas fa-place-of-worship"></i>',
            wellness:    '<i class="fas fa-spa"></i>',
            road_trip:   '<i class="fas fa-car"></i>',
            backpacking: '<i class="fas fa-backpack"></i>',
            city_break:  '<i class="fas fa-city"></i>',
            safari:      '<i class="fas fa-paw"></i>',
            cruise:      '<i class="fas fa-ship"></i>',
            honeymoon:   '<i class="fas fa-ring"></i>',
            photography: '<i class="fas fa-camera"></i>',
        };
        return icons[mood] || '<i class="fas fa-map-marker-alt"></i>';
    }

    filterTags.forEach(tag => {
        tag.addEventListener('click', function () {
            filterTags.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            applyFilter(this.dataset.filter);
        });
    });

    window.initDestinations = fetchDestinations;
    fetchDestinations();
})();
</script>

<script>
function filterByStyle(style, cardEl) {
    const styleMap = {
        adventure: { moods: ['adventurous'], categories: ['adventurous', 'mountain'], label: 'Adventure Travel' },
        beach:     { moods: ['relaxed', 'beach'], categories: ['beach'],              label: 'Beach & Relaxation' },
        cultural:  { moods: ['cultural'],          categories: ['historical', 'food_culture', 'general'], label: 'Cultural Immersion' },
        food:      { moods: ['foodie'],             categories: ['food_culture'],      label: 'Culinary Tours' },
    };

    const styleGrid    = document.getElementById('styleDestinationsGrid');
    const styleEmpty   = document.getElementById('styleEmpty');
    const styleHeader  = document.getElementById('styleResultsHeader');
    const styleTitle   = document.getElementById('styleResultsTitle');
    const styleCount   = document.getElementById('styleResultsCount');
    const styleViewAll = document.getElementById('styleViewAll');

    document.querySelectorAll('.category-card').forEach(c => c.classList.remove('active-style'));
    if (cardEl) cardEl.classList.add('active-style');

    const mapping = styleMap[style];
    if (!mapping || !window._allDestinations || window._allDestinations.length === 0) return;

    const results = window._allDestinations.filter(d =>
        mapping.moods.includes(d.mood) || mapping.categories.includes(d.category)
    ).slice(0, 8);

    styleHeader.style.display = 'block';
    styleTitle.textContent    = mapping.label;
    styleCount.textContent    = results.length + ' destinations';

    styleGrid.style.display   = results.length ? 'grid' : 'none';
    styleEmpty.style.display  = results.length ? 'none' : 'block';
    styleViewAll.style.display = 'block';

    styleGrid.innerHTML = results.map(d => buildStyleCard(d)).join('');

    styleGrid.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function buildStyleCard(d) {
    const image       = d.image_url || 'https://picsum.photos/seed/' + encodeURIComponent(d.name) + '/600/400';
    const price       = d.price_from ? 'From $' + Number(d.price_from).toLocaleString() : '';
    const badge       = d.badge ? '<span class="destination-badge">' + d.badge + '</span>' : '';
    const hiddenGem   = Number(d.is_hidden_gem) === 1
        ? '<span class="destination-badge" style="background:rgba(138,43,226,0.85);"><i class="fas fa-gem"></i> Hidden Gem</span>' : '';
    const description = d.description
        ? (d.description.length > 110 ? d.description.substring(0, 110) + '…' : d.description) : '';

    return '<div class="destination-card">' +
        '<div class="destination-image" style="background-image:url(\'' + image + '\');background-size:cover;background-position:center;height:200px;position:relative;border-radius:6px 6px 0 0;overflow:hidden;">' +
            '<div style="position:absolute;inset:0;background:linear-gradient(to bottom,transparent 40%,rgba(0,0,0,0.6));"></div>' +
            '<div style="position:absolute;top:12px;left:12px;display:flex;gap:6px;flex-wrap:wrap;">' + badge + hiddenGem + '</div>' +
            '<div style="position:absolute;bottom:12px;left:14px;right:14px;">' +
                '<h3 style="color:#fff;margin:0;font-size:17px;font-weight:700;text-shadow:0 1px 4px rgba(0,0,0,0.7);">' + d.name + '</h3>' +
                '<p style="color:rgba(255,255,255,0.85);margin:3px 0 0;font-size:13px;"><i class="fas fa-map-marker-alt" style="margin-right:4px;"></i>' + d.country + '</p>' +
            '</div>' +
        '</div>' +
        '<div class="destination-info">' +
            '<p style="color:var(--text-muted);font-size:13px;margin:0 0 12px;line-height:1.5;">' + description + '</p>' +
            '<div style="display:flex;justify-content:space-between;align-items:center;">' +
                '<span style="font-size:12px;color:var(--text-muted);background:rgba(201,169,110,0.12);border:1px solid var(--border);border-radius:20px;padding:3px 10px;"><i class="fas fa-tag"></i> ' + (d.category || '').replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase()) + '</span>' +
                (price ? '<div style="font-size:15px;font-weight:700;color:var(--deep);">' + price + '</div>' : '') +
            '</div>' +
            '<a href="/destinations/' + (d.id || '') + '" class="primary-button" style="text-decoration:none;padding:9px;font-size:13px;">' +
                '<i class="fas fa-compass"></i> Explore' +
            '</a>' +
        '</div>' +
    '</div>';
}
</script>



</body>
</html>

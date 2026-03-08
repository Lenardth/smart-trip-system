<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/landing.css') }}">
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
        <a href="/discover"><i class="fas fa-compass"></i> Discover</a>
        <a href="/destinations"><i class="fas fa-map-marked-alt"></i> Destinations</a>
        <a href="/community"><i class="fas fa-users"></i> Community</a>
        <a href="/login"><i class="fas fa-sign-in-alt"></i> Login</a>
    </div>
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
                <select id="moodSelect" name="mood">
                    <option value="adventurous">🏔️ Adventurous — Thrills & outdoors</option>
                    <option value="relaxed">🌴 Relaxed — Rest & recharge</option>
                    <option value="cultural">🏛️ Cultural — History & arts</option>
                    <option value="romantic">💖 Romantic — Intimate & scenic</option>
                    <option value="foodie">🍽️ Foodie — Cuisine & markets</option>
                    <option value="wellness">🧘 Wellness — Spa & mindfulness</option>
                    <option value="nightlife">🎉 Nightlife — Music & entertainment</option>
                    <option value="nature">🌿 Nature — Wildlife & eco-tourism</option>
                </select>
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
                <select id="originSelect" name="origin">
                    <option value="johannesburg">Johannesburg (JNB)</option>
                    <option value="cape_town">Cape Town (CPT)</option>
                    <option value="durban">Durban (DUR)</option>
                    <option value="london">London (LHR)</option>
                    <option value="dubai">Dubai (DXB)</option>
                    <option value="new_york">New York (JFK)</option>
                    <option value="amsterdam">Amsterdam (AMS)</option>
                    <option value="frankfurt">Frankfurt (FRA)</option>
                    <option value="singapore">Singapore (SIN)</option>
                    <option value="sydney">Sydney (SYD)</option>
                    <option value="other">Other city</option>
                </select>
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
        <span class="filter-tag active" data-filter="all">All</span>
        <span class="filter-tag" data-filter="tropical">Tropical</span>
        <span class="filter-tag" data-filter="mountain">Mountain</span>
        <span class="filter-tag" data-filter="historical">Historical</span>
        <span class="filter-tag" data-filter="beach">Beach</span>
        <span class="filter-tag" data-filter="food">Food</span>
        <span class="filter-tag" data-filter="culture">Art &amp; Culture</span>
        <span class="filter-tag" data-filter="eco">Eco-Tourism</span>
    </div>

    <div class="destinations-grid" id="destinationsGrid">
        <!-- Destinations will be loaded dynamically via API -->
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
        <div class="category-card" onclick="filterByStyle('adventure')">
            <div class="category-icon"><i class="fas fa-hiking"></i></div>
            <h3>Adventure Travel</h3>
            <p>Hiking, trekking, and extreme sports destinations</p>
        </div>
        <div class="category-card" onclick="filterByStyle('beach')">
            <div class="category-icon"><i class="fas fa-umbrella-beach"></i></div>
            <h3>Beach &amp; Relaxation</h3>
            <p>Perfect spots for sunbathing and unwinding</p>
        </div>
        <div class="category-card" onclick="filterByStyle('cultural')">
            <div class="category-icon"><i class="fas fa-landmark"></i></div>
            <h3>Cultural Immersion</h3>
            <p>Historical sites and cultural experiences</p>
        </div>
        <div class="category-card" onclick="filterByStyle('food')">
            <div class="category-icon"><i class="fas fa-utensils"></i></div>
            <h3>Culinary Tours</h3>
            <p>Foodie paradises and cooking experiences</p>
        </div>
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
            <!-- Testimonials will be loaded dynamically via API -->
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

<script src="{{ asset('js/pages/landing.js') }}"></script>

</body>
</html>

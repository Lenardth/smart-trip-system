@extends('layouts.public')

@section('title', 'Smart booking')

@section('content')
<section class="hero hero-with-image hero-pattern" 
         data-bg="{{ $heroImage ?? 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=1920&q=80' }}">
    <div class="hero-content">
        <h1 class="hero-title">Plan Your Perfect Journey with AI</h1>
        <p class="hero-subtitle">Personalized travel recommendations based on your mood, preferences, and budget. Discover destinations you'll love.</p>
        <div class="hero-buttons">
            <a href="{{ route('plan-trip') }}" class="primary-button">
                <i class="fas fa-magic"></i> Start Planning Now
            </a>
            <button class="hero-btn-secondary" data-action="toggleQuickBuilder">
                <i class="fas fa-bolt"></i> Quick Builder
            </button>
            <a href="{{ route('flights.index') }}" class="hero-btn-flights">
                <i class="fas fa-plane"></i> Book Flights
            </a>
        </div>
    </div>
</section>

<div id="quickBuilderWrapper" class="quick-plan-wrapper"><div class="quick-plan">
    <h2 class="section-title">Quick Trip Builder</h2>
    <p class="section-subtitle">Tell us about your ideal trip and our AI will find the perfect destinations for you</p>

    <div class="qb-steps">
        <div class="qb-step active" data-step="1">
            <span class="qb-step-num">1</span>
            <span class="qb-step-label">Who & Mood</span>
        </div>
        <div class="qb-step-line qb-step-line-hidden"></div>
        <div class="qb-step qb-step-hidden" data-step="2">
            <span class="qb-step-num">2</span>
            <span class="qb-step-label">Trip Details</span>
        </div>
        <div class="qb-step-line qb-step-line-hidden"></div>
        <div class="qb-step qb-step-hidden" data-step="3">
            <span class="qb-step-num">3</span>
            <span class="qb-step-label">Preferences</span>
        </div>
    </div>

    <div class="qb-panel" id="qbPanel1">
        <div class="quick-plan-form">
            <div class="form-group">
                <label><i class="fas fa-users"></i> Travelling With</label>
                <select id="companionSelect" name="companion">
                    <option value="solo">Solo (just me)</option>
                    <option value="couple">Couple (partner and I)</option>
                    <option value="family_young">Family with young children under 12</option>
                    <option value="family_teens">Family with teenagers</option>
                    <option value="friends_small">Friends small group, 2 to 4</option>
                    <option value="friends_large">Friends large group (5+)</option>
                    <option value="business">Business Travel</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-heart"></i> Travel Mood</label>
                <div class="custom-select-wrapper" id="moodSelectWrapper">
                    <div class="custom-select-trigger" id="moodSelectTrigger">
                        <span class="custom-select-icon"><i class="fas fa-hiking"></i></span>
                        <span class="custom-select-text">Adventurous, thrills and outdoors</span>
                        <i class="fas fa-chevron-down custom-select-arrow"></i>
                    </div>
                    <div class="custom-select-dropdown" id="moodDropdown">
                        <div class="custom-select-group">Popular Moods</div>
                        <div class="custom-select-option selected" data-value="adventurous"><i class="fas fa-hiking"></i> Adventurous, thrills and outdoors</div>
                        <div class="custom-select-option" data-value="relaxed"><i class="fas fa-umbrella-beach"></i> Relaxed, rest and recharge</div>
                        <div class="custom-select-option" data-value="romantic"><i class="fas fa-heart"></i> Romantic, intimate and scenic</div>
                        <div class="custom-select-option" data-value="cultural"><i class="fas fa-landmark"></i> Cultural, history and arts</div>
                        <div class="custom-select-option" data-value="foodie"><i class="fas fa-utensils"></i> Foodie, cuisine and markets</div>
                        <div class="custom-select-option" data-value="nature"><i class="fas fa-tree"></i> Nature, wildlife and eco tourism</div>

                        <div class="custom-select-group">Mindset &amp; Wellness</div>
                        <div class="custom-select-option" data-value="wellness"><i class="fas fa-spa"></i> Wellness, spa and mindfulness</div>
                        <div class="custom-select-option" data-value="spiritual"><i class="fas fa-place-of-worship"></i> Spiritual, temples and retreats</div>
                        <div class="custom-select-option" data-value="digital_detox"><i class="fas fa-power-off"></i> Digital Detox, unplug and reconnect</div>
                        <div class="custom-select-option" data-value="slow_travel"><i class="fas fa-feather"></i> Slow Travel, immerse and do not rush</div>
                        <div class="custom-select-option" data-value="healing"><i class="fas fa-hand-holding-heart"></i> Healing, recovery and self care</div>
                        <div class="custom-select-option" data-value="solo_discovery"><i class="fas fa-user"></i> Solo Discovery, find yourself</div>

                        <div class="custom-select-group">Social &amp; Entertainment</div>
                        <div class="custom-select-option" data-value="nightlife"><i class="fas fa-music"></i> Nightlife, music and entertainment</div>
                        <div class="custom-select-option" data-value="festival"><i class="fas fa-star"></i> Festival Chaser, events and carnivals</div>
                        <div class="custom-select-option" data-value="sports"><i class="fas fa-futbol"></i> Sports Fan, matches and stadiums</div>
                        <div class="custom-select-option" data-value="party"><i class="fas fa-glass-cheers"></i> Party Mode, celebrate and socialise</div>
                        <div class="custom-select-option" data-value="lgbtq"><i class="fas fa-rainbow"></i> LGBTQ+ Friendly, inclusive destinations</div>
                        <div class="custom-select-option" data-value="social"><i class="fas fa-users"></i> Social Butterfly, meet new people</div>

                        <div class="custom-select-group">Exploration Styles</div>
                        <div class="custom-select-option" data-value="road_trip"><i class="fas fa-car"></i> Road Trip, open roads and freedom</div>
                        <div class="custom-select-option" data-value="backpacking"><i class="fas fa-suitcase"></i> Backpacking, rough it and explore</div>
                        <div class="custom-select-option" data-value="island_hopping"><i class="fas fa-water"></i> Island Hopping, sea and archipelagos</div>
                        <div class="custom-select-option" data-value="city_break"><i class="fas fa-city"></i> City Break, urban buzz and skylines</div>
                        <div class="custom-select-option" data-value="off_beaten"><i class="fas fa-map"></i> Off the Beaten Path, hidden gems</div>
                        <div class="custom-select-option" data-value="luxury_escape"><i class="fas fa-gem"></i> Luxury Escape, five star everything</div>
                        <div class="custom-select-option" data-value="safari"><i class="fas fa-paw"></i> Safari and Wildlife, raw nature up close</div>
                        <div class="custom-select-option" data-value="cruise"><i class="fas fa-ship"></i> Cruise, sea views and port hopping</div>

                        <div class="custom-select-group">Seasonal &amp; Weather</div>
                        <div class="custom-select-option" data-value="sun_seeker"><i class="fas fa-sun"></i> Sun Seeker, heat and beaches</div>
                        <div class="custom-select-option" data-value="snow_lover"><i class="fas fa-snowflake"></i> Snow Lover, skiing and winter wonderlands</div>
                        <div class="custom-select-option" data-value="autumn_vibes"><i class="fas fa-leaf"></i> Autumn Vibes, foliage and cosy escapes</div>
                        <div class="custom-select-option" data-value="monsoon_magic"><i class="fas fa-cloud-rain"></i> Monsoon Magic, lush greens and rain</div>
                        <div class="custom-select-option" data-value="northern_lights"><i class="fas fa-moon"></i> Aurora Chaser, northern lights</div>

                        <div class="custom-select-group">Learning &amp; Growth</div>
                        <div class="custom-select-option" data-value="language"><i class="fas fa-language"></i> Language Immersion, learn while travelling</div>
                        <div class="custom-select-option" data-value="photography"><i class="fas fa-camera"></i> Photography Trip, capture stunning moments</div>
                        <div class="custom-select-option" data-value="volunteer"><i class="fas fa-hands-helping"></i> Volunteer Travel, give back while exploring</div>
                        <div class="custom-select-option" data-value="study_abroad"><i class="fas fa-graduation-cap"></i> Study Abroad, education and experience</div>
                        <div class="custom-select-option" data-value="cooking_class"><i class="fas fa-utensils"></i> Culinary School, cook like a local</div>
                        <div class="custom-select-option" data-value="art_retreat"><i class="fas fa-paint-brush"></i> Art Retreat, paint, sculpt and create</div>

                        <div class="custom-select-group">Family &amp; Groups</div>
                        <div class="custom-select-option" data-value="family_fun"><i class="fas fa-child"></i> Family Fun, kids activities and theme parks</div>
                        <div class="custom-select-option" data-value="multigenerational"><i class="fas fa-users"></i> Multi Generational, everyone included</div>
                        <div class="custom-select-option" data-value="honeymoon"><i class="fas fa-ring"></i> Honeymoon, perfect romantic start</div>
                        <div class="custom-select-option" data-value="girls_trip"><i class="fas fa-female"></i> Girls Trip, friendship and fun</div>
                        <div class="custom-select-option" data-value="boys_trip"><i class="fas fa-male"></i> Boys Trip, adventure and bonding</div>
                        <div class="custom-select-option" data-value="group_adventure"><i class="fas fa-mountain"></i> Group Adventure, shared thrills</div>
                    </div>
                    <input type="hidden" id="moodSelect" name="mood" value="adventurous">
                </div>
            </div>
            <div class="form-group">
                <label><i class="fas fa-star"></i> Experience Level</label>
                <select id="experienceSelect" name="experience">
                    <option value="first_time">First time traveller</option>
                    <option value="occasional">Occasional traveller (1 to 2 trips/year)</option>
                    <option value="regular">Regular traveller (3 to 5 trips/year)</option>
                    <option value="frequent">Frequent traveller (6+ trips/year)</option>
                </select>
            </div>
        </div>
        <div class="qb-nav">
            <button class="primary-button qb-next-btn" data-next="2" data-action="showStep" data-params='{"args":[2]}'>
                Next <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <div class="qb-panel" id="qbPanel2">
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
                <div class="flex-gap-8-center">
                    <input type="number" id="durationSelect" name="duration" min="1" max="365" value="7" class="form-input-number-sm">
                    <span class="text-muted fs-13">days</span>
                </div>
                <small class="text-muted fs-11 mt-4 d-block">Enter any number of days</small>
            </div>
            <div class="form-group">
                <label><i class="fas fa-wallet"></i> Budget Per Person</label>
                <select id="budgetSelect" name="budget">
                    <option value="backpacker" data-usd-max="500">Backpacker</option>
                    <option value="budget" data-usd-min="500" data-usd-max="1500">Budget</option>
                    <option value="mid" data-usd-min="1500" data-usd-max="4000">Mid range</option>
                    <option value="premium" data-usd-min="4000" data-usd-max="8000">Premium</option>
                    <option value="luxury" data-usd-min="8000">Luxury</option>
                </select>
            </div>
        </div>
        <div class="qb-nav">
            <button class="secondary-button qb-back-btn" data-back="1" data-action="showStep" data-params='{"args":[1]}'>
                <i class="fas fa-arrow-left"></i> Back
            </button>
            <button class="primary-button qb-next-btn" data-next="3" data-action="showStep" data-params='{"args":[3]}'>
                Next <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <div class="qb-panel" id="qbPanel3">
        <div class="quick-plan-form">
            <div class="form-group">
                <label><i class="fas fa-globe"></i> Preferred Region</label>
                <select id="regionSelect" name="region">
                    <option value="any">Anywhere (surprise me!)</option>
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
                    <option value="hotel">3 to 4 star hotel</option>
                    <option value="resort">5 star resort</option>
                    <option value="villa">Private villa / Airbnb</option>
                    <option value="glamping">Glamping / Eco lodge</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-plane-departure"></i> Flying From</label>
                <input type="text" id="originInput" name="origin" placeholder="e.g. Johannesburg, London, New York, Dubai..." autocomplete="off">
                <small class="input-hint"><i class="fas fa-info-circle"></i> Enter any city or airport name</small>
            </div>
        </div>
        <div class="qb-nav">
            <button class="secondary-button qb-back-btn" data-back="2" data-action="showStep" data-params='{"args":[2]}'>
                <i class="fas fa-arrow-left"></i> Back
            </button>
            <button class="primary-button" id="generatePlanBtn" data-action="generateQuickPlan">
                <i class="fas fa-robot"></i> Generate AI Suggestions
            </button>
        </div>
    </div>
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
    <p class="section-subtitle">Discover destinations loaded from live travel data</p>
    <div class="slideshow-container">
        <div class="slides" id="featuredSlides"></div>
        <div class="slide-number">0 / 0</div>
        <div class="slide-controls">
            <button class="slide-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
            <button class="slide-btn next-btn"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="slide-indicators"></div>
    </div>
</section>

<section class="discover-section">
    <div class="discover-header">
        <h2 class="section-title">Discover Trending Destinations</h2>
        <a href="{{ route('plan-trip') }}" class="secondary-button">Plan with AI <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="filter-tags">
        <span class="filter-tag active" data-filter="all"       data-action="applyDestinationFilter"><i class="fas fa-globe"></i> All</span>
        <span class="filter-tag" data-filter="trending"         data-action="applyDestinationFilter"><i class="fas fa-fire"></i> Trending</span>
        <span class="filter-tag" data-filter="beach"            data-action="applyDestinationFilter"><i class="fas fa-umbrella-beach"></i> Beach</span>
        <span class="filter-tag" data-filter="mountain"         data-action="applyDestinationFilter"><i class="fas fa-mountain"></i> Mountain</span>
        <span class="filter-tag" data-filter="historical"       data-action="applyDestinationFilter"><i class="fas fa-landmark"></i> Historical</span>
        <span class="filter-tag" data-filter="food_culture"     data-action="applyDestinationFilter"><i class="fas fa-utensils"></i> Food &amp; Culture</span>
        <span class="filter-tag" data-filter="eco_tourism"      data-action="applyDestinationFilter"><i class="fas fa-leaf"></i> Eco-Tourism</span>
        <span class="filter-tag" data-filter="romantic"         data-action="applyDestinationFilter"><i class="fas fa-heart"></i> Romantic</span>
        <span class="filter-tag" data-filter="adventurous"      data-action="applyDestinationFilter"><i class="fas fa-hiking"></i> Adventure</span>
        <span class="filter-tag" data-filter="hidden_gem"       data-action="applyDestinationFilter"><i class="fas fa-gem"></i> Hidden Gems</span>
    </div>
    <div id="destinationsLoading" class="loading-placeholder">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Loading destinations...</p>
    </div>
    <div class="destinations-grid" id="destinationsGrid"></div>
    <div id="destinationsEmpty" class="empty-placeholder hidden">
        <i class="fas fa-map-marked-alt"></i>
        <p>No destinations found for this filter.</p>
    </div>
</section>

<div class="ready-to-fly-section">
    <h2 class="section-title">Ready to Fly?</h2>
    <p class="section-subtitle">Find and book flights to your dream destinations</p>
    <div class="ready-to-fly-grid">
        <a href="{{ route('flights.index') }}" class="ready-to-fly-card">
            <div class="ready-to-fly-icon"><i class="fas fa-search"></i></div>
            <h3>Search Flights</h3>
            <p>Find flights worldwide with flexible dates</p>
        </a>
        <a href="{{ route('discover') }}" class="ready-to-fly-card">
            <div class="ready-to-fly-icon"><i class="fas fa-compass"></i></div>
            <h3>Discover Places</h3>
            <p>Explore destinations from around the world</p>
        </a>
        <a href="{{ route('bookings.index') }}" class="ready-to-fly-card">
            <div class="ready-to-fly-icon"><i class="fas fa-ticket-alt"></i></div>
            <h3>My Bookings</h3>
            <p>View and manage your bookings</p>
        </a>
    </div>
    <div class="ready-to-fly-cta">
        <a href="{{ route('flights.index') }}" class="primary-button">
            <i class="fas fa-plane"></i> Start Booking Flights Now
        </a>
    </div>
</div>

<div class="explore-style-section">
    <h2 class="section-title">Explore By Travel Style</h2>
    <p class="section-subtitle">Find destinations that match your preferred travel experience</p>
    <div class="explore-categories">
        <div class="category-card is-active-style" data-trip-kind="adventure">
            <div class="category-icon"><i class="fas fa-hiking"></i></div>
            <h3>Adventure Travel</h3>
            <p>Hiking, trekking, and extreme sports destinations</p>
        </div>
        <div class="category-card" data-trip-kind="beach">
            <div class="category-icon"><i class="fas fa-umbrella-beach"></i></div>
            <h3>Beach &amp; Relaxation</h3>
            <p>Perfect spots for sunbathing and unwinding</p>
        </div>
        <div class="category-card" data-trip-kind="cultural">
            <div class="category-icon"><i class="fas fa-landmark"></i></div>
            <h3>Cultural Immersion</h3>
            <p>Historical sites and cultural experiences</p>
        </div>
        <div class="category-card" data-trip-kind="food">
            <div class="category-icon"><i class="fas fa-utensils"></i></div>
            <h3>Culinary Tours</h3>
            <p>Foodie paradises and cooking experiences</p>
        </div>
    </div>
    <div id="styleResultsHeader" class="style-results-header">
        <div class="style-results-header-inner">
            <h3 id="styleResultsTitle"></h3>
            <span id="styleResultsCount"></span>
        </div>
    </div>
    <div class="destinations-grid" id="styleDestinationsGrid"></div>
    <div id="styleEmpty" class="style-empty">
        <i class="fas fa-map-marked-alt"></i>
        <p>No destinations found for this style.</p>
    </div>
    <div id="styleViewAll" class="style-view-all">
        <a href="/plan-trip" class="secondary-button">
            <i class="fas fa-compass"></i> View All Destinations
        </a>
    </div>
</div>

<div class="how-it-works-section">
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
    <div class="ai-banner-header">
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

<div class="smart-features-section">
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
            <p>AI predicts optimal travel dates based on historical weather patterns and seasonal trends.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-chart-pie feature-icon"></i>
            <h3>Real-Time Cost Analysis</h3>
            <p>Live price tracking for flights, hotels, and activities with alerts for price drops.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-heartbeat feature-icon"></i>
            <h3>Mood &amp; Interest Matching</h3>
            <p>Advanced personality profiling to match destinations with your emotional state.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-sync-alt feature-icon"></i>
            <h3>Dynamic Itinerary Adjuster</h3>
            <p>Automatically suggests itinerary changes based on real-time factors.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-leaf feature-icon"></i>
            <h3>Sustainable Travel Options</h3>
            <p>Highlights eco-friendly accommodations and sustainable tourism activities.</p>
        </div>
    </div>
</div>

<div class="newsletter">
    <h2 class="section-title">Get Travel Inspiration</h2>
    <p class="section-subtitle">Subscribe to receive weekly destination ideas, travel tips, and exclusive deals</p>
    <div class="newsletter-input">
        <input type="email" id="newsletterEmail" placeholder="Enter your email address">
        <button data-action="subscribeNewsletter">
            <i class="fas fa-paper-plane"></i> Subscribe
        </button>
    </div>
    <p class="privacy">We respect your privacy. Unsubscribe at any time.</p>
</div>
@endsection

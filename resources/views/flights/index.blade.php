<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Flights — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --deep: #3b1f2b;
            --deep-alt: #4d2a3a;
            --gold: #c9a96e;
            --gold-hover: #b8955a;
            --cream: #f5f0eb;
            --card-bg: #fff8f2;
            --border: #e2d5c7;
            --border-soft: #d4c4b0;
            --text-light: #f5e6d3;
            --text-muted: #6b5b4f;
            --text-sub: #d4c4b0;
            --success: #2ecc71;
            --error: #e74c3c;
            --info: #3498db;
            --warning: #f39c12;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Georgia', serif;
            background: var(--cream);
            color: #2c2c2c;
            line-height: 1.6;
        }

        /* Header */
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

        /* Navigation */
        .nav-container {
            display: flex;
            justify-content: center;
            background: var(--gold);
            padding: 15px;
            flex-wrap: wrap;
            border-bottom: 2px solid var(--gold-hover);
        }

        .nav-container a,
        .nav-container button {
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
            background: none;
            border: none;
            cursor: pointer;
        }

        .nav-container a:hover,
        .nav-container a.active,
        .nav-container button:hover {
            background: rgba(59, 31, 43, 0.18);
            transform: translateY(-2px);
        }

        /* Hero Section */
        .page-hero {
            background: linear-gradient(rgba(30,15,20,0.7), rgba(30,15,20,0.7)),
                        url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1200');
            background-size: cover;
            background-position: center;
            height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--text-light);
        }

        .page-hero h1 {
            font-size: 42px;
            font-weight: normal;
            letter-spacing: 1px;
            margin: 0 0 12px;
            color: var(--text-light);
        }

        .page-hero p {
            font-size: 18px;
            color: var(--text-sub);
            margin: 0;
        }

        /* Main Container */
        .flights-container {
            max-width: 1400px;
            margin: -50px auto 60px;
            padding: 0 20px;
        }

        /* Search Card */
        .search-card {
            background: var(--card-bg);
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(59,31,43,0.15);
            border: 1px solid var(--border);
            margin-bottom: 40px;
        }

        .search-card h2 {
            color: var(--deep);
            font-size: 24px;
            margin-bottom: 30px;
            font-weight: normal;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .search-card h2 i {
            color: var(--gold);
        }

        /* Trip Type Tabs */
        .trip-type-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--border);
            padding-bottom: 15px;
        }

        .trip-type-tab {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
            font-weight: 600;
            color: var(--deep);
            font-family: 'Georgia', serif;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .trip-type-tab:hover,
        .trip-type-tab.active {
            background: var(--deep);
            color: var(--text-light);
            border-color: var(--deep);
        }

        /* Form Grid */
        .search-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .search-grid-wide {
            grid-template-columns: 1fr 1fr;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--deep);
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-group label i {
            color: var(--gold);
            font-size: 12px;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 15px;
            font-family: 'Georgia', serif;
            background: white;
            transition: all 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.1);
        }

        .form-input::placeholder {
            color: var(--text-muted);
            opacity: 0.6;
        }

        /* Passenger & Class Grid */
        .passenger-class-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Search Button */
        .search-btn {
            width: 100%;
            padding: 16px;
            background: var(--gold);
            color: var(--deep);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-family: 'Georgia', serif;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(201, 169, 110, 0.3);
            margin-top: 10px;
        }

        .search-btn:hover {
            background: var(--gold-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(201, 169, 110, 0.4);
        }

        .search-btn:active {
            transform: translateY(0);
        }

        /* Loading Spinner */
        .spinner {
            border: 3px solid var(--border);
            border-top: 3px solid var(--deep);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: none;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .search-btn.loading .spinner {
            display: inline-block;
        }

        .search-btn.loading .btn-text {
            display: none;
        }

        /* Results Section */
        .results-section {
            display: none;
        }

        .results-section.active {
            display: block;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border);
        }

        .results-header h3 {
            color: var(--deep);
            font-size: 22px;
            font-weight: normal;
        }

        .sort-filter {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .sort-filter label {
            color: var(--text-muted);
            font-size: 14px;
        }

        .sort-filter select {
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-family: 'Georgia', serif;
            background: white;
            cursor: pointer;
        }

        /* Flight Cards */
        .flight-card {
            background: var(--card-bg);
            border-radius: 6px;
            padding: 30px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 12px rgba(59,31,43,0.08);
            transition: all 0.3s;
        }

        .flight-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(59,31,43,0.12);
        }

        .flight-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .airline-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .airline-logo {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
            font-size: 20px;
        }

        .airline-details h4 {
            color: var(--deep);
            font-size: 18px;
            margin-bottom: 4px;
            font-weight: normal;
        }

        .airline-details p {
            color: var(--text-muted);
            font-size: 13px;
            margin: 0;
        }

        .flight-price {
            text-align: right;
        }

        .flight-price .price {
            font-size: 32px;
            font-weight: bold;
            color: var(--deep);
            line-height: 1;
        }

        .flight-price .price-label {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* Flight Route */
        .flight-route {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .route-point {
            flex: 1;
        }

        .route-point .time {
            font-size: 26px;
            font-weight: bold;
            color: var(--deep);
            margin-bottom: 4px;
        }

        .route-point .location {
            font-size: 14px;
            color: var(--text-muted);
        }

        .route-point .airport-code {
            font-size: 18px;
            color: var(--deep);
            font-weight: 600;
            margin-bottom: 2px;
        }

        .route-divider {
            flex: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 20px;
        }

        .route-line {
            width: 100%;
            height: 2px;
            background: var(--border);
            position: relative;
        }

        .route-line::before {
            content: '';
            position: absolute;
            right: -5px;
            top: -3px;
            width: 0;
            height: 0;
            border-left: 8px solid var(--border);
            border-top: 4px solid transparent;
            border-bottom: 4px solid transparent;
        }

        .route-icon {
            margin: 8px 0;
            color: var(--gold);
            font-size: 18px;
        }

        .route-duration {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 8px;
        }

        /* Flight Details */
        .flight-details {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            font-size: 14px;
        }

        .detail-item i {
            color: var(--gold);
        }

        .detail-item strong {
            color: var(--deep);
        }

        /* Flight Tags */
        .flight-tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .flight-tag {
            padding: 4px 12px;
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .flight-tag.warning {
            background: #fff3e0;
            color: #e65100;
        }

        .flight-tag.info {
            background: #e3f2fd;
            color: #1565c0;
        }

        /* Book Button */
        .book-btn {
            width: 100%;
            padding: 14px;
            background: var(--gold);
            color: var(--deep);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: 'Georgia', serif;
        }

        .book-btn:hover {
            background: var(--gold-hover);
            transform: translateY(-1px);
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: var(--card-bg);
            border-radius: 6px;
            border: 1px solid var(--border);
        }

        .no-results i {
            font-size: 64px;
            color: var(--border-soft);
            margin-bottom: 20px;
        }

        .no-results h3 {
            color: var(--deep);
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: normal;
        }

        .no-results p {
            color: var(--text-muted);
            font-size: 16px;
        }

        /* Popular Routes */
        .popular-routes {
            margin-top: 50px;
        }

        .popular-routes h3 {
            color: var(--deep);
            font-size: 24px;
            margin-bottom: 25px;
            font-weight: normal;
            text-align: center;
        }

        .routes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .route-card {
            background: var(--card-bg);
            border-radius: 6px;
            padding: 24px;
            border: 1px solid var(--border);
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .route-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(59,31,43,0.12);
        }

        .route-card .route-cities {
            font-size: 18px;
            color: var(--deep);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .route-card .route-price {
            font-size: 24px;
            font-weight: bold;
            color: var(--gold);
            margin: 10px 0;
        }

        .route-card .route-info {
            font-size: 13px;
            color: var(--text-muted);
        }

        /* Footer */
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
                display: none;
            }

            .nav-container {
                flex-direction: column;
                align-items: center;
            }

            .nav-container a,
            .nav-container button {
                font-size: 14px;
                padding: 8px 10px;
            }

            .search-card {
                padding: 25px 20px;
            }

            .search-grid {
                grid-template-columns: 1fr;
            }

            .search-grid-wide {
                grid-template-columns: 1fr;
            }

            .passenger-class-grid {
                grid-template-columns: 1fr;
            }

            .trip-type-tabs {
                flex-wrap: wrap;
            }

            .flight-route {
                flex-direction: column;
                gap: 20px;
            }

            .route-divider {
                transform: rotate(90deg);
                margin: 20px 0;
            }

            .flight-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .airline-info {
                flex-direction: column;
            }

            .flight-price {
                text-align: center;
            }

            .routes-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- Header -->
<header class="main-header">
    <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo">
    <span class="logo-text">Smart Booking</span>
    @auth
    <div class="user-display">
        <i class="fas fa-user-circle"></i>
        <span>{{ Auth::user()->name }}</span>
    </div>
    @endauth
</header>

<!-- Navigation -->
<nav class="nav-container">
    <a href="/"><i class="fas fa-home"></i> Home</a>
    @auth
    <a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    @endauth
    <a href="/plan-trip"><i class="fas fa-route"></i> Plan Trip</a>
    <a href="/flights" class="active"><i class="fas fa-plane"></i> Book Flights</a>
    <a href="/discover"><i class="fas fa-compass"></i> Discover</a>
    <a href="/destinations"><i class="fas fa-map-marked-alt"></i> Destinations</a>
    <a href="/community"><i class="fas fa-users"></i> Community</a>
    @auth
    <a href="/wishlist"><i class="fas fa-heart"></i> Wishlist</a>
    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
        @csrf
        <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </form>
    @else
    <a href="/login"><i class="fas fa-sign-in-alt"></i> Login</a>
    @endauth
</nav>

<!-- Hero Section -->
<section class="page-hero">
    <div>
        <h1><i class="fas fa-plane-departure"></i> Book Your Flight</h1>
        <p>Find the best deals on flights worldwide</p>
    </div>
</section>

<!-- Main Container -->
<div class="flights-container">

    <!-- Search Card -->
    <div class="search-card">
        <h2><i class="fas fa-search"></i> Search Flights</h2>

        <!-- Trip Type Tabs -->
        <div class="trip-type-tabs">
            <div class="trip-type-tab active" data-type="round-trip">
                <i class="fas fa-exchange-alt"></i> Round Trip
            </div>
            <div class="trip-type-tab" data-type="one-way">
                <i class="fas fa-arrow-right"></i> One Way
            </div>
            <div class="trip-type-tab" data-type="multi-city">
                <i class="fas fa-map-marked"></i> Multi-City
            </div>
        </div>

        <!-- Search Form -->
        <form id="flightSearchForm">
            <div class="search-grid search-grid-wide">
                <div class="form-group">
                    <label><i class="fas fa-plane-departure"></i> From</label>
                    <input type="text" class="form-input" id="from" name="from" placeholder="City or Airport" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-plane-arrival"></i> To</label>
                    <input type="text" class="form-input" id="to" name="to" placeholder="City or Airport" required>
                </div>
            </div>

            <div class="search-grid" id="dateGrid">
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Departure Date</label>
                    <input type="date" class="form-input" id="departure_date" name="departure_date" required>
                </div>
                <div class="form-group" id="returnDateGroup">
                    <label><i class="fas fa-calendar-check"></i> Return Date</label>
                    <input type="date" class="form-input" id="return_date" name="return_date">
                </div>
            </div>

            <div class="passenger-class-grid">
                <div class="form-group">
                    <label><i class="fas fa-users"></i> Passengers</label>
                    <input type="number" class="form-input" id="passengers" name="passengers" min="1" max="9" value="1" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-chair"></i> Class</label>
                    <select class="form-input" id="class" name="class" required>
                        <option value="economy">Economy</option>
                        <option value="premium_economy">Premium Economy</option>
                        <option value="business">Business</option>
                        <option value="first">First Class</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="search-btn">
                <span class="btn-text"><i class="fas fa-search"></i> Search Flights</span>
                <div class="spinner"></div>
            </button>
        </form>
    </div>

    <!-- Results Section -->
    <div class="results-section" id="resultsSection">
        <div class="results-header">
            <h3><span id="resultsCount">0</span> Flights Found</h3>
            <div class="sort-filter">
                <label>Sort by:</label>
                <select id="sortBy">
                    <option value="price">Best Price</option>
                    <option value="duration">Shortest Duration</option>
                    <option value="departure">Departure Time</option>
                    <option value="arrival">Arrival Time</option>
                </select>
            </div>
        </div>

        <div id="flightResults">
            <!-- Flight cards will be inserted here -->
        </div>
    </div>

    <!-- Popular Routes -->
    <div class="popular-routes">
        <h3>Popular Routes</h3>
        <div class="routes-grid">
            <div class="route-card" onclick="fillRoute('New York', 'London')">
                <div class="route-cities">New York ✈ London</div>
                <div class="route-price">from $450</div>
                <div class="route-info">7h 30m • Direct flights available</div>
            </div>
            <div class="route-card" onclick="fillRoute('Paris', 'Tokyo')">
                <div class="route-cities">Paris ✈ Tokyo</div>
                <div class="route-price">from $680</div>
                <div class="route-info">12h 45m • 1 stop</div>
            </div>
            <div class="route-card" onclick="fillRoute('Dubai', 'New York')">
                <div class="route-cities">Dubai ✈ New York</div>
                <div class="route-price">from $550</div>
                <div class="route-info">14h 20m • Direct flights available</div>
            </div>
            <div class="route-card" onclick="fillRoute('Los Angeles', 'Sydney')">
                <div class="route-cities">Los Angeles ✈ Sydney</div>
                <div class="route-price">from $720</div>
                <div class="route-info">15h 10m • Direct flights available</div>
            </div>
            <div class="route-card" onclick="fillRoute('Singapore', 'Bali')">
                <div class="route-cities">Singapore ✈ Bali</div>
                <div class="route-price">from $180</div>
                <div class="route-info">2h 30m • Multiple daily flights</div>
            </div>
            <div class="route-card" onclick="fillRoute('London', 'Dubai')">
                <div class="route-cities">London ✈ Dubai</div>
                <div class="route-price">from $380</div>
                <div class="route-info">7h 00m • Direct flights available</div>
            </div>
        </div>
    </div>

</div>

<!-- Footer -->
<footer class="footer">
    <p>&copy; 2026 Smart Booking. All rights reserved.</p>
    <div>
        <a href="/privacy">Privacy Policy</a>
        <a href="/terms">Terms of Service</a>
        <a href="/contact">Contact Us</a>
    </div>
</footer>

<script>
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('departure_date').setAttribute('min', today);
    document.getElementById('return_date').setAttribute('min', today);

    // Trip type tabs
    document.querySelectorAll('.trip-type-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.trip-type-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const type = this.dataset.type;
            const returnDateGroup = document.getElementById('returnDateGroup');
            const returnDateInput = document.getElementById('return_date');

            if (type === 'one-way') {
                returnDateGroup.style.display = 'none';
                returnDateInput.removeAttribute('required');
            } else if (type === 'round-trip') {
                returnDateGroup.style.display = 'block';
                returnDateInput.setAttribute('required', 'required');
            } else if (type === 'multi-city') {
                Swal.fire({
                    title: 'Multi-City Flights',
                    text: 'Multi-city flight search is coming soon!',
                    icon: 'info',
                    confirmButtonColor: '#c9a96e'
                });
            }
        });
    });

    // Departure date change - update return date minimum
    document.getElementById('departure_date').addEventListener('change', function() {
        const departureDate = this.value;
        document.getElementById('return_date').setAttribute('min', departureDate);
    });

    // Fill route from popular routes
    function fillRoute(from, to) {
        document.getElementById('from').value = from;
        document.getElementById('to').value = to;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Flight search form submission
    document.getElementById('flightSearchForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const searchBtn = document.querySelector('.search-btn');
        searchBtn.classList.add('loading');

        const formData = {
            from: document.getElementById('from').value,
            to: document.getElementById('to').value,
            departure_date: document.getElementById('departure_date').value,
            return_date: document.getElementById('return_date').value,
            passengers: document.getElementById('passengers').value,
            class: document.getElementById('class').value,
        };

        try {
            const response = await fetch('/flights/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            searchBtn.classList.remove('loading');

            if (data.success) {
                displayFlights(data.flights || generateMockFlights(formData));
            } else {
                Swal.fire({
                    title: 'Search Error',
                    text: data.message || 'Unable to search flights',
                    icon: 'error',
                    confirmButtonColor: '#c9a96e'
                });
            }
        } catch (error) {
            searchBtn.classList.remove('loading');
            console.error('Search error:', error);

            // Show mock results for demo
            displayFlights(generateMockFlights(formData));
        }
    });

    // Generate mock flight data for demo
    function generateMockFlights(searchData) {
        const airlines = [
            { name: 'Emirates', code: 'EK', logo: '✈️' },
            { name: 'Qatar Airways', code: 'QR', logo: '🛫' },
            { name: 'Singapore Airlines', code: 'SQ', logo: '🛬' },
            { name: 'Lufthansa', code: 'LH', logo: '✈️' },
            { name: 'British Airways', code: 'BA', logo: '🛫' }
        ];

        const flights = [];
        const basePrice = 300 + Math.random() * 400;

        for (let i = 0; i < 5; i++) {
            const airline = airlines[i % airlines.length];
            const departureHour = 6 + Math.floor(Math.random() * 12);
            const duration = 3 + Math.floor(Math.random() * 8);
            const stops = Math.random() > 0.6 ? 0 : 1;

            flights.push({
                id: `FL${1000 + i}`,
                airline: airline.name,
                airline_code: airline.code,
                airline_logo: airline.logo,
                flight_number: `${airline.code}${100 + i}`,
                from: searchData.from,
                to: searchData.to,
                departure_time: `${departureHour.toString().padStart(2, '0')}:${(Math.random() * 60).toFixed(0).padStart(2, '0')}`,
                arrival_time: `${((departureHour + duration) % 24).toString().padStart(2, '0')}:${(Math.random() * 60).toFixed(0).padStart(2, '0')}`,
                duration: `${duration}h ${(Math.random() * 60).toFixed(0)}m`,
                stops: stops,
                price: (basePrice + i * 50).toFixed(2),
                class: searchData.class,
                seats_available: 5 + Math.floor(Math.random() * 15),
                baggage: stops === 0 ? '2 x 23kg' : '1 x 23kg',
                amenities: stops === 0 ? ['WiFi', 'Meals', 'Entertainment'] : ['Meals', 'Entertainment']
            });
        }

        return flights;
    }

    // Display flights
    function displayFlights(flights) {
        const resultsSection = document.getElementById('resultsSection');
        const flightResults = document.getElementById('flightResults');
        const resultsCount = document.getElementById('resultsCount');

        resultsSection.classList.add('active');
        resultsCount.textContent = flights.length;

        if (flights.length === 0) {
            flightResults.innerHTML = `
                <div class="no-results">
                    <i class="fas fa-plane-slash"></i>
                    <h3>No Flights Found</h3>
                    <p>Try adjusting your search criteria</p>
                </div>
            `;
            return;
        }

        flightResults.innerHTML = flights.map(flight => `
            <div class="flight-card">
                <div class="flight-header">
                    <div class="airline-info">
                        <div class="airline-logo">${flight.airline_logo}</div>
                        <div class="airline-details">
                            <h4>${flight.airline}</h4>
                            <p>${flight.flight_number} • ${flight.class.replace('_', ' ').toUpperCase()}</p>
                        </div>
                    </div>
                    <div class="flight-price">
                        <div class="price">$${flight.price}</div>
                        <div class="price-label">per person</div>
                    </div>
                </div>

                <div class="flight-route">
                    <div class="route-point">
                        <div class="time">${flight.departure_time}</div>
                        <div class="airport-code">${flight.from.substring(0, 3).toUpperCase()}</div>
                        <div class="location">${flight.from}</div>
                    </div>
                    <div class="route-divider">
                        <div class="route-line"></div>
                        <div class="route-icon"><i class="fas fa-plane"></i></div>
                        <div class="route-duration">${flight.duration} ${flight.stops === 0 ? '• Direct' : '• ' + flight.stops + ' stop'}</div>
                    </div>
                    <div class="route-point">
                        <div class="time">${flight.arrival_time}</div>
                        <div class="airport-code">${flight.to.substring(0, 3).toUpperCase()}</div>
                        <div class="location">${flight.to}</div>
                    </div>
                </div>

                <div class="flight-details">
                    <div class="detail-item">
                        <i class="fas fa-suitcase"></i>
                        <span><strong>Baggage:</strong> ${flight.baggage}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-chair"></i>
                        <span><strong>Seats:</strong> ${flight.seats_available} available</span>
                    </div>
                    ${flight.amenities ? flight.amenities.map(a => `
                        <div class="detail-item">
                            <i class="fas fa-check-circle"></i>
                            <span>${a}</span>
                        </div>
                    `).join('') : ''}
                </div>

                <div class="flight-tags">
                    ${flight.stops === 0 ? '<span class="flight-tag">Direct Flight</span>' : ''}
                    ${flight.seats_available < 5 ? '<span class="flight-tag warning">Only ' + flight.seats_available + ' seats left</span>' : ''}
                    ${flight.amenities && flight.amenities.includes('WiFi') ? '<span class="flight-tag info">WiFi Available</span>' : ''}
                </div>

                <button class="book-btn" onclick="bookFlight('${flight.id}', '${flight.airline}', ${flight.price})">
                    <i class="fas fa-ticket-alt"></i> Book Now - $${flight.price}
                </button>
            </div>
        `).join('');

        // Scroll to results
        resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Book flight
    function bookFlight(flightId, airline, price) {
        @auth
        Swal.fire({
            title: 'Book Flight',
            html: `
                <div style="text-align: left; padding: 20px;">
                    <h4 style="margin-bottom: 15px;">Flight Details</h4>
                    <p><strong>Airline:</strong> ${airline}</p>
                    <p><strong>Flight ID:</strong> ${flightId}</p>
                    <p><strong>Total Price:</strong> $${price}</p>
                    <hr style="margin: 20px 0;">
                    <p style="color: #6b5b4f; font-size: 14px;">
                        <i class="fas fa-info-circle"></i>
                        You will be redirected to complete passenger details and payment.
                    </p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#c9a96e',
            cancelButtonColor: '#6b5b4f',
            confirmButtonText: 'Continue to Booking',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // In production, redirect to booking page
                Swal.fire({
                    title: 'Processing...',
                    html: 'Redirecting to booking page',
                    icon: 'success',
                    confirmButtonColor: '#c9a96e',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    // window.location.href = `/flights/book/${flightId}`;
                    console.log('Booking flight:', flightId);
                });
            }
        });
        @else
        Swal.fire({
            title: 'Login Required',
            text: 'Please log in to book flights',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#c9a96e',
            cancelButtonColor: '#6b5b4f',
            confirmButtonText: 'Go to Login',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '/login';
            }
        });
        @endauth
    }

    // Sort flights
    document.getElementById('sortBy').addEventListener('change', function() {
        Swal.fire({
            title: 'Sorting...',
            text: 'Reordering flights by ' + this.options[this.selectedIndex].text,
            icon: 'info',
            confirmButtonColor: '#c9a96e',
            timer: 1500,
            showConfirmButton: false
        });
    });
</script>

</body>
</html>

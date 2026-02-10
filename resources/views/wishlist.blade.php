<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Wishlist — Smart Booking</title>
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
                        url('https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=1200');
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
        }

        .page-hero p {
            font-size: 18px;
            color: var(--text-sub);
            margin: 0;
        }

        /* Main Container */
        .wishlist-container {
            max-width: 1200px;
            margin: -50px auto 60px;
            padding: 0 20px;
        }

        /* Stats Card */
        .stats-card {
            background: var(--card-bg);
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(59,31,43,0.15);
            border: 1px solid var(--border);
            display: flex;
            justify-content: space-around;
            gap: 30px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-item .number {
            font-size: 36px;
            font-weight: bold;
            color: var(--gold);
            margin-bottom: 5px;
        }

        .stat-item .label {
            font-size: 14px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Filter Section */
        .filter-section {
            background: var(--card-bg);
            border-radius: 8px;
            padding: 20px 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .filter-section h3 {
            color: var(--deep);
            font-size: 18px;
            font-weight: normal;
            margin: 0;
        }

        .filter-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-controls select,
        .filter-controls input {
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-family: 'Georgia', serif;
            background: white;
            font-size: 14px;
        }

        .clear-all-btn {
            background: var(--error);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .clear-all-btn:hover {
            background: #c0392b;
            transform: translateY(-1px);
        }

        /* Wishlist Grid */
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }

        .wishlist-card {
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(59,31,43,0.1);
            border: 1px solid var(--border);
            transition: all 0.3s;
            position: relative;
        }

        .wishlist-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(59,31,43,0.15);
        }

        .wishlist-image {
            height: 220px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .wishlist-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--deep);
            color: var(--text-light);
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .remove-btn {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(231, 76, 60, 0.9);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.3s;
            opacity: 0;
        }

        .wishlist-card:hover .remove-btn {
            opacity: 1;
        }

        .remove-btn:hover {
            background: #c0392b;
            transform: scale(1.1);
        }

        .wishlist-content {
            padding: 25px;
        }

        .wishlist-content h3 {
            color: var(--deep);
            font-size: 22px;
            margin-bottom: 8px;
            font-weight: normal;
        }

        .wishlist-location {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .wishlist-location i {
            color: var(--gold);
        }

        .wishlist-description {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .wishlist-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid var(--border);
        }

        .wishlist-price {
            font-size: 24px;
            font-weight: bold;
            color: var(--gold);
        }

        .wishlist-price span {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: normal;
        }

        .plan-trip-btn {
            background: var(--gold);
            color: var(--deep);
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .plan-trip-btn:hover {
            background: var(--gold-hover);
            transform: translateY(-1px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: var(--card-bg);
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .empty-state i {
            font-size: 80px;
            color: var(--border-soft);
            margin-bottom: 25px;
        }

        .empty-state h3 {
            color: var(--deep);
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: normal;
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 16px;
            margin-bottom: 30px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .browse-btn {
            background: var(--gold);
            color: var(--deep);
            border: none;
            padding: 14px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .browse-btn:hover {
            background: var(--gold-hover);
            transform: translateY(-2px);
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

            .stats-card {
                flex-direction: column;
                gap: 20px;
            }

            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-controls {
                flex-direction: column;
                width: 100%;
            }

            .filter-controls select,
            .filter-controls input {
                width: 100%;
            }

            .wishlist-grid {
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
    <div class="user-display">
        <i class="fas fa-user-circle"></i>
        <span>{{ Auth::user()->name }}</span>
    </div>
</header>

<!-- Navigation -->
<nav class="nav-container">
    <a href="/"><i class="fas fa-home"></i> Home</a>
    <a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="/plan-trip"><i class="fas fa-route"></i> Plan Trip</a>
    <a href="/flights"><i class="fas fa-plane"></i> Book Flights</a>
    <a href="/discover"><i class="fas fa-compass"></i> Discover</a>
    <a href="/destinations"><i class="fas fa-map-marked-alt"></i> Destinations</a>
    <a href="/community"><i class="fas fa-users"></i> Community</a>
    <a href="/wishlist" class="active"><i class="fas fa-heart"></i> Wishlist</a>
    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
        @csrf
        <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </form>
</nav>

<!-- Hero Section -->
<section class="page-hero">
    <div>
        <h1><i class="fas fa-heart"></i> My Wishlist</h1>
        <p>Your dream destinations await</p>
    </div>
</section>

<!-- Main Container -->
<div class="wishlist-container">
    
    @if($wishlistItems->count() > 0)
    
    <!-- Stats Card -->
    <div class="stats-card">
        <div class="stat-item">
            <div class="number">{{ $wishlistItems->count() }}</div>
            <div class="label">Saved Destinations</div>
        </div>
        <div class="stat-item">
            <div class="number">{{ $wishlistItems->unique('destination.continent')->count() }}</div>
            <div class="label">Continents</div>
        </div>
        <div class="stat-item">
            <div class="number">${{ number_format($wishlistItems->avg('destination.estimated_cost'), 0) }}</div>
            <div class="label">Avg. Budget</div>
        </div>
        <div class="stat-item">
            <div class="number">{{ $wishlistItems->unique('destination.category')->count() }}</div>
            <div class="label">Categories</div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <h3><i class="fas fa-filter"></i> Filter Destinations</h3>
        <div class="filter-controls">
            <select id="filterContinent" onchange="filterWishlist()">
                <option value="all">All Continents</option>
                <option value="Asia">Asia</option>
                <option value="Europe">Europe</option>
                <option value="Africa">Africa</option>
                <option value="North America">North America</option>
                <option value="South America">South America</option>
                <option value="Oceania">Oceania</option>
            </select>
            <select id="filterCategory" onchange="filterWishlist()">
                <option value="all">All Categories</option>
                <option value="beach">Beach</option>
                <option value="mountain">Mountain</option>
                <option value="city">City</option>
                <option value="adventure">Adventure</option>
            </select>
            <input type="search" id="searchWishlist" placeholder="Search destinations..." onkeyup="filterWishlist()">
            <button class="clear-all-btn" onclick="clearAllWishlist()">
                <i class="fas fa-trash-alt"></i> Clear All
            </button>
        </div>
    </div>

    <!-- Wishlist Grid -->
    <div class="wishlist-grid" id="wishlistGrid">
        @foreach($wishlistItems as $item)
        <div class="wishlist-card" 
             data-continent="{{ $item->destination->continent }}" 
             data-category="{{ $item->destination->category }}"
             data-name="{{ strtolower($item->destination->name) }}">
            
            <div class="wishlist-image" style="background-image: url('{{ $item->destination->image ?? 'https://via.placeholder.com/400x300' }}')">
                <span class="wishlist-badge">{{ $item->destination->category }}</span>
                <button class="remove-btn" onclick="removeFromWishlist({{ $item->destination->id }}, '{{ $item->destination->name }}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="wishlist-content">
                <h3>{{ $item->destination->name }}</h3>
                <div class="wishlist-location">
                    <i class="fas fa-map-marker-alt"></i>
                    {{ $item->destination->city }}, {{ $item->destination->country }}
                </div>
                <p class="wishlist-description">
                    {{ Str::limit($item->destination->short_description ?? $item->destination->description, 100) }}
                </p>
                <div class="wishlist-meta">
                    <div class="wishlist-price">
                        ${{ number_format($item->destination->estimated_cost, 0) }}
                        <span>/ person</span>
                    </div>
                    <button class="plan-trip-btn" onclick="planTrip({{ $item->destination->id }}, '{{ $item->destination->name }}')">
                        <i class="fas fa-route"></i> Plan Trip
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @else

    <!-- Empty State -->
    <div class="empty-state">
        <i class="fas fa-heart-broken"></i>
        <h3>Your Wishlist is Empty</h3>
        <p>Start building your dream travel list by exploring our amazing destinations</p>
        <a href="/discover" class="browse-btn">
            <i class="fas fa-compass"></i> Browse Destinations
        </a>
    </div>

    @endif

</div>

<!-- Footer -->
<footer class="footer">
    <p>&copy; 2024 Smart Booking. All rights reserved.</p>
    <div>
        <a href="/privacy">Privacy Policy</a>
        <a href="/terms">Terms of Service</a>
        <a href="/contact">Contact Us</a>
    </div>
</footer>

<script>
    // Filter wishlist
    function filterWishlist() {
        const continent = document.getElementById('filterContinent').value;
        const category = document.getElementById('filterCategory').value;
        const search = document.getElementById('searchWishlist').value.toLowerCase();
        
        const cards = document.querySelectorAll('.wishlist-card');
        let visibleCount = 0;
        
        cards.forEach(card => {
            const cardContinent = card.dataset.continent;
            const cardCategory = card.dataset.category;
            const cardName = card.dataset.name;
            
            const matchContinent = continent === 'all' || cardContinent === continent;
            const matchCategory = category === 'all' || cardCategory === category;
            const matchSearch = search === '' || cardName.includes(search);
            
            if (matchContinent && matchCategory && matchSearch) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        if (visibleCount === 0 && (continent !== 'all' || category !== 'all' || search !== '')) {
            Swal.fire({
                title: 'No Results',
                text: 'No destinations match your filters',
                icon: 'info',
                confirmButtonColor: '#c9a96e'
            });
        }
    }

    // Remove from wishlist
    async function removeFromWishlist(destinationId, destinationName) {
        const result = await Swal.fire({
            title: 'Remove from Wishlist?',
            text: `Remove ${destinationName} from your wishlist?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#6b5b4f',
            confirmButtonText: 'Yes, remove it',
            cancelButtonText: 'Cancel'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`/wishlist/${destinationId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        title: 'Removed!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#c9a96e',
                        timer: 2000
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to remove from wishlist',
                    icon: 'error',
                    confirmButtonColor: '#c9a96e'
                });
            }
        }
    }

    // Clear all wishlist
    async function clearAllWishlist() {
        const result = await Swal.fire({
            title: 'Clear All?',
            text: 'This will remove all destinations from your wishlist',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#6b5b4f',
            confirmButtonText: 'Yes, clear all',
            cancelButtonText: 'Cancel'
        });

        if (result.isConfirmed) {
            // In production, make API call to clear all
            Swal.fire({
                title: 'Feature Coming Soon',
                text: 'Bulk remove functionality will be available soon',
                icon: 'info',
                confirmButtonColor: '#c9a96e'
            });
        }
    }

    // Plan trip
    function planTrip(destinationId, destinationName) {
        Swal.fire({
            title: 'Plan Your Trip',
            text: `Ready to plan your trip to ${destinationName}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#c9a96e',
            cancelButtonColor: '#6b5b4f',
            confirmButtonText: 'Yes, let\'s go!',
            cancelButtonText: 'Not yet'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `/plan-trip?destination=${destinationId}`;
            }
        });
    }
</script>

</body>
</html>

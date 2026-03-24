<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Destinations — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite([
        'resources/css/blade/base.css',
        'resources/css/blade/destinations/index.css',
        'resources/js/blade/base.js',
        'resources/js/blade/destinations/index.js'
    ])
</head>
<body>

@include('partials.public-navigation')

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

@include('partials.public-footer')
</body>
</html>

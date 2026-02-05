<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Plan Trip — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --deep:#3b1f2b;--deep-alt:#4d2a3a;--gold:#c9a96e;--gold-hover:#b8955a;
            --cream:#f5f0eb;--card-bg:#fff8f2;--border:#e2d5c7;--border-soft:#d4c4b0;
            --text-light:#f5e6d3;--text-muted:#6b5b4f;--text-sub:#d4c4b0;
        }
        body{font-family:'Georgia',serif;margin:0;padding:0;background:var(--cream);color:#2c2c2c;text-align:center;}
        .main-header{display:flex;align-items:center;justify-content:flex-start;gap:15px;padding:20px 40px 20px 60px;background-color:var(--deep);}
        .logo{height:100px;width:auto;min-width:100px;object-fit:contain;filter:brightness(0) invert(1);}
        .logo-text{font-size:32px;font-weight:700;color:var(--text-light);letter-spacing:2px;text-shadow:1px 1px 3px rgba(0,0,0,0.4);font-variant:small-caps;}
        .nav-container{display:flex;justify-content:center;background:var(--gold);padding:15px;flex-wrap:wrap;border-bottom:2px solid var(--gold-hover);}
        .nav-container a{text-decoration:none;color:var(--deep);font-size:15px;font-weight:bold;padding:10px 15px;border-radius:4px;transition:all 0.3s ease;display:flex;align-items:center;gap:8px;letter-spacing:0.5px;font-family:'Georgia',serif;}
        .nav-container a:hover,.nav-container a.active{background:rgba(59,31,43,0.18);transform:translateY(-2px);}

        .page-hero{background:linear-gradient(rgba(30,15,20,0.6),rgba(30,15,20,0.6)),url('/img/pexels-mikegles-30931569.jpg');background-size:cover;background-position:center;height:220px;display:flex;align-items:center;justify-content:center;text-align:center;color:var(--text-light);}
        .page-hero h1{font-size:34px;font-weight:normal;letter-spacing:1px;margin:0 0 8px;color:var(--text-light);}
        .page-hero p{font-size:16px;color:var(--text-sub);margin:0;}

        .section-title{color:var(--deep);font-size:28px;margin-bottom:10px;position:relative;padding-bottom:15px;font-weight:normal;letter-spacing:1px;}
        .section-title:after{content:'';position:absolute;bottom:0;left:50%;transform:translateX(-50%);width:60px;height:2px;background:var(--gold);}
        .section-subtitle{color:var(--text-muted);font-size:16px;margin-bottom:30px;max-width:800px;margin-left:auto;margin-right:auto;}

        .primary-button{background:var(--gold);color:var(--deep);border:none;padding:12px 30px;border-radius:4px;cursor:pointer;font-weight:bold;font-size:15px;transition:background 0.3s ease,box-shadow 0.3s ease;display:inline-flex;align-items:center;justify-content:center;gap:10px;font-family:'Georgia',serif;letter-spacing:0.5px;box-shadow:0 2px 6px rgba(0,0,0,0.15);text-decoration:none;}
        .primary-button:hover{background:var(--gold-hover);box-shadow:0 3px 10px rgba(0,0,0,0.22);}
        .primary-button:disabled{background:var(--border-soft);color:#8a7e74;cursor:not-allowed;box-shadow:none;}

        /* Planner Wrap */
        .planner-wrap{max-width:1100px;margin:40px auto;padding:0 20px;}

        /* Step Indicators */
        .steps{display:flex;justify-content:center;gap:0;margin-bottom:40px;position:relative;}
        .steps::before{content:'';position:absolute;top:20px;left:50%;transform:translateX(-50%);width:70%;height:2px;background:var(--border);z-index:0;}
        .step{position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;flex:1;max-width:180px;}
        .step-circle{width:42px;height:42px;border-radius:50%;background:var(--card-bg);border:2px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:16px;font-weight:bold;transition:all 0.3s ease;}
        .step.active .step-circle,.step.done .step-circle{background:var(--gold);border-color:var(--gold);color:var(--deep);}
        .step-label{margin-top:8px;font-size:13px;color:var(--text-muted);font-weight:bold;}
        .step.active .step-label{color:var(--deep);}

        /* Mood Cards */
        .mood-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px;margin:20px 0 30px;}
        .mood-card{background:var(--card-bg);border:2px solid var(--border);border-radius:8px;padding:24px 14px;cursor:pointer;transition:all 0.3s ease;text-align:center;}
        .mood-card:hover{border-color:var(--gold);transform:translateY(-3px);box-shadow:0 4px 14px rgba(59,31,43,0.12);}
        .mood-card.selected{border-color:var(--gold);background:linear-gradient(135deg,#fff8f2,#fdf0dc);box-shadow:0 4px 14px rgba(201,169,110,0.25);}
        .mood-card .mood-icon{font-size:2em;color:var(--deep);margin-bottom:10px;}
        .mood-card h4{color:var(--deep);font-weight:normal;font-size:15px;margin:0;}
        .mood-card p{color:var(--text-muted);font-size:12px;margin:6px 0 0;}

        /* Form Groups */
        .form-group{display:flex;flex-direction:column;gap:8px;text-align:left;margin-bottom:20px;}
        .form-group label{font-weight:bold;color:var(--deep);font-size:14px;letter-spacing:0.5px;}
        .form-group input,.form-group select,.form-group textarea{padding:12px 14px;border:1px solid var(--border-soft);border-radius:4px;font-size:15px;color:var(--deep);background:var(--card-bg);font-family:'Georgia',serif;transition:border-color 0.3s ease;}
        .form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 2px rgba(201,169,110,0.2);}
        .form-group textarea{resize:vertical;min-height:90px;}

        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:20px;}

        /* Budget Slider */
        .budget-display{text-align:center;font-size:24px;font-weight:normal;color:var(--deep);margin:10px 0;}
        .budget-display span{color:var(--gold);font-weight:bold;}
        input[type="range"]{-webkit-appearance:none;width:100%;height:6px;background:var(--border);border-radius:3px;outline:none;}
        input[type="range"]::-webkit-slider-thumb{-webkit-appearance:none;width:22px;height:22px;border-radius:50%;background:var(--gold);cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,0.2);}
        .budget-labels{display:flex;justify-content:space-between;font-size:12px;color:var(--text-muted);margin-top:6px;}

        /* Planner Card */
        .planner-card{background:var(--card-bg);border-radius:6px;padding:36px;border:1px solid var(--border);box-shadow:0 3px 10px rgba(59,31,43,0.08);margin-bottom:30px;}
        .planner-card h3{color:var(--deep);font-weight:normal;font-size:20px;margin-top:0;text-align:left;border-bottom:1px solid var(--border);padding-bottom:12px;}

        /* Itinerary Preview */
        .itinerary-preview{background:linear-gradient(135deg,var(--deep),var(--deep-alt));border-radius:6px;padding:36px;color:var(--text-light);border:1px solid rgba(201,169,110,0.2);box-shadow:0 8px 28px rgba(59,31,43,0.25);}
        .itinerary-preview h3{color:var(--text-light);font-weight:normal;font-size:22px;margin-top:0;border-bottom:1px solid rgba(201,169,110,0.25);padding-bottom:12px;}
        .itin-day{display:flex;gap:18px;padding:16px 0;border-bottom:1px solid rgba(201,169,110,0.15);align-items:flex-start;}
        .itin-day:last-child{border-bottom:none;}
        .itin-day-num{background:var(--gold);color:var(--deep);width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:15px;flex-shrink:0;}
        .itin-day h4{color:var(--text-light);font-weight:normal;font-size:16px;margin:0 0 4px;}
        .itin-day p{color:var(--text-sub);font-size:13px;margin:0;line-height:1.5;}

        .btn-row{display:flex;justify-content:center;gap:16px;margin-top:30px;flex-wrap:wrap;}
        .secondary-button{background:transparent;color:var(--deep);border:1px solid var(--deep);padding:10px 25px;border-radius:4px;cursor:pointer;font-weight:bold;font-size:15px;transition:all 0.3s ease;font-family:'Georgia',serif;letter-spacing:0.5px;}
        .secondary-button:hover{background:var(--deep);color:var(--text-light);}

        .footer{background:var(--deep);color:var(--text-sub);text-align:center;padding:30px 20px;margin-top:60px;}
        .footer a{color:var(--gold);margin:0 10px;transition:color 0.3s ease;text-decoration:none;}
        .footer a:hover{color:var(--text-light);}

        @media(max-width:768px){
            .main-header{justify-content:center;padding:15px 20px;}.logo{height:60px;min-width:60px;}.logo-text{font-size:24px;}
            .nav-container{flex-direction:column;align-items:center;}.nav-container a{font-size:14px;padding:8px 10px;}
            .form-row{grid-template-columns:1fr;}.steps::before{width:80%;}.mood-grid{grid-template-columns:repeat(3,1fr);}
        }
    </style>
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
        <a href="/flights"><i class="fas fa-plane"></i> Book Flights</a> <!-- Flight Booking Added -->
        <a href="/discover"><i class="fas fa-compass"></i> Discover</a>
        <a href="/destinations"><i class="fas fa-map-marked-alt"></i> Destinations</a>
        <a href="/community"><i class="fas fa-users"></i> Community</a>
        <a href="/login"><i class="fas fa-sign-in-alt"></i> Login</a>
    </div>
</nav>


<section class="page-hero">
    <div>
        <h1><i class="fas fa-route"></i> Plan Your Trip</h1>
        <p>Let AI build the perfect itinerary tailored to your mood, budget, and style.</p>
    </div>
</section>

<div class="planner-wrap">

    <!-- Steps -->
    <div class="steps">
        <div class="step active"><div class="step-circle">1</div><div class="step-label">Mood & Style</div></div>
        <div class="step"><div class="step-circle">2</div><div class="step-label">Destination</div></div>
        <div class="step"><div class="step-circle">3</div><div class="step-label">Dates & Budget</div></div>
        <div class="step"><div class="step-circle">4</div><div class="step-label">Itinerary</div></div>
    </div>

    <!-- Step 1: Mood -->
    <div class="planner-card" id="step1">
        <h3><i class="fas fa-heart" style="color:var(--gold);margin-right:8px;"></i>How are you feeling?</h3>
        <p style="color:var(--text-muted);text-align:left;margin-top:0;">Choose the mood that best describes what kind of trip you're looking for.</p>
        <div class="mood-grid">
            <div class="mood-card selected" onclick="selectMood(this)">
                <div class="mood-icon"><i class="fas fa-hiking"></i></div>
                <h4>Adventurous</h4>
                <p>Thrills & exploration</p>
            </div>
            <div class="mood-card" onclick="selectMood(this)">
                <div class="mood-icon"><i class="fas fa-spa"></i></div>
                <h4>Relaxed</h4>
                <p>Peace & tranquility</p>
            </div>
            <div class="mood-card" onclick="selectMood(this)">
                <div class="mood-icon"><i class="fas fa-landmark"></i></div>
                <h4>Cultural</h4>
                <p>History & art</p>
            </div>
            <div class="mood-card" onclick="selectMood(this)">
                <div class="mood-icon"><i class="fas fa-heart"></i></div>
                <h4>Romantic</h4>
                <p>Love & escape</p>
            </div>
            <div class="mood-card" onclick="selectMood(this)">
                <div class="mood-icon"><i class="fas fa-utensils"></i></div>
                <h4>Foodie</h4>
                <p>Cuisine & flavor</p>
            </div>
            <div class="mood-card" onclick="selectMood(this)">
                <div class="mood-icon"><i class="fas fa-leaf"></i></div>
                <h4>Eco-Travel</h4>
                <p>Nature & sustainability</p>
            </div>
        </div>
    </div>

    <!-- Step 2: Destination -->
    <div class="planner-card" id="step2">
        <h3><i class="fas fa-map-marker-alt" style="color:var(--gold);margin-right:8px;"></i>Pick a Destination</h3>
        <div class="form-group">
            <label>Where do you want to go?</label>
            <select>
                <option value="">— Let AI choose for me —</option>
                <option>Bali, Indonesia</option>
                <option>Kyoto, Japan</option>
                <option>Swiss Alps, Switzerland</option>
                <option>Santorini, Greece</option>
                <option>Paris, France</option>
                <option>Lisbon, Portugal</option>
                <option>Bangkok, Thailand</option>
                <option>Amalfi Coast, Italy</option>
                <option>New Zealand</option>
                <option>Morocco</option>
            </select>
        </div>
        <div class="form-group">
            <label>Travel Companion</label>
            <select>
                <option>Solo Travel</option>
                <option>Couple</option>
                <option>Family</option>
                <option>Friends Group</option>
            </select>
        </div>
    </div>

    <!-- Step 3: Dates & Budget -->
    <div class="planner-card" id="step3">
        <h3><i class="fas fa-calendar-alt" style="color:var(--gold);margin-right:8px;"></i>Dates & Budget</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Departure Date</label>
                <input type="date">
            </div>
            <div class="form-group">
                <label>Return Date</label>
                <input type="date">
            </div>
        </div>
        <div class="form-group">
            <label>Total Budget (per person)</label>
            <div class="budget-display">$<span id="budgetVal">2,500</span></div>
            <input type="range" min="500" max="10000" step="100" value="2500" oninput="document.getElementById('budgetVal').textContent=Number(this.value).toLocaleString()">
            <div class="budget-labels"><span>$500</span><span>$10,000+</span></div>
        </div>
        <div class="form-group">
            <label>Special Requirements</label>
            <textarea placeholder="Dietary needs, accessibility requirements, interests…"></textarea>
        </div>
    </div>

    <!-- Step 4: AI Itinerary Preview -->
    <div class="itinerary-preview" id="step4">
        <h3><i class="fas fa-robot" style="color:var(--gold);margin-right:8px;"></i>AI-Generated Itinerary Preview</h3>
        <p style="color:var(--text-sub);margin-top:0;font-size:14px;">Based on your selections, here is a suggested 7-day itinerary:</p>
        <div class="itin-day">
            <div class="itin-day-num">1</div>
            <div><h4>Arrival & Welcome</h4><p>Arrive at Ngurah Rai Airport. Transfer to your villa in Ubud. Enjoy a traditional Balinese welcome drink and relax after your journey.</p></div>
        </div>
        <div class="itin-day">
            <div class="itin-day-num">2</div>
            <div><h4>Rice Terraces & Temples</h4><p>Morning visit to Tegallalang Rice Terraces at sunrise. Afternoon tour of Tirta Empul temple for a purification ceremony.</p></div>
        </div>
        <div class="itin-day">
            <div class="itin-day-num">3</div>
            <div><h4>Adventure Day</h4><p>White-water rafting on the Ayung River. Evening at a local village for a traditional Kecak dance performance.</p></div>
        </div>
        <div class="itin-day">
            <div class="itin-day-num">4</div>
            <div><h4>Cooking & Culture</h4><p>Full-day Balinese cooking class. Learn to make traditional dishes using locally sourced ingredients. Free evening to explore Ubud market.</p></div>
        </div>
        <div class="itin-day">
            <div class="itin-day-num">5–7</div>
            <div><h4>Beach & Relaxation</h4><p>Move to a beachfront resort in Seminyak. Enjoy snorkeling, spa treatments, and stunning sunset dinners on the coast.</p></div>
        </div>
    </div>

    <div class="btn-row">
        <button class="secondary-button"><i class="fas fa-arrow-left"></i> Back</button>
        <button class="primary-button" style="font-size:17px;padding:14px 40px;"><i class="fas fa-magic"></i> Generate Full Itinerary</button>
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

<script>
function selectMood(el) {
    document.querySelectorAll('.mood-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
}
</script>
</body>
</html>

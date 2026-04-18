<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="340" alt="Laravel Logo">
</p>

<h1 align="center">Smart Booking</h1>
<h3 align="center">AI-Powered Travel Planning Web Application</h3>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel&logoColor=white" alt="Laravel 11">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white" alt="PHP 8.2">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3-06B6D4?style=flat&logo=tailwindcss&logoColor=white" alt="Tailwind">
  <img src="https://img.shields.io/badge/Pusher-Real--Time-300D4F?style=flat&logo=pusher&logoColor=white" alt="Pusher">
  <img src="https://img.shields.io/badge/Tests-PHPUnit-6C757D?style=flat&logo=php&logoColor=white" alt="PHPUnit">
  <img src="https://img.shields.io/badge/CI%2FCD-GitHub%20Actions-2088FF?style=flat&logo=githubactions&logoColor=white" alt="GitHub Actions">
</p>

---

## Overview

**Smart Booking** is a full-stack travel planning application built from scratch using **Laravel 11**. Users describe how they want to feel on holiday — adventurous, relaxed, romantic, cultural — and the system generates a complete personalised day-by-day itinerary, matching them to destinations, activities, and a daily budget schedule automatically.

The project demonstrates end-to-end application development: database design, back-end business logic, AI-powered recommendations, real-time communication, REST API design, role-based access control, multi-currency support, real embedded data from external APIs, and automated testing.

### Latest Updates (2026)
- ✅ **Real Data Integration**: All accommodations, images, and news now fetched from live APIs (Pexels, OpenStreetMap, NewsAPI)
- ✅ **Multi-Currency Support**: Real-time currency conversion with 28+ currencies via live exchange rates
- ✅ **Smart Location Detection**: GPS-based airport detection with 30+ major international airports
- ✅ **AI-Powered Suggestions**: Groq AI integration for intelligent destination recommendations
- ✅ **Silent Country Lock**: Seamless destination persistence across plan-trip, flights, and accommodations
- ✅ **Enhanced Receipt System**: PDF receipts with user-selected currency formatting

---

## Key Functional Areas

### Custom Trip Planning Algorithm
Rather than relying on an external paid API, the application implements a **Hybrid Scoring Engine** in pure PHP that generates itineraries with no internet dependency and zero cost per request.

```
Score = 0.4 × MoodMatch + 0.3 × BudgetFit + 0.2 × CompanionFit + 0.1 × Rating
```

---

### Dual-Role Flight & Booking System
The application supports two distinct user roles with clearly separated capabilities:

| Role | What they can do |
|---|---|
| **Traveller** | Search flights, book seats, manage bookings, cancel with seat return |
| **Agency** | Create and publish flights, manage their own listings, view all incoming bookings |

Booking is wrapped in a **database transaction** to prevent race conditions when two users attempt to book the last available seat simultaneously.

---

### Authentication and Security
The authentication layer is built on top of Laravel Breeze and extended with:
- Email verification via cryptographically signed URLs
- Password reset via email token
- Password confirmation gate for sensitive actions
- Client-side session timeout warning (`session-timeout.js`)
- `CheckTraveler` middleware enforcing role-based route access
- Tracking of `last_login_at` and `last_login_ip` per user

---

### Real-Time Features
Integrated **Pusher** with **Laravel Echo** to deliver real-time in-app messaging between users. The dashboard chat panel updates instantly without polling.

---

### Media Management
Users can upload travel photos and videos which are processed server-side:
- Auto-generated **300×300 thumbnail** and **800×800 medium** variant via Intervention Image
- Batch delete, favourite toggle, metadata editing, and trip association
- Storage usage stats surfaced on the dashboard

---

### Destinations and Discovery
- **Real embedded data** from OpenStreetMap via Overpass API for accommodations
- **Live destination insights** with news, tourist sites, and activities from NewsAPI and Wikipedia
- **Real property images** from Pexels API with Unsplash fallback
- Up to **3 destinations compared side-by-side** using session-backed state
- Wishlist with summary stats (destinations saved, continents covered, average budget)
- **City nickname support** (e.g., "jozi" → Johannesburg, "NYC" → New York)

---

### PDF Export & Receipts
- Generated itineraries can be downloaded as formatted PDFs via `barryvdh/laravel-dompdf`
- **Trip receipts** with complete cost breakdown in user's selected currency
- Professional PDF generation with jsPDF for client-side export
- Includes flight details, accommodation costs, activities, taxes, and potential savings

---

## Technical Highlights

### Architecture
- **MVC** pattern strictly followed throughout; fat models, thin controllers
- **12 controllers** across web, API, and auth namespaces
- **13 Eloquent models** with relationships, scopes, and accessors
- **14 database migrations** building a normalised relational schema
- **REST API** endpoints under `/api` for media and trip data (JSON)
- **Blade component library** with 13 reusable UI components

### Database Design
14 tables covering the full domain:

| Area | Tables |
|---|---|
| Users & Roles | `users`, `agency_profiles`, `user_preferences` |
| Trips & Itineraries | `trips`, `itineraries`, `activities` |
| Flights & Bookings | `flights`, `bookings` |
| Destinations | `destinations`, `continents`, `saved_destinations` |
| Media | `media`, `memories`, `memory_frames` |

### Testing
Full **PHPUnit** test suite covering all authentication flows:

```
Auth/AuthenticationTest       — login, invalid credentials, logout
Auth/RegistrationTest         — new user creation and validation
Auth/EmailVerificationTest    — signed URL verification flow
Auth/PasswordResetTest        — reset link and token handling
Auth/PasswordUpdateTest       — current password check, new hash
Auth/PasswordConfirmationTest — confirmation gate
Feature/ProfileTest           — update profile, change password, delete account
```

### CI/CD
Two **GitHub Actions** workflows:
- `laravel.yml` — runs the full test suite on every push and pull request
- `laravel-ci-cd.yml` — builds frontend assets, runs tests, and deploys on merge to `main`

A custom `HealthCheck` artisan command is available for server-side monitoring.

---

## Technology Stack

| | Technology | Purpose |
|---|---|---|
| **Backend** | Laravel 11, PHP 8.2 | Application framework, ORM, routing |
| **Frontend** | Blade, Tailwind CSS, Alpine.js | Templating, styling, interactivity |
| **Build** | Vite, PostCSS | Asset bundling |
| **Database** | SQLite (dev), PostgreSQL (prod) | Relational data storage |
| **Real-Time** | Pusher, Laravel Echo | WebSocket messaging |
| **Images** | Pexels API, Unsplash | Real property and destination images |
| **PDF** | barryvdh/laravel-dompdf, jsPDF | Server & client-side PDF generation |
| **AI** | Groq (Mixtral, LLaMA) | AI-powered destination suggestions |
| **Maps** | Leaflet, OpenStreetMap | Interactive maps & geocoding |
| **APIs** | Geoapify, NewsAPI, Aviationstack | Places, news, flights data |
| **Currency** | Live Exchange Rates API | Real-time multi-currency conversion |
| **Testing** | PHPUnit | Automated test suite |
| **CI/CD** | GitHub Actions | Continuous integration & deployment |

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/          # 12 controllers (web, API, auth namespaces)
│   ├── Middleware/            # CheckTraveler — role-based access
│   └── Requests/             # Form request validation classes
├── Models/                   # 13 Eloquent models
└── View/Components/          # AppLayout, GuestLayout

resources/
├── css/
│   ├── app.css
│   └── pages/                # Page-specific styles (modular frontend)
│
├── js/
│   ├── app.js
│   ├── bootstrap.js
│   ├── session-timeout.js
│   └── pages/                # Page-specific scripts
│
└── views/
    ├── landing/
    │   └── index.blade.php        # Public landing page
    │
    ├── dashboard/
    │   └── index.blade.php        # Authenticated dashboard
    │
    ├── plan-trip/
    │   └── index.blade.php        # Trip planning wizard
    │
    ├── flights/
    │   └── index.blade.php        # Flight search & booking
    │
    ├── discover/
    │   └── index.blade.php        # Destination discovery
    │
    ├── destinations/
    │   └── index.blade.php        # Destination listings
    │
    ├── community/
    │   └── index.blade.php        # Community feed
    │
    ├── chat/
    │   └── index.blade.php        # Chat functionality
    │
    ├── wishlist/
    │   └── index.blade.php        # Saved destinations
    │
    ├── notifications/
    │   └── index.blade.php        # Notifications system
    │
    ├── layouts/                   # App layout, guest layout, navigation
    │
    ├── components/                # Reusable Blade UI components
    │
    ├── auth/                      # Authentication views (login, register, reset, etc.)
    │
    ├── profile/
    │   ├── edit.blade.php
    │   └── partials/              # Profile management forms
    │
    └── pdf/
        └── itinerary.blade.php    # PDF generation view

database/
├── migrations/               # 14 migrations, timestamped and ordered
├── seeders/                  # DatabaseSeeder + DestinationSeeder
└── factories/

routes/
├── web.php                   # Main web routes
├── api.php                   # REST API routes
└── auth.php                  # Breeze auth routes

tests/
├── Feature/Auth/             # 6 auth test files
└── Feature/ProfileTest.php
```

---

## Running Locally

```bash
git clone https://github.com/Lenardth/smart-trip-system.git
cd smart-trip-system
composer install
composer require barryvdh/laravel-dompdf
npm install
cp .env.example .env
touch database/database.sqlite

# Configure API keys in .env
# GEOAPIFY_KEY=your_key_here
# PEXELS_API_KEY=your_key_here
# NEWSAPI_KEY=your_key_here
# AVIATIONSTACK_KEY=your_key_here
# GROQ_API_KEY=your_key_here

php artisan key:generate
php artisan migrate
php artisan migrate --seed
php artisan db:seed --class=DestinationSeeder
php artisan db:seed --class=CommunitySeeder
php artisan storage:link
npm run build
php artisan serve
```

Open `http://localhost:8000`. A seeded agency account and sample destinations are included.

### Required API Keys

The application uses real embedded data from external APIs. Sign up for free API keys:

- **Geoapify** (Places/Accommodations): https://www.geoapify.com/
- **Pexels** (Images): https://www.pexels.com/api/
- **NewsAPI** (News): https://newsapi.org/
- **Aviationstack** (Flights): https://aviationstack.com/
- **Groq** (AI): https://console.groq.com/

All APIs have generous free tiers suitable for development and testing.

---

## Feature Walkthrough

- **Public Landing & Marketing**  
  High-level explanation of what Smart Booking does, key value propositions, and primary calls-to-action into planning a trip or exploring destinations.

- **Trip Planning Wizard (`plan-trip`)**  
  Four-step guided flow where travellers describe mood, budget, dates, companions, and preferences. Submitting the wizard triggers the hybrid scoring engine to generate a full multi-day itinerary.

- **Dashboard**  
  Authenticated overview showing upcoming trips, saved destinations, storage usage, recent activity, and quick links into flights, discovery, and community features.

- **Flights & Bookings**  
  Search and filter flights, select seats, and create bookings as a traveller. Agencies can create and manage flights, with atomic booking transactions and automatic seat availability updates.

- **Destinations & Discover**  
  Curated destination catalogue with mood tags, continent filters, budgets, and ratings. The discover and destinations pages allow browsing, comparing up to three places side-by-side, and adding to the wishlist.

- **Wishlist**  
  Session-backed and/or user-linked wishlist to quickly store interesting destinations with basic statistics on coverage and average costs.

- **Accommodations & Stays**  
  Real-time accommodation search powered by OpenStreetMap data via Overpass API. Features include:
  - Live property data with real images from Pexels API
  - Interactive maps showing accommodation locations
  - Local news and travel advisories for searched cities
  - Direct booking links to Booking.com
  - Smart city nickname recognition (jozi, NYC, etc.)
  - Trending destination recommendations

- **Multi-Currency Support**  
  Global currency conversion system supporting 28+ currencies:
  - Real-time exchange rates with automatic updates
  - Persistent currency selection across all pages
  - Intelligent price formatting with proper symbols and decimals
  - Currency switcher with search functionality
  - Applies to flights, accommodations, trip costs, and receipts

- **Community**  
  Front-end community experience with live-feeling forum topics, group trip concepts, trending tags, and travel stories, wired for real-time capabilities via Pusher and `resources/js/blade/community/index.js`.

- **Media & Memories**  
  Upload, resize, and manage travel media associated with trips, including thumbnails, favourites, and metadata editing, with usage stats surfaced to the user.

- **PDF Itinerary Export & Receipts**  
  Turn a generated itinerary into a nicely formatted PDF using a dedicated Blade view and `barryvdh/laravel-dompdf`. Trip receipts include:
  - Complete cost breakdown (flights, accommodation, activities, food, transport)
  - Tax calculations and service fees
  - Potential savings (early bird, group discounts, package deals)
  - Flight information and recommended activities
  - Travel tips and booking reference numbers
  - All prices displayed in user's selected currency

---

## Frontend, CSS & JavaScript Structure

- **Blade Views**  
  All pages are built with Blade templates under `resources/views`. Public pages like `landing.blade.php`, core pages such as `dashboard.blade.php`, `plan-trip.blade.php`, `flights.blade.php`, `discover.blade.php`, `destinations.blade.php`, `community.blade.php`, and `wishlist.blade.php` share common headers, navigation, and footers. Reusable UI is implemented via Blade components under `resources/views/components` and layouts under `resources/views/layouts`.

- **Global Styles**  
  Tailwind is configured in `resources/css/app.css` (using `@tailwind base`, `@tailwind components`, `@tailwind utilities`) and compiled via Vite. Global design tokens and shared page scaffolding live in `resources/css/blade/base.css`.

- **Page-Specific Styles**  
  Each major page has its own stylesheet under `resources/css/blade` (for example `landing.css`, `login.css`, `dashboard.css`, `plan-trip.css`, `discover.css`, `community.css`). These are loaded via `@vite` in the corresponding Blade files so each screen only ships the styles it needs. Some legacy static pages (like `destinations.blade.php`) also reference pre-compiled CSS in `public/css/`.

- **JavaScript Organisation**  
  The application-level bootstrap code is in `resources/js/app.js` and `resources/js/bootstrap.js`. Page-specific interactivity lives under `resources/js/blade` (for example `landing.js`, `login.js`, `dashboard.js`, `plan-trip.js`, `discover.js`, `community.js`). Laravel Echo and Pusher are configured here, along with things like the session timeout handler (`resources/js/session-timeout.js`).

- **How Assets Are Loaded**  
  Blade views use the `@vite` directive to load CSS and JS from `resources/**`, letting Vite handle bundling and cache-busting. Static assets that do not go through Vite are referenced via `asset(...)` and live under `public/` (for example `public/css/destinations.css` and `public/js/destinations.js`).

- **Adding a New Page**  
  1. Create a new Blade view under `resources/views` and register a route in `routes/web.php`.  
  2. Add any page-specific CSS under `resources/css/blade/your-page.css` and JS under `resources/js/blade/your-page.js`.  
  3. Include them via `@vite(['resources/css/app.css', 'resources/css/blade/base.css', 'resources/css/blade/your-page.css', 'resources/js/blade/your-page.js'])` in the new Blade view or an appropriate layout.  
  4. Run `npm run dev` (or `npm run build` for production) so Vite compiles the new assets.

---

## Developer Notes

- **Real Data Strategy**  
  The application prioritizes real embedded data from external APIs over dummy/seed data. See `REAL_DATA_STRATEGY.md` for complete documentation on:
  - API integration patterns and fallback strategies
  - Caching recommendations to stay within API limits
  - Image loading from Pexels with Unsplash fallback
  - Accommodation data from OpenStreetMap via Overpass API
  - Currency conversion with live exchange rates
  - News integration from NewsAPI

- **Trip Planning Algorithm**  
  The hybrid scoring engine lives in the PHP domain layer (models/services) and combines mood match, budget fit, companion suitability, and ratings into a single score per destination and activity. When extending it, prefer configurable weights and avoid hard-coding thresholds directly into controllers.

- **Validation & Requests**  
  Form validation is centralised in request classes under `app/Http/Requests`. Any new endpoints or forms should follow this pattern to keep controllers thin and avoid duplicated validation rules.

- **Testing**  
  The existing PHPUnit suite focuses on authentication and profile flows. New business logic (especially around bookings, payments, and itinerary generation) should be accompanied by feature tests under `tests/Feature` and, where appropriate, unit tests for small, pure services.

- **Real-Time Features**  
  When adding new real-time behaviour, expose the relevant configuration (`pusherKey`, `pusherCluster`, CSRF token) to the front end via Blade (as is done in `community.blade.php`) and handle subscriptions and event binding inside the appropriate `resources/js/blade/*.js` file.

- **Deployment**  
  GitHub Actions workflows already cover building assets, running tests, and deploying on merges to `main`. Any new environment variables or external services should be reflected in `.env.example` and, if necessary, documented in comments within the workflow files.

- **API Rate Limits**  
  The application uses multiple external APIs with free tier limits:
  - Geoapify: 3,000 requests/day
  - Pexels: 200 requests/hour
  - NewsAPI: 100 requests/day
  - Aviationstack: 100 requests/month
  
  Implement caching strategies (24-hour cache recommended) to stay within limits. See `REAL_DATA_STRATEGY.md` for monitoring and best practices.

---

## License

[MIT](https://opensource.org/licenses/MIT)

---

## Documentation

- **[REAL_DATA_STRATEGY.md](REAL_DATA_STRATEGY.md)** - Complete guide to API integration, data sources, and fallback strategies
- **[vercel.json](vercel.json)** - Vercel deployment configuration for serverless hosting
- **[.env.example](.env.example)** - Environment variables template with all required API keys

---

<p align="center">
  Designed and developed by <strong>Lenard</strong> &nbsp;·&nbsp; 2026
</p>

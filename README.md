<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="340" alt="Laravel Logo">
</p>

<h1 align="center">Smart Booking</h1>
<h3 align="center">AI-Powered Travel Planning Web Application</h3>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white" alt="PHP 8.2">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3-06B6D4?style=flat&logo=tailwindcss&logoColor=white" alt="Tailwind">
  <img src="https://img.shields.io/badge/Pusher-Real--Time-300D4F?style=flat&logo=pusher&logoColor=white" alt="Pusher">
  <img src="https://img.shields.io/badge/Tests-PHPUnit-6C757D?style=flat&logo=php&logoColor=white" alt="PHPUnit">
  <img src="https://img.shields.io/badge/CI%2FCD-GitHub%20Actions-2088FF?style=flat&logo=githubactions&logoColor=white" alt="GitHub Actions">
</p>

---

## Overview

**Smart Booking** is a full-stack travel planning application built from scratch using **Laravel 12**. Users describe how they want to feel on holiday — adventurous, relaxed, romantic, cultural — and the system generates a complete personalised day-by-day itinerary, matching them to destinations, activities, and a daily budget schedule automatically.

The project demonstrates end-to-end application development: database design, back-end business logic, AI-powered recommendations, real-time communication, REST API design, role-based access control, multi-currency support, real embedded data from external APIs, and automated testing.

### Latest Updates (2026)
-  **Real Data Integration**: All accommodations, images, and news now fetched from live APIs (Pexels, OpenStreetMap, NewsAPI)
-  **Multi-Currency Support**: Real-time currency conversion with 28+ currencies via live exchange rates
-  **Smart Location Detection**: GPS-based airport detection with 30+ major international airports
-  **AI-Powered Suggestions**: Groq AI integration for intelligent destination recommendations
-  **Silent Country Lock**: Seamless destination persistence across plan-trip, flights, and accommodations
-  **Enhanced Receipt System**: PDF receipts with user-selected currency formatting

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
  The application prioritizes real embedded data from external APIs over dummy/seed data:
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
  


---

## Project Timeline

A chronological record of how the project evolved from first commit to current state.

---

### Phase 1 — Project Bootstrap (1 Feb 2026)

| Date | What happened |
|---|---|
| 2026-02-01 | **First commit** — initial Laravel project scaffolded from scratch |
| 2026-02-01 | GitHub Actions CI workflow added for build and integration tests |
| 2026-02-01 | Composer platform locked to PHP 8.3 |
| 2026-02-02 | CI pipeline stabilised — Vite assets build, all tests green |
| 2026-02-02 | Node.js setup step added to workflow |

---

### Phase 2 — Core Features (3 – 10 Feb 2026)

| Date | What happened |
|---|---|
| 2026-02-03 | Initial application features scoped and started |
| 2026-02-05 | Login page updated; core feature set expanded |
| 2026-02-08 | Working features confirmed passing tests; PR #3 merged |
| 2026-02-10 | **Flight booking feature** introduced |
| 2026-02-10 | `.env` files removed from git tracking; `.gitignore` updated |
| 2026-02-10 | Laravel CI/CD pipeline configured |

---

### Phase 3 — CI/CD Hardening (14 – 22 Feb 2026)

| Date | What happened |
|---|---|
| 2026-02-14 | Local changes saved before sync |
| 2026-02-15 | CI/CD workflow refactored multiple times — caching, `.env` handling, PHPStan |
| 2026-02-22 | Multi-platform CI/CD pipeline templates added |
| 2026-02-22 | GitLab CI configuration explored |

---

### Phase 4 — PDF, README & AI Keys (23 Feb – 8 Mar 2026)

| Date | What happened |
|---|---|
| 2026-02-23 | **First full README** written describing the application |
| 2026-02-27 | **PDF itinerary export** working |
| 2026-03-04 | PDF rendering fixed and refined |
| 2026-03-04 | README updated with installation steps and repo URL |
| 2026-03-07 | `.env.example` introduced with all required keys |
| 2026-03-07 | AI provider keys (`GROQ_API_KEY`) added to environment template |

---

### Phase 5 — Modular File Separation (8 – 21 Mar 2026)

| Date | What happened |
|---|---|
| 2026-03-08 | CSS and JS begin to be split into per-page files |
| 2026-03-09 | `DestinationSeeder` and `CommunitySeeder` added |
| 2026-03-11 | Vite configuration updated for modular assets |
| 2026-03-15 | Full folder separation into `resources/css/blade/` and `resources/js/blade/` |
| 2026-03-21 | Jenkinsfile added alongside GitHub Actions workflows |
| 2026-03-21 | **MVC structure formalised** — folder structure locked in |
| 2026-03-21 | PR #14 (FolderSeperation) and PR #15 (foldersresources) merged |

---

### Phase 6 — Chat System & Vercel Deployment (22 – 29 Mar 2026)

| Date | What happened |
|---|---|
| 2026-03-22 | **Real-time chat system** built (Pusher + Laravel Echo); PR #16 merged |
| 2026-03-23 | Mobile navigation toggle added |
| 2026-03-24 | `vercel.json` created; Vercel deployment attempts begin |
| 2026-03-24 | Multiple `index.php` and `vercel.json` iterations to get serverless routing working |
| 2026-03-25 | `last_login_at` and `last_login_ip` tracking added to users table |
| 2026-03-25 | jsPDF `undefined` on load fixed |
| 2026-03-26 | **Accommodations feature** started |
| 2026-03-28 | Accommodation pages merged (PR #19, PR #21, PR #22) |

---

### Phase 7 — MVC Refactor & UI Overhaul (1 – 6 Apr 2026)

| Date | What happened |
|---|---|
| 2026-04-01 | Full MVC model adopted; controllers, services, and models reorganised |
| 2026-04-05 | **Complete CRUD operations**, Hotel model, itinerary views added |
| 2026-04-05 | AI prompt rewritten to produce human-sounding, undetectable output |
| 2026-04-05 | **Auth pages redesigned** — split-panel login/register matching project design language |
| 2026-04-05 | Navigation overhauled — gold drawer on mobile, white desktop header |
| 2026-04-05 | Logo sizing, filter invert, and fallback avatar initial fixed across all pages |
| 2026-04-05 | SweetAlert2 integrated globally |
| 2026-04-06 | Profile picture display, avatar consistency, and mobile drawer cleanup |
| 2026-04-06 | Accommodation seeding and `AccommodationSearch` table protection added |
| 2026-04-06 | PR #23 (MVC), PR #24, PR #25 merged |

---

### Phase 8 — Testing & Media (8 – 9 Apr 2026)

| Date | What happened |
|---|---|
| 2026-04-08 | **Unit tests added** for `Trip`, `Booking`, `Coupon`, and `PricingService` |
| 2026-04-08 | Idempotent migrations fixed to prevent duplicate column errors on Neon |
| 2026-04-08 | Comments removed from all PHP files |
| 2026-04-09 | **Camera capture and photo edit modal** added to dashboard |
| 2026-04-09 | Unified viewer/editor — edit tools shown inline when viewing a photo |
| 2026-04-09 | Dashboard stat counts rendered server-side, preventing JS race condition |
| 2026-04-09 | `bookings/show` view, community profile pictures, and author avatars fixed |

---

### Phase 9 — Real Data & Multi-Currency (15 – 18 Apr 2026)

| Date | What happened |
|---|---|
| 2026-04-15 | Auth CSS imports fixed; news fallback added gracefully |
| 2026-04-16 | **Complete system integration** and community features finalised |
| 2026-04-17 | All inline JS/CSS extracted from Blade files into modular assets |
| 2026-04-17 | Database management API (8 endpoints) added for remote migrations |
| 2026-04-18 | **Multi-currency support** shipped — 28+ currencies with live exchange rates |
| 2026-04-18 | **Silent country lock** — destination persists across plan-trip, flights, and accommodations |
| 2026-04-18 | **GPS airport detection** — auto-fills departure city based on user location |
| 2026-04-18 | **Destination insights** — live news, tourist sites, and activities per city |
| 2026-04-18 | Server-side currency conversion and `PriceConverter` service introduced |

---

### Phase 10 — Supabase & PDF Currency Fix (24 Apr 2026)

| Date | What happened |
|---|---|
| 2026-04-24 | Vercel + Supabase deployment configured with auto-migration and seeding |
| 2026-04-24 | **PDF receipt currency fix** — removed hardcoded `$` symbol, replaced with user-selected currency |

---

### Phase 11 — Cleanup & Feature Trim (13 – 14 May 2026)

| Date | What happened |
|---|---|
| 2026-05-13 | All Vercel-related files and references removed |
| 2026-05-13 | **Trimmed to 6 core features**: Auth, Dashboard, AI Trip Planning, Flights, Accommodations, Bookings |
| 2026-05-13 | Footer pages added; Pexels landing destinations; test and migration fixes |
| 2026-05-14 | **Currency picker UI**, footer redesign, profile picture upload, account deletion shipped |
| 2026-05-14 | Inline CSS/JS fully purged from Blade files |

---

### Phase 12 — Final Polish (18 – 23 May 2026)

| Date | What happened |
|---|---|
| 2026-05-18 | Project trimmed — unused routes, seeders, and views removed |
| 2026-05-20 | Codebase minimised and dead code cleaned out |
| 2026-05-23 | **Final git clean** — repository at current production-ready state |

---

### Development Stats

| Metric | Value |
|---|---|
| First commit | 2026-02-01 |
| Latest commit | 2026-05-23 |
| Duration | ~4 months |
| Total commits | 230+ |
| Pull requests merged | 26+ |
| Languages | PHP, JavaScript, CSS, SQL |
| Test files | 7 (auth + profile flows) |

---

## License

[MIT](https://opensource.org/licenses/MIT)

---

## Documentation

- **[vercel.json](vercel.json)** - Vercel deployment configuration for serverless hosting
- **[.env.example](.env.example)** - Environment variables template with all required API keys

---

<p align="center">
  Designed and developed by <strong>Lenard</strong> &nbsp;·&nbsp; 2026
</p>


---

## Developer Guide

See **[CLAUDE.md](CLAUDE.md)** for architecture details, service descriptions, route overview, environment variables, and contribution conventions.

---

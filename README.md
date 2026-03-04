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

**Smart Booking** is a full-stack travel planning application I designed and built from scratch using **Laravel 11**. Users describe how they want to feel on holiday — adventurous, relaxed, romantic, cultural — and the system generates a complete personalised day-by-day itinerary, matching them to destinations, activities, and a daily budget schedule automatically.

The project demonstrates end-to-end application development: database design, back-end business logic, a custom AI algorithm, real-time communication, REST API design, role-based access control, automated testing, and a full CI/CD pipeline.

> Built as a graded university project for the course of **Knyihár Gábor**.

---

## What I Built

### 🧠 Custom Trip Planning Algorithm
Rather than relying on an external paid API, I designed and implemented a **Hybrid Scoring Engine** in pure PHP that generates itineraries with no internet dependency and zero cost per request.

```
Score = 0.4 × MoodMatch + 0.3 × BudgetFit + 0.2 × CompanionFit + 0.1 × Rating
```

---

### ✈️ Dual-Role Flight & Booking System
The application supports two distinct user roles with different capabilities:

| Role | What they can do |
|---|---|
| **Traveller** | Search flights, book seats, manage bookings, cancel with seat return |
| **Agency** | Create and publish flights, manage their own listings, view all incoming bookings |

Booking is wrapped in a **database transaction** to prevent race conditions when two users attempt to book the last available seat simultaneously.

---

### 🔐 Full Authentication System
Built on top of Laravel Breeze, extended with:
- Email verification via cryptographically signed URLs
- Password reset via email token
- Password confirmation gate for sensitive actions
- Client-side session timeout warning (`session-timeout.js`)
- `CheckTraveler` middleware enforcing role-based route access
- Tracking of `last_login_at` and `last_login_ip` per user

---

### 📡 Real-Time Features
Integrated **Pusher** with **Laravel Echo** to deliver real-time in-app messaging between users. The dashboard chat panel updates instantly without polling.

---

### 📸 Media Management
Users can upload travel photos and videos which are processed server-side:
- Auto-generated **300×300 thumbnail** and **800×800 medium** variant via Intervention Image
- Batch delete, favourite toggle, metadata editing, and trip association
- Storage usage stats surfaced on the dashboard

---

### 🗺️ Destinations & Discovery
- Destination database seeded with real locations, mood tags, continent groupings, average daily costs, coordinates, and star ratings
- Up to **3 destinations compared side-by-side** using session-backed state
- Wishlist with summary stats (destinations saved, continents covered, average budget)

---

### 📄 PDF Export
Generated itineraries can be downloaded as formatted PDFs via `barryvdh/laravel-dompdf`, using a dedicated Blade template.

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
| **Database** | SQLite (dev), MySQL (prod) | Relational data storage |
| **Real-Time** | Pusher, Laravel Echo | WebSocket messaging |
| **Images** | Intervention Image | Server-side resizing & thumbnails |
| **PDF** | barryvdh/laravel-dompdf | Itinerary PDF generation |
| **AI (optional)** | Ollama (Mistral 7B, LLaMA 3.1) | Local LLM narrative summaries |
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

resources/views/
├── public/landing.blade.php  # Public landing page
├── dashboard.blade.php       # Authenticated dashboard
├── plan-trip.blade.php       # 4-step wizard
├── flights.blade.php         # Flight search & booking
├── discover.blade.php        # Destination discovery
├── community.blade.php       # Community feed
├── wishlist.blade.php        # Saved destinations
├── layouts/                  # app, guest, navigation partials
├── components/               # 13 reusable Blade components
├── auth/                     # 6 auth views (login, register, verify, reset…)
├── profile/                  # Profile edit with form partials
└── pdf/itinerary-pdf.blade.php

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


cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Open `http://localhost:8000`. A seeded agency account and sample destinations are included.

---

## License

[MIT](https://opensource.org/licenses/MIT)

---

<p align="center">
  Designed and developed by <strong>Lenard</strong> &nbsp;·&nbsp; 2026
</p>

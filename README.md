# Smart Booking

Smart Booking is a Laravel 12 travel planning application for AI-assisted destination ideas, trip planning, flight search, accommodation discovery, bookings, coupons, and multi-currency pricing.

The app is built as a Blade application with focused service classes, Eloquent models, Vite-managed frontend assets, and PHPUnit coverage for core behavior.

## Core Features

- AI travel suggestions through Groq, with graceful fallback behavior when the API key is not configured.
- Flight search through AeroDataBox, with deterministic fallback options for local development and testing.
- Accommodation search, normalization, pricing estimates, and travel news/warning endpoints.
- Trip planning, saved trip moods, dashboard statistics, and recent activity.
- Booking flow for flights and accommodations, including cancellation support and coupon validation.
- Currency preference support with live exchange rates and configured fallback rates.
- Public pages for discovery, landing content, about, contact, privacy, and terms.

## Stack

- PHP 8.2+
- Laravel 12
- Blade, Alpine.js, Tailwind CSS, and Vite
- SQLite by default for local development and tests
- PHPUnit 11
- Groq, AeroDataBox, Geoapify, GNews, NewsAPI, Pexels, and exchange-rate APIs where configured

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm run build
php artisan serve
```

The default local database connection is SQLite. Keep real API keys in `.env`; never commit them.

## Useful Commands

```bash
composer test
npm run build
php artisan optimize:clear
php artisan migrate:fresh --seed
```

## Environment

Required for normal local development:

- `APP_KEY`
- `DB_CONNECTION=sqlite`
- `DB_DATABASE=database/database.sqlite`

Optional integrations:

- `GROQ_API_KEY`
- `AERODATABOX_KEY`
- `GEOAPIFY_KEY`
- `GNEWS_API_KEY`
- `NEWSAPI_KEY`
- `PEXELS_API_KEY`
- `EXCHANGE_RATE_API_URL`

When optional API keys are missing, the app should continue to render usable screens and return controlled fallback responses where supported.

## Project Layout

```text
app/
  Http/Controllers/      Web and JSON endpoint controllers
  Models/                Eloquent domain models
  Services/              Business logic and external API integrations
  View/                  Blade components and view composers

config/                  Laravel and domain configuration
database/                Migrations, factories, seeders, local SQLite file
resources/
  css/                   Tailwind and Blade-specific styles
  js/                    Frontend modules
  views/                 Blade pages, layouts, partials, components
routes/web.php           Public, authenticated, and API-like web routes
tests/                   Feature and unit tests
```

## Testing

The test suite uses an in-memory SQLite database through `phpunit.xml`.

```bash
composer test
```

Add or update tests when changing service behavior, booking rules, pricing, currency formatting, or public API responses.

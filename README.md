# Smart Booking — AI-Powered Travel Platform

## Local Development

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force
npm install
npm run dev
php artisan serve
```

Login: `test@example.com` / `password`

## Vercel Deployment

### 1. Push to GitHub
```bash
git add .
git commit -m "deploy"
git push
```

### 2. Connect to Vercel
- Import the GitHub repo at vercel.com
- Framework: **Other**
- Build command: `npm run build`
- Output directory: `public`

### 3. Set Environment Variables in Vercel Dashboard

| Variable | Value |
|---|---|
| `APP_KEY` | Run `php artisan key:generate --show` locally |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | Your Vercel URL |
| `DATABASE_URL` | Neon/Supabase Postgres URL |
| `GROQ_API_KEY` | Your Groq API key |
| `SESSION_DRIVER` | `cookie` |
| `CACHE_DRIVER` | `array` |

### 4. Database (Neon Postgres — free tier)
1. Create account at neon.tech
2. Create a new project
3. Copy the connection string
4. Set as `DATABASE_URL` in Vercel

The app auto-migrates and seeds on first cold start.

## Tech Stack
- Laravel 12 · PHP 8.2
- SQLite (local) · PostgreSQL (production)
- Vite · Alpine.js · Tailwind CSS
- Groq AI · Pusher (optional)
- DomPDF · Leaflet Maps

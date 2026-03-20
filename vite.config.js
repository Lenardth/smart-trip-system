import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/pages/base.css',
                'resources/css/pages/login.css',
                'resources/css/pages/landing.css',
                'resources/css/pages/dashboard.css',
                'resources/css/pages/discover.css',
                'resources/css/pages/community.css',
                'resources/css/pages/plan-trip.css',
                'resources/js/app.js',
                'resources/js/pages/login.js',
                'resources/js/pages/landing.js',
                'resources/js/pages/dashboard.js',
                'resources/js/pages/discover.js',
                'resources/js/pages/community.js',
                'resources/js/pages/plan-trip.js',
                'resources/js/pages/destinations.js',
                'resources/js/pages/wishlist.js',
            ],
            refresh: true,
        }),
    ],
});
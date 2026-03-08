import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/pages/landing.js',
                'resources/js/pages/dashboard.js',
                'resources/js/pages/discover.js',
                'resources/js/pages/plan-trip.js',
                'resources/js/pages/wishlist.js',
                'resources/js/pages/community.js',
                'resources/js/pages/destinations.js',
                'resources/css/pages/community.css',
                'resources/css/pages/discover.css',
                'resources/css/pages/plan-trip.css',
                'resources/css/pages/landing.css',
            ],
            refresh: true,
        }),
    ],
});

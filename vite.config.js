import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/bootstrap.js',
                'resources/js/session-timeout.js',

                'resources/css/blade/base.css',
                'resources/js/blade/base.js',
                'resources/js/blade/global.js',
                'resources/js/blade/login.js',

                'resources/css/blade/layouts/app.css',
                'resources/css/blade/layouts/guest.css',

                'resources/css/blade/auth/confirm-password.css',
                'resources/css/blade/auth/forgot-password.css',
                'resources/css/blade/auth/login.css',
                'resources/css/blade/auth/register.css',
                'resources/css/blade/auth/reset-password.css',
                'resources/css/blade/auth/verify-email.css',
                'resources/js/blade/auth/register.js',

                'resources/css/blade/accommodations/index.css',
                'resources/js/blade/accommodations/index.js',

                'resources/css/blade/chat/index.css',
                'resources/js/blade/chat/index.js',

                'resources/css/blade/community/index.css',
                'resources/js/blade/community/index.js',

                'resources/css/blade/dashboard/index.css',
                'resources/js/blade/dashboard/index.js',

                'resources/css/blade/destinations/index.css',
                'resources/js/blade/destinations/index.js',

                'resources/css/blade/discover/index.css',
                'resources/js/blade/discover/index.js',

                'resources/css/blade/flights/index.css',
                'resources/js/blade/flights/index.js',

                'resources/css/blade/landing/index.css',
                'resources/js/blade/landing/index.js',

                'resources/css/blade/notifications/index.css',
                'resources/js/blade/notifications/index.js',

                'resources/css/blade/pdf/itinerary.css',
                'resources/js/blade/pdf/itinerary.js',

                'resources/css/blade/plan-trip/index.css',
                'resources/js/blade/plan-trip/index.js',

                'resources/css/blade/profile/edit.css',
                'resources/js/blade/profile/edit.js',

                'resources/css/blade/wishlist/index.css',
                'resources/js/blade/wishlist/index.js',
            ],
            refresh: true,
        }),
    ],
});

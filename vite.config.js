import laravel from 'laravel-vite-plugin';
import {
    defineConfig
} from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/blade/shared/travel-advisory.js',
                'resources/js/blade/shared/currency.js',
            ],
            refresh: true,
        }),
    ],
});
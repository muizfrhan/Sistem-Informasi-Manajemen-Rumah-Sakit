import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Halaman internal (dashboard, master, admin) — tidak berubah.
                'resources/css/app.css',
                'resources/js/app.js',
                // Landing page publik — bundle terpisah, design system sendiri.
                'resources/css/landing.css',
                'resources/js/landing/main.js',
            ],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

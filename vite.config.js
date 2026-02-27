import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['public/css/app.css', 'public/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '192.168.101.196',
        cors: {
            origin: '*',
            method: ['GET', 'HEAD', 'PUT', 'POST', 'DELETE', 'PATCH'],
            allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept'],
        },
    },
});

import { defineConfig } from 'vite'; // Adicione esta linha
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/calendar.ts',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        origin: 'http://172.17.16.45:5173',
        hmr: {
            host: '172.17.16.45',
        },
        cors: true,
    },
});
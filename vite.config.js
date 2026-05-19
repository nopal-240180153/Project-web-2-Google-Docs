import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'; // gunakan paket resmi @vitejs/plugin-vue

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    // === TAMBAHKAN BLOK SERVER INI UNTUK MENYEMBUHKAN EROR CORS VIA IP ===
    server: {
        host: '0.0.0.0',
        cors: true,
        hmr: {
            host: '192.168.1.6', // Sesuaikan dengan IP laptopmu yang ada di gambar console
        },
    },
});
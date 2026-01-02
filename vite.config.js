import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";

export default defineConfig({
    base: '/', // Base URL cho production build
    server: {
        base: '/',
        host: 'localhost', 
        port: 5173,
        strictPort: true,
        cors: true,
        hmr: {
            host: 'localhost',
            protocol: 'ws',
        },
        watch: {
            usePolling: false,
        },
        // Proxy for XAMPP local development
        origin: 'http://localhost:5173',
    },
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        vue(),
    ],
    optimizeDeps: {
        include: ['vue', 'vue-router', 'vuex', 'axios'],
    },
    build: {
        // Thêm cấu hình build để tránh lỗi minification
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: false,
            },
        },
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
        },
    },
});
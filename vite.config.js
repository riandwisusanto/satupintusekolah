import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import compression from 'vite-plugin-compression'

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        compression({ algorithm: 'brotliCompress' })
    ],
    resolve: {
        alias: {
        '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    build: {
        sourcemap: false,
        minify: 'terser',
        terserOptions: {
            compress: {
            drop_console: true,
            drop_debugger: true,
            },
        },
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
            manualChunks(id) {
                if (id.includes('node_modules')) {
                if (id.includes('vue')) return 'vue';
                if (id.includes('pinia')) return 'pinia';
                if (id.includes('vue-router')) return 'router';
                if (id.includes('chart.js')) return 'chart';
                }
            },
            },
        },
    },
});

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
    ],
    // esbuild: {
    //     drop: ['console', 'debugger'],
    // },
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js',
            '@': '/resources/js',
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    // Vue core libraries
                    'vendor-vue': ['vue', 'vue-router', 'pinia', 'vue-i18n'],
                    // Chart and Calendar libraries
                    'vendor-ui': ['chart.js', 'vue-chartjs', '@fullcalendar/core', '@fullcalendar/daygrid', '@fullcalendar/interaction', '@fullcalendar/vue3'],
                    // Utility libraries
                    'vendor-utils': ['axios', 'lodash'],
                    // Icon library (large but infrequently changes)
                    'lucide-vue-next': ['lucide-vue-next'],
                },
            },
        },
        // Builder components are large but acceptable (gzip: ~195KB)
        chunkSizeWarningLimit: 700,
    },
    optimizeDeps: {
        include: ['cropperjs', '@fullcalendar/core', '@fullcalendar/daygrid', '@fullcalendar/interaction', '@fullcalendar/vue3'],
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'localhost',
            port: 5173,
            protocol: 'ws',
        },
        cors: true,
    },
});

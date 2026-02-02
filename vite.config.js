import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
    ],
    esbuild: {
        drop: ['console', 'debugger'],
    },
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js',
            '@': '/resources/js',
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    // 1. Lucide icons - bundle ALL icons together to prevent waterfall loading
                    if (id.includes('lucide-vue-next')) {
                        return 'vendor-icons';
                    }

                    // 2. UI Framework components (Radix)
                    if (id.includes('radix-vue') || id.includes('class-variance-authority')) {
                        return 'vendor-ui-framework';
                    }

                    // 3. FullCalendar
                    if (id.includes('@fullcalendar')) {
                        return 'vendor-ui-calendar';
                    }

                    // 4. Chart.js
                    if (id.includes('chart.js')) {
                        return 'vendor-ui-charts';
                    }

                    // 5. Utility libraries
                    if (id.includes('axios') || id.includes('lodash') || id.includes('zod') || id.includes('dayjs')) {
                        return 'vendor-utils';
                    }

                    // 6. Vue core (Put last to avoid catching everything with 'vue' in path)
                    if (id.includes('node_modules/vue/') ||
                        id.includes('node_modules/vue-router/') ||
                        id.includes('node_modules/pinia/') ||
                        id.includes('node_modules/vue-i18n/') ||
                        id.includes('node_modules/@vue/')) {
                        return 'vendor-vue-core';
                    }
                },
            },
        },
        chunkSizeWarningLimit: 1000,
        sourcemap: false,
        minify: 'esbuild',
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

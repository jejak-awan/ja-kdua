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
                    // 1. Lucide icons (Very specific)
                    if (id.includes('lucide-vue-next')) {
                        return 'vendor-icons';
                    }

                    // 2. Tiptap and related extensions (Specific)
                    if (id.includes('@tiptap') || id.includes('prosemirror') || id.includes('lowlight') || id.includes('dompurify')) {
                        return 'vendor-tiptap';
                    }

                    // 3. UI Framework components (Radix)
                    if (id.includes('radix-vue') || id.includes('class-variance-authority')) {
                        return 'vendor-ui-framework';
                    }

                    // 4. FullCalendar
                    if (id.includes('@fullcalendar')) {
                        return 'vendor-ui-calendar';
                    }

                    // 5. Chart.js
                    if (id.includes('chart.js')) {
                        return 'vendor-ui-charts';
                    }

                    // 6. Utility libraries
                    if (id.includes('axios') || id.includes('lodash') || id.includes('zod') || id.includes('dayjs')) {
                        return 'vendor-utils';
                    }

                    // 7. Vue core (Put last to avoid catching everything with 'vue' in path)
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
        // Builder components are large but acceptable
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

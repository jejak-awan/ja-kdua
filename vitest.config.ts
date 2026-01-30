import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
    plugins: [vue()],
    test: {
        globals: true,
        environment: 'happy-dom',
        include: ['resources/js/**/*.{test,spec}.{ts,tsx}', 'tests/js/**/*.{test,spec}.{ts,tsx}'],
        exclude: ['node_modules', 'vendor'],
        coverage: {
            provider: 'v8',
            reporter: ['text', 'json', 'html'],
            include: ['resources/js/**/*.{ts,vue}'],
            exclude: ['resources/js/**/*.d.ts', 'resources/js/**/*.spec.ts', 'resources/js/**/*.test.ts'],
        },
        alias: {
            '@': resolve(__dirname, './resources/js'),
        },
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, './resources/js'),
        },
    },
})

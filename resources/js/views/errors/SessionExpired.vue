<template>
    <ErrorLayout>
        <template #icon>
            <div class="h-20 w-20 rounded-2xl bg-orange-500/10 border border-orange-500/20 flex items-center justify-center">
                <svg class="h-10 w-10 text-orange-600 dark:text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                </svg>
            </div>
        </template>

        <template #title>
            419
        </template>

        <template #message>
            {{ t('features.errors.419.title') }}
        </template>

        <template #description>
            <span v-if="route.query.reason === 'concurrent'">
                {{ t('features.errors.419.concurrent') }}
            </span>
            <span v-else-if="route.query.reason === 'timeout'">
                {{ t('features.errors.419.timeout') }}
            </span>
            <span v-else>
                {{ t('features.errors.419.message') }}
            </span>
        </template>

        <template #actions>
            <button
                @click="login"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-xl text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-orange-500 shadow-sm transition-all"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                {{ t('features.errors.419.login') }}
            </button>
            <button
                @click="refresh"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-border text-sm font-medium rounded-xl text-foreground bg-background hover:bg-muted/50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-orange-500 transition-all"
            >
                <svg class="w-4 h-4 mr-2 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                {{ t('features.errors.419.refresh') }}
            </button>
        </template>
        
        <template #footer>
            <div class="flex items-center justify-center gap-3">
                 <span>Error Code: 419</span>
                <span class="w-0.5 h-3 bg-border"></span>
                <span class="font-mono text-[10px] opacity-50">{{ traceId }}</span>
            </div>
        </template>
    </ErrorLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useI18n } from 'vue-i18n';
import ErrorLayout from '@/layouts/ErrorLayout.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { t } = useI18n();
const traceId = ref(`TRC-${Date.now().toString().slice(-6)}-${Math.random().toString(36).substring(7).toUpperCase()}`);

const login = () => {
    // Get redirect from route query (set by API interceptor)
    const redirect = route.query.redirect || '/admin';
    
    // Clean state
    authStore.logout();
    
    router.push({
        path: '/login',
        // Don't redirect back to error pages
        query: { 
            redirect: redirect !== '/419' && !redirect.includes('/419') ? redirect : '/admin' 
        }
    });
};

const refresh = () => {
    window.location.reload();
};
</script>

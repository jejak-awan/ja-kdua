<template>
    <ErrorLayout>
        <template #icon>
            <div class="h-20 w-20 rounded-2xl bg-orange-500/10 border border-orange-500/20 flex items-center justify-center">
                <Fingerprint class="h-10 w-10 text-orange-600 dark:text-orange-500" />
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
                class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-2xl text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-orange-500 shadow-sm transition-all active:scale-95"
            >
                <LogIn class="w-4 h-4 mr-2" />
                {{ t('features.errors.419.login') }}
            </button>
            <button
                @click="refresh"
                class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-border text-sm font-medium rounded-2xl text-foreground bg-muted hover:bg-muted/80 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-orange-500 transition-all active:scale-95"
            >
                <RefreshCw class="w-4 h-4 mr-2 text-muted-foreground" />
                {{ t('features.errors.419.refresh') }}
            </button>
        </template>
        
        <template #footer>
            <div class="flex items-center justify-center gap-3">
                 <span>Error Code: 419</span>
                <span class="w-1 h-1 rounded-full bg-border"></span>
                <span class="font-mono opacity-60">{{ traceId }}</span>
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
import { Fingerprint, LogIn, RefreshCw } from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { t } = useI18n();
const traceId = ref(`TRC-${Date.now().toString().slice(-6)}-${Math.random().toString(36).substring(7).toUpperCase()}`);

const login = () => {
    const redirect = route.query.redirect || '/admin';
    authStore.logout();
    router.push({
        path: '/login',
        query: { 
            redirect: redirect !== '/419' && !redirect.includes('/419') ? redirect : '/admin' 
        }
    });
};

const refresh = () => {
    window.location.reload();
};
</script>

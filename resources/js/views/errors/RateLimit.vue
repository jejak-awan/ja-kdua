<template>
    <ErrorLayout>
        <template #icon>
            <div class="h-20 w-20 rounded-2xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center">
                <svg class="h-10 w-10 text-purple-600 dark:text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </template>

        <template #title>
            429
        </template>

        <template #message>
            {{ t('features.errors.429.title') }}
        </template>

        <template #description>
            {{ t('features.errors.429.message') }}
        </template>

        <template #details>
            <div v-if="retryAfter" class="mt-2 mb-4 bg-purple-500/10 border border-purple-500/20 rounded-xl p-4 flex flex-col items-center justify-center">
                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400 mb-1">
                    {{ t('features.errors.429.retryAfter', { seconds: '' }).replace(':', '').trim() }}
                </span>
                <div class="text-2xl font-mono font-bold text-purple-600 dark:text-purple-400">
                    {{ retryAfter }}s
                </div>
            </div>
            <div v-else class="text-center text-sm text-muted-foreground/80 my-4">
                {{ t('features.errors.429.description') }}
            </div>
        </template>

        <template #actions>
            <button
                @click="goBack"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-border text-sm font-medium rounded-xl text-foreground bg-background hover:bg-muted/50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-purple-500 transition-all"
            >
                <svg class="w-4 h-4 mr-2 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ t('features.errors.404.back') }}
            </button>
            <router-link
                to="/"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-xl text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-purple-500 shadow-sm transition-all"
            >
                <svg class="w-4 h-4 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                {{ t('features.errors.404.home') }}
            </router-link>
        </template>

        <template #footer>
            <div class="flex items-center justify-center gap-3">
                 <span>Error Code: 429</span>
                <span class="w-0.5 h-3 bg-border"></span>
                <span class="font-mono text-[10px] opacity-50">{{ traceId }}</span>
            </div>
        </template>
    </ErrorLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import ErrorLayout from '@/layouts/ErrorLayout.vue';

const router = useRouter();
const { t } = useI18n();
const traceId = ref(`TRC-${Date.now().toString().slice(-6)}-${Math.random().toString(36).substring(7).toUpperCase()}`);

const retryAfter = computed(() => {
    // Retry-After header or value passed in state
    return window.history.state?.retryAfter || null;
});

const goBack = () => {
  if (window.history.length > 1) {
    router.go(-1);
  } else {
    router.push('/');
  }
};
</script>

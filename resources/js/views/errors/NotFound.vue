<template>
    <ErrorLayout>
        <template #icon>
            <div class="h-20 w-20 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center">
                <FileQuestion class="h-10 w-10 text-blue-600 dark:text-blue-500" />
            </div>
        </template>

        <template #title>
            404
        </template>

        <template #message>
            {{ t('features.errors.404.title') }}
        </template>

        <template #description>
            {{ t('features.errors.404.message') }}
        </template>

        <template #actions>
            <router-link
                to="/"
                class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-2xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 shadow-sm transition-all active:scale-95"
            >
                <Home class="w-4 h-4 mr-2" />
                {{ t('features.errors.404.home') }}
            </router-link>

            <button
                @click="goBack"
                class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-border text-sm font-medium rounded-2xl text-foreground bg-muted hover:bg-muted/80 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 transition-all active:scale-95"
            >
                <ArrowLeft class="w-4 h-4 mr-2 text-muted-foreground" />
                {{ t('features.errors.404.back') }}
            </button>
        </template>

        <template #footer>
            <div class="flex items-center justify-center gap-3">
                <span>Error Code: 404</span>
                <span class="w-1 h-1 rounded-full bg-border"></span>
                <span class="font-mono opacity-60">{{ traceId }}</span>
            </div>
        </template>
    </ErrorLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import ErrorLayout from '@/layouts/ErrorLayout.vue';
import { FileQuestion, Home, ArrowLeft } from 'lucide-vue-next';

const router = useRouter();
const { t } = useI18n();
const searchQuery = ref('');
const traceId = ref(`TRC-${Date.now().toString().slice(-6)}-${Math.random().toString(36).substring(7).toUpperCase()}`);

const popularLinks = computed(() => [
  { path: '/', label: t('common.navigation.breadcrumbs.home') || 'Home' },
  { path: '/blog', label: 'Blog' },
  { path: '/about', label: 'About Us' },
  { path: '/contact', label: 'Contact Support' },
]);

const goBack = () => {
  if (window.history.length > 1) {
    router.go(-1);
  } else {
    router.push('/');
  }
};

const search = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/search', query: { q: searchQuery.value } });
  }
};
</script>


<template>
    <ErrorLayout>
        <template #icon>
            <div class="h-24 w-24 rounded-[2rem] bg-blue-50 dark:bg-blue-900/10 flex items-center justify-center transform rotate-3 hover:rotate-6 transition-transform duration-300">
                <svg class="h-12 w-12 text-blue-600 dark:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
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

        <template #details>
            <!-- Search -->
            <div class="mb-6">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-muted-foreground group-focus-within:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        v-model="searchQuery"
                        type="text"
                        :placeholder="t('features.errors.404.searchPlaceholder')"
                        class="block w-full pl-10 pr-3 py-2.5 border border-border bg-muted/30 dark:bg-muted/10 rounded-xl leading-5 placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm transition-all"
                        @keyup.enter="search"
                    />
                </div>
            </div>

            <!-- Popular Links -->
            <div class="bg-gray-50/50 dark:bg-zinc-800/30 border border-border/50 rounded-xl p-4">
                <h3 class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-3 px-1">
                    {{ t('features.errors.404.popular') }}
                </h3>
                <div class="grid grid-cols-2 gap-2">
                    <router-link
                        v-for="link in popularLinks"
                        :key="link.path"
                        :to="link.path"
                        class="flex items-center px-3 py-2 text-sm font-medium text-foreground/80 hover:text-primary hover:bg-white dark:hover:bg-zinc-800 rounded-lg transition-all group"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-border group-hover:bg-primary transition-colors mr-3 flex-shrink-0"></span>
                        <span class="truncate">{{ link.label }}</span>
                    </router-link>
                </div>
            </div>
        </template>

        <template #actions>
            <button
                @click="goBack"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-border text-sm font-medium rounded-xl text-foreground bg-background hover:bg-muted/50 hover:border-muted-foreground/30 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary transition-all"
            >
                <svg class="w-4 h-4 mr-2 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ t('features.errors.404.back') }}
            </button>
            <router-link
                to="/"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-xl text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary shadow-sm hover:shadow transition-all"
            >
                <svg class="w-4 h-4 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                {{ t('features.errors.404.home') }}
            </router-link>
        </template>

        <template #footer>
            <div class="flex items-center justify-center gap-3">
                <span>Error Code: 404</span>
                <span class="w-0.5 h-3 bg-border"></span>
                <a href="#" @click.prevent="router.back()" class="hover:text-primary transition-colors underline decoration-dotted underline-offset-2">Report Broken Link</a>
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
const searchQuery = ref('');

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


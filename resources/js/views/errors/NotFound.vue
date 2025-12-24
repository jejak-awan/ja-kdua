<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full text-center">
      <!-- Error Code -->
      <div class="mb-8">
        <h1 class="text-9xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-primary-400 dark:from-primary-400 dark:to-primary-600">
          404
        </h1>
      </div>

      <!-- Error Message -->
      <div class="mb-8">
        <h2 class="text-3xl font-bold text-foreground mb-4">
          {{ t('features.errors.404.title') }}
        </h2>
        <p class="text-lg text-muted-foreground mb-2">
          {{ t('features.errors.404.message') }}
        </p>
        <p class="text-sm text-muted-foreground">
          {{ t('features.errors.404.description') }}
        </p>
      </div>

      <!-- Illustration -->
      <div class="mb-8 flex justify-center">
        <svg class="w-64 h-64 text-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>

      <!-- Actions -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
        <button
          @click="goBack"
          class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          {{ t('features.errors.404.back') }}
        </button>
        <router-link
          to="/"
          class="inline-flex items-center justify-center px-6 py-3 border border-input text-base font-medium rounded-lg text-foreground bg-card hover:bg-muted focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          {{ t('features.errors.404.home') }}
        </router-link>
      </div>

      <!-- Search -->
      <div class="max-w-md mx-auto mb-8">
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="t('features.errors.404.searchPlaceholder')"
            class="w-full px-4 py-3 pr-12 border border-input rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-card text-foreground placeholder-gray-500 dark:placeholder-gray-400"
            @keyup.enter="search"
          />
          <button
            @click="search"
            class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 text-muted-foreground hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Popular Links -->
      <div class="text-left max-w-md mx-auto">
        <h3 class="text-sm font-semibold text-foreground mb-3">
          {{ t('features.errors.404.popular') }}
        </h3>
        <div class="space-y-2">
          <router-link
            v-for="link in popularLinks"
            :key="link.path"
            :to="link.path"
            class="block px-4 py-2 text-sm text-foreground hover:bg-accent dark:hover:bg-card rounded-lg transition-colors"
          >
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            {{ link.label }}
          </router-link>
        </div>
      </div>

      <!-- Error Code for Reference -->
      <div class="mt-8 text-xs text-muted-foreground">
        Error Code: 404 | {{ new Date().toISOString() }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'; // Added computed for i18n reactivity if needed, though simple setup is fine
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

const router = useRouter();
const { t } = useI18n();
const searchQuery = ref('');

const popularLinks = computed(() => [
  { path: '/', label: t('common.navigation.breadcrumbs.home') },
  { path: '/blog', label: t('features.frontend.nav.blog') || 'Blog' }, // Assuming these exist, otherwise fallback
  { path: '/about', label: t('features.frontend.nav.about') || 'About Us' },
  { path: '/contact', label: t('features.frontend.nav.contact') || 'Contact' },
]);

// Note: I assumed features.frontend.nav keys exist from previous steps. 
// If not, I'll check.
// Checking my history: step 2056 created frontend.json with "nav": { "home":..., "blog":..., "about":..., "contact":... }
// So this is correct.

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


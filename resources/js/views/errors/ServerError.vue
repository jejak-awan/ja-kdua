<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-red-50 to-orange-50 dark:from-gray-900 dark:to-red-900 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full text-center">
      <!-- Error Code -->
      <div class="mb-8">
        <h1 class="text-9xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-orange-500 dark:from-red-400 dark:to-orange-400">
          500
        </h1>
      </div>

      <!-- Error Icon -->
      <div class="mb-8 flex justify-center">
        <div class="relative">
          <svg class="w-32 h-32 text-red-500 dark:text-red-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <div class="absolute top-0 right-0 w-4 h-4 bg-red-500 rounded-full animate-ping"></div>
        </div>
      </div>

      <!-- Error Message -->
      <div class="mb-8">
        <h2 class="text-3xl font-bold text-foreground mb-4">
          {{ t('features.errors.500.title') }}
        </h2>
        <p class="text-lg text-muted-foreground mb-2">
          {{ t('features.errors.500.message') }}
        </p>
        <p class="text-sm text-muted-foreground">
          {{ t('features.errors.500.description') }}
        </p>
      </div>

      <!-- Error Details (Collapsible) -->
      <div v-if="errorDetails" class="mb-8 max-w-lg mx-auto">
        <button
          @click="showDetails = !showDetails"
          class="text-sm text-muted-foreground hover:text-foreground dark:hover:text-white transition-colors flex items-center justify-center mx-auto"
        >
          <span>{{ showDetails ? t('features.errors.500.hideDetails') : t('features.errors.500.showDetails') }}</span>
          <svg
            class="w-4 h-4 ml-1 transform transition-transform"
            :class="{ 'rotate-180': showDetails }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        
        <transition name="slide-fade">
          <div v-if="showDetails" class="mt-4 p-4 bg-red-500/20 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-left">
            <pre class="text-xs text-foreground overflow-x-auto">{{ errorDetails }}</pre>
          </div>
        </transition>
      </div>

      <!-- Actions -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
        <button
          @click="retry"
          :disabled="retrying"
          class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg
            class="w-5 h-5 mr-2"
            :class="{ 'animate-spin': retrying }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          {{ retrying ? t('features.errors.500.retrying') : t('features.errors.500.retry') }}
        </button>
        
        <router-link
          to="/"
          class="inline-flex items-center justify-center px-6 py-3 border border-input text-base font-medium rounded-lg text-foreground bg-card hover:bg-muted focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          {{ t('features.errors.404.home') }}
        </router-link>
      </div>

      <!-- Report Issue -->
      <div class="mb-8">
        <button
          @click="reportIssue"
          class="text-sm text-muted-foreground hover:text-red-600 dark:hover:text-red-400 transition-colors"
        >
          <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
          {{ t('features.errors.500.report') }}
        </button>
      </div>

      <!-- Status Check -->
      <div class="text-sm text-muted-foreground">
        <p class="mb-2">{{ t('features.errors.500.checkStatus') }}</p>
        <div class="flex items-center justify-center gap-2">
          <div class="flex items-center gap-1">
            <div class="w-2 h-2 rounded-full" :class="serverStatus.api ? 'bg-green-500' : 'bg-red-500'"></div>
            <span>{{ t('features.errors.500.api') }}</span>
          </div>
          <div class="flex items-center gap-1">
            <div class="w-2 h-2 rounded-full" :class="serverStatus.database ? 'bg-green-500' : 'bg-red-500'"></div>
            <span>{{ t('features.errors.500.database') }}</span>
          </div>
          <div class="flex items-center gap-1">
            <div class="w-2 h-2 rounded-full" :class="serverStatus.cache ? 'bg-green-500' : 'bg-red-500'"></div>
            <span>{{ t('features.errors.500.cache') }}</span>
          </div>
        </div>
      </div>

      <!-- Error Code for Reference -->
      <div class="mt-8 text-xs text-muted-foreground">
        Error Code: 500 | {{ errorId }} | {{ new Date().toISOString() }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

const router = useRouter();
const { t } = useI18n();
const showDetails = ref(false);
const retrying = ref(false);
const errorId = ref(generateErrorId());
const errorDetails = ref(getErrorDetails());

const serverStatus = ref({
  api: false,
  database: false,
  cache: false,
});

function generateErrorId() {
  return `ERR-${Date.now()}-${Math.random().toString(36).substring(7).toUpperCase()}`;
}

function getErrorDetails() {
  // Get error details from route state if available
  const state = window.history.state;
  if (state?.errorDetails) {
    return state.errorDetails;
  }
  return null;
}

const retry = async () => {
  retrying.value = true;
  
  // Wait a moment for effect
  await new Promise(resolve => setTimeout(resolve, 1000));
  
  // Reload the page
  window.location.reload();
};

const reportIssue = () => {
  const subject = encodeURIComponent(`Server Error Report - ${errorId.value}`);
  const body = encodeURIComponent(`Error ID: ${errorId.value}\nTimestamp: ${new Date().toISOString()}\nURL: ${window.location.href}\n\nDeskripsi masalah:\n`);
  window.location.href = `mailto:support@jejakawan.com?subject=${subject}&body=${body}`;
};

const checkServerStatus = async () => {
  // Check API status
  try {
    await fetch('/api/v1/health', { method: 'HEAD' });
    serverStatus.value.api = true;
  } catch (e) {
    serverStatus.value.api = false;
  }

  // Simplified status check (in real app, this would be separate endpoints)
  serverStatus.value.database = serverStatus.value.api;
  serverStatus.value.cache = serverStatus.value.api;
};

onMounted(() => {
  checkServerStatus();
});
</script>

<style scoped>
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}
</style>


<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-yellow-50 to-amber-50 dark:from-gray-900 dark:to-yellow-900 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full text-center">
      <!-- Error Code -->
      <div class="mb-8">
        <h1 class="text-9xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-yellow-600 to-amber-500 dark:from-yellow-400 dark:to-amber-400">
          403
        </h1>
      </div>

      <!-- Error Icon -->
      <div class="mb-8 flex justify-center">
        <svg class="w-32 h-32 text-yellow-500 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
      </div>

      <!-- Error Message -->
      <div class="mb-8">
        <h2 class="text-3xl font-bold text-foreground mb-4">
          {{ t('features.errors.403.title') }}
        </h2>
        <p class="text-lg text-muted-foreground dark:text-gray-400 mb-2">
          {{ t('features.errors.403.message') }}
        </p>
        <p class="text-sm text-muted-foreground dark:text-muted-foreground">
          {{ t('features.errors.403.description') }}
        </p>
      </div>

      <!-- Reason (if available) -->
      <div v-if="reason" class="mb-8 max-w-lg mx-auto">
        <div class="p-4 bg-yellow-500/10 border border-yellow-200 dark:border-yellow-800 rounded-lg">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-left">
              <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-400 mb-1">
                {{ t('features.errors.403.reason') }}
              </h3>
              <p class="text-sm text-yellow-700 dark:text-yellow-500">
                {{ reason }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- User Status -->
      <div v-if="user" class="mb-8">
        <div class="inline-flex items-center px-4 py-2 bg-card dark:bg-gray-800 border border-border rounded-lg shadow-sm">
          <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span class="text-sm text-foreground">
            {{ t('features.errors.403.loggedInAs') }} <strong>{{ user.name }}</strong>
          </span>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
        <button
          v-if="!user"
          @click="goToLogin"
          class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
          </svg>
          {{ t('features.errors.403.login') }}
        </button>

        <button
          v-else
          @click="logout"
          class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          {{ t('features.errors.403.relogin') }}
        </button>

        <button
          @click="goBack"
          class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-lg text-foreground bg-card dark:bg-gray-800 hover:bg-muted dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          {{ t('features.errors.404.back') }}
        </button>
      </div>

      <!-- Contact Support -->
      <div class="mb-8">
        <p class="text-sm text-muted-foreground dark:text-gray-400 mb-3">
          {{ t('features.errors.403.support') }}
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <a
            href="mailto:admin@jejakawan.com"
            class="inline-flex items-center text-sm text-yellow-600 dark:text-yellow-400 hover:text-yellow-700 dark:hover:text-yellow-300 transition-colors"
          >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            admin@jejakawan.com
          </a>
          <router-link
            to="/contact"
            class="inline-flex items-center text-sm text-yellow-600 dark:text-yellow-400 hover:text-yellow-700 dark:hover:text-yellow-300 transition-colors"
          >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            {{ t('features.errors.403.contactForm') }}
          </router-link>
        </div>
      </div>

      <!-- Required Permissions -->
      <div v-if="requiredPermissions.length" class="max-w-md mx-auto mb-8">
        <div class="text-left p-4 bg-muted dark:bg-gray-800 border border-border rounded-lg">
          <h3 class="text-sm font-semibold text-foreground mb-2">
            {{ t('features.errors.403.requiredPermissions') }}
          </h3>
          <ul class="space-y-1">
            <li
              v-for="permission in requiredPermissions"
              :key="permission"
              class="flex items-center text-sm text-muted-foreground dark:text-gray-400"
            >
              <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ permission }}
            </li>
          </ul>
        </div>
      </div>

      <!-- Error Code for Reference -->
      <div class="mt-8 text-xs text-gray-400 dark:text-muted-foreground">
        Error Code: 403 | {{ new Date().toISOString() }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useI18n } from 'vue-i18n';

const router = useRouter();
const authStore = useAuthStore();
const { t } = useI18n();

const user = computed(() => authStore.user);
const reason = ref(getReasonFromState());
const requiredPermissions = ref(getRequiredPermissionsFromState());

function getReasonFromState() {
  const state = window.history.state;
  return state?.reason || null;
}

function getRequiredPermissionsFromState() {
  const state = window.history.state;
  return state?.requiredPermissions || [];
}

const goBack = () => {
  if (window.history.length > 1) {
    router.go(-1);
  } else {
    router.push('/');
  }
};

const goToLogin = () => {
  router.push({ 
    path: '/login', 
    query: { redirect: window.location.pathname } 
  });
};

const logout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>


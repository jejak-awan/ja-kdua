<template>
    <ErrorLayout>
        <template #icon>
            <div class="h-20 w-20 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center">
                <svg class="h-10 w-10 text-amber-600 dark:text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
        </template>

        <template #title>
            403
        </template>

        <template #message>
            {{ t('features.errors.403.title') }}
        </template>

        <template #description>
            {{ t('features.errors.403.message') }}
        </template>

        <template #details>
            <div class="space-y-4">
                <!-- Reason -->
                <div v-if="reason" class="flex flex-col bg-amber-500/10 border border-amber-500/20 rounded-xl p-3">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-500 mb-1">{{ t('features.errors.403.reason') }}</span>
                    <p class="text-sm text-foreground leading-snug break-words">
                        {{ reason }}
                    </p>
                </div>

                <!-- Required Permissions -->
                <div v-if="requiredPermissions.length" class="bg-muted border border-border rounded-xl p-3">
                    <h3 class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-2 flex items-center gap-1.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ t('features.errors.403.requiredPermissions') }}
                    </h3>
                    <div class="flex flex-wrap gap-1.5">
                        <span 
                            v-for="permission in requiredPermissions" 
                            :key="permission"
                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-card border border-border text-foreground"
                        >
                            {{ permission }}
                        </span>
                    </div>
                </div>
            </div>
        </template>

        <template #actions>
            <button
                v-if="!user"
                @click="goToLogin"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-xl text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-amber-500 shadow-sm transition-all"
            >
                <svg class="w-4 h-4 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                {{ t('features.errors.403.login') }}
            </button>

            <button
                v-else
                @click="logout"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-xl text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-amber-500 shadow-sm transition-all"
            >
                <svg class="w-4 h-4 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                {{ t('features.errors.403.relogin') }}
            </button>

            <button
                @click="goBack"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-border text-sm font-medium rounded-xl text-foreground bg-background hover:bg-muted/50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-amber-500 transition-all"
            >
                <svg class="w-4 h-4 mr-2 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ t('features.errors.404.back') }}
            </button>
        </template>

        <template #footer>
             <div class="flex flex-col items-center gap-2">
                <div v-if="user" class="inline-flex items-center px-2.5 py-1 bg-muted/50 rounded-lg text-xs font-mono text-muted-foreground">
                    <span class="opacity-70 mr-1.5">{{ t('features.errors.403.loggedInAs') }}</span>
                    <strong class="text-foreground">{{ user.name }}</strong>
                </div>
                <div class="flex items-center gap-3 mt-1">
                    <span>Error Code: 403</span>
                    <span class="w-0.5 h-3 bg-border"></span>
                    <span class="font-mono text-[10px] opacity-50">{{ traceId }}</span>
                    <span class="w-0.5 h-3 bg-border"></span>
                    <a href="mailto:admin@jejakawan.com" class="hover:text-amber-500 transition-colors underline decoration-dotted underline-offset-2">Contact Support</a>
                </div>
            </div>
        </template>
    </ErrorLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useI18n } from 'vue-i18n';
import ErrorLayout from '@/layouts/ErrorLayout.vue';

const router = useRouter();
const authStore = useAuthStore();
const { t } = useI18n();

const user = computed(() => authStore.user);
const reason = ref(window.history.state?.reason || null);
const requiredPermissions = ref(window.history.state?.requiredPermissions || []);
const traceId = ref(`TRC-${Date.now().toString().slice(-6)}-${Math.random().toString(36).substring(7).toUpperCase()}`);

const goBack = () => {
  if (window.history.length > 1) {
    router.go(-1);
  } else {
    router.push('/');
  }
};

const goToLogin = () => {
    authStore.logout(); // Clear any stale state first
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


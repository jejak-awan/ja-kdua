<template>
    <ErrorLayout>
        <template #icon>
            <div class="h-20 w-20 rounded-2xl bg-red-500/10 border border-red-500/20 flex items-center justify-center">
                <Lock class="h-10 w-10 text-red-600 dark:text-red-500" />
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

        <template #actions>
            <button
                v-if="!user"
                @click="goToLogin"
                class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-2xl text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500 shadow-sm transition-all active:scale-95"
            >
                <LogIn class="w-4 h-4 mr-2" />
                {{ t('features.errors.403.login') }}
            </button>

            <button
                v-else
                @click="logout"
                class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-2xl text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500 shadow-sm transition-all active:scale-95"
            >
                <LogIn class="w-4 h-4 mr-2" />
                {{ t('features.errors.403.relogin') }}
            </button>

            <button
                @click="goBack"
                class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-border text-sm font-medium rounded-2xl text-foreground bg-muted hover:bg-muted/80 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500 transition-all active:scale-95"
            >
                <ArrowLeft class="w-4 h-4 mr-2 text-muted-foreground" />
                {{ t('features.errors.404.back') }}
            </button>
        </template>

        <template #footer>
            <div class="flex items-center justify-center gap-3">
                <span>Error Code: 403</span>
                <span class="w-1 h-1 rounded-full bg-border"></span>
                <span class="font-mono opacity-60">{{ traceId }}</span>
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
import { Lock, LogIn, ArrowLeft } from 'lucide-vue-next';

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


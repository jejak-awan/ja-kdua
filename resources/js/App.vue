<template>
    <router-view />
    
    <!-- Session Timeout Warning Modal -->
    <SessionTimeoutModal
        :is-visible="isWarningVisible"
        :time-remaining="timeRemaining"
        @extend="extendSession"
        @logout="manualLogout"
    />
    
    <!-- Global Toast Notifications -->
    <Toast ref="toastRef" />
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useAuthStore } from './stores/auth';
import { useTheme } from './composables/useTheme';
import { useSessionTimeout } from './composables/useSessionTimeout';
import { useLanguage } from './composables/useLanguage';
import SessionTimeoutModal from './components/SessionTimeoutModal.vue';
import Toast from './components/ui/toast.vue';
import { setToastInstance } from './services/toast';

const authStore = useAuthStore();
const { loadActiveTheme } = useTheme();
const { initializeLanguage } = useLanguage();

// Toast reference
const toastRef = ref(null);

// Session timeout management
const {
    isWarningVisible,
    timeRemaining,
    extendSession,
    manualLogout,
} = useSessionTimeout();

// Initialize dark mode from localStorage or system preference
const initDarkMode = () => {
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

onMounted(async () => {
    initDarkMode();
    authStore.initAuth();
    
    // Initialize toast instance for global access
    if (toastRef.value) {
        setToastInstance({
            addToast: toastRef.value.addToast,
            removeToast: toastRef.value.removeToast,
        });
    }
    
    // Initialize language (non-blocking)
    initializeLanguage().catch(err => {
        console.warn('Language initialization failed:', err);
    });
    
    // Load active theme for frontend (non-blocking, don't wait)
    // Theme loading failure shouldn't prevent app from loading
    loadActiveTheme('frontend').catch(err => {
        console.warn('Theme loading failed, continuing without theme:', err);
    });
    
    if (authStore.isAuthenticated) {
        authStore.fetchUser();
    }
});
</script>

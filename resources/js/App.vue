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
    
    // Global listener for chunk loading errors (occurs after build/asset change)
    const handleChunkError = (e) => {
        // If session is already terminated, don't try to recover, just stop.
        if (window.__isSessionTerminated) {
            console.error('Chunk fail during session death. Aborting.');
            return;
        }

        if (e.message?.includes('Loading chunk') || e.message?.includes('CSS chunk')) {
            console.warn('Chunk loading failed, checking for reload guard...', e);
            
            // Reload Guard: Prevent infinite reload loops
            const now = Date.now();
            const lastReload = parseInt(sessionStorage.getItem('last_chunk_reload') || '0', 10);
            
            // Limit to 1 reload every 30 seconds
            if (now - lastReload > 30000) {
                sessionStorage.setItem('last_chunk_reload', now.toString());
                window.location.reload();
            } else {
                console.error('Excessive chunk reloads detected. Stopping to prevent browser hang.', e);
                // If it keeps failing, redirect to login or show an error instead of looping
                if (!window.location.pathname.includes('/login')) {
                    window.location.href = '/login?error=asset_mismatch';
                }
            }
        }
    };
    window.addEventListener('error', handleChunkError, true);

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

<template>
    <router-view />
    
    <!-- System Lock Overlay (Maintenance / Crash) -->
    <SystemOverlay />

    <!-- Session Timeout Warning Modal -->
    <SessionTimeoutModal
        :is-visible="isWarningVisible"
        :time-remaining="timeRemaining"
        @extend="extendSession"
        @logout="manualLogout"
    />
    
    <!-- Global Toast Notifications -->
    <Toast ref="toastRef" />
    
    <!-- Global Confirmation Modal -->
    <ConfirmModal
        v-model:isOpen="confirmState.isOpen"
        :title="confirmState.title"
        :message="confirmState.message"
        :description="confirmState.description"
        :variant="confirmState.variant"
        :confirmText="confirmState.confirmText"
        :cancelText="confirmState.cancelText"
        @confirm="confirmState.onConfirm"
        @cancel="confirmState.onCancel"
    />
    
    <!-- Global Error Modal (Session/Auth/Server Errors) -->
    <GlobalErrorModal />
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import { useAuthStore } from './stores/auth';
import { useCmsStore } from './stores/cms';
import { useTheme } from './composables/useTheme';
import { useSessionTimeout } from './composables/useSessionTimeout';
import { useLanguage } from './composables/useLanguage';
import { useConfirm } from './composables/useConfirm';
import SessionTimeoutModal from './components/SessionTimeoutModal.vue';
import SystemOverlay from './components/SystemOverlay.vue';
import { SystemMonitor } from './services/SystemMonitor';
import Toast from './components/ui/toast.vue';
import ConfirmModal from './components/ConfirmModal.vue';
import GlobalErrorModal from './components/GlobalErrorModal.vue';
import { setToastInstance } from './services/toast';

const authStore = useAuthStore();
const cmsStore = useCmsStore();
const { loadActiveTheme } = useTheme();
const { initializeLanguage } = useLanguage();
const { confirmState } = useConfirm();

// Toast reference
const toastRef = ref(null);

// Session timeout management
const {
    isWarningVisible,
    timeRemaining,
    extendSession,
    manualLogout,
} = useSessionTimeout();

const { themeSettings } = useTheme();

// Watch for favicon changes to update browser tab dynamically
watch(
    [() => themeSettings.value?.brand_favicon, () => cmsStore.siteSettings?.site_favicon],
    ([themeFav, siteFav]) => {
        const activeFavicon = themeFav || siteFav || '/favicon.svg';
        
        let link = document.querySelector("link[rel~='icon']");
        if (!link) {
            link = document.createElement('link');
            link.rel = 'icon';
            document.head.appendChild(link);
        }
        link.href = activeFavicon;
        
        // Also update standard favicon.ico if it exists to be safe
        const icoLink = document.querySelector("link[href$='favicon.ico']");
        if (icoLink) icoLink.href = activeFavicon;
    }, 
    { immediate: true }
);

// Watch for site name changes to update browser tab title dynamically
watch(() => cmsStore.siteSettings?.site_name, (newName) => {
    document.title = newName || 'JA CMS';
}, { immediate: true });

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
            return;
        }

        // Mismatched hash or missing chunk usually means new deployment
        if (e.message?.includes('Loading chunk') || e.message?.includes('CSS chunk') || e.message?.includes('HTML')) {
            console.warn('Chunk mismatch detected. Triggering System Lockdown.', e);
            SystemMonitor.triggerLockdown('chunk_error');
        }
    };
    
    // Listen for promise rejections too (dynamic imports)
    window.addEventListener('error', handleChunkError, true);
    window.addEventListener('unhandledrejection', (e) => {
        if (e.reason && (e.reason.message?.includes('Loading chunk') || e.reason.message?.includes('CSS chunk'))) {
             console.warn('Unhandled chunk rejection. Triggering Safe Mode.', e.reason);
             SystemMonitor.triggerLockdown('chunk_error');
        }
    });

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

    // Fetch public site settings for dynamic branding
    cmsStore.fetchPublicSettings().catch(err => {
        console.warn('Public settings fetch failed:', err);
    });
    
    if (authStore.isAuthenticated && !window.__isSessionTerminated) {
        authStore.fetchUser();
    }
});
</script>

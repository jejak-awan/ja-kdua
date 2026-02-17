<template>
    <router-view />
    
    <!-- System Lock Overlay (Maintenance / Crash) -->
    <SystemOverlay />

    <!-- Bot Shield Overlay (PoW Challenge) -->
    <ShieldOverlay 
        :visible="securityStore.isShieldVisible"
        :progress="securityStore.shieldProgress"
        :status-text="securityStore.shieldStatus"
    />

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
        v-model:is-open="confirmState.isOpen"
        :title="confirmState.title || ''"
        :message="confirmState.message"
        :description="confirmState.description"
        :variant="confirmState.variant || 'warning'"
        :confirm-text="confirmState.confirmText"
        :cancel-text="confirmState.cancelText"
        @confirm="(v) => confirmState.onConfirm?.(v)"
        @cancel="() => confirmState.onCancel?.()"
    />
    
    <!-- Global Error Modal (Session/Auth/Server Errors) -->
    <GlobalErrorModal />
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { onMounted, ref, watch } from 'vue';
import { useAuthStore } from './stores/auth';
import { useCmsStore } from './stores/cms';
import { useTheme } from './composables/useTheme';
import { useSessionTimeout } from './composables/useSessionTimeout';
import { useLanguage } from './composables/useLanguage';
import { useConfirm } from './composables/useConfirm';
import { SessionTimeoutModal, Toast, ConfirmModal, GlobalErrorModal } from '@/components/ui';
import SystemOverlay from './components/shared/SystemOverlay.vue';
import ShieldOverlay from './components/shared/ShieldOverlay.vue';
import { useSecurityStore } from './stores/security';
import { SystemMonitor } from './services/SystemMonitor';
import { setToastInstance, type ToastInstance } from './services/toast';

const authStore = useAuthStore();
const cmsStore = useCmsStore();
const { loadActiveTheme } = useTheme();
const { initializeLanguage } = useLanguage();
const { confirmState } = useConfirm();
const securityStore = useSecurityStore();

// Toast reference
const toastRef = ref<ToastInstance | null>(null);

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
        const activeFavicon = (themeFav || siteFav || '/favicon.svg') as string;
        
        let link = document.querySelector("link[rel~='icon']") as HTMLLinkElement | null;
        if (!link) {
            link = document.createElement('link');
            link.rel = 'icon';
            document.head.appendChild(link);
        }
        link.href = activeFavicon;
        
        // Also update standard favicon.ico if it exists to be safe
        const icoLink = document.querySelector("link[href$='favicon.ico']") as HTMLLinkElement | null;
        if (icoLink) icoLink.href = activeFavicon;
    }, 
    { immediate: true }
);

// Watch for site name changes to update browser tab title dynamically
watch(() => cmsStore.siteSettings?.site_name, (newName) => {
    document.title = (newName || 'JA CMS') as string;
}, { immediate: true });

// Theme initialization is handled by cmsStore.initTheme()

onMounted(async () => {
    cmsStore.initTheme();
    authStore.initAuth();
    
    // Global listener for chunk loading errors (occurs after build/asset change)
    const handleChunkError = (e: ErrorEvent | PromiseRejectionEvent | { message?: string }) => {
        // If session is already terminated, don't try to recover, just stop.
        if ((window as unknown as { __isSessionTerminated?: boolean }).__isSessionTerminated) {
            return;
        }

        // Mismatched hash or missing chunk usually means new deployment
        const msg = (e as ErrorEvent).message || (e as { message?: string }).message || '';
        if (msg.includes('Loading chunk') || msg.includes('CSS chunk') || msg.includes('HTML')) {
            logger.warning('Chunk mismatch detected. Triggering System Lockdown.', e);
            SystemMonitor.triggerLockdown('chunk_error');
        }
    };
    
    // Listen for promise rejections too (dynamic imports)
    window.addEventListener('error', handleChunkError, true);
    window.addEventListener('unhandledrejection', (e: PromiseRejectionEvent) => {
        if (e.reason && (e.reason.message?.includes('Loading chunk') || e.reason.message?.includes('CSS chunk'))) {
             logger.warning('Unhandled chunk rejection. Triggering Safe Mode.', e.reason);
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
        logger.warning('Language initialization failed:', err);
    });
    
    // Load active theme for frontend (non-blocking, don't wait)
    // Theme loading failure shouldn't prevent app from loading
    loadActiveTheme('frontend').catch(err => {
        logger.warning('Theme loading failed, continuing without theme:', err);
    });

    // --- NEW: Live Preview Listener for Customizer ---
    window.addEventListener('message', (event) => {
        // Only accept messages from same origin if needed, or check structure
        if (event.data && event.data.type === 'THEME_UPDATE') {
            const { settings, custom_css } = event.data;
            if (settings) {
                themeSettings.value = { ...themeSettings.value, ...settings };
            }
            if (custom_css !== undefined) {
                const { customCss, applyThemeStyles } = useTheme();
                customCss.value = custom_css;
                applyThemeStyles();
            }
            logger.info('Live theme update applied via Customizer');
        }
    });
    // --------------------------------------------------

    // Fetch public site settings for dynamic branding
    cmsStore.fetchPublicSettings().catch(err => {
        logger.warning('Public settings fetch failed:', err);
    });
    
    if (authStore.isAuthenticated && !(window as unknown as { __isSessionTerminated?: boolean }).__isSessionTerminated) {
        authStore.fetchUser();
    }

    // Drop the anti-flash shield after everything is likely rendered
    setTimeout(() => {
        document.documentElement.classList.remove('no-transitions');
    }, 150);
});
</script>

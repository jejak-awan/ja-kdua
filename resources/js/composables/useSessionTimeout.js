import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';

export function useSessionTimeout() {
    const router = useRouter();
    const authStore = useAuthStore();
    
    // Session configuration (in seconds)
    const SESSION_LIFETIME = parseInt(import.meta.env.VITE_SESSION_LIFETIME || '28800'); // 8 hours default
    const WARNING_TIME = 300; // Show warning 5 minutes before expiry
    
    // State
    const isWarningVisible = ref(false);
    const timeRemaining = ref(WARNING_TIME);
    const lastActivityTime = ref(Date.now());
    
    // Timers
    let warningTimer = null;
    let countdownTimer = null;
    let activityCheckTimer = null;
    
    // Computed
    const sessionExpiryTime = computed(() => {
        return lastActivityTime.value + (SESSION_LIFETIME * 1000);
    });
    
    const timeUntilExpiry = computed(() => {
        return Math.max(0, Math.floor((sessionExpiryTime.value - Date.now()) / 1000));
    });
    
    const isAuthenticated = computed(() => authStore.isAuthenticated);
    
    // Activity tracking
    const trackActivity = () => {
        if (!isAuthenticated.value) return;
        
        lastActivityTime.value = Date.now();
        resetTimers();
    };
    
    // Event listeners for user activity
    const activityEvents = ['mousedown', 'keypress', 'scroll', 'touchstart', 'click'];
    
    const setupActivityListeners = () => {
        activityEvents.forEach(event => {
            window.addEventListener(event, trackActivity, { passive: true });
        });
    };
    
    const removeActivityListeners = () => {
        activityEvents.forEach(event => {
            window.removeEventListener(event, trackActivity);
        });
    };
    
    // Timer management
    const startWarningTimer = () => {
        clearWarningTimer();
        
        if (!isAuthenticated.value) return;
        
        const timeUntilWarning = (SESSION_LIFETIME - WARNING_TIME) * 1000;
        
        warningTimer = setTimeout(() => {
            showWarning();
        }, timeUntilWarning);
    };
    
    const clearWarningTimer = () => {
        if (warningTimer) {
            clearTimeout(warningTimer);
            warningTimer = null;
        }
    };
    
    const startCountdownTimer = () => {
        clearCountdownTimer();
        
        countdownTimer = setInterval(() => {
            if (timeRemaining.value > 0) {
                timeRemaining.value--;
            } else {
                handleTimeout();
            }
        }, 1000);
    };
    
    const clearCountdownTimer = () => {
        if (countdownTimer) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }
    };
    
    const startActivityCheckTimer = () => {
        clearActivityCheckTimer();
        
        // Check every 30 seconds if session should show warning
        activityCheckTimer = setInterval(() => {
            if (!isAuthenticated.value) {
                stopAllTimers();
                return;
            }
            
            const timeLeft = timeUntilExpiry.value;
            
            if (timeLeft <= 0) {
                handleTimeout();
            } else if (timeLeft <= WARNING_TIME && !isWarningVisible.value) {
                showWarning();
            }
        }, 30000); // Check every 30 seconds
    };
    
    const clearActivityCheckTimer = () => {
        if (activityCheckTimer) {
            clearInterval(activityCheckTimer);
            activityCheckTimer = null;
        }
    };
    
    const stopAllTimers = () => {
        clearWarningTimer();
        clearCountdownTimer();
        clearActivityCheckTimer();
    };
    
    const resetTimers = () => {
        if (!isWarningVisible.value) {
            startWarningTimer();
        }
    };
    
    // Warning modal
    const showWarning = () => {
        if (!isAuthenticated.value) return;
        
        isWarningVisible.value = true;
        timeRemaining.value = timeUntilExpiry.value;
        startCountdownTimer();
    };
    
    const hideWarning = () => {
        isWarningVisible.value = false;
        clearCountdownTimer();
        timeRemaining.value = WARNING_TIME;
    };
    
    // Actions
    const extendSession = async () => {
        try {
            // Make a lightweight API call to extend the session
            await api.get('/user');
            
            // Reset activity time
            lastActivityTime.value = Date.now();
            
            // Hide warning and reset timers
            hideWarning();
            startWarningTimer();
            
            console.log('Session extended successfully');
        } catch (error) {
            console.error('Failed to extend session:', error);
            // If extend fails, likely session already expired
            handleTimeout();
        }
    };
    
    const handleTimeout = async () => {
        stopAllTimers();
        hideWarning();
        
        // Logout user
        await authStore.logout();
        
        // Redirect to login with message
        router.push({
            path: '/login',
            query: {
                timeout: '1',
                redirect: router.currentRoute.value.fullPath,
            },
        });
    };
    
    const manualLogout = async () => {
        stopAllTimers();
        hideWarning();
        removeActivityListeners();
        
        await authStore.logout();
        router.push('/login');
    };
    
    // Initialization
    const initialize = () => {
        if (!isAuthenticated.value) return;
        
        setupActivityListeners();
        startWarningTimer();
        startActivityCheckTimer();
        lastActivityTime.value = Date.now();
    };
    
    const cleanup = () => {
        stopAllTimers();
        removeActivityListeners();
        hideWarning();
    };
    
    // Lifecycle hooks
    onMounted(() => {
        initialize();
    });
    
    onUnmounted(() => {
        cleanup();
    });
    
    // Watch for auth changes
    const watchAuth = () => {
        const unwatch = authStore.$subscribe((mutation, state) => {
            if (state.isAuthenticated) {
                initialize();
            } else {
                cleanup();
            }
        });
        
        onUnmounted(unwatch);
    };
    
    watchAuth();
    
    return {
        // State
        isWarningVisible,
        timeRemaining,
        timeUntilExpiry,
        
        // Actions
        extendSession,
        manualLogout,
        showWarning,
        hideWarning,
        trackActivity,
        
        // Utilities
        initialize,
        cleanup,
    };
}


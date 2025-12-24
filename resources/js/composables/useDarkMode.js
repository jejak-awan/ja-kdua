import { ref, computed, watch, onMounted } from 'vue';
import api from '../services/api';

const THEME_KEY = 'admin-dark-mode';
const MODES = {
    LIGHT: 'light',
    DARK: 'dark',
    SYSTEM: 'system',
};

// Initialize IMMEDIATELY from localStorage (before any component mounts)
const savedMode = localStorage.getItem(THEME_KEY);
const systemPreference = window.matchMedia('(prefers-color-scheme: dark)').matches ? MODES.DARK : MODES.LIGHT;

// Global state (shared across all components)
const currentMode = ref(savedMode && Object.values(MODES).includes(savedMode) ? savedMode : MODES.SYSTEM);
const systemMode = ref(systemPreference);
const initialized = ref(false);

/**
 * Composable for dark/light mode management in admin panel
 * Separate from frontend theme system (useTheme)
 * 
 * Priority: Backend (if authenticated) → localStorage → System default
 */
export function useDarkMode() {
    // Get actual mode (resolve 'system' to light/dark)
    const actualMode = computed(() => {
        if (currentMode.value === MODES.SYSTEM) {
            return systemMode.value;
        }
        return currentMode.value;
    });

    // Check if dark mode is active
    const isDark = computed(() => actualMode.value === MODES.DARK);

    // Apply mode to document
    const applyMode = (mode) => {
        const root = document.documentElement;

        if (mode === MODES.DARK) {
            root.classList.add('dark');
        } else {
            root.classList.remove('dark');
        }
    };

    // Set mode
    const setMode = (mode) => {
        if (!Object.values(MODES).includes(mode)) {
            console.warn(`Invalid mode: ${mode}`);
            return;
        }

        currentMode.value = mode;
        localStorage.setItem(THEME_KEY, mode);
        applyMode(actualMode.value);

        // Sync with backend if available
        syncModeWithBackend(mode);
    };

    // Toggle between light and dark
    const toggleMode = () => {
        const newMode = isDark.value ? MODES.LIGHT : MODES.DARK;
        setMode(newMode);
    };

    // Detect system mode preference
    const detectSystemMode = () => {
        const isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
        systemMode.value = isDarkMode ? MODES.DARK : MODES.LIGHT;
    };

    // Listen to system mode changes
    const watchSystemMode = () => {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

        const handler = (e) => {
            systemMode.value = e.matches ? MODES.DARK : MODES.LIGHT;
        };

        mediaQuery.addEventListener('change', handler);

        // Cleanup
        return () => mediaQuery.removeEventListener('change', handler);
    };

    // Check if user is authenticated
    const isAuthenticated = () => {
        return !!localStorage.getItem('auth_token');
    };

    // Load preferences from backend (if authenticated)
    const loadFromBackend = async () => {
        if (!isAuthenticated()) return null;

        try {
            const response = await api.get('/profile/preferences');
            if (response.data?.success && response.data?.data?.dark_mode) {
                return response.data.data.dark_mode;
            }
        } catch (error) {
            console.debug('Failed to load preferences from backend:', error.message);
        }
        return null;
    };

    // Sync mode with backend
    const syncModeWithBackend = async (mode) => {
        if (!isAuthenticated()) return;

        try {
            await api.put('/profile/preferences', { dark_mode: mode });
        } catch (error) {
            // Silent fail - user can still use mode locally
            console.debug('Mode sync failed:', error.message);
        }
    };

    // Initialize mode
    const initMode = async () => {
        // Prevent double initialization
        if (initialized.value) {
            applyMode(actualMode.value);
            return;
        }
        initialized.value = true;

        // 1. Detect system mode
        detectSystemMode();

        // 2. Try to load from backend first (source of truth for logged-in users)
        const backendMode = await loadFromBackend();

        if (backendMode && Object.values(MODES).includes(backendMode)) {
            // Backend is source of truth - also update localStorage
            currentMode.value = backendMode;
            localStorage.setItem(THEME_KEY, backendMode);
        } else {
            // 3. Fallback to localStorage
            const saved = localStorage.getItem(THEME_KEY);
            if (saved && Object.values(MODES).includes(saved)) {
                currentMode.value = saved;
            }
        }

        // 4. Apply mode
        applyMode(actualMode.value);

        // 5. Watch system mode changes
        watchSystemMode();
    };

    // Watch mode changes
    watch(actualMode, (newMode) => {
        applyMode(newMode);
    });

    // Auto-init on mount
    onMounted(() => {
        initMode();
    });

    return {
        currentMode,
        actualMode,
        isDark,
        setMode,
        toggleMode,
        modes: MODES,
        loadFromBackend,
    };
}

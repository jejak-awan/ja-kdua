import { ref, computed, watch, onMounted } from 'vue';

const THEME_KEY = 'admin-dark-mode';
const MODES = {
    LIGHT: 'light',
    DARK: 'dark',
    SYSTEM: 'system',
};

// Global state (shared across all components)
const currentMode = ref(MODES.SYSTEM);
const systemMode = ref(MODES.LIGHT);

/**
 * Composable for dark/light mode management in admin panel
 * Separate from frontend theme system (useTheme)
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

    // Sync mode with backend
    const syncModeWithBackend = async (mode) => {
        try {
            const response = await fetch('/api/user/preferences', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include',
                body: JSON.stringify({ dark_mode: mode }),
            });

            if (!response.ok) {
                throw new Error('Failed to sync mode');
            }
        } catch (error) {
            // Silent fail - user can still use mode locally
            console.debug('Mode sync failed:', error.message);
        }
    };

    // Initialize mode
    const initMode = () => {
        // 1. Try to get from localStorage
        const saved = localStorage.getItem(THEME_KEY);

        if (saved && Object.values(MODES).includes(saved)) {
            currentMode.value = saved;
        }

        // 2. Detect system mode
        detectSystemMode();

        // 3. Apply mode
        applyMode(actualMode.value);

        // 4. Watch system mode changes
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
    };
}

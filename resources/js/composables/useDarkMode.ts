import { computed } from 'vue';
import { useCmsStore } from '../stores/cms';

export function useDarkMode() {
    const cmsStore = useCmsStore();

    const isDark = computed(() => cmsStore.isDarkMode);
    const actualMode = computed(() => cmsStore.isDarkMode ? 'dark' : 'light');
    const currentMode = computed(() => cmsStore.themeMode);

    const setMode = (mode: 'light' | 'dark' | 'system') => {
        cmsStore.setThemeMode(mode);
    };

    const toggleMode = () => {
        cmsStore.toggleDarkMode();
    };

    return {
        currentMode,
        actualMode,
        isDark,
        setMode,
        toggleMode,
        modes: {
            LIGHT: 'light',
            DARK: 'dark',
            SYSTEM: 'system'
        },
        loadFromBackend: () => cmsStore.loadThemePreferences(),
    };
}

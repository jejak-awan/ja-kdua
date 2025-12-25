import { ref, computed, watch } from 'vue';
import api from '../services/api';

// Global shared state
const activeTheme = ref(null);
const themeSettings = ref({});
const themeAssets = ref({ css: [], js: [] });
const customCss = ref('');
const cssVariables = ref('');
const loading = ref(false);
const error = ref(null);
const isLoading = ref(false); // Prevent multiple simultaneous loads

/**
 * Composable for theme management in frontend
 */
export function useTheme() {
    /**
     * Load active theme
     */
    const loadActiveTheme = async (type = 'frontend') => {
        // Prevent multiple simultaneous loads if we already have data or are loading
        if (isLoading.value || (activeTheme.value && type === 'frontend')) {
            return;
        }

        isLoading.value = true;
        loading.value = true;
        error.value = null;

        try {
            // Use public endpoint for frontend theme (no auth required)
            const endpoint = type === 'frontend'
                ? `/cms/themes/active?type=${type}`
                : `/admin/cms/themes/active?type=${type}`;

            const response = await api.get(endpoint);
            const data = response.data?.data || response.data;

            // Handle null response (no active theme)
            if (!data) {
                activeTheme.value = null;
                themeSettings.value = {};
                return;
            }

            activeTheme.value = data;
            themeSettings.value = data.settings || {};

            // Load theme assets
            if (data.assets) {
                themeAssets.value = data.assets;
                injectCssFiles(data.assets.css || []);
                injectJsFiles(data.assets.js || []);
            }

            if (data.custom_css) {
                customCss.value = data.custom_css;
                applyCustomCss();
            }

            applyThemeStyles();
        } catch (err) {
            console.warn('Failed to load active theme:', err);
            error.value = err.message || 'Failed to load theme';
            activeTheme.value = null;
            themeSettings.value = {};
        } finally {
            loading.value = false;
            isLoading.value = false;
        }
    };

    /**
     * Get theme setting with fallback
     */
    const getSetting = (key, defaultValue = null) => {
        if (!activeTheme.value) {
            return defaultValue;
        }

        if (themeSettings.value && themeSettings.value[key] !== undefined) {
            return themeSettings.value[key];
        }

        const manifest = activeTheme.value.manifest;
        if (manifest && manifest.settings_schema && manifest.settings_schema[key]) {
            return manifest.settings_schema[key].default ?? defaultValue;
        }

        return defaultValue;
    };

    /**
     * Apply theme styles (CSS variables)
     */
    const applyThemeStyles = () => {
        if (!activeTheme.value) return;

        const variables = [];
        const manifest = activeTheme.value.manifest;

        if (manifest && manifest.settings_schema) {
            Object.keys(manifest.settings_schema).forEach(key => {
                const setting = manifest.settings_schema[key];
                if (setting.type === 'color') {
                    const value = getSetting(key, setting.default);
                    if (value) {
                        const cssKey = '--theme-' + key.replace(/_/g, '-');
                        variables.push(`${cssKey}: ${value};`);
                    }
                }
            });
        }

        if (variables.length > 0) {
            cssVariables.value = `:root {\n  ${variables.join('\n  ')}\n}`;
            injectCssString(cssVariables.value, 'theme-variables');
        }
    };

    /**
     * Apply custom CSS
     */
    const applyCustomCss = () => {
        if (customCss.value) {
            injectCssString(customCss.value, 'theme-custom-css');
        }
    };

    /**
     * Inject CSS files into document
     */
    const injectCssFiles = (cssFiles) => {
        cssFiles.forEach((cssFile, index) => {
            const linkId = `theme-css-${index}`;
            const existing = document.getElementById(linkId);
            if (existing) existing.remove();

            const link = document.createElement('link');
            link.id = linkId;
            link.rel = 'stylesheet';
            link.href = cssFile.startsWith('http') || cssFile.startsWith('/') ? cssFile : `/${cssFile}`;
            document.head.appendChild(link);
        });
    };

    /**
     * Inject JS files into document
     */
    const injectJsFiles = (jsFiles) => {
        jsFiles.forEach((jsFile, index) => {
            const scriptId = `theme-js-${index}`;
            const existing = document.getElementById(scriptId);
            if (existing) existing.remove();

            const script = document.createElement('script');
            script.id = scriptId;
            script.src = jsFile.startsWith('http') || jsFile.startsWith('/') ? jsFile : `/${jsFile}`;
            script.defer = true;
            document.head.appendChild(script);
        });
    };

    /**
     * Inject CSS string into document
     */
    const injectCssString = (css, id) => {
        const existing = document.getElementById(id);
        if (existing) existing.remove();

        const style = document.createElement('style');
        style.id = id;
        style.textContent = css;
        document.head.appendChild(style);
    };

    const isThemeLoaded = computed(() => activeTheme.value !== null);
    const themeName = computed(() => activeTheme.value?.name || 'Default');
    const themeType = computed(() => activeTheme.value?.type || 'frontend');

    return {
        activeTheme,
        themeSettings,
        themeAssets,
        customCss,
        cssVariables,
        loading,
        error,
        isThemeLoaded,
        themeName,
        themeType,
        loadActiveTheme,
        getSetting,
        applyThemeStyles,
    };
}


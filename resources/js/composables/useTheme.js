import { ref, computed, onMounted, watch } from 'vue';
import api from '../services/api';

/**
 * Composable for theme management in frontend
 */
export function useTheme() {
    const activeTheme = ref(null);
    const themeSettings = ref({});
    const themeAssets = ref({ css: [], js: [] });
    const customCss = ref('');
    const cssVariables = ref('');
    const loading = ref(false);
    const error = ref(null);
    const isLoading = ref(false); // Prevent multiple simultaneous loads

    /**
     * Load active theme
     */
    const loadActiveTheme = async (type = 'frontend') => {
        // Prevent multiple simultaneous loads
        if (isLoading.value) {
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
                return; // Exit early, no theme to load
            }
            
            activeTheme.value = data;
            themeSettings.value = data.settings || {};
            
            // Load theme assets (already included in getActive response)
            if (data.assets) {
                themeAssets.value = data.assets;
                // Inject CSS files
                injectCssFiles(data.assets.css || []);
                // Inject JS files
                injectJsFiles(data.assets.js || []);
            }
            // Don't try to load assets from admin endpoint - they're already in response
            
            // Get custom CSS directly from theme data (already included in getActive response)
            if (data.custom_css) {
                customCss.value = data.custom_css;
                applyCustomCss();
            }
            
            // Apply theme styles
            applyThemeStyles();
        } catch (err) {
            // Don't redirect on error, just log it
            // Theme loading failure shouldn't break the app
            console.warn('Failed to load active theme:', err);
            error.value = err.message || 'Failed to load theme';
            // Set default theme if loading fails
            activeTheme.value = null;
            themeSettings.value = {};
        } finally {
            loading.value = false;
            isLoading.value = false;
        }
    };

    /**
     * Load theme assets
     * DEPRECATED: Assets are now included in getActive() response
     * This method is kept for backward compatibility but should not be used
     * Use data.assets from getActive() response instead
     */
    const loadThemeAssets = async (themeId) => {
        // Assets are now included in getActive() response
        // No need to make separate API call to admin endpoint
        // This method is deprecated but kept for compatibility
        console.warn('loadThemeAssets is deprecated. Use assets from getActive() response instead.');
    };

    /**
     * Load custom CSS
     * Note: Custom CSS is now loaded directly from getActive() response
     * This method is kept for backward compatibility but is no longer used
     */
    const loadCustomCss = async (themeId) => {
        // Custom CSS is now included in getActive() response
        // No need to make separate API call
        // This method is deprecated but kept for compatibility
    };

    /**
     * Get theme setting with fallback
     */
    const getSetting = (key, defaultValue = null) => {
        if (!activeTheme.value) {
            return defaultValue;
        }

        // Check current theme settings
        if (themeSettings.value && themeSettings.value[key] !== undefined) {
            return themeSettings.value[key];
        }

        // Check manifest defaults
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

        // Generate CSS variables from settings
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
            
            // Remove existing if any
            const existing = document.getElementById(linkId);
            if (existing) {
                existing.remove();
            }

            // Create new link
            const link = document.createElement('link');
            link.id = linkId;
            link.rel = 'stylesheet';
            // Handle both absolute URLs and relative paths
            if (cssFile.startsWith('http')) {
                link.href = cssFile;
            } else if (cssFile.startsWith('/')) {
                link.href = cssFile;
            } else {
                link.href = `/${cssFile}`;
            }
            document.head.appendChild(link);
        });
    };

    /**
     * Inject JS files into document
     */
    const injectJsFiles = (jsFiles) => {
        jsFiles.forEach((jsFile, index) => {
            const scriptId = `theme-js-${index}`;
            
            // Remove existing if any
            const existing = document.getElementById(scriptId);
            if (existing) {
                existing.remove();
            }

            // Create new script
            const script = document.createElement('script');
            script.id = scriptId;
            // Handle both absolute URLs and relative paths
            if (jsFile.startsWith('http')) {
                script.src = jsFile;
            } else if (jsFile.startsWith('/')) {
                script.src = jsFile;
            } else {
                script.src = `/${jsFile}`;
            }
            script.defer = true;
            document.head.appendChild(script);
        });
    };

    /**
     * Inject CSS string into document
     */
    const injectCssString = (css, id) => {
        // Remove existing if any
        const existing = document.getElementById(id);
        if (existing) {
            existing.remove();
        }

        // Create new style tag
        const style = document.createElement('style');
        style.id = id;
        style.textContent = css;
        document.head.appendChild(style);
    };

    /**
     * Refresh theme
     */
    const refreshTheme = async (type = 'frontend') => {
        await loadActiveTheme(type);
    };

    // Computed properties
    const isThemeLoaded = computed(() => activeTheme.value !== null);
    const themeName = computed(() => activeTheme.value?.name || 'Default');
    const themeType = computed(() => activeTheme.value?.type || 'frontend');

    // Watch for theme changes
    watch(() => activeTheme.value, () => {
        if (activeTheme.value) {
            applyThemeStyles();
        }
    });

    // Auto-load on mount
    onMounted(() => {
        loadActiveTheme();
    });

    return {
        // State
        activeTheme,
        themeSettings,
        themeAssets,
        customCss,
        cssVariables,
        loading,
        error,

        // Computed
        isThemeLoaded,
        themeName,
        themeType,

        // Methods
        loadActiveTheme,
        getSetting,
        refreshTheme,
        applyThemeStyles,
    };
}


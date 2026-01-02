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
     * Helper: Convert Hex to HSL (Space separated for Tailwind)
     */
    const hexToHsl = (hex) => {
        if (!hex || typeof hex !== 'string' || !hex.startsWith('#')) return null;

        let r = 0, g = 0, b = 0;
        if (hex.length === 4) {
            r = parseInt('0x' + hex[1] + hex[1]);
            g = parseInt('0x' + hex[2] + hex[2]);
            b = parseInt('0x' + hex[3] + hex[3]);
        } else if (hex.length === 7) {
            r = parseInt('0x' + hex[1] + hex[2]);
            g = parseInt('0x' + hex[3] + hex[4]);
            b = parseInt('0x' + hex[5] + hex[6]);
        }

        r /= 255;
        g /= 255;
        b /= 255;

        const cmin = Math.min(r, g, b), cmax = Math.max(r, g, b), delta = cmax - cmin;
        let h = 0, s = 0, l = 0;

        if (delta === 0) h = 0;
        else if (cmax === r) h = ((g - b) / delta) % 6;
        else if (cmax === g) h = (b - r) / delta + 2;
        else h = (r - g) / delta + 4;

        h = Math.round(h * 60);
        if (h < 0) h += 360;

        l = (cmax + cmin) / 2;
        s = delta === 0 ? 0 : delta / (1 - Math.abs(2 * l - 1));

        s = +(s * 100).toFixed(1);
        l = +(l * 100).toFixed(1);

        return `${h} ${s}% ${l}%`;
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
                const value = getSetting(key, setting.default);

                if (!value) return;

                const cssKey = '--theme-' + key.replace(/_/g, '-');

                if (setting.type === 'color') {
                    variables.push(`${cssKey}: ${value};`);

                    // Inject HSL version for Shadcn compatibility
                    const hslValue = hexToHsl(value);
                    if (hslValue) {
                        variables.push(`${cssKey}-hsl: ${hslValue};`);
                    }
                } else if (setting.type === 'font' || setting.type === 'typography') {
                    // Inject font-family
                    const fontValue = String(value).includes(' ') ? `"${value}"` : value;
                    variables.push(`${cssKey}: ${fontValue};`);
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


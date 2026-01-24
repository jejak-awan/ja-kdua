import { createI18n } from 'vue-i18n';
// @ts-ignore
import config from '../lang/config';
// @ts-ignore
import en from '../lang/en/index.js';
// @ts-ignore
import id from '../lang/id/index.js';
// @ts-ignore
import builderTranslations from './components/builder/lang/index.js';
import _ from 'lodash';

/**
 * Detect the best locale to use
 * Priority: 1. localStorage, 2. Browser language, 3. Default
 */
const detectLocale = (): string => {
    // 1. Check localStorage first (user preference)
    const savedLocale = localStorage.getItem('locale');
    if (savedLocale && config.availableLocales.some((l: any) => l.code === savedLocale)) {
        return savedLocale;
    }

    // 2. Try to detect from browser
    const browserLang = navigator.language || (navigator as any).userLanguage;
    if (browserLang) {
        // Try exact match first (e.g., 'id-ID' -> 'id')
        const langCode = browserLang.split('-')[0].toLowerCase();
        if (config.availableLocales.some((l: any) => l.code === langCode)) {
            return langCode;
        }
    }

    // 3. Fallback to default
    return config.locale;
};

const detectedLocale = detectLocale();

// Merge global messages with builder-specific translations
const messages = {
    en: _.merge({}, en, builderTranslations.en),
    id: _.merge({}, id, builderTranslations.id)
};

const i18n = createI18n({
    ...config,
    locale: detectedLocale,
    messages: messages,
});

// Save detected locale to localStorage for next visit
if (!localStorage.getItem('locale')) {
    localStorage.setItem('locale', detectedLocale);
}

export default i18n;

// Helper function to switch language
export const setLocale = (locale: string) => {
    if (i18n.global) {
        (i18n.global.locale as any).value = locale;
    }
    localStorage.setItem('locale', locale);
    document.documentElement.lang = locale;
};

// Get current locale
export const getLocale = () => (i18n.global.locale as any).value;

// Get available locales
export const getAvailableLocales = () => config.availableLocales;

// Get detected browser locale (for debugging)
export const getBrowserLocale = () => {
    const browserLang = navigator.language || (navigator as any).userLanguage;
    return browserLang ? browserLang.split('-')[0].toLowerCase() : null;
};

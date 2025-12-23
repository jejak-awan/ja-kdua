import { ref, computed } from 'vue';
import i18n, { setLocale as i18nSetLocale, getLocale as i18nGetLocale } from '../i18n';
import api from '../services/api';
import { parseResponse, ensureArray } from '../utils/responseParser';

const currentLanguage = ref(null);
const languages = ref([]);
const loading = ref(false);
const initialized = ref(false);

// RTL language codes
const RTL_LANGUAGES = ['ar', 'he', 'fa', 'ur', 'yi'];

// localStorage key - MUST match i18n.js
const LOCALE_STORAGE_KEY = 'locale';

/**
 * Composable for language management
 * 
 * IMPORTANT: This composable syncs with Vue I18n (i18n.js).
 * The source of truth for current locale is localStorage['locale'].
 */
export function useLanguage() {
  /**
   * Check if language is RTL
   */
  const isRTL = computed(() => {
    if (!currentLanguage.value) return false;
    return RTL_LANGUAGES.includes(currentLanguage.value.code.toLowerCase());
  });

  /**
   * Get current language code
   */
  const currentLanguageCode = computed(() => {
    return currentLanguage.value?.code || i18nGetLocale();
  });

  /**
   * Load available languages from API
   */
  const loadLanguages = async () => {
    loading.value = true;
    try {
      const response = await api.get('/cms/languages');
      const { data } = parseResponse(response);
      languages.value = ensureArray(data);
    } catch (error) {
      console.error('Failed to load languages:', error);
      languages.value = [];
    } finally {
      loading.value = false;
    }
  };

  /**
   * Set current language with full persistence
   */
  const setLanguage = async (languageCode) => {
    // Update Vue I18n (this saves to localStorage)
    i18nSetLocale(languageCode);

    // Find language object for UI
    const language = languages.value.find(l => l.code === languageCode);
    if (language) {
      currentLanguage.value = language;
    } else {
      // If language not in list, create a minimal object
      currentLanguage.value = { code: languageCode, name: languageCode.toUpperCase() };
    }

    // Update document attributes
    updateDocumentLanguage(languageCode);


  };

  /**
   * Update document language and RTL
   */
  const updateDocumentLanguage = (languageCode) => {
    if (typeof document === 'undefined') return;

    // Set lang attribute
    document.documentElement.setAttribute('lang', languageCode);

    // Handle RTL
    const isRTLLang = RTL_LANGUAGES.includes(languageCode.toLowerCase());
    if (isRTLLang) {
      document.documentElement.setAttribute('dir', 'rtl');
      document.documentElement.classList.add('rtl');
    } else {
      document.documentElement.setAttribute('dir', 'ltr');
      document.documentElement.classList.remove('rtl');
    }
  };

  /**
   * Initialize language from localStorage (persisted preference)
   * 
   * Priority:
   * 1. localStorage['locale'] (user preference - ALWAYS wins)
   * 2. Browser language (if supported)
   * 3. Database default
   */
  const initializeLanguage = async () => {
    // Prevent double initialization
    if (initialized.value) {
      return;
    }
    initialized.value = true;

    // Load languages for dropdown
    await loadLanguages();

    // Get current locale from Vue I18n (which already read from localStorage)
    const currentLocale = i18nGetLocale();

    // Find matching language object
    let language = languages.value.find(l => l.code === currentLocale);

    if (!language && languages.value.length > 0) {
      // Locale not found in DB, but we still honor user preference
      // Create a temporary language object
      language = { code: currentLocale, name: currentLocale.toUpperCase() };
    }

    if (language) {
      currentLanguage.value = language;
      updateDocumentLanguage(language.code);
    }


  };

  /**
   * Reset initialization flag (for testing)
   */
  const resetInitialization = () => {
    initialized.value = false;
  };

  /**
   * Get flag emoji or icon for language
   */
  const getLanguageFlag = (language) => {
    if (!language) return '🌐';

    // Handle string (language code)
    if (typeof language === 'string') {
      const flagMap = {
        'en': '🇺🇸', 'id': '🇮🇩', 'ar': '🇸🇦', 'he': '🇮🇱',
        'fr': '🇫🇷', 'de': '🇩🇪', 'es': '🇪🇸', 'pt': '🇵🇹',
        'zh': '🇨🇳', 'ja': '🇯🇵', 'ko': '🇰🇷', 'ru': '🇷🇺',
      };
      const code = language.toLowerCase().split('-')[0];
      return flagMap[code] || '🌐';
    }

    // Handle language object with flag property
    if (language.flag) return language.flag;

    // Fallback to emoji flags
    const flagMap = {
      'en': '🇺🇸', 'id': '🇮🇩', 'ar': '🇸🇦', 'he': '🇮🇱',
      'fr': '🇫🇷', 'de': '🇩🇪', 'es': '🇪🇸', 'pt': '🇵🇹',
      'zh': '🇨🇳', 'ja': '🇯🇵', 'ko': '🇰🇷', 'ru': '🇷🇺',
    };

    if (!language.code) return '🌐';
    const code = language.code.toLowerCase().split('-')[0];
    return flagMap[code] || '🌐';
  };

  /**
   * Translate text using Vue I18n
   */
  const t = (key, params = {}) => {
    if (i18n && i18n.global) {
      return i18n.global.t(key, params);
    }
    return key;
  };

  return {
    currentLanguage,
    languages,
    loading,
    isRTL,
    currentLanguageCode,
    loadLanguages,
    setLanguage,
    getLanguageFlag,
    t,
    initializeLanguage,
    resetInitialization,
  };
}

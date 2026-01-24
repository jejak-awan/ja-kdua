import { defineStore } from 'pinia';
import api from '../services/api';
import { parseResponse, ensureArray } from '../utils/responseParser';
import type { CMSState, Content, SiteSettings } from '../types/cms';

export const useCmsStore = defineStore('cms', {
    state: (): CMSState => ({
        contents: [],
        categories: [],
        tags: [],
        media: [],
        settings: {}, // Store settings by group or flat key-value
        siteSettings: {
            site_name: 'JA-CMS',
            site_description: '',
            site_url: '',
            admin_email: '',
            site_version: 'v1.0 Janari',
            site_logo: '',
            site_favicon: '/favicon.svg'
        },
        currentContent: null,
        loading: false,
        loadingGroups: {}, // To track loading state for specific settings groups
        settingsPromises: {}, // To store promises for ongoing settings group fetches
        themeMode: 'system', // 'light', 'dark', 'system'
        isDarkMode: false,
    }),

    actions: {
        async fetchSettingsGroup(group: string) {
            // If already loading, return existing promise
            if (this.loadingGroups[group]) {
                return this.settingsPromises[group];
            }

            // Mark this group as loading
            this.loadingGroups = { ...this.loadingGroups, [group]: true };

            // Create and store the promise for this fetch operation
            const promise = (async () => {
                try {
                    const response = await api.get(`/admin/ja/settings/group/${group}`);
                    const settingsData = response.data?.data || response.data || {};
                    this.settings = { ...this.settings, ...settingsData };
                    return settingsData;
                }
                catch (error) {
                    console.error(`Error fetching ${group} settings:`, error);
                    return {};
                } finally {
                    this.loadingGroups = { ...this.loadingGroups, [group]: false };
                    delete this.settingsPromises[group];
                }
            })();

            this.settingsPromises = { ...this.settingsPromises, [group]: promise };
            return promise;
        },

        async fetchPublicSettings(): Promise<SiteSettings> {
            try {
                const response = await api.get('/public/settings');
                const settingsData = response.data?.data || {};
                this.siteSettings = { ...this.siteSettings, ...settingsData };
                return settingsData;
            } catch (error) {
                console.error('Error fetching public settings:', error);
                return this.siteSettings;
            }
        },

        async fetchContents(params: any = {}) {
            this.loading = true;
            try {
                const response = await api.get('/cms/contents', { params });
                const { data } = parseResponse(response);
                this.contents = ensureArray(data);
                return { data: this.contents };
            } catch (error) {
                console.error('Error fetching contents:', error);
                this.contents = [];
                return { data: [] };
            } finally {
                this.loading = false;
            }
        },

        async fetchContent(slug: string): Promise<Content | null> {
            this.loading = true;
            try {
                const response = await api.get(`/cms/contents/${slug}`);
                this.currentContent = response.data;
                return response.data;
            } catch (error) {
                console.error('Error fetching content:', error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async fetchCategories() {
            try {
                const response = await api.get('/cms/categories');
                const { data } = parseResponse(response);
                this.categories = ensureArray(data);
                return this.categories;
            } catch (error) {
                console.error('Error fetching categories:', error);
                this.categories = [];
                return [];
            }
        },

        async fetchTags() {
            try {
                const response = await api.get('/cms/tags');
                const { data } = parseResponse(response);
                this.tags = ensureArray(data);
                return this.tags;
            } catch (error) {
                console.error('Error fetching tags:', error);
                this.tags = [];
                return [];
            }
        },

        async initTheme() {
            const THEME_KEY = 'admin-dark-mode';

            // 1. Detect system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            // 2. Load from localStorage or fallback to system
            const saved = localStorage.getItem(THEME_KEY) || 'system';
            this.themeMode = saved as 'light' | 'dark' | 'system';

            // 3. Resolve actual dark mode state
            this.isDarkMode = saved === 'dark' || (saved === 'system' && prefersDark);

            // 4. Apply to document
            this.applyThemeToDocument();

            // 5. Watch for system changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (this.themeMode === 'system') {
                    this.isDarkMode = e.matches;
                    this.applyThemeToDocument();
                }
            });

            // 6. Try to load from backend (if authenticated)
            await this.loadThemePreferences();
        },

        async loadThemePreferences() {
            if (!localStorage.getItem('auth_token')) return;
            try {
                const response = await api.get('/profile/preferences');
                if (response.data?.success && response.data?.data?.dark_mode) {
                    const backendMode = response.data.data.dark_mode;
                    if (this.themeMode !== backendMode) {
                        this.setThemeMode(backendMode, false); // Don't sync back to backend
                    }
                }
            } catch (error: any) {
                console.debug('Failed to load theme preferences:', error.message);
            }
        },

        async syncThemeWithBackend(mode: string) {
            if (!localStorage.getItem('auth_token')) return;
            try {
                await api.put('/profile/preferences', { dark_mode: mode });
            } catch (error: any) {
                console.debug('Theme sync failed:', error.message);
            }
        },

        setThemeMode(mode: 'light' | 'dark' | 'system', syncToBackend = true) {
            const THEME_KEY = 'admin-dark-mode';
            this.themeMode = mode;
            localStorage.setItem(THEME_KEY, mode);

            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.isDarkMode = mode === 'dark' || (mode === 'system' && prefersDark);

            this.applyThemeToDocument();

            if (syncToBackend) {
                this.syncThemeWithBackend(mode);
            }
        },

        toggleDarkMode(value?: boolean) {
            // If value is provided (e.g. from a switch), use it. 
            // Otherwise, toggle current state.
            const isDark = value !== undefined ? value : !this.isDarkMode;
            const next = isDark ? 'dark' : 'light';
            this.setThemeMode(next);
        },

        applyThemeToDocument() {
            if (this.isDarkMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        },
    },
});

import { defineStore } from 'pinia';
import api from '../services/api';
import { parseResponse, ensureArray } from '../utils/responseParser';

export const useCmsStore = defineStore('cms', {
    state: () => ({
        contents: [],
        categories: [],
        tags: [],
        media: [],
        settings: {}, // Store settings by group or flat key-value
        currentContent: null,
        loading: false,
        loadingGroups: {}, // To track loading state for specific settings groups
        settingsPromises: {}, // To store promises for ongoing settings group fetches
    }),

    actions: {
        async fetchSettingsGroup(group) {
            // If a request for this group is already in progress, return the existing promise
            if (this.loadingGroups[group]) {
                return this.settingsPromises[group];
            }

            // Mark this group as loading
            this.loadingGroups = { ...this.loadingGroups, [group]: true };

            // Create and store the promise for this fetch operation
            const promise = (async () => {
                try {
                    const response = await api.get(`/admin/cms/settings/group/${group}`);
                    // Settings endpoint returns: { success: true, data: { key: value, ... } }
                    // Extract the data object directly (not using parseResponse which is for arrays)
                    const settingsData = response.data?.data || response.data || {};

                    // Merge into settings state
                    this.settings = { ...this.settings, ...settingsData };
                    return settingsData;
                }
                catch (error) {
                    console.error(`Error fetching ${group} settings:`, error);
                    return {};
                } finally {
                    // Mark this group as no longer loading and clear the promise
                    this.loadingGroups = { ...this.loadingGroups, [group]: false };
                    delete this.settingsPromises[group];
                }
            })();

            this.settingsPromises = { ...this.settingsPromises, [group]: promise };
            return promise;
        },

        async fetchContents(params = {}) {
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

        async fetchContent(slug) {
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
    },
});


import { defineStore } from 'pinia';
import axios from 'axios';
import { parseResponse, ensureArray } from '../utils/responseParser';

export const useCmsStore = defineStore('cms', {
    state: () => ({
        contents: [],
        categories: [],
        tags: [],
        media: [],
        currentContent: null,
        loading: false,
    }),

    actions: {
        async fetchContents(params = {}) {
            this.loading = true;
            try {
                const response = await axios.get('/api/v1/cms/contents', { params });
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
                const response = await axios.get(`/api/v1/cms/contents/${slug}`);
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
                const response = await axios.get('/api/v1/cms/categories');
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
                const response = await axios.get('/api/v1/cms/tags');
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


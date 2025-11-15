import { defineStore } from 'pinia';
import axios from 'axios';

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
                this.contents = response.data.data || response.data;
                return response.data;
            } catch (error) {
                console.error('Error fetching contents:', error);
                return null;
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
                this.categories = response.data;
                return response.data;
            } catch (error) {
                console.error('Error fetching categories:', error);
                return [];
            }
        },

        async fetchTags() {
            try {
                const response = await axios.get('/api/v1/cms/tags');
                this.tags = response.data;
                return response.data;
            } catch (error) {
                console.error('Error fetching tags:', error);
                return [];
            }
        },
    },
});


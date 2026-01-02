import { defineStore } from 'pinia';
import api from '@/services/api';

export const useContentStore = defineStore('content', {
    state: () => ({
        currentContent: null,
        loading: false,
    }),

    actions: {
        async fetchContent(slug) {
            this.loading = true;
            try {
                // Determine endpoint based on type? 
                // Currently code uses /cms/contents/{slug} generic endpoint
                const response = await api.get(`/cms/contents/${slug}`);
                this.currentContent = response.data.data || response.data;
            } catch (e) {
                console.error('Failed to fetch content:', e);
                this.currentContent = null;
            } finally {
                this.loading = false;
            }
        },

        clearContent() {
            this.currentContent = null;
        }
    }
});

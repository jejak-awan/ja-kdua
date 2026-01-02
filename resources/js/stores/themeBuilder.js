import { defineStore } from 'pinia';
import api from '@/services/api';

export const useThemeBuilderStore = defineStore('themeBuilder', {
    state: () => ({
        currentTemplate: null,
        loading: false,
    }),

    actions: {
        async resolveTemplate(context) {
            this.loading = true;
            try {
                // context: { url, route_name, post_type, is_home, is_404 }
                const response = await api.post('/theme-templates/resolve', context);
                // API returns { data: Template|null }
                this.currentTemplate = response.data?.data || null;
            } catch (e) {
                console.error('Failed to resolve theme template:', e);
                this.currentTemplate = null;
            } finally {
                this.loading = false;
            }
        },

        clearTemplate() {
            this.currentTemplate = null;
        }
    }
});

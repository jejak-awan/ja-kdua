import { ref } from 'vue';
import api from '../services/api';

// Global shared state for menus to avoid double fetching across components
const menus = ref({});
const loading = ref(false);
const error = ref(null);

/**
 * Composable for menu management in frontend
 */
export function useMenu() {
    /**
     * Fetch menu items by location slug
     * @param {string} location 
     * @returns {Promise<Object|null>}
     */
    const fetchMenuByLocation = async (location) => {
        // Return cached menu if available
        if (menus.value[location]) {
            return menus.value[location];
        }

        loading.value = true;
        error.value = null;

        try {
            // Use the public endpoint added to routes/api.php
            const response = await api.get(`/cms/menus/location/${location}`);
            const data = response.data?.data || response.data;

            if (data) {
                menus.value[location] = data;
            }

            return data;
        } catch (err) {
            console.error(`Failed to fetch menu for location ${location}:`, err);
            error.value = err.message || 'Failed to load menu';
            return null;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Clear menu cache
     * @param {string|null} location If null, clears all
     */
    const clearMenuCache = (location = null) => {
        if (location) {
            delete menus.value[location];
        } else {
            menus.value = {};
        }
    };

    return {
        menus,
        loading,
        error,
        fetchMenuByLocation,
        clearMenuCache
    };
}

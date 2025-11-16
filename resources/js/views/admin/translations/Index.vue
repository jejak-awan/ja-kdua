<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Translations</h1>
            <p class="text-sm text-gray-500 mt-1">Language: {{ languageCode }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-6">
            <div class="mb-4">
                <input v-model="search" type="text" placeholder="Search translations..." class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div v-if="loading" class="text-center py-8"><p class="text-gray-500">Loading...</p></div>
            <div v-else-if="filteredTranslations.length === 0" class="text-center py-8"><p class="text-gray-500">No translations found</p></div>
            <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Key</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Translation</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="translation in filteredTranslations" :key="translation.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ translation.key }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ translation.value || '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button @click="editTranslation(translation)" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../../services/api';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const route = useRoute();
const languageCode = route.params.lang || 'en';

const translations = ref([]);
const loading = ref(false);
const search = ref('');

const filteredTranslations = computed(() => {
    if (!search.value) return translations.value;
    const searchLower = search.value.toLowerCase();
    return translations.value.filter(t => t.key.toLowerCase().includes(searchLower) || (t.value && t.value.toLowerCase().includes(searchLower)));
});

const fetchTranslations = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/admin/cms/translations/${languageCode}`);
        const { data } = parseResponse(response);
        translations.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch translations:', error);
        translations.value = [];
    } finally {
        loading.value = false;
    }
};

const editTranslation = (translation) => {
    const value = prompt('Enter translation:', translation.value || '');
    if (value !== null) {
        updateTranslation(translation.id, value);
    }
};

const updateTranslation = async (id, value) => {
    try {
        await api.put(`/admin/cms/translations/${id}`, { value });
        await fetchTranslations();
    } catch (error) {
        console.error('Failed to update translation:', error);
        alert('Failed to update translation');
    }
};

onMounted(() => {
    fetchTranslations();
});
</script>


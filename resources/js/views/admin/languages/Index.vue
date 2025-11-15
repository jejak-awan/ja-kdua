<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Languages</h1>
            <button @click="showCreateModal = true" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Language
            </button>
        </div>
        <div class="bg-white shadow rounded-lg">
            <div v-if="loading" class="p-6 text-center"><p class="text-gray-500">Loading...</p></div>
            <div v-else-if="languages.length === 0" class="p-6 text-center"><p class="text-gray-500">No languages found</p></div>
            <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Default</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="lang in languages" :key="lang.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ lang.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ lang.code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span v-if="lang.is_default" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Default</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <router-link :to="{ name: 'translations', params: { lang: lang.code } }" class="text-indigo-600 hover:text-indigo-900">Translations</router-link>
                                <button @click="setDefault(lang)" v-if="!lang.is_default" class="text-green-600 hover:text-green-900">Set Default</button>
                                <button @click="deleteLanguage(lang)" class="text-red-600 hover:text-red-900">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../../services/api';

const languages = ref([]);
const loading = ref(false);
const showCreateModal = ref(false);

const fetchLanguages = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/languages');
        languages.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch languages:', error);
    } finally {
        loading.value = false;
    }
};

const setDefault = async (lang) => {
    try {
        await api.post(`/admin/cms/languages/${lang.id}/set-default`);
        await fetchLanguages();
    } catch (error) {
        console.error('Failed to set default language:', error);
        alert('Failed to set default language');
    }
};

const deleteLanguage = async (lang) => {
    if (!confirm(`Delete language "${lang.name}"?`)) return;
    try {
        await api.delete(`/admin/cms/languages/${lang.id}`);
        await fetchLanguages();
    } catch (error) {
        console.error('Failed to delete language:', error);
        alert('Failed to delete language');
    }
};

onMounted(() => {
    fetchLanguages();
});
</script>


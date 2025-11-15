<template>
    <div>
        <div class="mb-6"><h1 class="text-2xl font-bold text-gray-900">Plugins</h1></div>
        <div class="bg-white shadow rounded-lg">
            <div v-if="loading" class="p-6 text-center"><p class="text-gray-500">Loading...</p></div>
            <div v-else-if="plugins.length === 0" class="p-6 text-center"><p class="text-gray-500">No plugins found</p></div>
            <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Version</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="plugin in plugins" :key="plugin.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ plugin.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ plugin.version || '1.0.0' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="plugin.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">{{ plugin.is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button v-if="!plugin.is_active" @click="activatePlugin(plugin)" class="text-green-600 hover:text-green-900">Activate</button>
                                <button v-else @click="deactivatePlugin(plugin)" class="text-yellow-600 hover:text-yellow-900">Deactivate</button>
                                <button @click="openSettings(plugin)" class="text-indigo-600 hover:text-indigo-900">Settings</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <PluginSettingsModal v-if="showSettingsModal" @close="showSettingsModal = false" @saved="handleSettingsSaved" :plugin="selectedPlugin" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../../services/api';
import PluginSettingsModal from '../../../components/plugins/PluginSettingsModal.vue';

const plugins = ref([]);
const loading = ref(false);
const showSettingsModal = ref(false);
const selectedPlugin = ref(null);

const fetchPlugins = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/plugins');
        plugins.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch plugins:', error);
    } finally {
        loading.value = false;
    }
};

const activatePlugin = async (plugin) => {
    try {
        await api.post(`/admin/cms/plugins/${plugin.id}/activate`);
        await fetchPlugins();
    } catch (error) {
        console.error('Failed to activate plugin:', error);
        alert('Failed to activate plugin');
    }
};

const deactivatePlugin = async (plugin) => {
    try {
        await api.post(`/admin/cms/plugins/${plugin.id}/deactivate`);
        await fetchPlugins();
    } catch (error) {
        console.error('Failed to deactivate plugin:', error);
        alert('Failed to deactivate plugin');
    }
};

const openSettings = (plugin) => {
    selectedPlugin.value = plugin;
    showSettingsModal.value = true;
};

const handleSettingsSaved = () => {
    fetchPlugins();
    showSettingsModal.value = false;
};

onMounted(() => {
    fetchPlugins();
});
</script>


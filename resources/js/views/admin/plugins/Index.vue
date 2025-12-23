<template>
    <div>
        <div class="mb-6"><h1 class="text-2xl font-bold text-foreground">{{ t('features.developer.plugins.title') }}</h1></div>
        <div class="bg-card shadow rounded-lg">
            <div v-if="loading" class="p-6 text-center"><p class="text-muted-foreground">{{ t('features.developer.plugins.loading') }}</p></div>
            <div v-else-if="plugins.length === 0" class="p-6 text-center"><p class="text-muted-foreground">{{ t('features.developer.plugins.empty') }}</p></div>
            <table v-else class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">{{ t('features.developer.plugins.table.name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">{{ t('features.developer.plugins.table.version') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">{{ t('features.developer.plugins.table.status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase">{{ t('features.developer.plugins.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    <tr v-for="plugin in plugins" :key="plugin.id" class="hover:bg-muted">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">{{ plugin.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">{{ plugin.version || '1.0.0' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="plugin.is_active ? 'bg-green-500/20 text-green-400' : 'bg-secondary text-secondary-foreground'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">{{ plugin.is_active ? t('features.developer.plugins.status.active') : t('features.developer.plugins.status.inactive') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button v-if="!plugin.is_active" @click="activatePlugin(plugin)" class="text-green-600 hover:text-green-900">{{ t('features.developer.plugins.actions.activate') }}</button>
                                <button v-else @click="deactivatePlugin(plugin)" class="text-yellow-600 hover:text-yellow-900">{{ t('features.developer.plugins.actions.deactivate') }}</button>
                                <button @click="openSettings(plugin)" class="text-indigo-600 hover:text-indigo-900">{{ t('features.developer.plugins.actions.settings') }}</button>
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
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import PluginSettingsModal from '../../../components/plugins/PluginSettingsModal.vue';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const { t } = useI18n();

const plugins = ref([]);
const loading = ref(false);
const showSettingsModal = ref(false);
const selectedPlugin = ref(null);

const fetchPlugins = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/plugins');
        const { data } = parseResponse(response);
        plugins.value = ensureArray(data);
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
        alert(t('features.developer.plugins.messages.failed_activate'));
    }
};

const deactivatePlugin = async (plugin) => {
    try {
        await api.post(`/admin/cms/plugins/${plugin.id}/deactivate`);
        await fetchPlugins();
    } catch (error) {
        console.error('Failed to deactivate plugin:', error);
        alert(t('features.developer.plugins.messages.failed_deactivate'));
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


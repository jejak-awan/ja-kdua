<template>
    <div>
        <div class="mb-6"><h1 class="text-2xl font-bold text-foreground">{{ t('features.developer.plugins.title') }}</h1></div>
        <div class="bg-card border border-border rounded-lg">
            <div v-if="loading" class="p-6 text-center"><p class="text-muted-foreground">{{ t('features.developer.plugins.loading') }}</p></div>
            <div v-else-if="plugins.length === 0" class="p-6 text-center"><p class="text-muted-foreground">{{ t('features.developer.plugins.empty') }}</p></div>
            <table v-else class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground">{{ t('features.developer.plugins.table.name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground">{{ t('features.developer.plugins.table.version') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground">{{ t('features.developer.plugins.table.status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground">{{ t('features.developer.plugins.table.actions') }}</th>
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
                                <button v-if="!plugin.is_active" @click="togglePlugin(plugin)" class="text-green-600 hover:text-green-900">{{ t('features.developer.plugins.actions.activate') }}</button>
                                <button v-else @click="togglePlugin(plugin)" class="text-yellow-600 hover:text-yellow-900">{{ t('features.developer.plugins.actions.deactivate') }}</button>
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

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { useToast } from '../../../composables/useToast';
import PluginSettingsModal from '../../../components/plugins/PluginSettingsModal.vue';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const { t } = useI18n();
const toast = useToast();

const plugins = ref<any[]>([]);
const loading = ref(false);
const showSettingsModal = ref(false);
const selectedPlugin = ref<any>(null);

const fetchPlugins = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/ja/plugins');
        const { data } = parseResponse(response);
        plugins.value = ensureArray(data);
    } catch (error: any) {
        console.error('Failed to fetch plugins:', error);
        toast.error.default(t('features.developer.plugins.messages.failed_fetch')); // Keep generic message or use fromResponse if applicable, but fetch usually generic
    } finally {
        loading.value = false;
    }
};

const togglePlugin = async (plugin: any) => {
    try {
        if (plugin.is_active) {
            await api.post(`/admin/ja/plugins/${plugin.id}/deactivate`);
            plugin.is_active = false;
            toast.success.action(t('features.developer.plugins.messages.deactivated'));
        } else {
            await api.post(`/admin/ja/plugins/${plugin.id}/activate`);
            plugin.is_active = true;
            toast.success.action(t('features.developer.plugins.messages.activated'));
        }
    } catch (error: any) {
        console.error('Failed to toggle plugin:', error);
        toast.error.fromResponse(error);
    }
};

const openSettings = (plugin: any) => {
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


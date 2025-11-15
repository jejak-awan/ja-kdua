<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
            <p class="mt-1 text-sm text-gray-500">Manage your CMS settings and configuration</p>
        </div>

        <div v-if="loading" class="bg-white shadow rounded-lg p-12 text-center">
            <p class="text-gray-500">Loading settings...</p>
        </div>

        <div v-else class="bg-white shadow rounded-lg">
            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px overflow-x-auto">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            'px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap',
                            activeTab === tab.id
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                    >
                        {{ tab.label }}
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div v-if="currentSettings.length === 0" class="text-center py-8">
                        <p class="text-gray-500">No settings found for this group</p>
                    </div>

                    <div v-else class="space-y-6">
                        <div
                            v-for="setting in currentSettings"
                            :key="setting.id"
                            class="border-b border-gray-200 pb-6 last:border-b-0 last:pb-0"
                        >
                            <label class="block text-sm font-medium text-gray-900 mb-1">
                                {{ setting.key }}
                                <span v-if="setting.description" class="text-xs font-normal text-gray-500 ml-2">
                                    - {{ setting.description }}
                                </span>
                            </label>

                            <!-- String Input -->
                            <input
                                v-if="setting.type === 'string'"
                                v-model="formData[setting.key]"
                                type="text"
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            />

                            <!-- Number Input -->
                            <input
                                v-else-if="setting.type === 'integer'"
                                v-model.number="formData[setting.key]"
                                type="number"
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            />

                            <!-- Boolean Checkbox -->
                            <div v-else-if="setting.type === 'boolean'" class="mt-1">
                                <label class="flex items-center">
                                    <input
                                        v-model="formData[setting.key]"
                                        type="checkbox"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">
                                        {{ formData[setting.key] ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </label>
                            </div>

                            <!-- Textarea -->
                            <textarea
                                v-else-if="setting.type === 'text'"
                                v-model="formData[setting.key]"
                                rows="4"
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            ></textarea>

                            <!-- JSON Editor -->
                            <div v-else-if="setting.type === 'json'" class="mt-1">
                                <textarea
                                    v-model="formData[setting.key]"
                                    rows="6"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                                    placeholder='{"key": "value"}'
                                ></textarea>
                                <p class="mt-1 text-xs text-gray-500">Enter valid JSON format</p>
                            </div>

                            <!-- Current Value Display -->
                            <p v-if="setting.value" class="mt-1 text-xs text-gray-400">
                                Current: {{ formatValue(setting.value, setting.type) }}
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-4 pt-6 border-t">
                        <button
                            type="button"
                            @click="resetForm"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                        >
                            Reset
                        </button>
                        <button
                            type="submit"
                            :disabled="saving"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ saving ? 'Saving...' : 'Save Settings' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import api from '../../../services/api';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const loading = ref(false);
const saving = ref(false);
const activeTab = ref('general');
const settings = ref([]);
const formData = ref({});

const tabs = [
    { id: 'general', label: 'General' },
    { id: 'email', label: 'Email' },
    { id: 'seo', label: 'SEO' },
    { id: 'security', label: 'Security' },
    { id: 'performance', label: 'Performance' },
    { id: 'media', label: 'Media' },
];

const currentSettings = computed(() => {
    if (!settings.value || !Array.isArray(settings.value)) {
        return [];
    }
    return settings.value.filter(s => s && s.group === activeTab.value);
});

const fetchSettings = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/settings');
        const { data } = parseResponse(response);
        settings.value = ensureArray(data);
        initializeFormData();
    } catch (error) {
        settings.value = [];
    } finally {
        loading.value = false;
    }
};

const initializeFormData = () => {
    formData.value = {};
    settings.value.forEach(setting => {
        let value = setting.value;
        
        // Cast value based on type
        if (setting.type === 'boolean') {
            value = value === '1' || value === 1 || value === 'true' || value === true;
        } else if (setting.type === 'integer') {
            value = value ? parseInt(value) : null;
        } else if (setting.type === 'json') {
            if (typeof value === 'string') {
                try {
                    value = JSON.parse(value);
                    value = JSON.stringify(value, null, 2);
                } catch (e) {
                    value = value;
                }
            } else {
                value = JSON.stringify(value, null, 2);
            }
        }
        
        formData.value[setting.key] = value;
    });
};

const resetForm = () => {
    initializeFormData();
};

const formatValue = (value, type) => {
    if (type === 'boolean') {
        return value ? 'Yes' : 'No';
    } else if (type === 'json') {
        return typeof value === 'string' ? value : JSON.stringify(value);
    }
    return value;
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        // Prepare settings array for bulk update
        const settingsToUpdate = currentSettings.value.map(setting => {
            let value = formData.value[setting.key];
            
            // Handle JSON type
            if (setting.type === 'json' && typeof value === 'string') {
                try {
                    value = JSON.parse(value);
                } catch (e) {
                    // Invalid JSON, keep original value
                }
            }
            
            return {
                key: setting.key,
                value: value,
                type: setting.type,
                group: setting.group,
            };
        });

        await api.post('/admin/cms/settings/bulk-update', {
            settings: settingsToUpdate,
        });
        
        alert('Settings saved successfully');
        await fetchSettings();
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to save settings');
    } finally {
        saving.value = false;
    }
};

watch(activeTab, () => {
    // Reset form when switching tabs
    initializeFormData();
});

onMounted(() => {
    fetchSettings();
});
</script>

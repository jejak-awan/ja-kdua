<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-white">
                    <h3 class="text-lg font-semibold">Theme Settings: {{ theme?.name }}</h3>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <div v-if="settings.length === 0" class="text-center py-8">
                        <p class="text-gray-500">No settings available for this theme</p>
                    </div>
                    <div v-else>
                        <div
                            v-for="setting in settings"
                            :key="setting.key"
                            class="mb-4"
                        >
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ setting.label }}
                            </label>
                            <input
                                v-if="setting.type === 'text' || setting.type === 'number' || setting.type === 'email' || setting.type === 'url'"
                                v-model="form[setting.key]"
                                :type="setting.type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            />
                            <textarea
                                v-else-if="setting.type === 'textarea'"
                                v-model="form[setting.key]"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            ></textarea>
                            <select
                                v-else-if="setting.type === 'select'"
                                v-model="form[setting.key]"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option
                                    v-for="option in setting.options"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                            <div v-else-if="setting.type === 'checkbox'" class="flex items-center">
                                <input
                                    v-model="form[setting.key]"
                                    type="checkbox"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                />
                                <span class="ml-2 text-sm text-gray-700">{{ setting.description }}</span>
                            </div>
                            <p v-if="setting.description && setting.type !== 'checkbox'" class="mt-1 text-xs text-gray-500">
                                {{ setting.description }}
                            </p>
                        </div>
                    </div>
                </form>

                <div class="flex items-center justify-end space-x-3 p-6 border-t sticky bottom-0 bg-white">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="handleSubmit"
                        :disabled="saving"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ saving ? 'Saving...' : 'Save Settings' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';

const props = defineProps({
    theme: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

const settings = ref([]);
const form = ref({});
const saving = ref(false);

const fetchSettings = async () => {
    if (!props.theme) return;
    
    try {
        const response = await api.get(`/admin/cms/themes/${props.theme.id}/settings`);
        settings.value = response.data.settings || [];
        
        // Load current values
        const valuesResponse = await api.get(`/admin/cms/themes/${props.theme.id}/settings/values`);
        form.value = valuesResponse.data || {};
    } catch (error) {
        console.error('Failed to fetch theme settings:', error);
    }
};

const handleSubmit = async () => {
    if (!props.theme) return;
    
    saving.value = true;
    try {
        await api.put(`/admin/cms/themes/${props.theme.id}/settings`, form.value);
        emit('saved');
    } catch (error) {
        console.error('Failed to save settings:', error);
        alert('Failed to save settings');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchSettings();
});
</script>


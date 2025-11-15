<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">Custom CSS: {{ theme?.name }}</h3>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 p-6 overflow-hidden">
                    <textarea
                        v-model="css"
                        rows="20"
                        class="w-full h-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                        placeholder="/* Enter your custom CSS here */"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 p-6 border-t">
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
                        {{ saving ? 'Saving...' : 'Save CSS' }}
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

const css = ref('');
const saving = ref(false);

const fetchCSS = async () => {
    if (!props.theme) return;
    
    try {
        const response = await api.get(`/admin/cms/themes/${props.theme.id}/custom-css`);
        css.value = response.data.css || '';
    } catch (error) {
        console.error('Failed to fetch custom CSS:', error);
    }
};

const handleSubmit = async () => {
    if (!props.theme) return;
    
    saving.value = true;
    try {
        await api.put(`/admin/cms/themes/${props.theme.id}/custom-css`, { css: css.value });
        emit('saved');
    } catch (error) {
        console.error('Failed to save CSS:', error);
        alert('Failed to save CSS');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchCSS();
});
</script>


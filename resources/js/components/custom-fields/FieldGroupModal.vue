<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">
                        {{ fieldGroup ? 'Edit Field Group' : 'Create Field Group' }}
                    </h3>
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Field group name"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Description
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Field group description"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Attach To
                        </label>
                        <select
                            v-model="form.attachable_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option :value="null">None</option>
                            <option value="App\\Models\\Content">Content</option>
                            <option value="App\\Models\\Category">Category</option>
                            <option value="App\\Models\\Media">Media</option>
                        </select>
                    </div>
                </form>

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
                        {{ saving ? 'Saving...' : (fieldGroup ? 'Update' : 'Create') }}
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
    fieldGroup: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);

const form = ref({
    name: '',
    description: '',
    attachable_type: null,
});

const loadFieldGroup = () => {
    if (props.fieldGroup) {
        form.value = {
            name: props.fieldGroup.name || '',
            description: props.fieldGroup.description || '',
            attachable_type: props.fieldGroup.attachable_type || null,
        };
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        if (props.fieldGroup) {
            await api.put(`/admin/cms/field-groups/${props.fieldGroup.id}`, form.value);
        } else {
            await api.post('/admin/cms/field-groups', form.value);
        }
        emit('saved');
    } catch (error) {
        console.error('Failed to save field group:', error);
        alert(error.response?.data?.message || 'Failed to save field group');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadFieldGroup();
});
</script>


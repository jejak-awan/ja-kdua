<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-white">
                    <h3 class="text-lg font-semibold">
                        {{ field ? 'Edit Custom Field' : 'Create Custom Field' }}
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
                            Label <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.label"
                            type="text"
                            required
                            @input="generateName"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Field label"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                            placeholder="field_name"
                        />
                        <p class="mt-1 text-xs text-gray-500">Field identifier (snake_case)</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Type <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.type"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="text">Text</option>
                            <option value="textarea">Textarea</option>
                            <option value="number">Number</option>
                            <option value="email">Email</option>
                            <option value="url">URL</option>
                            <option value="date">Date</option>
                            <option value="datetime">DateTime</option>
                            <option value="boolean">Boolean</option>
                            <option value="select">Select</option>
                            <option value="multiselect">Multi Select</option>
                            <option value="radio">Radio</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="file">File</option>
                            <option value="image">Image</option>
                            <option value="json">JSON</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Field Group
                        </label>
                        <select
                            v-model="form.field_group_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option :value="null">No Group</option>
                            <option
                                v-for="group in fieldGroups"
                                :key="group.id"
                                :value="group.id"
                            >
                                {{ group.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Default Value
                        </label>
                        <input
                            v-model="form.default_value"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Default value"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Options (for select, radio, checkbox)
                        </label>
                        <textarea
                            v-model="form.options"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                            placeholder="option1,option2,option3"
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">Comma-separated values</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Instructions
                        </label>
                        <textarea
                            v-model="form.instructions"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Field instructions for users"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center">
                            <input
                                v-model="form.is_required"
                                type="checkbox"
                                id="is_required"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            />
                            <label for="is_required" class="ml-2 block text-sm text-gray-900">
                                Required
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input
                                v-model="form.is_searchable"
                                type="checkbox"
                                id="is_searchable"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            />
                            <label for="is_searchable" class="ml-2 block text-sm text-gray-900">
                                Searchable
                            </label>
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
                        {{ saving ? 'Saving...' : (field ? 'Update' : 'Create') }}
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
    field: {
        type: Object,
        default: null,
    },
    fieldGroups: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);

const form = ref({
    label: '',
    name: '',
    type: 'text',
    field_group_id: null,
    default_value: '',
    options: '',
    instructions: '',
    is_required: false,
    is_searchable: false,
});

const generateName = () => {
    if (!form.value.name || form.value.name === slugify(form.value.label)) {
        form.value.name = slugify(form.value.label);
    }
};

const slugify = (text) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '_')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '_')
        .replace(/^_+/, '')
        .replace(/_+$/, '');
};

const loadField = () => {
    if (props.field) {
        form.value = {
            label: props.field.label || '',
            name: props.field.name || '',
            type: props.field.type || 'text',
            field_group_id: props.field.field_group_id || null,
            default_value: props.field.default_value || '',
            options: Array.isArray(props.field.options) ? props.field.options.join(',') : (props.field.options || ''),
            instructions: props.field.instructions || '',
            is_required: props.field.is_required || false,
            is_searchable: props.field.is_searchable || false,
        };
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        const payload = {
            ...form.value,
            options: form.value.options ? form.value.options.split(',').map(o => o.trim()) : [],
        };
        
        if (props.field) {
            await api.put(`/admin/cms/custom-fields/${props.field.id}`, payload);
        } else {
            await api.post('/admin/cms/custom-fields', payload);
        }
        emit('saved');
    } catch (error) {
        console.error('Failed to save field:', error);
        alert(error.response?.data?.message || 'Failed to save field');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadField();
});
</script>


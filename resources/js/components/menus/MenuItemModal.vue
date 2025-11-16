<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">{{ item ? 'Edit Menu Item' : 'Add Menu Item' }}</h3>
                    <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Label <span class="text-red-500">*</span></label>
                        <input v-model="form.label" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                        <select v-model="form.type" required @change="handleTypeChange" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="link">Link</option>
                            <option value="page">Page</option>
                            <option value="category">Category</option>
                            <option value="content">Content</option>
                        </select>
                    </div>
                    <div v-if="form.type === 'link'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL <span class="text-red-500">*</span></label>
                        <input v-model="form.url" type="url" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div v-else-if="form.type === 'page'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Page</label>
                        <select v-model="form.target_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option :value="null">Select page...</option>
                        </select>
                    </div>
                    <div v-else-if="form.type === 'category'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select v-model="form.target_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option :value="null">Select category...</option>
                        </select>
                    </div>
                    <div v-else-if="form.type === 'content'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                        <select v-model="form.target_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option :value="null">Select content...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CSS Classes</label>
                        <input v-model="form.css_classes" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="flex items-center">
                        <input v-model="form.open_in_new_tab" type="checkbox" id="new_tab" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="new_tab" class="ml-2 block text-sm text-gray-900">Open in new tab</label>
                    </div>
                </form>
                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button @click="$emit('close')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button @click="handleSubmit" :disabled="saving" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">{{ saving ? 'Saving...' : (item ? 'Update' : 'Create') }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';

const props = defineProps({
    item: { type: Object, default: null },
    menuId: { type: [String, Number], required: true },
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);
const form = ref({
    label: '',
    type: 'link',
    url: '',
    target_id: null,
    css_classes: '',
    open_in_new_tab: false,
    parent_id: null,
});

const handleTypeChange = () => {
    form.value.url = '';
    form.value.target_id = null;
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        const payload = { ...form.value, menu_id: props.menuId };
        if (props.item) {
            await api.put(`/admin/cms/menu-items/${props.item.id}`, payload);
        } else {
            await api.post('/admin/cms/menu-items', payload);
        }
        emit('saved');
    } catch (error) {
        console.error('Failed to save menu item:', error);
        alert('Failed to save menu item');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    if (props.item) {
        form.value = { ...props.item };
    }
});
</script>


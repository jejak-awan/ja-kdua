<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">Edit Media</h3>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <div v-if="loading" class="text-center py-8">
                        <p class="text-gray-500">Loading...</p>
                    </div>

                    <form v-else @submit.prevent="handleSubmit" class="space-y-4">
                        <!-- Preview -->
                        <div v-if="form.url && form.mime_type?.startsWith('image/')" class="mb-4">
                            <img :src="form.url" :alt="form.alt" class="w-full h-64 object-contain bg-gray-100 rounded-lg" />
                        </div>

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Name
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            />
                        </div>

                        <!-- Alt Text -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Alt Text
                            </label>
                            <input
                                v-model="form.alt"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Alternative text for accessibility"
                            />
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            ></textarea>
                        </div>

                        <!-- Folder -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Folder
                            </label>
                            <select
                                v-model="form.folder_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option :value="null">No Folder</option>
                                <option
                                    v-for="folder in folders"
                                    :key="folder.id"
                                    :value="folder.id"
                                >
                                    {{ folder.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Media Info -->
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                            <div>
                                <p class="text-xs text-gray-500">Type</p>
                                <p class="text-sm text-gray-900">{{ form.mime_type }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Size</p>
                                <p class="text-sm text-gray-900">{{ formatFileSize(form.size) }}</p>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
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
                        {{ saving ? 'Saving...' : 'Save Changes' }}
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
    media: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close', 'updated']);

const loading = ref(false);
const saving = ref(false);
const folders = ref([]);

const form = ref({
    name: '',
    alt: '',
    description: '',
    folder_id: null,
    url: '',
    mime_type: '',
    size: 0,
});

const fetchFolders = async () => {
    try {
        const response = await api.get('/admin/cms/media-folders');
        folders.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch folders:', error);
    }
};

const loadMedia = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/admin/cms/media/${props.media.id}`);
        const media = response.data;
        
        form.value = {
            name: media.name || '',
            alt: media.alt || '',
            description: media.description || '',
            folder_id: media.folder_id || null,
            url: media.url || '',
            mime_type: media.mime_type || '',
            size: media.size || 0,
        };
    } catch (error) {
        console.error('Failed to load media:', error);
    } finally {
        loading.value = false;
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        await api.put(`/admin/cms/media/${props.media.id}`, {
            name: form.value.name,
            alt: form.value.alt,
            description: form.value.description,
            folder_id: form.value.folder_id,
        });
        
        emit('updated');
    } catch (error) {
        console.error('Failed to update media:', error);
        alert('Failed to update media');
    } finally {
        saving.value = false;
    }
};

const formatFileSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

onMounted(() => {
    loadMedia();
    fetchFolders();
});
</script>


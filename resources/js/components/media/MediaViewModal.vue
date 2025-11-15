<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-75" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">{{ media.name }}</h3>
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
                    <!-- Image Preview -->
                    <div v-if="media.mime_type?.startsWith('image/')" class="mb-6">
                        <img :src="media.url" :alt="media.alt || media.name" class="w-full h-auto rounded-lg" />
                    </div>

                    <!-- Media Info -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Details</h4>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-xs text-gray-500">Name</dt>
                                    <dd class="text-sm text-gray-900">{{ media.name }}</dd>
                                </div>
                                <div v-if="media.alt">
                                    <dt class="text-xs text-gray-500">Alt Text</dt>
                                    <dd class="text-sm text-gray-900">{{ media.alt }}</dd>
                                </div>
                                <div v-if="media.description">
                                    <dt class="text-xs text-gray-500">Description</dt>
                                    <dd class="text-sm text-gray-900">{{ media.description }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500">Type</dt>
                                    <dd class="text-sm text-gray-900">{{ media.mime_type }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500">Size</dt>
                                    <dd class="text-sm text-gray-900">{{ formatFileSize(media.size) }}</dd>
                                </div>
                                <div v-if="media.folder">
                                    <dt class="text-xs text-gray-500">Folder</dt>
                                    <dd class="text-sm text-gray-900">{{ media.folder.name }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-3">URL</h4>
                            <div class="flex items-center space-x-2">
                                <input
                                    :value="media.url"
                                    readonly
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm"
                                />
                                <button
                                    @click="copyUrl"
                                    class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm"
                                >
                                    Copy
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Actions for Images -->
                    <div v-if="media.mime_type?.startsWith('image/')" class="mt-6 pt-6 border-t">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Actions</h4>
                        <div class="flex space-x-2">
                            <button
                                @click="generateThumbnail"
                                :disabled="generatingThumbnail"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 text-sm"
                            >
                                {{ generatingThumbnail ? 'Generating...' : 'Generate Thumbnail' }}
                            </button>
                            <button
                                @click="showResizeModal = true"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm"
                            >
                                Resize
                            </button>
                        </div>
                    </div>

                    <!-- Usage Detail -->
                    <div class="mt-6 pt-6 border-t">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Usage</h4>
                        <div v-if="loadingUsage" class="text-sm text-gray-500">
                            Loading usage information...
                        </div>
                        <div v-else-if="usageDetail && usageDetail.length > 0" class="space-y-2">
                            <div
                                v-for="usage in usageDetail"
                                :key="usage.id"
                                class="text-sm text-gray-600 p-2 bg-gray-50 rounded"
                            >
                                <div class="font-medium">{{ usage.type }}</div>
                                <div v-if="usage.title" class="text-xs text-gray-500">{{ usage.title }}</div>
                                <div v-if="usage.url" class="text-xs text-indigo-600 mt-1">
                                    <a :href="usage.url" target="_blank" class="hover:underline">{{ usage.url }}</a>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-500">
                            This media is not used anywhere.
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Resize Modal -->
        <ResizeMediaModal
            v-if="showResizeModal"
            @close="showResizeModal = false"
            @resized="handleResized"
            :media="media"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import ResizeMediaModal from './ResizeMediaModal.vue';

const props = defineProps({
    media: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close', 'updated']);

const loadingUsage = ref(false);
const usageDetail = ref([]);
const generatingThumbnail = ref(false);
const showResizeModal = ref(false);

const fetchUsageDetail = async () => {
    loadingUsage.value = true;
    try {
        const response = await api.get(`/admin/cms/media/${props.media.id}/usage`);
        usageDetail.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch usage detail:', error);
        usageDetail.value = [];
    } finally {
        loadingUsage.value = false;
    }
};

const generateThumbnail = async () => {
    generatingThumbnail.value = true;
    try {
        await api.post(`/admin/cms/media/${props.media.id}/thumbnail`);
        alert('Thumbnail generated successfully');
        emit('updated');
    } catch (error) {
        console.error('Failed to generate thumbnail:', error);
        alert(error.response?.data?.message || 'Failed to generate thumbnail');
    } finally {
        generatingThumbnail.value = false;
    }
};

const handleResized = () => {
    emit('updated');
    showResizeModal.value = false;
};

const copyUrl = () => {
    navigator.clipboard.writeText(props.media.url);
    alert('URL copied to clipboard!');
};

const formatFileSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

onMounted(() => {
    fetchUsageDetail();
});
</script>


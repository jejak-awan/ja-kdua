<template>
    <div class="media-picker">
        <button
            type="button"
            @click="showModal = true"
            class="w-full px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-indigo-500 hover:text-indigo-600 transition-colors"
        >
            {{ label || 'Select Media' }}
        </button>

        <!-- Media Picker Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            @click.self="showModal = false"
        >
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col">
                    <!-- Header -->
                    <div class="flex items-center justify-between p-4 border-b">
                        <h3 class="text-lg font-semibold">Select Media</h3>
                        <button
                            @click="showModal = false"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 overflow-y-auto p-4">
                        <div v-if="loading" class="text-center py-8">
                            <p class="text-gray-500">Loading media...</p>
                        </div>
                        <div v-else-if="mediaList.length === 0" class="text-center py-8">
                            <p class="text-gray-500">No media found</p>
                        </div>
                        <div v-else class="grid grid-cols-4 gap-4">
                            <div
                                v-for="media in mediaList"
                                :key="media.id"
                                @click="selectMedia(media)"
                                class="cursor-pointer border-2 rounded-lg overflow-hidden transition-all hover:border-indigo-500"
                                :class="selectedMedia?.id === media.id ? 'border-indigo-500' : 'border-gray-200'"
                            >
                                <div class="aspect-square bg-gray-100 flex items-center justify-center">
                                    <img
                                        v-if="media.type === 'image'"
                                        :src="media.url"
                                        :alt="media.name"
                                        class="w-full h-full object-cover"
                                    >
                                    <div v-else class="text-gray-400">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="p-2 bg-white">
                                    <p class="text-xs text-gray-600 truncate">{{ media.name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between p-4 border-t">
                        <button
                            @click="showUpload = true"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                        >
                            Upload New
                        </button>
                        <div class="flex space-x-2">
                            <button
                                @click="showModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                @click="confirmSelection"
                                :disabled="!selectedMedia"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                            >
                                Select
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <div
            v-if="showUpload"
            class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50"
            @click.self="showUpload = false"
        >
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold mb-4">Upload Media</h3>
                    <MediaUpload @uploaded="handleMediaUploaded" />
                    <button
                        @click="showUpload = false"
                        class="mt-4 w-full px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import api from '../services/api';
import MediaUpload from './MediaUpload.vue';

const props = defineProps({
    label: {
        type: String,
        default: 'Select Media',
    },
});

const emit = defineEmits(['selected']);

const showModal = ref(false);
const showUpload = ref(false);
const loading = ref(false);
const mediaList = ref([]);
const selectedMedia = ref(null);

const fetchMedia = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/media');
        mediaList.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch media:', error);
    } finally {
        loading.value = false;
    }
};

const selectMedia = (media) => {
    selectedMedia.value = media;
};

const confirmSelection = () => {
    if (selectedMedia.value) {
        emit('selected', selectedMedia.value);
        showModal.value = false;
        selectedMedia.value = null;
    }
};

const handleMediaUploaded = (response) => {
    // Response can be the media object directly or {media: {...}, url: "..."}
    const media = response.media || response;
    if (media) {
        mediaList.value.unshift(media);
        showUpload.value = false;
        selectMedia(media);
        confirmSelection();
    }
};

// Watch for modal open to fetch media
watch(showModal, (isOpen) => {
    if (isOpen) {
        fetchMedia();
    }
});
</script>


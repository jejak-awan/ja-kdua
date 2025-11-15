<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">Upload Media</h3>
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
                    <!-- Drag & Drop Area -->
                    <div
                        @drop="handleDrop"
                        @dragover.prevent
                        @dragenter.prevent
                        :class="[
                            'border-2 border-dashed rounded-lg p-12 text-center transition-colors',
                            isDragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'
                        ]"
                    >
                        <input
                            ref="fileInput"
                            type="file"
                            multiple
                            @change="handleFileSelect"
                            class="hidden"
                        />
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <p class="mt-4 text-sm text-gray-600">
                            <button
                                @click="$refs.fileInput.click()"
                                class="text-indigo-600 hover:text-indigo-700 font-medium"
                            >
                                Click to upload
                            </button>
                            or drag and drop
                        </p>
                        <p class="mt-2 text-xs text-gray-500">PNG, JPG, GIF, PDF, MP4 up to 10MB</p>
                    </div>

                    <!-- Selected Files -->
                    <div v-if="selectedFiles.length > 0" class="mt-6 space-y-2">
                        <h4 class="text-sm font-medium text-gray-700">Selected Files:</h4>
                        <div v-for="(file, index) in selectedFiles" :key="index" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ file.name }}</p>
                                <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
                            </div>
                            <button
                                @click="removeFile(index)"
                                class="text-red-600 hover:text-red-700"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Upload Progress -->
                    <div v-if="uploading" class="mt-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-700">Uploading...</span>
                            <span class="text-sm text-gray-500">{{ uploadProgress }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div
                                class="bg-indigo-600 h-2 rounded-full transition-all"
                                :style="{ width: uploadProgress + '%' }"
                            ></div>
                        </div>
                    </div>
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
                        @click="handleUpload"
                        :disabled="selectedFiles.length === 0 || uploading"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ uploading ? 'Uploading...' : `Upload ${selectedFiles.length} File${selectedFiles.length > 1 ? 's' : ''}` }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../../services/api';

const props = defineProps({
    folderId: {
        type: Number,
        default: null,
    },
});

const emit = defineEmits(['close', 'uploaded']);

const fileInput = ref(null);
const selectedFiles = ref([]);
const uploading = ref(false);
const uploadProgress = ref(0);
const isDragging = ref(false);

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    addFiles(files);
};

const handleDrop = (event) => {
    isDragging.value = false;
    const files = Array.from(event.dataTransfer.files);
    addFiles(files);
};

const addFiles = (files) => {
    files.forEach(file => {
        if (file.size > 10 * 1024 * 1024) {
            alert(`File ${file.name} is too large. Maximum size is 10MB.`);
            return;
        }
        if (!selectedFiles.value.find(f => f.name === file.name && f.size === file.size)) {
            selectedFiles.value.push(file);
        }
    });
};

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
};

const handleUpload = async () => {
    if (selectedFiles.value.length === 0) return;

    uploading.value = true;
    uploadProgress.value = 0;

    try {
        for (let i = 0; i < selectedFiles.value.length; i++) {
            const file = selectedFiles.value[i];
            const formData = new FormData();
            formData.append('file', file);
            if (props.folderId) {
                formData.append('folder_id', props.folderId);
            }

            await api.post('/admin/cms/media/upload', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
                onUploadProgress: (progressEvent) => {
                    const fileProgress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    const totalProgress = Math.round(((i * 100) + fileProgress) / selectedFiles.value.length);
                    uploadProgress.value = totalProgress;
                },
            });
        }

        emit('uploaded');
        selectedFiles.value = [];
        uploadProgress.value = 0;
    } catch (error) {
        console.error('Upload error:', error);
        alert('Failed to upload files');
    } finally {
        uploading.value = false;
    }
};

const formatFileSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>


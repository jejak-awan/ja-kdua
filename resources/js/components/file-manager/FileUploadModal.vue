<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">Upload Files</h3>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                        <input
                            ref="fileInput"
                            type="file"
                            multiple
                            @change="handleFileSelect"
                            class="hidden"
                        >
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <p class="mt-4 text-sm text-gray-600">
                            <button
                                @click="$refs.fileInput.click()"
                                class="text-indigo-600 hover:text-indigo-800"
                            >
                                Click to upload
                            </button>
                            or drag and drop
                        </p>
                        <p class="mt-2 text-xs text-gray-500">Multiple files supported</p>
                    </div>

                    <div v-if="selectedFiles.length > 0" class="mt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Selected Files:</h4>
                        <ul class="space-y-1">
                            <li
                                v-for="(file, index) in selectedFiles"
                                :key="index"
                                class="text-sm text-gray-600 flex items-center justify-between"
                            >
                                <span>{{ file.name }}</span>
                                <button
                                    @click="removeFile(index)"
                                    class="text-red-600 hover:text-red-800"
                                >
                                    Remove
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="handleUpload"
                        :disabled="uploading || selectedFiles.length === 0"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ uploading ? 'Uploading...' : 'Upload' }}
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
    path: {
        type: String,
        default: '/',
    },
});

const emit = defineEmits(['close', 'uploaded']);

const fileInput = ref(null);
const selectedFiles = ref([]);
const uploading = ref(false);

const handleFileSelect = (event) => {
    selectedFiles.value = Array.from(event.target.files);
};

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
};

const handleUpload = async () => {
    if (selectedFiles.value.length === 0) return;

    uploading.value = true;
    try {
        const formData = new FormData();
        selectedFiles.value.forEach(file => {
            formData.append('files[]', file);
        });
        formData.append('path', props.path);

        await api.post('/admin/cms/file-manager/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        
        emit('uploaded');
    } catch (error) {
        console.error('Failed to upload files:', error);
        alert('Failed to upload files');
    } finally {
        uploading.value = false;
    }
};
</script>


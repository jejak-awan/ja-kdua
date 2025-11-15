<template>
    <div class="media-upload">
        <div v-if="!preview" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
            <input
                ref="fileInput"
                type="file"
                @change="handleFileSelect"
                accept="image/*"
                class="hidden"
            />
            <button
                @click="$refs.fileInput.click()"
                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
            >
                Choose File
            </button>
            <p class="mt-2 text-sm text-gray-500">PNG, JPG, GIF up to 10MB</p>
        </div>

        <div v-else class="relative">
            <img :src="preview" alt="Preview" class="w-full h-64 object-cover rounded-lg" />
            <div class="mt-4 flex space-x-2">
                <button
                    @click="uploadFile"
                    :disabled="uploading"
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50"
                >
                    {{ uploading ? 'Uploading...' : 'Upload' }}
                </button>
                <button
                    @click="clearPreview"
                    class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700"
                >
                    Cancel
                </button>
            </div>
            <div v-if="uploadedMedia" class="mt-4 p-4 bg-green-50 rounded-lg">
                <p class="text-sm text-green-800">Uploaded successfully!</p>
                <p class="text-xs text-green-600 mt-1">{{ uploadedMedia.url }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../services/api';

const emit = defineEmits(['uploaded']);

const fileInput = ref(null);
const preview = ref(null);
const selectedFile = ref(null);
const uploading = ref(false);
const uploadedMedia = ref(null);

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 10 * 1024 * 1024) {
        alert('File size must be less than 10MB');
        return;
    }

    selectedFile.value = file;

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        preview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const uploadFile = async () => {
    if (!selectedFile.value) return;

    uploading.value = true;
    const formData = new FormData();
    formData.append('file', selectedFile.value);

    try {
        const response = await api.post('/admin/cms/media/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        uploadedMedia.value = response.data;
        emit('uploaded', response.data);
        
        // Clear after 3 seconds
        setTimeout(() => {
            clearPreview();
        }, 3000);
    } catch (error) {
        console.error('Upload error:', error);
        alert('Failed to upload file');
    } finally {
        uploading.value = false;
    }
};

const clearPreview = () => {
    preview.value = null;
    selectedFile.value = null;
    uploadedMedia.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};
</script>


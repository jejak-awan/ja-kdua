<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg max-w-2xl w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">{{ $t('features.media.modals.upload.title') }}</h3>
                    <button
                        @click="$emit('close')"
                        class="text-muted-foreground hover:text-muted-foreground"
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
                            isDragging ? 'border-indigo-500 bg-indigo-50' : 'border-input'
                        ]"
                    >
                        <input
                            ref="fileInput"
                            type="file"
                            multiple
                            @change="handleFileSelect"
                            class="hidden"
                        >
                        <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <p class="mt-4 text-sm text-muted-foreground">
                            <button
                                @click="$refs.fileInput.click()"
                                class="text-indigo-600 hover:text-indigo-700 font-medium"
                            >
                                {{ $t('features.media.modals.upload.clickToUpload') }}
                            </button>
                            {{ $t('features.media.modals.upload.dragAndDrop') }}
                        </p>
                        <p class="mt-2 text-xs text-muted-foreground">{{ $t('features.media.modals.upload.formats') }}</p>
                    </div>

                    <!-- Selected Files -->
                    <div v-if="selectedFiles.length > 0" class="mt-6 space-y-2">
                        <h4 class="text-sm font-medium text-foreground">{{ $t('features.media.modals.upload.selectedFiles') }}</h4>
                        <div v-for="(file, index) in selectedFiles" :key="index" class="flex items-center justify-between p-3 bg-muted rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-foreground">{{ file.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ formatFileSize(file.size) }}</p>
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
                            <span class="text-sm text-foreground">{{ $t('features.media.modals.upload.uploading') }}</span>
                            <span class="text-sm text-muted-foreground">{{ uploadProgress }}%</span>
                        </div>
                        <div class="w-full bg-muted rounded-full h-2">
                            <div
                                class="bg-indigo-600 h-2 rounded-full transition-all"
                                :style="{ width: uploadProgress + '%' }"
                            />
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                    >
                        {{ $t('features.media.actions.cancel') }}
                    </button>
                    <button
                        @click="handleUpload"
                        :disabled="selectedFiles.length === 0 || uploading"
                        class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80 disabled:opacity-50"
                    >
                        {{ uploading ? $t('features.media.modals.upload.uploading') : $t('features.media.modals.upload.uploadAction', { count: selectedFiles.length }) }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';

const { t } = useI18n();

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
            alert(t('features.media.messages.fileTooLarge', { name: file.name }));
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
        alert(t('features.media.messages.uploadFailed'));
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


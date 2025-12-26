<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">{{ $t('features.file_manager.modals.upload.title') }}</h3>
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="$emit('close')"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </Button>
                </div>

                <div class="p-6">
                    <div class="border-2 border-dashed border-input rounded-lg p-8 text-center">
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
                            <Button
                                variant="link"
                                @click="$refs.fileInput.click()"
                                class="h-auto p-0 font-medium text-primary"
                            >
                                {{ $t('features.file_manager.labels.clickToUpload') }}
                            </Button>
                            {{ $t('features.file_manager.labels.dragAndDrop') }}
                        </p>
                        <p class="mt-2 text-xs text-muted-foreground">{{ $t('features.file_manager.labels.multipleSupported') }}</p>
                    </div>

                    <div v-if="selectedFiles.length > 0" class="mt-4">
                        <h4 class="text-sm font-medium text-foreground mb-2">{{ $t('features.file_manager.labels.selectedFiles') }}</h4>
                        <ul class="space-y-1">
                            <li
                                v-for="(file, index) in selectedFiles"
                                :key="index"
                                class="text-sm text-muted-foreground flex items-center justify-between"
                            >
                                <span>{{ file.name }}</span>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="removeFile(index)"
                                    class="text-destructive hover:text-destructive hover:bg-destructive/10"
                                >
                                    {{ $t('features.file_manager.actions.remove') }}
                                </Button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <Button
                        variant="outline"
                        @click="$emit('close')"
                    >
                        {{ $t('features.file_manager.actions.cancel') }}
                    </Button>
                    <Button
                        @click="handleUpload"
                        :disabled="uploading || selectedFiles.length === 0"
                    >
                        {{ uploading ? $t('features.file_manager.actions.uploading') : $t('features.file_manager.actions.upload') }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import Button from '../ui/button.vue';

const { t } = useI18n();

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
        alert(t('features.file_manager.messages.uploadFailed'));
    } finally {
        uploading.value = false;
    }
};
</script>


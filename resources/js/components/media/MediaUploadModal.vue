<template>
    <Dialog :open="true" @update:open="$emit('close')">
        <DialogContent class="sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>{{ $t('features.media.modals.upload.title') }}</DialogTitle>
                <DialogDescription>
                    Drag and drop files here or click to select files to upload.
                </DialogDescription>
            </DialogHeader>

            <div class="py-4">
                <!-- Drag & Drop Area -->
                <div
                    @drop="handleDrop"
                    @dragover.prevent
                    @dragenter.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    :class="[
                        'border-2 border-dashed rounded-lg p-12 text-center transition-colors',
                        isDragging ? 'border-primary bg-primary/10' : 'border-muted-foreground/25 hover:border-primary/50'
                    ]"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        multiple
                        @change="handleFileSelect"
                        class="hidden"
                    >
                    <CloudUpload class="mx-auto h-12 w-12 text-muted-foreground opacity-50 mb-4" />
                    <div class="text-sm text-muted-foreground">
                        <Button
                            variant="link"
                            @click="$refs.fileInput.click()"
                            class="h-auto p-0 text-primary font-medium"
                        >
                            {{ $t('features.media.modals.upload.clickToUpload') }}
                        </Button>
                        {{ $t('features.media.modals.upload.dragAndDrop') }}
                    </div>
                    <p class="mt-2 text-xs text-muted-foreground">{{ $t('features.media.modals.upload.formats') }}</p>
                </div>

                <!-- Selected Files -->
                <div v-if="selectedFiles.length > 0" class="mt-6 space-y-2 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-3">
                        {{ $t('features.media.modals.upload.selectedFiles') }}
                    </h4>
                    <div v-for="(file, index) in selectedFiles" :key="index" class="flex items-center justify-between p-3 bg-muted/50 rounded-lg border border-border">
                        <div class="flex-1 min-w-0 mr-4">
                            <p class="text-sm font-medium text-foreground truncate">{{ file.name }}</p>
                            <p class="text-xs text-muted-foreground">{{ formatFileSize(file.size) }}</p>
                        </div>
                        <Button
                            variant="ghost"
                            size="icon"
                            @click="removeFile(index)"
                            class="text-muted-foreground hover:text-destructive h-8 w-8"
                        >
                            <Trash2 class="w-4 h-4" />
                        </Button>
                    </div>
                </div>

                <!-- Upload Progress -->
                <div v-if="uploading" class="mt-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-foreground">{{ $t('features.media.modals.upload.uploading') }}</span>
                        <span class="text-sm text-muted-foreground">{{ uploadProgress }}%</span>
                    </div>
                    <div class="w-full bg-secondary rounded-full h-2 overflow-hidden">
                        <div
                            class="bg-primary h-full transition-all duration-300 ease-out"
                            :style="{ width: uploadProgress + '%' }"
                        />
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button
                    variant="outline"
                    @click="$emit('close')"
                >
                    {{ $t('features.media.actions.cancel') }}
                </Button>
                <Button
                    @click="handleUpload"
                    :disabled="uploading || !isValid"
                >
                    <Loader2 v-if="uploading" class="mr-2 h-4 w-4 animate-spin" />
                    {{ uploading ? $t('features.media.modals.upload.uploading') : $t('features.media.modals.upload.uploadAction', { count: selectedFiles.length }) }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from '@/composables/useToast.js';
import { CloudUpload, Trash2, Loader2 } from 'lucide-vue-next';
import api from '../../services/api';
import Button from '../ui/button.vue';
import Dialog from '@/components/ui/dialog.vue';
import DialogContent from '@/components/ui/dialog-content.vue';
import DialogHeader from '@/components/ui/dialog-header.vue';
import DialogTitle from '@/components/ui/dialog-title.vue';
import DialogDescription from '@/components/ui/dialog-description.vue';
import DialogFooter from '@/components/ui/dialog-footer.vue';

const { t } = useI18n();
const toast = useToast();

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

const isValid = computed(() => {
    return selectedFiles.value.length > 0;
});

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
            toast.error.fileTooLarge();
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
        toast.error.fromResponse(error);
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


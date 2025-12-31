<template>
    <Dialog :open="true" @update:open="$emit('close')">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ $t('features.file_manager.modals.upload.title') }}</DialogTitle>
            </DialogHeader>

            <div class="grid gap-4 py-4">
                <div 
                    class="border-2 border-dashed border-input rounded-lg p-8 text-center hover:bg-muted/50 transition-colors cursor-pointer"
                    @click="$refs.fileInput.click()"
                    @dragover.prevent
                    @drop.prevent="handleDrop"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        multiple
                        @change="handleFileSelect"
                        class="hidden"
                    >
                    <CloudUpload class="mx-auto h-12 w-12 text-muted-foreground" />
                    <p class="mt-4 text-sm text-muted-foreground">
                        <span class="font-semibold text-primary hover:underline">
                            {{ $t('features.file_manager.labels.clickToUpload') }}
                        </span>
                        {{ $t('features.file_manager.labels.dragAndDrop') }}
                    </p>
                    <p class="mt-2 text-xs text-muted-foreground">{{ $t('features.file_manager.labels.multipleSupported') }}</p>
                </div>

                <div v-if="selectedFiles.length > 0" class="space-y-2 max-h-[200px] overflow-y-auto pr-2">
                    <h4 class="text-sm font-medium text-foreground sticky top-0 bg-background pb-2">{{ $t('features.file_manager.labels.selectedFiles') }}</h4>
                    <div
                        v-for="(file, index) in selectedFiles"
                        :key="index"
                        class="flex items-center justify-between p-2 bg-muted rounded-md group"
                    >
                        <span class="text-sm truncate max-w-[250px]" :title="file.name">{{ file.name }}</span>
                        <Button
                            variant="ghost"
                            size="icon"
                            @click="removeFile(index)"
                            class="h-6 w-6 text-muted-foreground hover:text-destructive"
                        >
                            <X class="w-4 h-4" />
                        </Button>
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button
                    variant="outline"
                    @click="$emit('close')"
                    type="button"
                >
                    {{ $t('features.file_manager.actions.cancel') }}
                </Button>
                <Button
                    @click="handleUpload"
                    :disabled="uploading || !isValid"
                    type="submit"
                >
                    <Loader2 v-if="uploading" class="mr-2 h-4 w-4 animate-spin" />
                    {{ uploading ? $t('features.file_manager.actions.uploading') : $t('features.file_manager.actions.upload') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import Button from '../ui/button.vue';
import Dialog from '../ui/dialog.vue';
import DialogContent from '../ui/dialog-content.vue';
import DialogHeader from '../ui/dialog-header.vue';
import DialogTitle from '../ui/dialog-title.vue';
import DialogFooter from '../ui/dialog-footer.vue';
import { CloudUpload, X, Loader2 } from 'lucide-vue-next';
import { useToast } from '../../composables/useToast';

const { t } = useI18n();
const toast = useToast();

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

const isValid = computed(() => {
    return selectedFiles.value.length > 0;
});

const handleFileSelect = (event) => {
    if (event.target.files) {
        selectedFiles.value = [...selectedFiles.value, ...Array.from(event.target.files)];
    }
};

const handleDrop = (event) => {
    if (event.dataTransfer.files) {
        selectedFiles.value = [...selectedFiles.value, ...Array.from(event.dataTransfer.files)];
    }
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
        emit('close');
    } catch (error) {
        if (error.response?.status === 422) {
             // Handle checking specifically for file errors if backend returns them in a specific structure
             // For now standard fromResponse is better than generic message
             toast.error.fromResponse(error);
        } else {
            console.error('Failed to upload files:', error);
            toast.error.fromResponse(error);
        }
    } finally {
        uploading.value = false;
    }
};
</script>


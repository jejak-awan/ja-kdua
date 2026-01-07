<template>
    <div class="media-upload">
        <div v-if="!preview" class="border-2 border-dashed border-input rounded-lg p-6 text-center">
            <input
                ref="fileInput"
                type="file"
                @change="handleFileSelect"
                :accept="effectiveConstraints.allowedExtensions.map(ext => '.' + ext).join(',')"
                class="hidden"
            >
            <button
                type="button"
                @click="$refs.fileInput.click()"
                class="bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary/80"
            >
                {{ $t('features.media.modals.upload.chooseFile') }}
            </button>
            <p class="mt-2 text-sm text-muted-foreground">
                {{ effectiveConstraints.allowedExtensions.join(', ').toUpperCase() }} 
                {{ $t('features.content.form.maxSizeHint', { size: (effectiveConstraints.maxSize / 1024).toFixed(0), extensions: '' }).replace('| ', '') }}
            </p>
            <p v-if="effectiveConstraints.minWidth || effectiveConstraints.minHeight" class="text-[10px] text-muted-foreground/60 italic">
                {{ $t('features.content.form.minHint', { dimensions: (effectiveConstraints.minWidth || '?') + 'x' + (effectiveConstraints.minHeight || '?') + 'px' }) }}
            </p>
            <div v-if="error" class="mt-3 p-2 bg-red-500/10 border border-red-500/20 rounded-md">
                <p class="text-xs text-red-500 font-medium">{{ error }}</p>
            </div>
        </div>

        <div v-else class="relative">
            <img :src="preview" alt="Preview" class="w-full h-64 object-cover rounded-lg">
            <div class="mt-4 flex space-x-2">
                <button
                    type="button"
                    @click="uploadFile"
                    :disabled="uploading"
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50"
                >
                    {{ uploading ? $t('features.media.modals.upload.uploading') : $t('features.media.modals.upload.upload') }}
                </button>
                <button
                    type="button"
                    @click="clearPreview"
                    class="bg-muted text-white px-4 py-2 rounded-md hover:bg-muted"
                >
                    {{ $t('features.media.actions.cancel') }}
                </button>
            </div>
            <div v-if="uploadedMedia" class="mt-4 p-4 bg-green-500/20 rounded-lg">
                <p class="text-sm text-green-800 dark:text-green-400">{{ $t('features.media.modals.upload.uploadedSuccess') }}</p>
                <p class="text-xs text-green-600 dark:text-green-500 mt-1">{{ uploadedMedia.url }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { storeToRefs } from 'pinia';
import api from '../services/api';
import { useCmsStore } from '../stores/cms';

const props = defineProps({
    folderId: {
        type: [Number, String],
        default: null
    },
    constraints: {
        type: Object,
        default: () => ({
            allowedExtensions: ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            maxSize: null, // If null, will use global setting
            minWidth: null,
            minHeight: null,
            maxWidth: null,
            maxHeight: null
        })
    }
});

const emit = defineEmits(['uploaded']);
const cmsStore = useCmsStore();
const { settings } = storeToRefs(cmsStore);

// Define reactive settings reference using storeToRefs
const globalMaxSize = computed(() => {
    // max_upload_size is stored in KB in settings
    const size = settings.value.max_upload_size;
    return size ? parseInt(size) : 10240; // Default 10MB in KB
});

const effectiveConstraints = computed(() => {
    const defaultExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    const allowedExtensions = props.constraints.allowedExtensions || defaultExtensions;
    
    // System limit is the hard cap
    const systemLimitKB = globalMaxSize.value;
    
    // Feature limit (if provided) is capped by system limit
    let maxSizeKB = props.constraints.maxSize ? parseInt(props.constraints.maxSize) : systemLimitKB;
    if (maxSizeKB > systemLimitKB) {
        maxSizeKB = systemLimitKB;
    }

    return {
        ...props.constraints,
        allowedExtensions,
        maxSize: maxSizeKB
    };
});

const fileInput = ref(null);
const preview = ref(null);
const selectedFile = ref(null);
const uploading = ref(false);
const uploadedMedia = ref(null);
const error = ref(null);

onMounted(async () => {
    // Always fetch media settings to ensure they're loaded
    await cmsStore.fetchSettingsGroup('media');
});

const handleFileSelect = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    error.value = null;
    const extension = file.name.split('.').pop().toLowerCase();
    
    // Check extension
    if (effectiveConstraints.value.allowedExtensions && !effectiveConstraints.value.allowedExtensions.includes(extension)) {
        error.value = `File type .${extension} is not allowed. Allowed: ${effectiveConstraints.value.allowedExtensions.join(', ')}`;
        return;
    }

    // Check size
    const maxSizeInBytes = (effectiveConstraints.value.maxSize || 10240) * 1024;
    if (file.size > maxSizeInBytes) {
        error.value = `File size exceeds limit of ${formatSize(maxSizeInBytes)}`;
        return;
    }

    // Check dimensions for images
    if (file.type.startsWith('image/') && !file.type.includes('svg')) {
        try {
            const dimensions = await getImageDimensions(file);
            const { minWidth, minHeight, maxWidth, maxHeight } = effectiveConstraints.value;

            if (minWidth && dimensions.width < minWidth) {
                error.value = `Image width must be at least ${minWidth}px (current: ${dimensions.width}px)`;
                return;
            }
            if (minHeight && dimensions.height < minHeight) {
                error.value = `Image height must be at least ${minHeight}px (current: ${dimensions.height}px)`;
                return;
            }
            if (maxWidth && dimensions.width > maxWidth) {
                error.value = `Image width exceeds limit of ${maxWidth}px (current: ${dimensions.width}px)`;
                return;
            }
            if (maxHeight && dimensions.height > maxHeight) {
                error.value = `Image height exceeds limit of ${maxHeight}px (current: ${dimensions.height}px)`;
                return;
            }
        } catch (e) {
            console.error('Failed to get image dimensions:', e);
        }
    }

    selectedFile.value = file;

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        preview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const getImageDimensions = (file) => {
    return new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => {
            URL.revokeObjectURL(url);
            resolve({ width: img.width, height: img.height });
        };
        img.onerror = reject;
        img.src = url;
    });
};

const uploadFile = async () => {
    if (!selectedFile.value) return;

    uploading.value = true;
    error.value = null;
    const formData = new FormData();
    formData.append('file', selectedFile.value);
    
    if (props.folderId) {
        formData.append('folder_id', props.folderId);
    }

    // Pass constraints to backend for double-checked validation
    if (effectiveConstraints.value.minWidth) formData.append('min_width', effectiveConstraints.value.minWidth);
    if (effectiveConstraints.value.minHeight) formData.append('min_height', effectiveConstraints.value.minHeight);
    if (effectiveConstraints.value.maxWidth) formData.append('max_width', effectiveConstraints.value.maxWidth);
    if (effectiveConstraints.value.maxHeight) formData.append('max_height', effectiveConstraints.value.maxHeight);

    try {
        const response = await api.post('/admin/ja/media/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        const responseData = response.data;
        uploadedMedia.value = responseData.data || responseData;
        emit('uploaded', responseData.data?.media || responseData);
        
        // Clear after 3 seconds
        setTimeout(() => {
            clearPreview();
        }, 3000);
    } catch (err) {
        console.error('Upload error:', err);
        const backendError = err.response?.data?.errors?.file?.[0] || err.response?.data?.message || 'Failed to upload file';
        error.value = backendError;
    } finally {
        uploading.value = false;
    }
};

const clearPreview = () => {
    preview.value = null;
    selectedFile.value = null;
    uploadedMedia.value = null;
    error.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const formatSize = (bytes) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>


<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card border border-border shadow-lg rounded-lg max-w-2xl w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">{{ $t('features.media.modals.resize.title') }}</h3>
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="$emit('close')"
                    >
                        <X class="w-5 h-5" />
                    </Button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">
                                {{ $t('features.media.modals.resize.width') }}
                            </label>
                            <input
                                v-model.number="width"
                                type="number"
                                min="1"
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">
                                {{ $t('features.media.modals.resize.height') }}
                            </label>
                            <input
                                v-model.number="height"
                                type="number"
                                min="1"
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input
                            v-model="maintainAspectRatio"
                            type="checkbox"
                            id="aspect-ratio"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                        >
                        <label for="aspect-ratio" class="ml-2 block text-sm text-foreground">
                            {{ $t('features.media.modals.resize.maintainAspectRatio') }}
                        </label>
                    </div>

                    <div v-if="media.mime_type?.startsWith('image/')" class="mt-4">
                        <label class="block text-sm font-medium text-foreground mb-2">{{ $t('features.media.modals.resize.preview') }}</label>
                        <div class="border border-input rounded-lg p-4 bg-muted">
                            <img
                                :src="media.url"
                                :alt="media.name"
                                class="max-w-full h-auto mx-auto"
                                :style="previewStyle"
                                @load="onImageLoad"
                            >
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <Button
                        variant="outline"
                        @click="$emit('close')"
                    >
                        {{ $t('features.media.modals.resize.cancel') }}
                    </Button>
                    <Button
                        @click="handleResize"
                        :disabled="resizing || !isValid"
                    >
                        {{ resizing ? $t('features.media.modals.resize.resizing') : $t('features.media.modals.resize.resize') }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { X } from 'lucide-vue-next';
import api from '../../services/api';
import toast from '../../services/toast';
import Button from '../ui/button.vue';

const props = defineProps({
    media: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close', 'resized']);
const { t } = useI18n();

const width = ref(null);
const height = ref(null);
const maintainAspectRatio = ref(true);
const resizing = ref(false);
const originalDimensions = ref({ width: 0, height: 0 });

const originalAspectRatio = computed(() => {
    if (originalDimensions.value.width && originalDimensions.value.height) {
        return originalDimensions.value.width / originalDimensions.value.height;
    }
    return null;
});

const isValid = computed(() => {
    return !!width.value && !!height.value;
});

const onImageLoad = (e) => {
    const img = e.target;
    width.value = img.naturalWidth;
    height.value = img.naturalHeight;
    originalDimensions.value = {
        width: img.naturalWidth,
        height: img.naturalHeight
    };
};

const previewStyle = computed(() => {
    if (!width.value || !height.value) return {};
    return {
        width: `${width.value}px`,
        height: `${height.value}px`,
        objectFit: 'contain',
    };
});

const isUpdating = ref(false);

watch(width, (newWidth) => {
    if (isUpdating.value || !maintainAspectRatio.value || !originalAspectRatio.value || !newWidth) return;
    
    isUpdating.value = true;
    height.value = Math.round(newWidth / originalAspectRatio.value);
    isUpdating.value = false;
});

watch(height, (newHeight) => {
    if (isUpdating.value || !maintainAspectRatio.value || !originalAspectRatio.value || !newHeight) return;
    
    isUpdating.value = true;
    width.value = Math.round(newHeight * originalAspectRatio.value);
    isUpdating.value = false;
});

const handleResize = async () => {
    if (!width.value || !height.value) return;

    resizing.value = true;
    try {
        await api.post(`/admin/ja/media/${props.media.id}/resize`, {
            width: width.value,
            height: height.value,
            maintain_aspect_ratio: maintainAspectRatio.value,
        });
        toast.success(t('features.media.modals.resize.success'));
        emit('resized');
    } catch (error) {
        console.error('Failed to resize media:', error);
        toast.error('Error', error.response?.data?.message || t('features.media.modals.resize.failed'));
    } finally {
        resizing.value = false;
    }
};
</script>


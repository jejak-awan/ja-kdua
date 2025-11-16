<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">Resize Media</h3>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Width (px)
                            </label>
                            <input
                                v-model.number="width"
                                type="number"
                                min="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Height (px)
                            </label>
                            <input
                                v-model.number="height"
                                type="number"
                                min="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input
                            v-model="maintainAspectRatio"
                            type="checkbox"
                            id="aspect-ratio"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        >
                        <label for="aspect-ratio" class="ml-2 block text-sm text-gray-900">
                            Maintain Aspect Ratio
                        </label>
                    </div>

                    <div v-if="media.mime_type?.startsWith('image/')" class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
                        <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                            <img
                                :src="media.url"
                                :alt="media.name"
                                class="max-w-full h-auto mx-auto"
                                :style="previewStyle"
                            >
                        </div>
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
                        @click="handleResize"
                        :disabled="resizing || !width || !height"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ resizing ? 'Resizing...' : 'Resize' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import api from '../../services/api';

const props = defineProps({
    media: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close', 'resized']);

const width = ref(null);
const height = ref(null);
const maintainAspectRatio = ref(true);
const resizing = ref(false);

const originalAspectRatio = computed(() => {
    if (props.media.width && props.media.height) {
        return props.media.width / props.media.height;
    }
    return null;
});

const previewStyle = computed(() => {
    if (!width.value || !height.value) return {};
    return {
        width: `${width.value}px`,
        height: `${height.value}px`,
        objectFit: 'contain',
    };
});

watch([width, height], () => {
    if (maintainAspectRatio.value && originalAspectRatio.value) {
        if (width.value && !height.value) {
            height.value = Math.round(width.value / originalAspectRatio.value);
        } else if (height.value && !width.value) {
            width.value = Math.round(height.value * originalAspectRatio.value);
        }
    }
});

const handleResize = async () => {
    if (!width.value || !height.value) return;

    resizing.value = true;
    try {
        await api.post(`/admin/cms/media/${props.media.id}/resize`, {
            width: width.value,
            height: height.value,
            maintain_aspect_ratio: maintainAspectRatio.value,
        });
        alert('Media resized successfully');
        emit('resized');
    } catch (error) {
        console.error('Failed to resize media:', error);
        alert(error.response?.data?.message || 'Failed to resize media');
    } finally {
        resizing.value = false;
    }
};
</script>


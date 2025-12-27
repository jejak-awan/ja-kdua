<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-75" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="bg-card border border-border shadow-lg rounded-lg max-w-6xl w-full max-h-[90vh] flex flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">Edit Image: {{ media.name }}</h3>
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="$emit('close')"
                    >
                        <X class="w-5 h-5" />
                    </Button>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Editor Canvas -->
                        <div class="lg:col-span-2">
                            <div class="bg-secondary rounded-lg p-4 min-h-[400px] flex items-center justify-center">
                                <div class="w-full max-w-full">
                                    <img
                                        ref="imageElement"
                                        :src="imageSrc"
                                        alt="Image to edit"
                                        class="max-w-full max-h-[500px] mx-auto"
                                        @load="initCropper"
                                    />
                                </div>
                            </div>

                            <!-- Crop Presets -->
                            <div v-if="showCrop" class="mt-4">
                                <label class="block text-sm font-medium text-foreground mb-2">Aspect Ratio</label>
                                    <Button
                                        v-for="preset in cropPresets"
                                        :key="preset.value"
                                        variant="outline"
                                        size="sm"
                                        @click="setAspectRatio(preset.value)"
                                        :class="[
                                            currentAspectRatio === preset.value ? 'bg-primary text-primary-foreground' : ''
                                        ]"
                                    >
                                        {{ preset.label }}
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="setAspectRatio(null)"
                                        :class="[
                                            currentAspectRatio === null ? 'bg-primary text-primary-foreground' : ''
                                        ]"
                                    >
                                        Free
                                    </Button>
                                </div>
                            </div>

                        <!-- Tools Sidebar -->
                        <div class="space-y-6">
                            <!-- Transform Tools -->
                            <div>
                                <h4 class="text-sm font-medium text-foreground mb-3">Transform</h4>
                                <div class="grid grid-cols-1 gap-2">
                                    <Button
                                        variant="outline"
                                        @click="rotate(90)"
                                        class="justify-start h-9"
                                    >
                                        <RotateCw class="w-4 h-4 mr-2" />
                                        Rotate 90°
                                    </Button>
                                    <Button
                                        variant="outline"
                                        @click="flip('horizontal')"
                                        class="justify-start h-9"
                                    >
                                        <FlipHorizontal class="w-4 h-4 mr-2" />
                                        Flip Horizontal
                                    </Button>
                                    <Button
                                        variant="outline"
                                        @click="flip('vertical')"
                                        class="justify-start h-9"
                                    >
                                        <FlipVertical class="w-4 h-4 mr-2" />
                                        Flip Vertical
                                    </Button>
                                </div>
                            </div>

                            <!-- Filters -->
                            <div>
                                <h4 class="text-sm font-medium text-foreground mb-3">Filters</h4>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs text-muted-foreground mb-1">Brightness</label>
                                        <input
                                            v-model.number="filters.brightness"
                                            type="range"
                                            min="0"
                                            max="200"
                                            step="1"
                                            @input="applyFilters"
                                            class="w-full"
                                        />
                                        <div class="text-xs text-muted-foreground text-center">{{ filters.brightness }}%</div>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-muted-foreground mb-1">Contrast</label>
                                        <input
                                            v-model.number="filters.contrast"
                                            type="range"
                                            min="0"
                                            max="200"
                                            step="1"
                                            @input="applyFilters"
                                            class="w-full"
                                        />
                                        <div class="text-xs text-muted-foreground text-center">{{ filters.contrast }}%</div>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-muted-foreground mb-1">Saturation</label>
                                        <input
                                            v-model.number="filters.saturation"
                                            type="range"
                                            min="0"
                                            max="200"
                                            step="1"
                                            @input="applyFilters"
                                            class="w-full"
                                        />
                                        <div class="text-xs text-muted-foreground text-center">{{ filters.saturation }}%</div>
                                    </div>
                                    <Button
                                        variant="secondary"
                                        @click="resetFilters"
                                        class="w-full h-9"
                                    >
                                        <RefreshCw class="w-4 h-4 mr-2" />
                                        Reset Filters
                                    </Button>
                                </div>
                            </div>

                            <!-- Resize -->
                            <div>
                                <h4 class="text-sm font-medium text-foreground mb-3">Resize</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <input
                                            v-model.number="resizeWidth"
                                            type="number"
                                            placeholder="Width"
                                            class="flex-1 px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm"
                                        />
                                        <span class="text-muted-foreground">×</span>
                                        <input
                                            v-model.number="resizeHeight"
                                            type="number"
                                            placeholder="Height"
                                            class="flex-1 px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm"
                                        />
                                    </div>
                                    <div class="flex items-center">
                                        <input
                                            v-model="maintainAspectRatio"
                                            type="checkbox"
                                            id="maintain-ratio"
                                            class="mr-2"
                                        />
                                        <label for="maintain-ratio" class="text-xs text-muted-foreground">Maintain Aspect Ratio</label>
                                    </div>
                                    <Button
                                        @click="applyResize"
                                        class="w-full h-9"
                                    >
                                        Apply Resize
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between p-6 border-t">
                    <div class="flex items-center space-x-2">
                        <input
                            v-model="saveAsNew"
                            type="checkbox"
                            id="save-as-new"
                            class="mr-2"
                        />
                        <label for="save-as-new" class="text-sm text-foreground">Save as new version</label>
                    </div>
                    <div class="flex items-center space-x-3">
                        <Button
                            variant="ghost"
                            @click="resetAll"
                        >
                            <Undo2 class="w-4 h-4 mr-2" />
                            Reset All
                        </Button>
                        <Button
                            variant="outline"
                            @click="$emit('close')"
                        >
                            Cancel
                        </Button>
                        <Button
                            @click="saveImage"
                            :disabled="saving"
                        >
                            <Save class="w-4 h-4 mr-2" />
                            {{ saving ? 'Saving...' : 'Save Image' }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { 
    X, 
    RotateCw, 
    FlipHorizontal, 
    FlipVertical, 
    RefreshCw, 
    Save, 
    Undo2 
} from 'lucide-vue-next';
import Cropper from 'cropperjs';
import api from '../../services/api';
import Button from '../ui/button.vue';

const props = defineProps({
    media: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close', 'updated']);

const imageElement = ref(null);
const imageSrc = ref(props.media.url);
const cropper = ref(null);
const showCrop = ref(false);
const currentAspectRatio = ref(null);
const saveAsNew = ref(false);
const saving = ref(false);

const cropPresets = [
    { label: '1:1', value: 1 },
    { label: '4:3', value: 4/3 },
    { label: '16:9', value: 16/9 },
    { label: '3:2', value: 3/2 },
    { label: '2:3', value: 2/3 },
];

const filters = ref({
    brightness: 100,
    contrast: 100,
    saturation: 100,
});

const resizeWidth = ref(null);
const resizeHeight = ref(null);
const maintainAspectRatio = ref(true);

const originalImage = ref(null);
const canvas = ref(null);
const ctx = ref(null);

const initCropper = () => {
    if (!imageElement.value || cropper.value) return;
    
    // Wait for image to be fully loaded
    if (!imageElement.value.complete) {
        imageElement.value.onload = () => {
            initCropper();
        };
        return;
    }

    cropper.value = new Cropper(imageElement.value, {
        aspectRatio: currentAspectRatio.value || undefined,
        viewMode: 1,
        dragMode: 'move',
        autoCropArea: 0.8,
        restore: false,
        guides: true,
        center: true,
        highlight: false,
        cropBoxMovable: true,
        cropBoxResizable: true,
        toggleDragModeOnDblclick: false,
    });

    showCrop.value = true;
};

const setAspectRatio = (ratio) => {
    currentAspectRatio.value = ratio;
    if (cropper.value) {
        cropper.value.setAspectRatio(ratio || undefined);
    }
};

const rotate = (degrees) => {
    if (cropper.value) {
        cropper.value.rotate(degrees);
    } else {
        applyTransform({ rotate: degrees });
    }
};

const flip = (direction) => {
    // For flip, we need to apply transform directly
    applyTransform({ flip: direction });
};

const applyTransform = async (transform) => {
    if (!imageElement.value) return;

    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.src = imageElement.value.src;

    await new Promise((resolve) => {
        img.onload = resolve;
    });

    if (!canvas.value) {
        canvas.value = document.createElement('canvas');
        ctx.value = canvas.value.getContext('2d');
    }

    canvas.value.width = img.width;
    canvas.value.height = img.height;

    ctx.value.save();
    ctx.value.translate(canvas.value.width / 2, canvas.value.height / 2);

    if (transform.rotate) {
        ctx.value.rotate((transform.rotate * Math.PI) / 180);
    }

    if (transform.flip === 'horizontal') {
        ctx.value.scale(-1, 1);
    } else if (transform.flip === 'vertical') {
        ctx.value.scale(1, -1);
    }

    ctx.value.drawImage(img, -img.width / 2, -img.height / 2);
    ctx.value.restore();

    imageSrc.value = canvas.value.toDataURL('image/png');
    imageElement.value.src = imageSrc.value;

    // Reinitialize cropper
    if (cropper.value) {
        cropper.value.destroy();
        cropper.value = null;
    }
    setTimeout(() => {
        initCropper();
    }, 100);
};

const applyFilters = () => {
    if (!imageElement.value) return;

    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.src = originalImage.value || imageElement.value.src;

    img.onload = () => {
        if (!canvas.value) {
            canvas.value = document.createElement('canvas');
            ctx.value = canvas.value.getContext('2d');
        }

        canvas.value.width = img.width;
        canvas.value.height = img.height;

        ctx.value.filter = `
            brightness(${filters.value.brightness}%)
            contrast(${filters.value.contrast}%)
            saturate(${filters.value.saturation}%)
        `;

        ctx.value.drawImage(img, 0, 0);
        imageSrc.value = canvas.value.toDataURL('image/png');
        imageElement.value.src = imageSrc.value;
    };
};

const resetFilters = () => {
    filters.value = {
        brightness: 100,
        contrast: 100,
        saturation: 100,
    };
    imageSrc.value = originalImage.value || props.media.url;
    imageElement.value.src = imageSrc.value;
};

const applyResize = () => {
    if (!resizeWidth.value && !resizeHeight.value) return;

    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.src = imageElement.value.src;

    img.onload = () => {
        let width = resizeWidth.value || img.width;
        let height = resizeHeight.value || img.height;

        if (maintainAspectRatio.value) {
            const aspectRatio = img.width / img.height;
            if (resizeWidth.value && !resizeHeight.value) {
                height = width / aspectRatio;
            } else if (resizeHeight.value && !resizeWidth.value) {
                width = height * aspectRatio;
            } else if (resizeWidth.value && resizeHeight.value) {
                // Use the smaller dimension to maintain aspect ratio
                const widthRatio = width / img.width;
                const heightRatio = height / img.height;
                const ratio = Math.min(widthRatio, heightRatio);
                width = img.width * ratio;
                height = img.height * ratio;
            }
        }

        if (!canvas.value) {
            canvas.value = document.createElement('canvas');
            ctx.value = canvas.value.getContext('2d');
        }

        canvas.value.width = width;
        canvas.value.height = height;

        ctx.value.drawImage(img, 0, 0, width, height);
        imageSrc.value = canvas.value.toDataURL('image/png');
        imageElement.value.src = imageSrc.value;

        resizeWidth.value = null;
        resizeHeight.value = null;

        // Reinitialize cropper
        if (cropper.value) {
            cropper.value.destroy();
            cropper.value = null;
        }
        setTimeout(() => {
            initCropper();
        }, 100);
    };
};

const resetAll = () => {
    if (cropper.value) {
        cropper.value.destroy();
        cropper.value = null;
    }
    imageSrc.value = props.media.url;
    originalImage.value = null;
    filters.value = {
        brightness: 100,
        contrast: 100,
        saturation: 100,
    };
    resizeWidth.value = null;
    resizeHeight.value = null;
    currentAspectRatio.value = null;
    showCrop.value = false;
    setTimeout(() => {
        initCropper();
    }, 100);
};

const saveImage = async () => {
    saving.value = true;
    try {
        let imageData = imageSrc.value;

        // If cropper is active, get cropped image
        if (cropper.value) {
            const croppedCanvas = cropper.value.getCroppedCanvas({
                maxWidth: 4096,
                maxHeight: 4096,
            });
            imageData = croppedCanvas.toDataURL('image/png');
        }

        // Convert data URL to blob
        const response = await fetch(imageData);
        const blob = await response.blob();
        const fileName = props.media.file_name || props.media.name || 'edited_image.png';
        const file = new File([blob], fileName, { type: props.media.mime_type || 'image/png' });

        // Create FormData
        const formData = new FormData();
        formData.append('image', file);
        formData.append('save_as_new', saveAsNew.value ? '1' : '0');

        // Send to API
        const apiResponse = await api.post(`/admin/cms/media/${props.media.id}/edit`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        emit('updated');
        emit('close');
    } catch (error) {
        console.error('Failed to save image:', error);
        alert(error.response?.data?.message || 'Failed to save image');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    originalImage.value = props.media.url;
});

onUnmounted(() => {
    if (cropper.value) {
        cropper.value.destroy();
    }
});
</script>

<style>
/* CropperJS styles are loaded via CDN or included in app.css */
.cropper-container {
    max-width: 100%;
    max-height: 500px;
}
</style>


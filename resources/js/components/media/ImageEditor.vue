<template>
    <div class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-black/95 backdrop-blur-sm" @click.self="$emit('close')">
        <!-- Main Container -->
        <div class="relative flex flex-col w-full h-full max-w-5xl max-h-[90vh] bg-zinc-950 md:rounded-xl overflow-hidden shadow-none border border-white/5"
             @keydown.enter="handleEnterKey" tabindex="0" autofocus>
            
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-white/10 bg-zinc-950/50 backdrop-blur-md z-10">
                <div class="text-sm font-medium text-white/80">
                    {{ t('features.media.modals.editor.title') }}
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 mr-2">
                         <input type="checkbox" id="saveAsNew" v-model="saveAsNew" class="rounded border-white/20 bg-white/5 text-primary focus:ring-primary/50" />
                         <label for="saveAsNew" class="text-xs text-white/60 cursor-pointer select-none">Save copy</label>
                    </div>
                    
                    <div v-if="saveAsNew" class="animate-in fade-in slide-in-from-right-2 duration-300">
                        <input 
                            type="text" 
                            v-model="customFilename" 
                            class="bg-white/5 border border-white/10 rounded px-2 py-1 text-xs text-white w-32 focus:outline-none focus:border-white/40 focus:bg-white/10 transition-colors placeholder-white/20"
                            placeholder="File name"
                        />
                    </div>

                    <Button variant="ghost" size="sm" @click="$emit('close')" class="text-white/60 hover:text-white hover:bg-white/10">
                        <X class="w-4 h-4 mr-2" stroke-width="1.5" />
                        {{ t('common.actions.cancel') }}
                    </Button>
                    <Button size="sm" @click="saveImage" :disabled="saving || !hasChanges" class="bg-primary hover:bg-primary/90 text-primary-foreground min-w-[100px]">
                        <Save class="w-4 h-4 mr-2" v-if="!saving" stroke-width="1.5" />
                        <span v-else class="w-4 h-4 mr-2 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
                        {{ t('common.actions.save') }}
                    </Button>
                </div>
            </div>

            <!-- Canvas Area -->
            <div class="flex-1 relative flex items-center justify-center bg-[#0a0a0a] overflow-hidden p-8 user-select-none">
                <div v-if="!imageLoaded" class="absolute inset-0 flex items-center justify-center text-white/40">
                    <span class="animate-pulse">Loading image...</span>
                </div>
                
                <!-- Main Image Display -->
                <div class="relative max-w-full max-h-full transition-all duration-300">
                    <img 
                        ref="imageElement"
                        :src="currentImageSrc" 
                        class="max-w-full max-h-[calc(80vh-180px)] object-contain shadow-none transition-all duration-300 rounded-xl"
                        :style="activeMode === 'adjust' ? filterStyle : ''"
                        @load="onImageLoad"
                        crossorigin="anonymous"
                    />
                </div>
            </div>

            <!-- Sub Toolbar (Active Mode Tools) -->
            <div class="h-24 border-t border-white/10 bg-zinc-900/90 backdrop-blur-md flex items-center justify-center px-6 transition-all relative z-10">
                
                <!-- Crop Tools -->
                <div v-if="activeMode === 'crop'" class="flex items-center gap-4 animate-in fade-in slide-in-from-bottom-2 duration-300">
                    <div class="flex items-center gap-1 bg-black/40 p-1 rounded-xl border border-white/5">
                        <Button 
                            v-for="preset in cropPresets" 
                            :key="preset.label"
                            variant="ghost" 
                            size="sm"
                            class="text-xs h-7 px-3"
                            :class="currentAspectRatio === preset.value ? 'bg-white/20 text-white' : 'text-white/40 hover:text-white hover:bg-white/10'"
                            @click="setAspectRatio(preset.value)"
                        >
                            {{ preset.label }}
                        </Button>
                    </div>
                    
                    <div class="w-px h-8 bg-white/10"></div>
                    
                    <div class="flex items-center gap-1">
                        <Button size="sm" variant="ghost" class="h-8 w-8 p-0 text-white/60 hover:text-white hover:bg-white/10 rounded-lg" @click="rotate(90)" title="Rotate">
                            <RotateCw class="w-4 h-4" stroke-width="1.5" />
                        </Button>
                        <Button size="sm" variant="ghost" class="h-8 w-8 p-0 text-white/60 hover:text-white hover:bg-white/10 rounded-lg" @click="flip('horizontal')" title="Flip Horizontal">
                            <FlipHorizontal class="w-4 h-4" stroke-width="1.5" />
                        </Button>
                        <Button size="sm" variant="ghost" class="h-8 w-8 p-0 text-white/60 hover:text-white hover:bg-white/10 rounded-lg" @click="flip('vertical')" title="Flip Vertical">
                            <FlipVertical class="w-4 h-4" stroke-width="1.5" />
                        </Button>
                    </div>

                    <div class="w-px h-8 bg-white/10"></div>

                    <div class="flex gap-2">
                        <Button size="sm" variant="ghost" class="text-white/60 hover:text-white hover:bg-white/10" @click="cancelCrop">Cancel</Button>
                        <Button size="sm" @click="applyCrop" class="bg-white text-black hover:bg-white/90">Apply Crop</Button>
                    </div>
                </div>

                <!-- Adjust Tools -->
                <div v-if="activeMode === 'adjust'" class="flex flex-col md:flex-row items-center gap-6 w-full max-w-4xl animate-in fade-in slide-in-from-bottom-2 duration-300">
                     <!-- Presets -->
                    <div class="flex items-center gap-2 overflow-x-auto max-w-[200px] md:max-w-none no-scrollbar pr-4 border-r border-white/10 mr-2">
                        <button 
                            v-for="preset in filterPresets" 
                            :key="preset.name"
                            @click="applyPreset(preset)"
                            class="flex flex-col items-center justify-center min-w-[60px] gap-1 group"
                        >
                            <div class="w-10 h-10 rounded-xl border border-white/5 bg-black/40 group-hover:bg-white/10 flex items-center justify-center transition-colors">
                                <component :is="preset.icon" class="w-4 h-4 text-white/60 group-hover:text-white" stroke-width="1.5" />
                            </div>
                            <span class="text-[10px] text-white/40 group-hover:text-white/80">{{ preset.name }}</span>
                        </button>
                    </div>

                    <!-- Sliders -->
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 w-full">
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-medium w-20 text-white/60">Brightness</span>
                            <div class="flex-1 relative h-5 flex items-center">
                                <input type="range" v-model="filters.brightness" min="0" max="200" 
                                    class="w-full h-1 bg-white/10 rounded-xl appearance-none cursor-pointer accent-white hover:accent-primary focus:outline-none focus:ring-2 focus:ring-primary/50 text-white" />
                            </div>
                            <span class="text-xs w-8 text-right text-white/80 tabular-nums">{{ filters.brightness }}%</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-medium w-20 text-white/60">Contrast</span>
                            <div class="flex-1 relative h-5 flex items-center">
                                <input type="range" v-model="filters.contrast" min="0" max="200" 
                                    class="w-full h-1 bg-white/10 rounded-xl appearance-none cursor-pointer accent-white hover:accent-primary focus:outline-none focus:ring-2 focus:ring-primary/50 text-white" />
                            </div>
                            <span class="text-xs w-8 text-right text-white/80 tabular-nums">{{ filters.contrast }}%</span>
                        </div>
                        <!-- Saturation -->
                         <div class="flex items-center gap-3">
                            <span class="text-xs font-medium w-20 text-white/60">Saturation</span>
                            <div class="flex-1 relative h-5 flex items-center">
                                <input type="range" v-model="filters.saturation" min="0" max="200" 
                                    class="w-full h-1 bg-white/10 rounded-xl appearance-none cursor-pointer accent-white hover:accent-primary focus:outline-none focus:ring-2 focus:ring-primary/50 text-white" />
                            </div>
                            <span class="text-xs w-8 text-right text-white/80 tabular-nums">{{ filters.saturation }}%</span>
                        </div>
                    </div>

                    <div class="pl-4 border-l border-white/10 flex flex-col gap-2">
                        <Button size="sm" variant="ghost" class="text-white/40 hover:text-white h-7 text-xs" @click="resetFilters">Reset</Button>
                        <Button size="sm" @click="applyFilters" class="bg-white text-black hover:bg-white/90">Apply</Button>
                    </div>
                </div>

                <!-- Resize Tools -->
                <div v-if="activeMode === 'resize'" class="flex items-center gap-6 animate-in fade-in slide-in-from-bottom-2 duration-300">
                     <div class="flex items-center gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] text-white/40 uppercase font-bold tracking-wider px-1">Width</label>
                            <input 
                                type="number" 
                                v-model="resizeConfig.width" 
                                class="bg-black/40 border border-white/5 rounded-xl px-3 py-1.5 text-sm text-white w-28 focus:outline-none focus:border-white/40 focus:ring-1 focus:ring-white/40 transition-all placeholder-white/20"
                                placeholder="Width"
                            />
                        </div>
                        <span class="text-white/20 mt-5">×</span>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] text-white/40 uppercase font-bold tracking-wider px-1">Height</label>
                            <input 
                                type="number" 
                                v-model="resizeConfig.height" 
                                class="bg-black/40 border border-white/5 rounded-xl px-3 py-1.5 text-sm text-white w-28 focus:outline-none focus:border-white/40 focus:ring-1 focus:ring-white/40 transition-all placeholder-white/20"
                                placeholder="Height"
                            />
                        </div>
                        <div class="flex items-end h-full pb-1 ml-2">
                            <button 
                                @click="resizeConfig.maintainAspectRatio = !resizeConfig.maintainAspectRatio"
                                class="p-2 rounded-xl transition-colors border"
                                :class="resizeConfig.maintainAspectRatio ? 'bg-primary/20 text-primary border-primary/30' : 'bg-transparent text-white/20 border-transparent hover:text-white/60'"
                                title="Lock Aspect Ratio"
                            >
                                <Lock v-if="resizeConfig.maintainAspectRatio" class="w-4 h-4" stroke-width="1.5" />
                                <Unlock v-else class="w-4 h-4" stroke-width="1.5" />
                            </button>
                        </div>
                    </div>
                    <div class="w-px h-10 bg-white/10 mx-2"></div>
                    <Button @click="applyResize" class="bg-white text-black hover:bg-white/90">Apply Resize</Button>
                </div>
                
                <div v-if="activeMode === 'view'" class="text-sm text-white/40 animate-in fade-in duration-300">
                    Select a tool below to start editing
                </div>
            </div>

            <!-- Main Toolbar (Bottom) -->
            <div class="h-20 bg-zinc-950 flex items-center justify-center gap-8 md:gap-16 pb-safe border-t border-white/10 z-20">
                <button 
                    v-for="mode in modes" 
                    :key="mode.id"
                    @click="setMode(mode.id)"
                    class="flex flex-col items-center gap-1.5 group min-w-[64px] outline-none"
                    :disabled="!imageLoaded"
                >
                    <div 
                        class="p-2.5 rounded-xl transition-all duration-300 relative"
                        :class="activeMode === mode.id ? 'bg-white text-black scale-110 shadow-none' : 'text-zinc-500 group-hover:text-zinc-300 group-hover:bg-white/5'"
                    >
                        <component :is="mode.icon" class="w-5 h-5" stroke-width="1.5" />
                        <div v-if="activeMode === mode.id" class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-white opacity-0"></div>
                    </div>
                    <span 
                        class="text-[10px] font-medium tracking-wide transition-colors duration-300 uppercase"
                        :class="activeMode === mode.id ? 'text-white' : 'text-zinc-600 group-hover:text-zinc-500'"
                    >
                        {{ mode.label }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, shallowRef, computed, onUnmounted, markRaw, watch, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    X, Save, Crop, Sliders, Scaling, RotateCw, FlipHorizontal, FlipVertical,
    Lock, Unlock, Eye, Sparkles, Sun, Moon, Palette
} from 'lucide-vue-next';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import api from '../../services/api';
import Button from '../ui/button.vue';
import toast from '../../services/toast';

const props = defineProps({
    media: { type: Object, required: true },
});

const emit = defineEmits(['close', 'updated']);
const { t } = useI18n();

// --- State ---
const activeMode = ref('view'); 
const imageElement = ref(null);
const currentImageSrc = ref(props.media.url);
const imageLoaded = ref(false);
const saving = ref(false);
const saveAsNew = ref(false);
const customFilename = ref('');

// Initialize filename from prop
watch(() => props.media.name, (newName) => {
    if (newName) customFilename.value = newName + '_edited';
}, { immediate: true });

const modes = [
    { id: 'view', label: 'View', icon: Eye },
    { id: 'crop', label: 'Crop', icon: Crop },
    { id: 'adjust', label: 'Adjust', icon: Sliders },
    { id: 'resize', label: 'Resize', icon: Scaling },
];

// --- Cropper State ---
const cropper = shallowRef(null);
const cropperReady = ref(false);
const currentAspectRatio = ref(NaN);
const cropPresets = [
    { label: 'Free', value: NaN },
    { label: '1:1', value: 1 },
    { label: '16:9', value: 16/9 },
    { label: '4:3', value: 4/3 },
    { label: '2:3', value: 2/3 },
];

// --- Adjust State ---
const filters = ref({
    brightness: 100,
    contrast: 100,
    saturation: 100,
});

const filterPresets = [
    { name: 'Auto', icon: Sparkles, settings: { brightness: 110, contrast: 110, saturation: 115 } },
    { name: 'Warm', icon: Sun, settings: { brightness: 105, contrast: 105, saturation: 120 } },
    { name: 'Mood', icon: Moon, settings: { brightness: 90, contrast: 120, saturation: 80 } },
    { name: 'B&W', icon: Palette, settings: { brightness: 100, contrast: 120, saturation: 0 } },
];

// --- Resize State ---
const resizeConfig = ref({
    width: 0,
    height: 0,
    maintainAspectRatio: true,
    originalRatio: 1
});

// --- Computed ---
const filterStyle = computed(() => {
    return {
        filter: `brightness(${filters.value.brightness}%) contrast(${filters.value.contrast}%) saturate(${filters.value.saturation}%)`
    };
});

const hasChanges = computed(() => {
    // If image source changed (e.g. it's now a data URL from crop/resize/filter)
    const isSourceChanged = currentImageSrc.value !== props.media.url;
    
    // If we have pending filter changes in adjust mode
    const pendingFilters = activeMode.value === 'adjust' && isFilterDirty();
    
    // If Save As New is toggled
    const isSaveAsNew = saveAsNew.value;

    return isSourceChanged || pendingFilters || isSaveAsNew;
});

// --- Methods ---

const onImageLoad = (e) => {
    imageLoaded.value = true;
    const img = e.target;
    if (img.naturalWidth) {
        resizeConfig.value.width = img.naturalWidth;
        resizeConfig.value.height = img.naturalHeight;
        resizeConfig.value.originalRatio = img.naturalWidth / img.naturalHeight;
    }
};

const handleEnterKey = () => {
    // If in crop mode, Apply Crop
    if (activeMode.value === 'crop') {
        applyCrop();
    }
    // If in adjust mode, Apply Filters
    else if (activeMode.value === 'adjust') {
        applyFilters();
    }
    // If in resize mode, Apply Resize
    else if (activeMode.value === 'resize') {
        applyResize();
    }
    // Else if view mode (or generic save), maybe trigger save? 
    // Usually 'Enter' in modal means save, but here we have distinct 'Apply' vs 'Save' 
    // Let's stick to mode-specific Apply for safety, or Save for View mode.
    else if (activeMode.value === 'view') {
        saveImage();
    }
};

const triggerCropUpdate = () => {
    // Helper to force update crop box if needed
     if (cropper.value && cropperReady.value) {
        // Sometimes just setting ratio isn't enough to visually reset the box if it's already drawn
        // We can reset the crop box to center
        // cropper.value.reset(); // This might reset everything including rotation
        // Instead, let's just ensure the box is within bounds
        cropper.value.clear();
        cropper.value.crop();
    }
};

const setMode = async (mode) => {
    if (activeMode.value === mode) return;

    if (activeMode.value === 'crop') {
        destroyCropper();
    }
    
    // If switching FROM Adjust TO Crop, apply the filters first
    if (activeMode.value === 'adjust' && mode === 'crop') {
        if (isFilterDirty()) {
            await applyFilters();
        }
    }

    activeMode.value = mode;

    if (mode === 'crop') {
        await nextTick();
        initCropper();
    }
};

// --- Crop Logic ---
const initCropper = () => {
    if (!imageElement.value || cropper.value) return;
    
    cropperReady.value = false;
    
    const cropperInstance = new Cropper(imageElement.value, {
        aspectRatio: currentAspectRatio.value,
        viewMode: 1, 
        dragMode: 'move', 
        autoCropArea: 0.8,
        responsive: true,
        restore: false,
        guides: true,
        center: true,
        highlight: false,
        background: false,
        cropBoxMovable: true,
        cropBoxResizable: true,
        toggleDragModeOnDblclick: false,
        ready() {
            cropperReady.value = true;
        }
    });
    
    cropper.value = markRaw(cropperInstance);
};

const destroyCropper = () => {
    if (cropper.value) {
        cropper.value.destroy();
        cropper.value = null;
    }
    cropperReady.value = false;
};

const setAspectRatio = (ratio) => {
    currentAspectRatio.value = ratio;
    if (cropper.value && cropperReady.value) {
        cropper.value.setAspectRatio(ratio);
        // Force a re-crop to ensure the box respects the new ratio immediately
        // Some versions of cropperjs might need a nudge
        if (!isNaN(ratio)) {
             cropper.value.setData(cropper.value.getData()); 
        }
    }
};

const rotate = (deg) => {
    if (cropper.value && cropperReady.value) cropper.value.rotate(deg);
};

const flip = (dir) => {
    if (!cropper.value || !cropperReady.value) return;
    
    const data = cropper.value.getData();
    if (dir === 'horizontal') cropper.value.scaleX(data.scaleX === -1 ? 1 : -1);
    if (dir === 'vertical') cropper.value.scaleY(data.scaleY === -1 ? 1 : -1);
};

const applyCrop = () => {
    if (!cropper.value || !cropperReady.value) return;
    
    const canvas = cropper.value.getCroppedCanvas();
    
    if (canvas) {
        currentImageSrc.value = canvas.toDataURL(props.media.mime_type || 'image/png');
        
        resizeConfig.value.width = canvas.width;
        resizeConfig.value.height = canvas.height;
        resizeConfig.value.originalRatio = canvas.width / canvas.height;
        
        setMode('view'); 
    } else {
        console.error("Failed to get cropped canvas");
    }
};

const cancelCrop = () => {
    setMode('view');
};

// --- Adjust Logic ---
const applyPreset = (preset) => {
    filters.value = { ...preset.settings };
};

const isFilterDirty = () => {
    return filters.value.brightness !== 100 || 
           filters.value.contrast !== 100 || 
           filters.value.saturation !== 100;
};

const resetFilters = () => {
    filters.value = { brightness: 100, contrast: 100, saturation: 100 };
};

const applyFilters = async () => {
    if (!imageElement.value) return;

    return new Promise((resolve) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.src = currentImageSrc.value;
        
        img.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = img.width;
            canvas.height = img.height;
            
            ctx.filter = `brightness(${filters.value.brightness}%) contrast(${filters.value.contrast}%) saturate(${filters.value.saturation}%)`;
            ctx.drawImage(img, 0, 0);
            
            currentImageSrc.value = canvas.toDataURL(props.media.mime_type || 'image/png');
            resetFilters(); 
            resolve();
        };
    });
};

// --- Resize Logic ---
watch(() => resizeConfig.value.width, (newWidth) => {
    if (!activeMode.value === 'resize' || !resizeConfig.value.maintainAspectRatio) return;
    resizeConfig.value.height = Math.round(newWidth / resizeConfig.value.originalRatio);
});

watch(() => resizeConfig.value.height, (newHeight) => {
    if (!activeMode.value === 'resize' || !resizeConfig.value.maintainAspectRatio) return;
    resizeConfig.value.width = Math.round(newHeight * resizeConfig.value.originalRatio);
});

const applyResize = () => {
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.src = currentImageSrc.value;
    
    img.onload = () => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = resizeConfig.value.width;
        canvas.height = resizeConfig.value.height;
        
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        
        currentImageSrc.value = canvas.toDataURL(props.media.mime_type || 'image/png');
        setMode('view');
    };
};

// --- Image Helpers ---

function base64ToBlob(base64) {
  const parts = base64.split(';base64,');
  const contentType = parts[0].split(':')[1];
  const raw = window.atob(parts[1]);
  const rawLength = raw.length;
  const uInt8Array = new Uint8Array(rawLength);

  for (let i = 0; i < rawLength; ++i) {
    uInt8Array[i] = raw.charCodeAt(i);
  }

  return new Blob([uInt8Array], { type: contentType });
}

const getSecureBlob = async () => {
    // If it's already a base64 string, we can convert directly
    if (currentImageSrc.value.startsWith('data:')) {
        return base64ToBlob(currentImageSrc.value);
    }

    // Otherwise (HTTP URL), we need to draw it to a canvas to get the data
    // This requires the image to be loaded and have crossorigin="anonymous"
    if (!imageElement.value) throw new Error("Image element not found");

    const canvas = document.createElement('canvas');
    canvas.width = imageElement.value.naturalWidth;
    canvas.height = imageElement.value.naturalHeight;
    const ctx = canvas.getContext('2d');
    
    // Draw the image to the canvas
    ctx.drawImage(imageElement.value, 0, 0);
    
    // Get Data URL
    const dataLink = canvas.toDataURL(props.media.mime_type || 'image/png');
    return base64ToBlob(dataLink);
};

// --- Save Final ---
const saveImage = async () => {
    saving.value = true;
    try {
        if (activeMode.value === 'adjust' && isFilterDirty()) await applyFilters();
        if (activeMode.value === 'crop') applyCrop();

        // Get blob securely (whether it's base64 or url)
        const blob = await getSecureBlob();
        
        if (blob.size === 0) throw new Error("Generated image is empty");
        
        const formData = new FormData();
        const fileName = props.media.file_name || 'edited-image.png';
        // Use blob.type to ensure it matches the actual data (e.g. image/png)
        const file = new File([blob], fileName, { type: blob.type });
        
        formData.append('image', file);
        formData.append('save_as_new', saveAsNew.value ? '1' : '0');
        if (saveAsNew.value && customFilename.value) {
            formData.append('custom_filename', customFilename.value);
        }

        await api.post(`/admin/ja/media/${props.media.id}/edit`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        emit('updated');
        emit('close');
    } catch (err) {
        console.error("Failed to save", err);
        let msg = t('features.media.modals.editor.failed') || 'Failed to save image';
        if (err.response && err.response.data) {
            if (err.response.data.errors) {
                // Formatting validation errors
                const errors = err.response.data.errors;
                const errorMessages = Object.values(errors).flat().join('\n');
                msg += '\n' + errorMessages;
            } else if (err.response.data.message) {
                 msg += ': ' + err.response.data.message;
            }
        }
        toast.error('Error', msg);
    } finally {
        saving.value = false;
    }
};

onUnmounted(() => {
    destroyCropper();
});
</script>

<style scoped>
input[type=range]::-webkit-slider-thumb {
    -webkit-appearance: none;
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

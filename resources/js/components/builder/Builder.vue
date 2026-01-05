<template>
    <!-- Fullscreen overlay backdrop -->
    <div 
        v-if="isFullscreen" 
        class="bg-background" 
        :style="{ position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, zIndex: 9998 }"
        @click="toggleFullscreen"
    ></div>
    
    <div 
        :class="[
            'flex flex-col bg-zinc-950 text-foreground group/builder dark',
            !isFullscreen && 'relative h-[calc(100vh-10rem)] min-h-[500px] border border-zinc-800 rounded-xl overflow-hidden shadow-inner'
        ]"
        :style="isFullscreen ? { position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, zIndex: 9999 } : {}"
    >
            <!-- Toolbar -->
            <div class="h-12 bg-zinc-950 border-b border-zinc-800 flex items-center justify-between px-4 z-10 shrink-0">
                <!-- Left Section -->
                <div class="flex items-center gap-2">
                    <!-- Undo/Redo -->
                    <div class="flex items-center border-r border-border pr-3 mr-1 gap-1">
                        <Button variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground" :disabled="!builder.canUndo.value" @click="builder.undo" :title="t('features.builder.canvas.toolbar.undo')">
                            <Undo2 class="w-4 h-4" />
                        </Button>
                        <Button variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground" :disabled="!builder.canRedo.value" @click="builder.redo" :title="t('features.builder.canvas.toolbar.redo')">
                            <Redo2 class="w-4 h-4" />
                        </Button>
                    </div>
                    
                    <!-- Device Selector -->
                    <div class="flex items-center gap-1 text-muted-foreground">
                        <Button variant="ghost" size="icon" class="h-8 w-8" :class="{ 'bg-accent text-accent-foreground': builder.deviceMode.value === 'desktop' }" @click="builder.deviceMode.value = 'desktop'" :title="t('features.builder.canvas.toolbar.desktop')">
                            <Monitor class="w-4 h-4" />
                        </Button>
                        <Button variant="ghost" size="icon" class="h-8 w-8" :class="{ 'bg-accent text-accent-foreground': builder.deviceMode.value === 'tablet' }" @click="builder.deviceMode.value = 'tablet'" :title="t('features.builder.canvas.toolbar.tablet')">
                            <Tablet class="w-4 h-4" />
                        </Button>
                        <Button variant="ghost" size="icon" class="h-8 w-8" :class="{ 'bg-accent text-accent-foreground': builder.deviceMode.value === 'mobile' }" @click="builder.deviceMode.value = 'mobile'" :title="t('features.builder.canvas.toolbar.mobile')">
                            <Smartphone class="w-4 h-4" />
                        </Button>
                    </div>
                </div>
                
                <!-- Right Section -->
                <div class="flex items-center gap-2">
                    <!-- Preview Toggle -->
                    <Button variant="ghost" size="sm" class="h-8 gap-2 text-xs" :class="{ 'bg-accent': builder.isPreview.value }" @click="builder.isPreview.value = !builder.isPreview.value">
                        <Eye class="w-4 h-4" />
                        <span class="hidden sm:inline">Preview</span>
                    </Button>
                    
                    <!-- Fullscreen Toggle -->
                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="toggleFullscreen" :title="isFullscreen ? 'Exit Fullscreen' : 'Fullscreen'">
                        <Minimize2 v-if="isFullscreen" class="w-4 h-4" />
                        <Maximize2 v-else class="w-4 h-4" />
                    </Button>
                    
                    <!-- Exit Fullscreen Button (prominent when in fullscreen) -->
                    <Button v-if="isFullscreen" variant="outline" size="sm" class="h-8 gap-2 text-xs border-border" @click="toggleFullscreen">
                        <X class="w-4 h-4" />
                        Exit
                    </Button>
                </div>
            </div>

            <div class="flex flex-1 min-h-0 overflow-hidden">
            <div class="theme-provider contents">
                <Sidebar />
                <Canvas :context="context" />
                <PropertiesPanel />
                <!-- LayersPanel docked in PropertiesPanel -->
                <BreadcrumbsBar />
            </div>
            </div>
            
            <!-- Shared Media Picker -->
            <MediaPicker v-model:open="builder.showMediaPicker.value" @selected="handleMediaSelect">
                <template #trigger><span class="hidden"></span></template>
            </MediaPicker>
        </div>
</template>

<script setup>
import { ref, provide, watch, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useBuilder } from '@/composables/useBuilder';
import Sidebar from './sidebar/Sidebar.vue';
import Canvas from './canvas/Canvas.vue';
import PropertiesPanel from './properties/PropertiesPanel.vue';
import BreadcrumbsBar from './canvas/BreadcrumbsBar.vue';
import MediaPicker from '@/components/MediaPicker.vue';
import Button from '@/components/ui/button.vue';
import { 
    Undo2, 
    Redo2,
    Monitor,
    Tablet,
    Smartphone,
    Eye,
    Maximize2,
    Minimize2,
    X
} from 'lucide-vue-next';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    context: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['update:modelValue']);
const { t } = useI18n();

// Fullscreen state
const isFullscreen = ref(false);
const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value;
    // Prevent body scroll when fullscreen
    document.body.style.overflow = isFullscreen.value ? 'hidden' : '';
};

// Provide builder state
const builder = useBuilder();
provide('builder', builder);

// Sync initial ModelValue -> Instance State
watch(() => props.modelValue, (newVal) => {
    if (newVal && builder.blocks.value !== newVal) {
         builder.blocks.value = JSON.parse(JSON.stringify(newVal));
         // Initialize history snapshot after load
         setTimeout(() => builder.takeSnapshot(), 100);
    }
}, { immediate: true, deep: true });

// Sync Instance State -> ModelValue (Emit up)
watch(builder.blocks, (newVal) => {
    emit('update:modelValue', newVal);
}, { deep: true });

// Keyboard Shortcuts
const handleKeydown = (e) => {
    // Don't trigger if user is in an input field
    if (e.target.closest('input, textarea, [contenteditable]')) return;

    const selectedIndex = builder.editingIndex.value;
    
    // Check for Ctrl/Cmd key combinations
    if (e.ctrlKey || e.metaKey) {
        switch (e.key.toLowerCase()) {
            case 'z':
                e.preventDefault();
                if (e.shiftKey) {
                    builder.redo();
                } else {
                    builder.undo();
                }
                break;
            case 'y':
                e.preventDefault();
                builder.redo();
                break;
            case 'c':
                if (selectedIndex !== null) {
                    e.preventDefault();
                    builder.copyBlock(selectedIndex);
                }
                break;
            case 'x':
                if (selectedIndex !== null) {
                    e.preventDefault();
                    builder.cutBlock(selectedIndex);
                }
                break;
            case 'v':
                if (builder.canPaste.value) {
                    e.preventDefault();
                    builder.pasteBlock(selectedIndex);
                }
                break;
            case 'd':
                if (selectedIndex !== null) {
                    e.preventDefault();
                    builder.duplicateBlock(selectedIndex);
                }
                break;
        }
    }
    
    // Non-Ctrl shortcuts
    if (selectedIndex !== null) {
        switch (e.key) {
            case 'Delete':
            case 'Backspace':
                e.preventDefault();
                builder.removeBlock(selectedIndex);
                break;
            case 'ArrowUp':
                if (e.altKey) {
                    e.preventDefault();
                    builder.moveBlockUp(selectedIndex);
                }
                break;
            case 'ArrowDown':
                if (e.altKey) {
                    e.preventDefault();
                    builder.moveBlockDown(selectedIndex);
                }
                break;
            case 'Escape':
                builder.editingIndex.value = null;
                break;
        }
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    // Load theme setting to ensure canvas matches the frontend theme
    builder.loadActiveTheme('admin'); 
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

const handleMediaSelect = (media) => {
    if (builder.activeBlockId.value && builder.activeMediaField.value) {
        // Find block
        const block = builder.blocks.value.find(b => b.id === builder.activeBlockId.value);
        if (block) {
            // Support nested keys (e.g. "images[0].url" or "images.0.url")
            const path = builder.activeMediaField.value;
            if (path.includes('.') || path.includes('[')) {
                // Simple Deep Set Implementation
                const parts = path.replace(/\[(\d+)\]/g, '.$1').split('.');
                let current = block.settings;
                for (let i = 0; i < parts.length - 1; i++) {
                    const key = parts[i];
                    if (!current[key]) current[key] = {}; // Create object if missing (though unlikely for arrays)
                    current = current[key];
                }
                current[parts[parts.length - 1]] = media.url;
            } else {
                block.settings[path] = media.url;
            }
        }
    }
    builder.showMediaPicker.value = false;
};
</script>

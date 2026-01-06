<template>
    <div class="builder-root h-full">
        <!-- Fullscreen overlay backdrop -->
        <div 
            v-if="isFullscreen" 
            class="bg-background" 
            :style="{ position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, zIndex: 9998 }"
            @click="toggleFullscreen"
        ></div>
        
        <div 
            :class="[
                'flex flex-col bg-background text-foreground group/builder h-full',
                isFullscreen ? '' : (props.context?.builderMode ? 'relative min-h-[500px] overflow-hidden' : 'relative h-[calc(100vh-10rem)] min-h-[500px] border border-border rounded-xl overflow-hidden shadow-inner')
            ]"
            :style="isFullscreen ? { position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, zIndex: 9999 } : {}"
        >
                <!-- Toolbar -->
                <div class="h-14 bg-background border-b border-border flex items-center justify-between px-4 z-50 shrink-0 shadow-sm">
                    <!-- Left Section: History & Actions -->
                    <div class="flex items-center gap-2 flex-1">
                        <div class="flex items-center bg-muted/30 rounded-lg p-1 gap-1">
                            <Button variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground" :disabled="!builder.canUndo.value" @click="builder.undo" :title="t('features.builder.canvas.toolbar.undo')">
                                <Undo2 class="w-4 h-4" />
                            </Button>
                            <Button variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground" :disabled="!builder.canRedo.value" @click="builder.redo" :title="t('features.builder.canvas.toolbar.redo')">
                                <Redo2 class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>

                    <!-- Center Section: Device Selector -->
                    <div class="flex items-center justify-center flex-1">
                        <div class="flex items-center bg-muted/30 rounded-lg p-1 gap-1">
                            <Button 
                                variant="ghost" 
                                size="icon" 
                                class="h-8 w-8 transition-all" 
                                :class="{ 'bg-background shadow-sm text-primary': builder.deviceMode.value === 'desktop' }" 
                                @click="builder.deviceMode.value = 'desktop'" 
                                :title="t('features.builder.canvas.toolbar.desktop')"
                            >
                                <Monitor class="w-4 h-4" />
                            </Button>
                            <Button 
                                variant="ghost" 
                                size="icon" 
                                class="h-8 w-8 transition-all" 
                                :class="{ 'bg-background shadow-sm text-primary': builder.deviceMode.value === 'tablet' }" 
                                @click="builder.deviceMode.value = 'tablet'" 
                                :title="t('features.builder.canvas.toolbar.tablet')"
                            >
                                <Tablet class="w-4 h-4" />
                            </Button>
                            <Button 
                                variant="ghost" 
                                size="icon" 
                                class="h-8 w-8 transition-all" 
                                :class="{ 'bg-background shadow-sm text-primary': builder.deviceMode.value === 'mobile' }" 
                                @click="builder.deviceMode.value = 'mobile'" 
                                :title="t('features.builder.canvas.toolbar.mobile')"
                            >
                                <Smartphone class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                    
                    <!-- Right Section: Preview & System -->
                    <div class="flex items-center justify-end gap-2 flex-1">
                        <!-- Preview Toggle -->
                        <Button 
                            variant="outline" 
                            size="sm" 
                            class="h-9 gap-2 text-xs font-semibold px-4 transition-all" 
                            :class="builder.isPreview.value ? 'bg-primary text-primary-foreground border-primary' : 'hover:bg-accent'" 
                            @click="builder.isPreview.value = !builder.isPreview.value"
                        >
                            <Eye v-if="!builder.isPreview.value" class="w-4 h-4" />
                            <EyeOff v-else class="w-4 h-4" />
                            <span>{{ builder.isPreview.value ? 'Editor' : 'Preview' }}</span>
                        </Button>
                        
                        <div class="w-px h-6 bg-border mx-1"></div>

                        <!-- Fullscreen Toggle / Exit -->
                        <Button 
                            v-if="!isFullscreen"
                            variant="ghost" 
                            size="icon" 
                            class="h-9 w-9 text-muted-foreground hover:text-foreground" 
                            @click="toggleFullscreen" 
                            title="Fullscreen"
                        >
                            <Maximize2 class="w-4 h-4" />
                        </Button>

                        <Button 
                            v-else
                            variant="destructive" 
                            size="sm" 
                            class="h-9 gap-2 text-xs font-bold px-4 shadow-lg shadow-destructive/20" 
                            @click="toggleFullscreen"
                        >
                            <X class="w-4 h-4" />
                            Exit Builder
                        </Button>
                    </div>
                </div>

                <div class="flex flex-1 min-h-0 overflow-hidden">
                <div class="theme-provider contents">
                    <Sidebar v-if="!builder.isPreview.value" />
                    <Canvas :context="context" />
                    <PropertiesPanel v-if="!builder.isPreview.value" />
                </div>
                </div>
                
                <!-- Shared Media Picker -->
                <MediaPicker v-model:open="builder.showMediaPicker.value" @selected="handleMediaSelect">
                    <template #trigger><span class="hidden"></span></template>
                </MediaPicker>

                <!-- Global Context Menu -->
                <ContextMenu />
            </div>
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
import ContextMenu from './ui/ContextMenu.vue';
import Button from '@/components/ui/button.vue';
import { 
    Undo2, 
    Redo2,
    Monitor,
    Tablet,
    Smartphone,
    Eye,
    EyeOff,
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
    // User requested to rely on app.css, so we remove manual theme injection
    // builder.loadActiveTheme('admin'); 
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

const handleMediaSelect = (media) => {
    builder.handleMediaSelect(media);
};
</script>

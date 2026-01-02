<template>
    <div class="flex flex-col h-[calc(100vh-10rem)] min-h-[500px] border rounded-xl overflow-hidden bg-background shadow-inner relative group/builder">
        <!-- Toolbar -->
        <div class="h-12 bg-background border-b border-border flex items-center px-4 z-10 shrink-0">
            <div class="flex items-center gap-2">
                <div class="flex items-center mr-2 border-r border-border pr-2 gap-1">
                    <Button 
                        variant="ghost" 
                        size="icon" 
                        class="h-8 w-8 text-muted-foreground hover:text-foreground"
                        :disabled="!builder.canUndo.value"
                        @click="builder.undo"
                        title="Undo (Ctrl+Z)"
                    >
                        <Undo2 class="w-4 h-4" />
                    </Button>
                    <Button 
                        variant="ghost" 
                        size="icon" 
                        class="h-8 w-8 text-muted-foreground hover:text-foreground"
                        :disabled="!builder.canRedo.value"
                        @click="builder.redo"
                        title="Redo (Ctrl+Y)"
                    >
                        <Redo2 class="w-4 h-4" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Main Content Area (Sidebar + Canvas + Properties Panel) -->
        <div class="flex flex-1 min-h-0 overflow-hidden">
            <Sidebar />
            <Canvas />
            <PropertiesPanel />
        </div>
        
        <!-- Shared Media Picker -->
         <MediaPicker
            v-model:open="builder.showMediaPicker.value"
            @selected="handleMediaSelect"
        >
            <template #trigger><span class="hidden"></span></template>
        </MediaPicker>
    </div>
</template>

<script setup>
import { ref, provide, watch, onMounted, onUnmounted } from 'vue';
import { useBuilder } from '@/composables/useBuilder';
import Sidebar from './sidebar/Sidebar.vue';
import Canvas from './canvas/Canvas.vue';
import PropertiesPanel from './properties/PropertiesPanel.vue';
import MediaPicker from '@/components/MediaPicker.vue';
import Button from '@/components/ui/button.vue';
import { 
    Undo2, 
    Redo2 
} from 'lucide-vue-next';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue']);

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
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

const handleMediaSelect = (media) => {
    if (builder.activeBlockId.value && builder.activeMediaField.value) {
        // Find block
        const block = builder.blocks.value.find(b => b.id === builder.activeBlockId.value);
        if (block) {
            block.settings[builder.activeMediaField.value] = media.url;
        }
    }
    builder.showMediaPicker.value = false;
};
</script>

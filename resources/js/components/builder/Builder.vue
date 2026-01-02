<template>
    <div class="flex h-[calc(100vh-12rem)] min-h-[600px] border rounded-xl overflow-hidden bg-background shadow-inner relative group/builder">
        <!-- Toolbar -->
        <div class="absolute top-0 left-0 right-0 h-12 bg-background border-b border-border flex items-center px-4 z-10">
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

        <!-- Modular Components -->
        <Sidebar />
        <Canvas />
        <PropertiesPanel />
        
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
    // Check for Ctrl/Cmd key
    if ((e.ctrlKey || e.metaKey) && !e.target.closest('input, textarea')) {
        if (e.key === 'z') {
            e.preventDefault();
            if (e.shiftKey) {
                builder.redo();
            } else {
                builder.undo();
            }
        } else if (e.key === 'y') {
            e.preventDefault();
            builder.redo();
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

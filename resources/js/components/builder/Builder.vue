<template>
    <div class="flex h-[calc(100vh-12rem)] min-h-[600px] border rounded-xl overflow-hidden bg-background shadow-inner relative group/builder">
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
import { provide, watch } from 'vue';
import { useBuilder } from '@/composables/useBuilder';
import Sidebar from './sidebar/Sidebar.vue';
import Canvas from './canvas/Canvas.vue';
import PropertiesPanel from './properties/PropertiesPanel.vue';
import MediaPicker from '@/components/MediaPicker.vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue']);

// Initialize Builder State
const builder = useBuilder();

// Sync initial ModelValue -> Instance State
watch(() => props.modelValue, (newVal) => {
    // Only update if different length to avoid loop, or better: use deep change check?
    // Careful with loops. 
    // Ideally, we just set it once at start? 
    // Or if parent updates it (e.g. from server).
    // For now, let's just initialize it.
    if (newVal && builder.blocks.value !== newVal) {
         builder.blocks.value = newVal;
    }
}, { immediate: true, deep: true });

// Sync Instance State -> ModelValue (Emit up)
watch(builder.blocks, (newVal) => {
    emit('update:modelValue', newVal);
}, { deep: true });

// Provide the builder instance to all children
provide('builder', builder);

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

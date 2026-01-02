<template>
    <div 
        v-if="builder.showLayersPanel.value"
        class="fixed z-40 w-72 bg-sidebar border border-border shadow-2xl rounded-xl flex flex-col max-h-[70vh] pointer-events-auto will-change-transform"
        :class="isDragging ? 'transition-none shadow-3xl' : 'transition-transform duration-300 ease-out'"
        :style="{ 
            transform: `translate3d(${position.x}px, ${position.y}px, 0)`
        }"
    >
        <!-- Panel Header -->
        <div 
            class="h-10 shrink-0 flex items-center justify-between px-3 border-b border-border bg-sidebar-accent/10 cursor-grab active:cursor-grabbing"
            @mousedown="startDragging"
        >
            <div class="flex items-center gap-2 select-none">
                <Layers class="w-4 h-4 text-primary" />
                <span class="text-xs font-bold">Layers</span>
            </div>
            <div class="flex items-center gap-1">
                <Button variant="ghost" size="icon" class="h-6 w-6" @click="builder.showLayersPanel.value = false">
                    <X class="w-3.5 h-3.5" />
                </Button>
            </div>
        </div>

        <!-- Tree Content -->
        <div class="flex-1 overflow-y-auto p-2 custom-scrollbar">
            <LayersTree :blocks="builder.blocks.value" />
        </div>
        
        <!-- Footer / Stats -->
        <div class="h-8 shrink-0 px-3 flex items-center justify-between border-t border-border bg-muted/30">
            <span class="text-[9px] text-muted-foreground font-medium">{{ builder.blocks.value.length }} Root Elements</span>
        </div>
    </div>
</template>

<script setup>
import { inject, ref, onMounted, onUnmounted } from 'vue';
import { Layers, X } from 'lucide-vue-next';
import Button from '@/components/ui/button.vue';
import LayersTree from './LayersTree.vue';

const builder = inject('builder');

// Draggable State
const position = ref({ x: window.innerWidth - 340 - 288, y: 80 });
const isDragging = ref(false);
const dragOffset = ref({ x: 0, y: 0 });

const startDragging = (e) => {
    isDragging.value = true;
    dragOffset.value = {
        x: e.clientX - position.value.x,
        y: e.clientY - position.value.y
    };
    
    window.addEventListener('mousemove', onDragging);
    window.addEventListener('mouseup', stopDragging);
};

const onDragging = (e) => {
    if (!isDragging.value) return;
    
    // Bounds checking (optional but good practice)
    let newX = e.clientX - dragOffset.value.x;
    let newY = e.clientY - dragOffset.value.y;
    
    // Constrain to window
    newX = Math.max(0, Math.min(newX, window.innerWidth - 288));
    newY = Math.max(0, Math.min(newY, window.innerHeight - 100));
    
    position.value = { x: newX, y: newY };
};

const stopDragging = () => {
    isDragging.value = false;
    window.removeEventListener('mousemove', onDragging);
    window.removeEventListener('mouseup', stopDragging);
};

onUnmounted(() => {
    stopDragging();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: hsl(var(--muted-foreground) / 0.15);
    border-radius: 10px;
}
</style>

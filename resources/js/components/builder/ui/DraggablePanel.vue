<template>
    <div 
        ref="panelRef"
        class="fixed flex flex-col bg-card border border-border/50 rounded-lg shadow-xl transition-shadow duration-200 z-[100]"
        :class="[
            isDragging ? 'cursor-grabbing ring-1 ring-primary/20 scale-[1.005]' : '',
            isCollapsed ? 'h-auto w-auto' : ''
        ]"
        :style="{
            left: `${x}px`,
            top: `${y}px`,
            width: isCollapsed ? 'auto' : `${width}px`,
            height: isCollapsed ? 'auto' : `${height}px`,
            zIndex: zIndex
        }"
        @mousedown="bringToFront"
    >
        <!-- Refined Header / Drag Handle -->
        <div 
            class="h-8 px-2 flex items-center justify-between border-b border-border/40 bg-muted/50 rounded-t-lg select-none cursor-grab active:cursor-grabbing backdrop-blur-md group"
            @mousedown="startDrag"
        >
            <div class="flex items-center gap-2 text-muted-foreground group-hover:text-foreground transition-colors">
                <slot name="icon"></slot>
                <span v-if="!isCollapsed" class="text-[10px] font-bold">{{ title }}</span>
            </div>
            
            <div class="flex items-center gap-1" @mousedown.stop>
                <!-- Collapse Toggle -->
                <button 
                    @click="toggleCollapse"
                    class="h-5 w-5 flex items-center justify-center hover:bg-background/80 rounded transition-colors text-muted-foreground hover:text-foreground"
                    :title="isCollapsed ? 'Expand' : 'Collapse'"
                >
                    <component :is="isCollapsed ? 'Maximize2' : 'Minus'" class="w-3 h-3" />
                </button>
            </div>
        </div>

        <!-- Content -->
        <div v-show="!isCollapsed" class="flex-1 overflow-hidden flex flex-col bg-card relative rounded-b-lg">
            <slot></slot>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Maximize2, Minus } from 'lucide-vue-next';

const props = defineProps({
    title: { type: String, default: 'Panel' },
    initialX: { type: Number, default: 20 },
    initialY: { type: Number, default: 80 },
    width: { type: Number, default: 320 },
    height: { type: Number, default: 600 }, // Fixed height for scrolling
    startCollapsed: { type: Boolean, default: false }
});

const panelRef = ref(null);
const x = ref(props.initialX);
const y = ref(props.initialY);
const zIndex = ref(100);
const isDragging = ref(false);
const isCollapsed = ref(props.startCollapsed);

let startX = 0;
let startY = 0;
let initialLeft = 0;
let initialTop = 0;

const bringToFront = () => {
    const allPanels = document.querySelectorAll('.fixed.flex');
    let maxZ = 0;
    allPanels.forEach(el => {
        const z = parseInt(window.getComputedStyle(el).zIndex);
        if (!isNaN(z) && z > maxZ) maxZ = z;
    });
    zIndex.value = maxZ + 1;
};

const startDrag = (e) => {
    isDragging.value = true;
    startX = e.clientX;
    startY = e.clientY;
    initialLeft = x.value;
    initialTop = y.value;
    
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', stopDrag);
    
    bringToFront();
};

const onDrag = (e) => {
    if (!isDragging.value) return;
    
    const dx = e.clientX - startX;
    const dy = e.clientY - startY;
    
    // Boundary checks 
    const newX = initialLeft + dx;
    const newY = initialTop + dy;
    
    // Simple bounds
    const maxX = window.innerWidth - 40;
    const maxY = window.innerHeight - 40; 
    
    x.value = Math.max(0, Math.min(newX, maxX));
    y.value = Math.max(0, Math.min(newY, maxY));
};

const stopDrag = () => {
    isDragging.value = false;
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
};

const toggleCollapse = () => {
    isCollapsed.value = !isCollapsed.value;
};

// Expose position for external saving if needed
defineExpose({ x, y, isCollapsed });
</script>

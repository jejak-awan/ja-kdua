<template>
    <div 
        class="before-after-container relative overflow-hidden select-none group/slider"
        :class="[padding]"
        :style="{ borderRadius: border? border.radius : '8px' }"
        @mousedown="startDrag"
        @touchstart="startDrag"
        ref="containerRef"
    >
        <!-- After Image (Background) -->
        <img 
            v-if="afterImage" 
            :src="afterImage" 
            class="w-full h-auto object-cover block"
            alt="After" 
        />
        <div v-else class="w-full h-64 bg-gray-100 flex items-center justify-center text-gray-400">
            No After Image
        </div>

        <!-- Before Image (Overlay) -->
        <div 
            class="absolute top-0 left-0 h-full overflow-hidden"
            :style="{ width: `${currentPosition}%` }"
        >
            <img 
                v-if="beforeImage" 
                :src="beforeImage" 
                class="absolute top-0 left-0 h-full max-w-none object-cover" 
                :style="{ width: containerWidth + 'px' }"
                alt="Before" 
            />
             <div v-else class="w-full h-full bg-gray-200 flex items-center justify-center text-gray-500 absolute top-0 left-0">
                No Before Image
            </div>
        </div>

        <!-- Labels -->
        <div v-if="showLabels" class="absolute top-4 left-4 z-20 bg-black/50 text-white px-2 py-1 rounded text-xs backdrop-blur-sm pointer-events-none">
            {{ beforeLabel }}
        </div>
        <div v-if="showLabels" class="absolute top-4 right-4 z-20 bg-black/50 text-white px-2 py-1 rounded text-xs backdrop-blur-sm pointer-events-none">
            {{ afterLabel }}
        </div>

        <!-- Slider Handle -->
        <div 
            class="absolute top-0 bottom-0 z-10 cursor-ew-resize flex items-center justify-center transition-transform hover:scale-105"
            :style="{ 
                left: `${currentPosition}%`, 
                width: `${sliderWidth}px`, 
                backgroundColor: sliderColor || '#ffffff',
                transform: 'translateX(-50%)'
            }"
        >
            <div 
                class="w-8 h-8 rounded-full shadow-lg flex items-center justify-center"
                :style="{ backgroundColor: sliderColor || '#ffffff' }"
            >
                <div class="flex gap-0.5">
                    <div class="w-0.5 h-3 bg-black/20"></div>
                    <div class="w-0.5 h-3 bg-black/20"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    beforeImage: String,
    afterImage: String,
    beforeLabel: { type: String, default: 'Before' },
    afterLabel: { type: String, default: 'After' },
    showLabels: { type: Boolean, default: true },
    sliderPosition: { type: [Number, String], default: 50 },
    sliderColor: String,
    sliderWidth: { type: [Number, String], default: 4 },
    padding: String,
    border: Object
});

const containerRef = ref(null);
const currentPosition = ref(Number(props.sliderPosition));
const containerWidth = ref(0);
const isDragging = ref(false);

const updateWidth = () => {
    if (containerRef.value) {
        containerWidth.value = containerRef.value.offsetWidth;
    }
};

const handleMove = (event) => {
    if (!isDragging.value || !containerRef.value) return;
    
    const rect = containerRef.value.getBoundingClientRect();
    const clientX = event.touches ? event.touches[0].clientX : event.clientX;
    const x = Math.max(0, Math.min(clientX - rect.left, rect.width));
    
    currentPosition.value = (x / rect.width) * 100;
};

const startDrag = (event) => {
    isDragging.value = true;
    handleMove(event); // Snap to click immediately
};

const stopDrag = () => {
    isDragging.value = false;
};

onMounted(() => {
    updateWidth();
    window.addEventListener('resize', updateWidth);
    window.addEventListener('mousemove', handleMove);
    window.addEventListener('touchmove', handleMove);
    window.addEventListener('mouseup', stopDrag);
    window.addEventListener('touchend', stopDrag);
    
    // Initial resize check
    nextTick(updateWidth);
});

onUnmounted(() => {
    window.removeEventListener('resize', updateWidth);
    window.removeEventListener('mousemove', handleMove);
    window.removeEventListener('touchmove', handleMove);
    window.removeEventListener('mouseup', stopDrag);
    window.removeEventListener('touchend', stopDrag);
});
</script>

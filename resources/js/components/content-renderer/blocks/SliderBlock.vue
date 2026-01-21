<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    items: {
        type: Array,
        default: () => []
    },
    autoplay: { type: Boolean, default: true },
    autoplaySpeed: { type: [Number, String], default: 5000 },
    showArrows: { type: Boolean, default: true },
    showDots: { type: Boolean, default: true },
    height: { type: [Number, String], default: 500 },
    overlayEnabled: { type: Boolean, default: true },
    overlayColor: { type: String, default: 'rgba(0,0,0,0.4)' },
    slideTransition: { type: String, default: 'fade' }
});

const currentIndex = ref(0);
let autoplayInterval = null;

const safeItems = computed(() => props.items || []);

const nextSlide = () => {
    if (safeItems.value.length > 1) {
        currentIndex.value = (currentIndex.value + 1) % safeItems.value.length;
    }
};

const prevSlide = () => {
    if (safeItems.value.length > 1) {
        currentIndex.value = (currentIndex.value - 1 + safeItems.value.length) % safeItems.value.length;
    }
};

const goToSlide = (index) => {
    currentIndex.value = index;
};

const startAutoplay = () => {
    if (props.autoplay && safeItems.value.length > 1) {
        const speed = parseInt(props.autoplaySpeed) || 5000;
        autoplayInterval = setInterval(nextSlide, speed);
    }
};

const stopAutoplay = () => {
    if (autoplayInterval) {
        clearInterval(autoplayInterval);
        autoplayInterval = null;
    }
};

const containerStyles = computed(() => {
    const h = typeof props.height === 'number' ? `${props.height}px` : props.height;
    return {
        height: h,
        position: 'relative',
        overflow: 'hidden'
    };
});

onMounted(startAutoplay);
onUnmounted(stopAutoplay);

watch(() => props.autoplay, (newVal) => {
    stopAutoplay();
    if (newVal) startAutoplay();
});
</script>

<template>
    <div 
        class="slider-renderer group"
        :style="containerStyles"
        @mouseenter="stopAutoplay"
        @mouseleave="startAutoplay"
    >
        <!-- Slides -->
        <div class="relative w-full h-full">
            <transition-group
                :name="slideTransition"
                tag="div"
                class="relative w-full h-full"
            >
                <div
                    v-for="(item, index) in safeItems"
                    v-show="index === currentIndex"
                    :key="index"
                    class="absolute inset-0 w-full h-full flex items-center justify-center translate-gpu"
                >
                    <!-- Background -->
                    <div 
                        class="absolute inset-0 bg-cover bg-center"
                        :style="{ 
                            backgroundImage: item.image ? `url(${item.image})` : 'none',
                            backgroundColor: item.image ? 'transparent' : '#1a1a1a'
                        }"
                    >
                        <!-- Overlay -->
                        <div 
                            v-if="overlayEnabled"
                            class="absolute inset-0" 
                            :style="{ backgroundColor: overlayColor || 'rgba(0,0,0,0.4)' }"
                        ></div>
                    </div>
                    
                    <!-- Content -->
                    <div 
                        class="relative z-10 px-6 max-w-4xl"
                        :class="[
                            item.alignment === 'left' ? 'text-left mr-auto' : 
                            item.alignment === 'right' ? 'text-right ml-auto' : 
                            'text-center mx-auto'
                        ]"
                    >
                        <h2 v-if="item.title" class="text-3xl md:text-5xl lg:text-6xl font-extrabold mb-4 text-white drop-shadow-md leading-tight">
                            {{ item.title }}
                        </h2>
                        <div v-if="item.content" class="text-base md:text-lg lg:text-xl text-white/90 mb-8 max-w-2xl mx-auto drop-shadow-sm prose prose-invert" v-html="item.content"></div>
                        
                        <a 
                            v-if="item.buttonText" 
                            :href="item.buttonUrl || '#'"
                            class="inline-block px-8 py-3.5 bg-white text-black font-bold rounded-full hover:scale-105 transition-transform shadow-xl"
                        >
                            {{ item.buttonText }}
                        </a>
                    </div>
                </div>
            </transition-group>
        </div>
        
        <!-- Navigation Arrows -->
        <template v-if="showArrows && safeItems.length > 1">
            <button 
                @click="prevSlide"
                class="absolute left-6 top-1/2 -translate-y-1/2 w-12 h-12 bg-black/10 backdrop-blur-md rounded-full flex items-center justify-center text-white border border-white/10 hover:bg-white/20 transition-all opacity-0 group-hover:opacity-100 z-20"
            >
                <ChevronLeft class="w-6 h-6" />
            </button>
            <button 
                @click="nextSlide"
                class="absolute right-6 top-1/2 -translate-y-1/2 w-12 h-12 bg-black/10 backdrop-blur-md rounded-full flex items-center justify-center text-white border border-white/10 hover:bg-white/20 transition-all opacity-0 group-hover:opacity-100 z-20"
            >
                <ChevronRight class="w-6 h-6" />
            </button>
        </template>
        
        <!-- Dots -->
        <div v-if="showDots && safeItems.length > 1" class="absolute bottom-8 left-1/2 -translate-x-1/2 flex gap-2 z-20">
            <button 
                v-for="(_, index) in safeItems" 
                :key="index"
                @click="goToSlide(index)"
                class="h-2 rounded-full transition-all"
                :class="index === currentIndex ? 'bg-white w-6' : 'bg-white/40 w-2 hover:bg-white/60'"
            ></button>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.6s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from {
    transform: translateX(100%);
}
.slide-leave-to {
    transform: translateX(-100%);
}

.prose-invert {
    color: white;
}
</style>

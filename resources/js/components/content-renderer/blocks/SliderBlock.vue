<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    slides: {
        type: Array,
        default: () => [
            { title: 'Slide 1', subtitle: 'First slide content', image: '', button_text: '', button_url: '' },
            { title: 'Slide 2', subtitle: 'Second slide content', image: '', button_text: '', button_url: '' }
        ]
    },
    autoplay: { type: Boolean, default: true },
    autoplay_speed: { type: Number, default: 5000 },
    show_arrows: { type: Boolean, default: true },
    show_dots: { type: Boolean, default: true },
    height: { type: String, default: 'h-[500px]' },
    overlay_color: { type: String, default: 'rgba(0,0,0,0.4)' },
    animation: { type: String, default: 'fade' }
});

const currentIndex = ref(0);
let autoplayInterval = null;

const safeSlides = computed(() => props.slides || []);

const nextSlide = () => {
    if (safeSlides.value.length > 0) {
        currentIndex.value = (currentIndex.value + 1) % safeSlides.value.length;
    }
};

const prevSlide = () => {
    if (safeSlides.value.length > 0) {
        currentIndex.value = (currentIndex.value - 1 + safeSlides.value.length) % safeSlides.value.length;
    }
};

const goToSlide = (index) => {
    currentIndex.value = index;
};

const startAutoplay = () => {
    if (props.autoplay && safeSlides.value.length > 1) {
        autoplayInterval = setInterval(nextSlide, props.autoplay_speed || 5000);
    }
};

const stopAutoplay = () => {
    if (autoplayInterval) {
        clearInterval(autoplayInterval);
        autoplayInterval = null;
    }
};

const containerClasses = computed(() => {
    return ['relative overflow-hidden group', props.height || 'h-[500px]'].filter(Boolean);
});

const transitionName = computed(() => {
    return props.animation === 'slide' ? 'slide' : 'fade';
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
        :class="containerClasses"
        @mouseenter="stopAutoplay"
        @mouseleave="startAutoplay"
    >
        <!-- Slides -->
        <div class="relative w-full h-full">
            <transition-group
                :name="transitionName"
                tag="div"
                class="relative w-full h-full"
            >
                <div
                    v-for="(slide, index) in safeSlides"
                    v-show="index === currentIndex"
                    :key="index"
                    class="absolute inset-0 w-full h-full"
                >
                    <!-- Background -->
                    <div 
                        class="absolute inset-0 bg-cover bg-center"
                        :style="{ 
                            backgroundImage: slide.image ? `url(${slide.image})` : 'none',
                            backgroundColor: slide.image ? 'transparent' : 'hsl(var(--muted))'
                        }"
                    >
                        <div class="absolute inset-0" :style="{ backgroundColor: overlay_color || 'rgba(0,0,0,0.4)' }"></div>
                    </div>
                    
                    <!-- Content -->
                    <div class="relative z-10 h-full flex flex-col items-center justify-center text-center text-white px-6">
                        <h2 class="text-4xl md:text-6xl font-extrabold mb-4 drop-shadow-lg">{{ slide.title || '' }}</h2>
                        <p class="text-lg md:text-2xl opacity-90 max-w-2xl mb-8 drop-shadow-md">{{ slide.subtitle || '' }}</p>
                        <a 
                            v-if="slide.button_text" 
                            :href="slide.button_url || '#'"
                            class="px-8 py-4 bg-primary text-primary-foreground font-bold rounded-full hover:opacity-90 transition-all shadow-xl"
                        >
                            {{ slide.button_text }}
                        </a>
                    </div>
                </div>
            </transition-group>
        </div>
        
        <!-- Navigation Arrows -->
        <template v-if="show_arrows && safeSlides.length > 1">
            <button 
                @click="prevSlide"
                class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-white/30 transition-all opacity-0 group-hover:opacity-100"
            >
                <ChevronLeft class="w-6 h-6" />
            </button>
            <button 
                @click="nextSlide"
                class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-white/30 transition-all opacity-0 group-hover:opacity-100"
            >
                <ChevronRight class="w-6 h-6" />
            </button>
        </template>
        
        <!-- Dots -->
        <div v-if="show_dots && safeSlides.length > 1" class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2">
            <button 
                v-for="(slide, index) in safeSlides" 
                :key="index"
                @click="goToSlide(index)"
                class="w-3 h-3 rounded-full transition-all"
                :class="index === currentIndex ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/70'"
            ></button>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: transform 0.5s ease;
}
.slide-enter-from {
    transform: translateX(100%);
}
.slide-leave-to {
    transform: translateX(-100%);
}
</style>

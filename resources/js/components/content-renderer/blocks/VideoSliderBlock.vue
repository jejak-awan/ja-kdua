<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { ChevronLeft, ChevronRight, Play, Film, X } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    items: { type: Array, default: () => [] },
    showArrows: { type: Boolean, default: true },
    showDots: { type: Boolean, default: true },
    showThumbnails: { type: Boolean, default: true },
    thumbnailPosition: { type: String, default: 'bottom' },
    autoplay: { type: Boolean, default: false },
    autoplaySpeed: { type: [Number, String], default: 5000 },
    aspectRatio: { type: String, default: '16:9' },
    slidesPerView: { type: [Number, String], default: 1 },
    gap: { type: [Number, String], default: 20 },
    videoAutoplay: { type: Boolean, default: false },
    videoMuted: { type: Boolean, default: true },
    videoLoop: { type: Boolean, default: false },
    showControls: { type: Boolean, default: true },
    showPlayButton: { type: Boolean, default: true },
    playButtonSize: { type: [Number, String], default: 80 },
    playButtonColor: { type: String, default: '#ffffff' },
    overlayColor: { type: String, default: 'rgba(0,0,0,0.3)' }
});

const activeIndex = ref(0);
const lightboxOpen = ref(false);
const activeVideo = ref(null);

const safeItems = computed(() => props.items || []);

const getThumb = (video) => {
    if (video.thumbnail) return video.thumbnail;
    if (video.type === 'youtube' && video.videoId) {
        return `https://img.youtube.com/vi/${video.videoId}/hqdefault.jpg`;
    }
    return null;
};

const getEmbedUrl = (video) => {
    if (!video) return '';
    let url = '';
    if (video.type === 'youtube') {
        url = `https://www.youtube.com/embed/${video.videoId}?autoplay=1`;
    } else if (video.type === 'vimeo') {
        url = `https://player.vimeo.com/video/${video.videoId}?autoplay=1`;
    } else {
        url = video.videoId; // Link to mp4
    }
    
    if (props.videoMuted) url += '&mute=1';
    if (props.videoLoop) url += '&loop=1';
    if (!props.showControls) url += '&controls=0';
    
    return url;
};

const openLightbox = (video) => {
    activeVideo.value = video;
    lightboxOpen.value = true;
};

const closeLightbox = () => {
    lightboxOpen.value = false;
    activeVideo.value = null;
};

const next = () => {
    activeIndex.value = (activeIndex.value + 1) % Math.max(1, Math.ceil(safeItems.value.length / parseInt(props.slidesPerView)));
};

const prev = () => {
    const total = Math.ceil(safeItems.value.length / parseInt(props.slidesPerView));
    activeIndex.value = (activeIndex.value - 1 + total) % total;
};

const trackStyles = computed(() => ({
    display: 'flex',
    gap: `${props.gap}px`,
    transform: `translateX(-${activeIndex.value * 100}%)`,
    transition: 'transform 0.5s cubic-bezier(0.4, 0, 0.2, 1)'
}));

const slideStyles = computed(() => {
    const spv = parseInt(props.slidesPerView) || 1;
    const g = parseInt(props.gap) || 20;
    return {
        flex: `0 0 calc(${100 / spv}% - ${(g * (spv - 1)) / spv}px)`,
        minWidth: 0
    };
});

const aspectStyles = computed(() => {
    const [w, h] = props.aspectRatio.split(':').map(Number);
    return {
        paddingTop: `${(h / w) * 100}%`
    };
});

const playButtonStyles = computed(() => ({
    width: `${props.playButtonSize}px`,
    height: `${props.playButtonSize}px`,
    color: props.playButtonColor
}));
</script>

<template>
    <div class="video-slider-renderer group">
        <div class="relative overflow-hidden">
            <!-- Track -->
            <div :style="trackStyles">
                <div 
                    v-for="(video, index) in safeItems" 
                    :key="index"
                    class="video-slide"
                    :style="slideStyles"
                >
                    <div 
                        class="relative rounded-2xl overflow-hidden cursor-pointer bg-black/5"
                        :style="aspectStyles"
                        @click="openLightbox(video)"
                    >
                        <img 
                            v-if="getThumb(video)" 
                            :src="getThumb(video)" 
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover/slide:scale-110"
                        />
                        <div v-else class="absolute inset-0 flex items-center justify-center bg-muted">
                            <Film class="w-12 h-12 text-muted-foreground/30" />
                        </div>

                        <!-- Overlay -->
                        <div 
                            class="absolute inset-0 flex items-center justify-center transition-opacity duration-300"
                            :style="{ backgroundColor: overlayColor }"
                        >
                            <div 
                                v-if="showPlayButton"
                                class="rounded-full bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center transition-transform duration-300 group-hover:scale-110"
                                :style="playButtonStyles"
                            >
                                <Play fill="currentColor" class="w-2/5 h-2/5 translate-x-1" />
                            </div>
                        </div>

                        <div class="absolute bottom-0 left-0 right-0 p-6 bg-gradient-to-t from-black/60 to-transparent">
                            <h4 v-if="video.title" class="text-white font-bold text-lg leading-tight">{{ video.title }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Arrows -->
            <template v-if="showArrows && safeItems.length > parseInt(slidesPerView)">
                <button 
                    @click="prev"
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/10 backdrop-blur-md rounded-full flex items-center justify-center text-white border border-white/20 hover:bg-white/30 transition-all opacity-0 group-hover:opacity-100 z-10"
                >
                    <ChevronLeft class="w-6 h-6" />
                </button>
                <button 
                    @click="next"
                    class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/10 backdrop-blur-md rounded-full flex items-center justify-center text-white border border-white/20 hover:bg-white/30 transition-all opacity-0 group-hover:opacity-100 z-10"
                >
                    <ChevronRight class="w-6 h-6" />
                </button>
            </template>
        </div>

        <!-- Dots -->
        <div v-if="showDots && safeItems.length > parseInt(slidesPerView)" class="flex justify-center gap-2 mt-6">
            <button 
                v-for="i in Math.ceil(safeItems.length / parseInt(slidesPerView))" 
                :key="i"
                @click="activeIndex = i - 1"
                class="h-2 rounded-full transition-all duration-300"
                :class="activeIndex === i - 1 ? 'bg-primary w-8' : 'bg-muted-foreground/30 w-2 hover:bg-muted-foreground/50'"
            ></button>
        </div>

        <!-- Lightbox -->
        <transition name="lightbox">
            <div v-if="lightboxOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-12">
                <div class="absolute inset-0 bg-black/90 backdrop-blur-sm" @click="closeLightbox"></div>
                
                <div class="relative w-full max-w-6xl aspect-video bg-black rounded-3xl overflow-hidden shadow-2xl">
                    <button 
                        @click="closeLightbox"
                        class="absolute top-6 right-6 z-10 w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-colors"
                    >
                        <X class="w-6 h-6" />
                    </button>

                    <iframe 
                        v-if="activeVideo && activeVideo.type !== 'mp4'"
                        :src="getEmbedUrl(activeVideo)"
                        class="w-full h-full"
                        frameborder="0"
                        allow="autoplay; fullscreen; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                    
                    <video 
                        v-else-if="activeVideo && activeVideo.type === 'mp4'"
                        :src="activeVideo.videoId"
                        class="w-full h-full"
                        controls
                        autoplay
                    ></video>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.lightbox-enter-active,
.lightbox-leave-active {
    transition: opacity 0.4s ease;
}
.lightbox-enter-from,
.lightbox-leave-to {
    opacity: 0;
}

.lightbox-enter-active .relative,
.lightbox-leave-active .relative {
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.lightbox-enter-from .relative {
    transform: scale(0.9) translateY(20px);
}
.lightbox-leave-to .relative {
    transform: scale(0.95);
}
</style>

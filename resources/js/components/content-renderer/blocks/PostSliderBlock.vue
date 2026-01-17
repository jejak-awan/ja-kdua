<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import api from '@/services/api';
import { ChevronLeft, ChevronRight, Loader2 } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    title: { type: String, default: '' },
    post_type: { type: String, default: 'post' },
    category: { type: String, default: '' },
    limit: { type: Number, default: 5 },
    autoplay: { type: Boolean, default: true },
    autoplay_speed: { type: Number, default: 5000 },
    show_arrows: { type: Boolean, default: true },
    show_dots: { type: Boolean, default: true },
    show_excerpt: { type: Boolean, default: true },
    height: { type: String, default: 'h-[500px]' },
    overlay_color: { type: String, default: 'rgba(0,0,0,0.5)' },
    padding: { type: String, default: '' }
});

const posts = ref([]);
const loading = ref(true);
const currentIndex = ref(0);
let autoplayInterval = null;

const containerClasses = computed(() => {
    return ['transition-all duration-300', props.padding].filter(Boolean);
});

const fetchPosts = async () => {
    loading.value = true;
    try {
        const params = {
            type: props.post_type,
            limit: props.limit,
            status: 'published'
        };
        if (props.category) params.category = props.category;
        
        const response = await api.get('/cms/contents', { params });
        posts.value = response.data?.data || response.data || [];
    } catch (err) {
        console.warn('PostSlider: Failed to fetch posts', err);
        // Fallback demo data
        posts.value = Array.from({ length: props.limit }, (_, i) => ({
            id: i + 1,
            title: `Sample Post ${i + 1}`,
            excerpt: 'This is a preview of how your posts will appear in the slider.',
            featured_image: null,
            slug: '#'
        }));
    } finally {
        loading.value = false;
    }
};

const nextSlide = () => {
    if (posts.value.length > 0) {
        currentIndex.value = (currentIndex.value + 1) % posts.value.length;
    }
};

const prevSlide = () => {
    if (posts.value.length > 0) {
        currentIndex.value = (currentIndex.value - 1 + posts.value.length) % posts.value.length;
    }
};

const goToSlide = (index) => {
    currentIndex.value = index;
};

const startAutoplay = () => {
    if (props.autoplay && posts.value.length > 1) {
        autoplayInterval = setInterval(nextSlide, props.autoplay_speed);
    }
};

const stopAutoplay = () => {
    if (autoplayInterval) {
        clearInterval(autoplayInterval);
        autoplayInterval = null;
    }
};

onMounted(() => {
    fetchPosts();
});

watch(() => [props.post_type, props.category, props.limit], fetchPosts);
watch(() => props.autoplay, (newVal) => {
    stopAutoplay();
    if (newVal) startAutoplay();
});
watch(() => posts.value.length, () => {
    stopAutoplay();
    startAutoplay();
});
</script>

<template>
    <div :class="containerClasses">
        <h2 v-if="title" class="text-3xl md:text-4xl font-extrabold mb-8 tracking-tight text-center">{{ title }}</h2>
        
        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-20">
            <Loader2 class="w-8 h-8 animate-spin text-primary" />
        </div>
        
        <!-- Slider -->
        <div 
            v-else
            :class="['relative overflow-hidden group', height]"
            @mouseenter="stopAutoplay"
            @mouseleave="startAutoplay"
        >
            <transition-group name="fade" tag="div" class="relative w-full h-full">
                <div
                    v-for="(post, index) in posts"
                    v-show="index === currentIndex"
                    :key="post.id"
                    class="absolute inset-0 w-full h-full"
                >
                    <!-- Background -->
                    <div 
                        class="absolute inset-0 bg-cover bg-center"
                        :style="{ 
                            backgroundImage: post.featured_image ? `url(${post.featured_image})` : 'none',
                            backgroundColor: post.featured_image ? 'transparent' : 'hsl(var(--muted))'
                        }"
                    >
                        <div class="absolute inset-0" :style="{ backgroundColor: overlay_color }"></div>
                    </div>
                    
                    <!-- Content -->
                    <div class="relative z-10 h-full flex flex-col items-center justify-center text-center text-white px-6">
                        <h3 class="text-3xl md:text-5xl font-extrabold mb-4 drop-shadow-lg max-w-4xl">{{ post.title }}</h3>
                        <p v-if="show_excerpt && post.excerpt" class="text-lg md:text-xl opacity-90 max-w-2xl mb-8 drop-shadow-md line-clamp-2">
                            {{ post.excerpt }}
                        </p>
                        <a 
                            :href="`/post/${post.slug || post.id}`"
                            class="px-8 py-4 bg-primary text-primary-foreground font-bold rounded-full hover:opacity-90 transition-all shadow-xl"
                        >
                            Read More
                        </a>
                    </div>
                </div>
            </transition-group>
            
            <!-- Navigation Arrows -->
            <template v-if="show_arrows && posts.length > 1">
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
            <div v-if="show_dots && posts.length > 1" class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2">
                <button 
                    v-for="(post, index) in posts" 
                    :key="index"
                    @click="goToSlide(index)"
                    class="w-3 h-3 rounded-full transition-all"
                    :class="index === currentIndex ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/70'"
                ></button>
            </div>
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
</style>

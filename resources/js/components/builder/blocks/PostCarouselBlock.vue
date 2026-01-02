<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import api from '@/services/api';
import { Calendar, User, Tag, Loader2, ChevronLeft, ChevronRight } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    title: { type: String, default: '' },
    post_type: { type: String, default: 'post' },
    category: { type: String, default: '' },
    limit: { type: Number, default: 8 },
    show_image: { type: Boolean, default: true },
    show_excerpt: { type: Boolean, default: true },
    show_date: { type: Boolean, default: true },
    show_author: { type: Boolean, default: true },
    show_category: { type: Boolean, default: true },
    padding: { type: String, default: 'py-12' },
    bgColor: { type: String, default: '' }
});

const posts = ref([]);
const loading = ref(true);
const scrollContainer = ref(null);

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
        console.warn('PostCarousel: Failed to fetch posts', err);
        // Fallback demo data
        posts.value = Array.from({ length: props.limit }, (_, i) => ({
            id: i + 1,
            title: `Sample Article ${i + 1}`,
            excerpt: 'Preview of this amazing article for the slider.',
            featured_image: null,
            author: { name: 'Editor' },
            category: { name: 'News' },
            published_at: new Date().toISOString()
        }));
    } finally {
        loading.value = false;
    }
};

const scroll = (direction) => {
    if (!scrollContainer.value) return;
    const container = scrollContainer.value;
    const scrollAmount = container.clientWidth * 0.8; // Scroll 80% of width
    
    if (direction === 'left') {
        container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else {
        container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
};

onMounted(fetchPosts);
watch(() => [props.post_type, props.category, props.limit], fetchPosts);

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
};

const containerClasses = computed(() => {
    return ['transition-all duration-500', props.padding].filter(Boolean);
});
</script>

<template>
    <section 
        :class="containerClasses"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div class="container mx-auto px-6">
            <div class="flex items-center justify-between mb-8">
                <h2 v-if="title" class="text-2xl md:text-3xl font-bold tracking-tight">{{ title }}</h2>
                
                <!-- Navigation -->
                <div class="flex gap-2">
                    <button @click="scroll('left')" class="w-10 h-10 rounded-full border border-border flex items-center justify-center hover:bg-muted transition-colors">
                        <ChevronLeft class="w-5 h-5" />
                    </button>
                    <button @click="scroll('right')" class="w-10 h-10 rounded-full border border-border flex items-center justify-center hover:bg-muted transition-colors">
                        <ChevronRight class="w-5 h-5" />
                    </button>
                </div>
            </div>
            
            <!-- Loading -->
            <div v-if="loading" class="flex items-center justify-center py-20">
                <Loader2 class="w-8 h-8 animate-spin text-primary" />
            </div>
            
            <!-- Carousel -->
            <div 
                v-else
                ref="scrollContainer"
                class="flex gap-6 overflow-x-auto snap-x snap-mandatory pb-8 -mx-6 px-6 scrollbar-hide"
            >
                <article 
                    v-for="post in posts" 
                    :key="post.id"
                    class="snap-start shrink-0 w-[280px] md:w-[350px] group bg-card border rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300"
                >
                    <!-- Image -->
                    <div v-if="show_image" class="aspect-video overflow-hidden bg-muted relative">
                        <img 
                            v-if="post.featured_image" 
                            :src="post.featured_image" 
                            :alt="post.title"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                        />
                         <div v-else class="w-full h-full flex items-center justify-center text-muted-foreground bg-muted/50">
                            <span class="text-xs">No Image</span>
                        </div>
                        
                         <!-- Category Badge (Overlaid) -->
                        <div v-if="show_category && post.category" class="absolute top-3 left-3">
                            <span class="px-2 py-1 text-[10px] font-bold bg-background/90 backdrop-blur text-foreground rounded shadow-sm">
                                {{ post.category.name || post.category }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-5">
                        <h3 class="text-lg font-bold mb-2 line-clamp-2 leading-tight group-hover:text-primary transition-colors">
                            {{ post.title }}
                        </h3>
                        
                        <p v-if="show_excerpt" class="text-sm text-muted-foreground line-clamp-2 mb-4">
                            {{ post.excerpt }}
                        </p>
                        
                        <!-- Meta -->
                        <div class="flex items-center gap-4 text-xs text-muted-foreground mt-auto">
                            <span v-if="show_author && post.author" class="flex items-center gap-1.5">
                                <User class="w-3.5 h-3.5" />
                                {{ post.author.name || post.author }}
                            </span>
                            <span v-if="show_date" class="flex items-center gap-1.5">
                                <Calendar class="w-3.5 h-3.5" />
                                {{ formatDate(post.published_at) }}
                            </span>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

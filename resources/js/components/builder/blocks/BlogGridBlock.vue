<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import api from '@/services/api';
import { Calendar, User, Tag, Loader2 } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    title: { type: String, default: '' },
    post_type: { type: String, default: 'post' },
    category: { type: String, default: '' },
    tag: { type: String, default: '' },
    limit: { type: Number, default: 6 },
    layout: { type: String, default: 'grid' },
    columns: { type: String, default: '3' },
    show_image: { type: Boolean, default: true },
    show_excerpt: { type: Boolean, default: true },
    show_date: { type: Boolean, default: true },
    show_author: { type: Boolean, default: true },
    show_category: { type: Boolean, default: true },
    padding: { type: String, default: 'py-16' },
    bgColor: { type: String, default: '' }
});

const posts = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchPosts = async () => {
    loading.value = true;
    error.value = null;
    
    try {
        const params = {
            type: props.post_type,
            limit: props.limit,
            status: 'published'
        };
        
        if (props.category) params.category = props.category;
        if (props.tag) params.tag = props.tag;
        
        const response = await api.get('/cms/contents', { params });
        posts.value = response.data?.data || response.data || [];
    } catch (err) {
        console.warn('BlogGrid: Failed to fetch posts', err);
        error.value = 'Unable to load posts';
        // Fallback to demo data for builder preview
        posts.value = Array.from({ length: props.limit }, (_, i) => ({
            id: i + 1,
            title: `Sample Post ${i + 1}`,
            excerpt: 'This is a preview of how your blog posts will appear. Configure the settings to customize.',
            featured_image: null,
            author: { name: 'Author' },
            category: { name: 'Category' },
            published_at: new Date().toISOString()
        }));
    } finally {
        loading.value = false;
    }
};

onMounted(fetchPosts);

watch(() => [props.post_type, props.category, props.tag, props.limit], fetchPosts);

const gridClass = computed(() => {
    const cols = {
        '1': 'grid-cols-1',
        '2': 'grid-cols-1 md:grid-cols-2',
        '3': 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
        '4': 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4'
    };
    return cols[props.columns] || cols['3'];
});

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
            <h2 v-if="title" class="text-3xl md:text-4xl font-extrabold mb-12 tracking-tight text-center">{{ title }}</h2>
            
            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center py-20">
                <Loader2 class="w-8 h-8 animate-spin text-primary" />
            </div>
            
            <!-- Grid Layout -->
            <div v-else-if="layout === 'grid'" :class="['grid gap-8', gridClass]">
                <article 
                    v-for="post in posts" 
                    :key="post.id"
                    class="group bg-card border rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300"
                >
                    <!-- Featured Image -->
                    <div v-if="show_image" class="aspect-video overflow-hidden bg-muted">
                        <img 
                            v-if="post.featured_image" 
                            :src="post.featured_image" 
                            :alt="post.title"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-muted-foreground">
                            <span class="text-xs uppercase tracking-widest">No Image</span>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6">
                        <!-- Category Badge -->
                        <div v-if="show_category && post.category" class="mb-3">
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-widest text-primary">
                                <Tag class="w-3 h-3" />
                                {{ post.category.name || post.category }}
                            </span>
                        </div>
                        
                        <h3 class="text-lg font-bold mb-2 line-clamp-2 group-hover:text-primary transition-colors">
                            {{ post.title }}
                        </h3>
                        
                        <p v-if="show_excerpt" class="text-sm text-muted-foreground line-clamp-3 mb-4">
                            {{ post.excerpt }}
                        </p>
                        
                        <!-- Meta -->
                        <div class="flex items-center gap-4 text-xs text-muted-foreground">
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
            
            <!-- List Layout -->
            <div v-else-if="layout === 'list'" class="space-y-6">
                <article 
                    v-for="post in posts" 
                    :key="post.id" 
                    class="group flex flex-col md:flex-row gap-6 bg-card border rounded-2xl overflow-hidden p-4 hover:shadow-lg transition-all"
                >
                    <div v-if="show_image" class="w-full md:w-48 aspect-video md:aspect-square rounded-xl overflow-hidden bg-muted shrink-0">
                        <img 
                            v-if="post.featured_image" 
                            :src="post.featured_image" 
                            :alt="post.title"
                            class="w-full h-full object-cover"
                        />
                    </div>
                    <div class="flex-1 py-2">
                        <h3 class="text-xl font-bold mb-2 group-hover:text-primary transition-colors">{{ post.title }}</h3>
                        <p v-if="show_excerpt" class="text-muted-foreground line-clamp-2">{{ post.excerpt }}</p>
                    </div>
                </article>
            </div>
            
            <!-- Empty State -->
            <div v-if="!loading && posts.length === 0" class="text-center py-20 text-muted-foreground">
                <p>No posts found. Try adjusting your filters.</p>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import api from '@/services/api';
import { Calendar, User, Tag, Loader2 } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    title: { type: String, default: '' },
    postType: { type: String, default: 'post' },
    category: { type: String, default: '' },
    tag: { type: String, default: '' },
    itemsPerPage: { type: [Number, String], default: 6 },
    layout: { type: String, default: 'grid' },
    columns: { type: [Number, String], default: '3' },
    gap: { type: [Number, String], default: 24 },
    showImage: { type: Boolean, default: true },
    showExcerpt: { type: Boolean, default: true },
    showDate: { type: Boolean, default: true },
    showAuthor: { type: Boolean, default: true },
    showCategory: { type: Boolean, default: true },
    imageAspectRatio: { type: String, default: '16:9' },
    cardBackgroundColor: { type: String, default: '' },
    padding: { type: String, default: 'py-16' },
    bgColor: { type: String, default: '' },
    isPreview: { type: Boolean, default: false }
});

const posts = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchPosts = async () => {
    loading.value = true;
    error.value = null;
    
    try {
        const params = {
            type: props.postType,
            limit: parseInt(props.itemsPerPage) || 6,
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
        posts.value = Array.from({ length: parseInt(props.itemsPerPage) || 6 }, (_, i) => ({
            id: i + 1,
            title: `Sample Post ${i + 1}`,
            slug: `sample-post-${i + 1}`,
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
watch(() => [props.postType, props.category, props.tag, props.itemsPerPage], fetchPosts);

const gridStyles = computed(() => {
    const cols = parseInt(props.columns) || 3;
    const gapVal = parseInt(props.gap) || 24;
    
    if (props.layout === 'list') {
        return { display: 'flex', flexDirection: 'column', gap: `${gapVal}px` };
    }
    
    return {
        display: 'grid',
        gridTemplateColumns: `repeat(${cols}, 1fr)`,
        gap: `${gapVal}px`
    };
});

const imageStyles = computed(() => {
    if (props.imageAspectRatio === 'custom') return {};
    const ratio = { '16:9': '56.25%', '4:3': '75%', '1:1': '100%' }[props.imageAspectRatio] || '56.25%';
    return { paddingBottom: ratio, position: 'relative' };
});

const cardStyles = computed(() => ({
    backgroundColor: props.cardBackgroundColor || 'transparent'
}));

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
};
</script>

<template>
    <div class="blog-block">
        <h2 v-if="title" class="blog-title">{{ title }}</h2>
        
        <!-- Loading State -->
        <div v-if="loading" class="blog-loading">
            <Loader2 class="loading-icon" />
        </div>
        
        <!-- Grid/List Layout -->
        <div v-else class="blog-grid" :style="gridStyles">
            <article 
                v-for="post in posts" 
                :key="post.id"
                class="post-card"
                :class="[`post-card--${layout}`]"
                :style="cardStyles"
            >
                <!-- Featured Image -->
                <div v-if="showImage" class="post-media" :style="imageStyles">
                    <router-link 
                        :to="isPreview ? '#' : (post.slug ? `/blog/${post.slug}` : '#')"
                        @click="isPreview && $event.preventDefault()"
                        class="media-link"
                    >
                        <img 
                            v-if="post.featured_image" 
                            :src="post.featured_image" 
                            :alt="post.title"
                            class="post-img"
                        />
                        <div v-else class="post-placeholder">
                            <span>No Image</span>
                        </div>
                    </router-link>
                </div>
                
                <!-- Content -->
                <div class="post-content">
                    <!-- Category Badge -->
                    <div v-if="showCategory && post.category" class="post-category-wrapper">
                        <span class="post-category">
                            <Tag :size="10" />
                            {{ post.category.name || post.category }}
                        </span>
                    </div>
                    
                    <router-link 
                        :to="isPreview ? '#' : (post.slug ? `/blog/${post.slug}` : '#')"
                        @click="isPreview && $event.preventDefault()"
                        class="post-title-link"
                    >
                        <h3 class="post-title">{{ post.title }}</h3>
                    </router-link>
                    
                    <p v-if="showExcerpt" class="post-excerpt">
                        {{ post.excerpt }}
                    </p>
                    
                    <!-- Meta -->
                    <div class="post-meta">
                        <div class="meta-items">
                            <span v-if="showAuthor && post.author" class="meta-item">
                                <User :size="12" />
                                {{ post.author.name || post.author }}
                            </span>
                            <span v-if="showDate" class="meta-item">
                                <Calendar :size="12" />
                                {{ formatDate(post.published_at) }}
                            </span>
                        </div>
                        
                        <router-link 
                            v-if="post.slug && !isPreview"
                            :to="`/blog/${post.slug}`"
                            class="read-more"
                        >
                            Read More →
                        </router-link>
                    </div>
                </div>
            </article>
        </div>
        
        <!-- Empty State -->
        <div v-if="!loading && posts.length === 0" class="blog-empty">
            <p>No posts found. Try adjusting your filters.</p>
        </div>
    </div>
</template>

<style scoped>
.blog-block { width: 100%; }
.blog-title { font-size: 2.5rem; font-weight: 800; text-align: center; margin-bottom: 3rem; }

.blog-loading { display: flex; align-items: center; justify-content: center; padding: 4rem 0; }
.loading-icon { width: 2.5rem; height: 2.5rem; animation: spin 1s linear infinite; color: #3b82f6; }

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.blog-grid { width: 100%; }

.post-card { 
    display: flex; 
    flex-direction: column; 
    background: #ffffff; 
    border-radius: 16px; 
    overflow: hidden; 
    border: 1px solid #f1f5f9; 
    transition: all 0.3s ease;
}
.post-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }

.post-card--list { flex-direction: row; align-items: stretch; }

.post-media { width: 100%; overflow: hidden; background: #f8fafc; flex-shrink: 0; }
.post-card--list .post-media { width: 240px; }

.media-link { display: block; width: 100%; height: 100%; position: absolute; inset: 0; }
.post-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
.post-card:hover .post-img { transform: scale(1.05); }

.post-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 0.875rem; }

.post-content { padding: 1.5rem; flex: 1; display: flex; flex-direction: column; }
.post-category-wrapper { margin-bottom: 0.75rem; }
.post-category { display: inline-flex; align-items: center; gap: 4px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #3b82f6; letter-spacing: 0.025em; }

.post-title-link { color: inherit; text-decoration: none; display: block; margin-bottom: 0.75rem; }
.post-title { font-size: 1.25rem; font-weight: 700; line-height: 1.4; transition: color 0.2s; }
.post-title-link:hover .post-title { color: #3b82f6; }

.post-excerpt { font-size: 0.875rem; color: #64748b; line-height: 1.6; margin-bottom: 1.5rem; flex: 1; display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
 overflow: hidden; }

.post-meta { display: flex; align-items: center; justify-content: space-between; padding-top: 1rem; border-top: 1px solid #f1f5f9; margin-top: auto; }
.meta-items { display: flex; gap: 1rem; }
.meta-item { display: flex; align-items: center; gap: 4px; font-size: 0.75rem; color: #94a3b8; }

.read-more { font-size: 0.75rem; font-weight: 700; color: #3b82f6; text-decoration: none; }
.read-more:hover { text-decoration: underline; }

.blog-empty { text-align: center; padding: 4rem 0; color: #64748b; }

@media (max-width: 768px) {
    .post-card--list { flex-direction: column; }
    .post-card--list .post-media { width: 100%; }
}
</style>

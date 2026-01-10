<template>
  <div class="min-h-screen flex flex-col">
    <!-- Loading State -->
    <div v-if="loading" class="flex-1 flex items-center justify-center min-h-[60vh]">
        <div class="flex flex-col items-center gap-4">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div>
            <span class="text-muted-foreground text-sm">Loading...</span>
        </div>
    </div>
    
    <template v-else>
        <BlockRenderer 
            v-if="pageData && pageData.blocks && pageData.blocks.length > 0" 
            :blocks="pageData.blocks"
            :context="{ page: pageData }"
            :is-preview="true"
        />
        
        <!-- Premium Blog Fallback -->
        <div v-else class="flex-1 flex flex-col bg-background">
             <!-- Blog Header -->
             <section class="py-20 bg-gradient-to-b from-primary/10 to-background dark:from-primary/20">
                 <div class="container mx-auto px-4 text-center">
                     <span class="text-primary font-bold tracking-wider uppercase text-xs mb-4 block">Blog JA-CMS</span>
                     <h1 class="text-4xl md:text-5xl font-bold mb-4 text-foreground">The Journal</h1>
                     <p class="text-muted-foreground max-w-lg mx-auto">Insights, tutorials, dan update terbaru dari tim JA-CMS.</p>
                 </div>
             </section>

             <!-- Blog Grid -->
             <section class="flex-1 py-16 bg-background">
                 <div class="container mx-auto px-4">
                     <!-- Featured Post -->
                     <div v-if="featuredPost" class="mb-12">
                         <h2 class="text-sm font-bold text-muted-foreground uppercase tracking-wider mb-6">Featured Article</h2>
                         <router-link :to="featuredPost.slug ? `/blog/${featuredPost.slug}` : '#'" class="block bg-card rounded-2xl overflow-hidden shadow-lg border border-border group hover:shadow-xl transition-all cursor-pointer">
                             <div class="grid grid-cols-1 lg:grid-cols-2">
                                 <div class="aspect-video lg:aspect-[4/3] overflow-hidden">
                                     <img 
                                         :src="featuredPost.image" 
                                         :alt="featuredPost.title"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                     >
                                 </div>
                                 <div class="p-8 lg:p-10 flex flex-col justify-center">
                                     <div class="flex items-center gap-3 mb-4">
                                         <span class="px-3 py-1 bg-primary text-primary-foreground text-xs font-bold rounded-full">{{ featuredPost.category }}</span>
                                         <span class="text-muted-foreground text-sm">{{ formatDate(featuredPost.published_at) }}</span>
                                     </div>
                                     <h3 class="text-2xl lg:text-3xl font-bold mb-4 text-foreground group-hover:text-primary transition-colors">
                                         {{ featuredPost.title }}
                                     </h3>
                                     <p class="text-muted-foreground mb-6 line-clamp-3">
                                         {{ featuredPost.excerpt }}
                                     </p>
                                     <div class="flex items-center gap-3">
                                         <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center font-bold text-primary text-sm">JA</div>
                                         <div>
                                             <p class="font-semibold text-foreground">JA-CMS Team</p>
                                             <p class="text-xs text-muted-foreground">{{ featuredPost.readTime }}</p>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </router-link>
                     </div>

                     <!-- Latest Articles -->
                     <div>
                         <h2 class="text-sm font-bold text-muted-foreground uppercase tracking-wider mb-6">Latest Articles</h2>
                         <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                             <router-link 
                                v-for="article in displayArticles" 
                                :key="article.id" 
                                :to="article.slug ? `/blog/${article.slug}` : '#'"
                                class="block bg-card rounded-xl overflow-hidden border border-border hover:shadow-lg transition-all group cursor-pointer"
                             >
                                 <article class="h-full flex flex-col">
                                     <div class="aspect-video overflow-hidden">
                                         <img 
                                             :src="article.image" 
                                             :alt="article.title"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                         >
                                     </div>
                                     <div class="p-5 flex-1 flex flex-col">
                                         <div class="flex items-center gap-2 text-xs mb-3">
                                             <span class="text-primary font-bold">{{ article.category }}</span>
                                             <span class="text-muted-foreground">•</span>
                                             <span class="text-muted-foreground">{{ article.readTime }}</span>
                                         </div>
                                         <h3 class="text-lg font-bold text-foreground mb-2 group-hover:text-primary transition-colors line-clamp-2">
                                             {{ article.title }}
                                         </h3>
                                         <p class="text-sm text-muted-foreground line-clamp-2 mb-4 flex-1">{{ article.excerpt }}</p>
                                         <div class="mt-auto pt-2 text-xs font-bold text-primary flex items-center gap-1">
                                            Read More 
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                         </div>
                                     </div>
                                 </article>
                             </router-link>
                         </div>
                     </div>
                 </div>
             </section>
        </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api'
import BlockRenderer from '@/components/builder/blocks/BlockRenderer.vue'

const pageData = ref(null)
const articles = ref([])
const loading = ref(true)

const featuredPost = computed(() => {
    return articles.value.length > 0 ? articles.value[0] : null
})

const displayArticles = computed(() => {
    return articles.value.slice(1)
})

const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    })
}

const fetchPosts = async () => {
    try {
        const response = await api.get('/cms/contents', {
            params: {
                type: 'post',
                status: 'published',
                sort: '-published_at',
                per_page: 9
            }
        });
        
        console.log('Blog fetch response:', response);
        
        let posts = [];
        
        // Defensive handling of response structure
        if (response.data && Array.isArray(response.data.data)) {
            // Standard wrapper { data: [...] }
            posts = response.data.data;
        } else if (response.data && Array.isArray(response.data)) {
            // Direct array [...]
            posts = response.data;
        } else if (response.data && response.data.data && Array.isArray(response.data.data.data)) {
             // Laravel paginate wrapper sometimes { data: { data: [...] } }
             posts = response.data.data.data;
        } else {
            console.warn('Unexpected API response structure:', response.data);
            posts = [];
        }
        
        if (!Array.isArray(posts)) {
            console.error('Posts is not an array:', posts);
            posts = [];
        }
        
        articles.value = posts.map(post => ({
            id: post.id,
            title: post.title,
            slug: post.slug,
            excerpt: post.excerpt,
            category: post.category?.name || 'Uncategorized',
            readTime: '5 min', 
            image: post.featured_image || '/images/fallback/blog-1.png',
            published_at: post.published_at
        }));
    } catch (err) {
        console.error('Failed to fetch posts:', err);
        // Fallback or empty state
        articles.value = [];
    }
}

onMounted(async () => {
  try {
    // Try to get the "blog" CMS page first
    const response = await api.get('/cms/contents/blog')
    pageData.value = response.data.data
  } catch (error) {
    if (error.response?.status !== 404) {
      console.error('Failed to fetch blog page:', error)
    }
  } finally {
    // If no builder content found, fetch default blog posts
    if (!pageData.value || !pageData.value.blocks || pageData.value.blocks.length === 0) {
        await fetchPosts()
    }
    loading.value = false
  }
})
</script>

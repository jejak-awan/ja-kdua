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
                     <div class="mb-12">
                         <h2 class="text-sm font-bold text-muted-foreground uppercase tracking-wider mb-6">Featured Article</h2>
                         <div class="bg-card rounded-2xl overflow-hidden shadow-lg border border-border group hover:shadow-xl transition-all cursor-pointer">
                             <div class="grid grid-cols-1 lg:grid-cols-2">
                                 <div class="aspect-video lg:aspect-[4/3] overflow-hidden">
                                     <img 
                                         :src="'/images/fallback/blog-featured.png'" 
                                         alt="Featured Article"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                     >
                                 </div>
                                 <div class="p-8 lg:p-10 flex flex-col justify-center">
                                     <div class="flex items-center gap-3 mb-4">
                                         <span class="px-3 py-1 bg-primary text-primary-foreground text-xs font-bold rounded-full">TEKNOLOGI</span>
                                         <span class="text-muted-foreground text-sm">Hari ini</span>
                                     </div>
                                     <h3 class="text-2xl lg:text-3xl font-bold mb-4 text-foreground group-hover:text-primary transition-colors">
                                         The Future of Content Management
                                     </h3>
                                     <p class="text-muted-foreground mb-6 line-clamp-3">
                                         Explore how modern CMS architectures are reshaping the way we build digital experiences. Headless, hybrid, and AI-driven solutions are leading the charge.
                                     </p>
                                     <div class="flex items-center gap-3">
                                         <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center font-bold text-primary text-sm">JA</div>
                                         <div>
                                             <p class="font-semibold text-foreground">JA-CMS Team</p>
                                             <p class="text-xs text-muted-foreground">5 min read</p>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <!-- Latest Articles -->
                     <div>
                         <h2 class="text-sm font-bold text-muted-foreground uppercase tracking-wider mb-6">Latest Articles</h2>
                         <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                             <article v-for="article in articles" :key="article.id" class="bg-card rounded-xl overflow-hidden border border-border hover:shadow-lg transition-all group cursor-pointer">
                                 <div class="aspect-video overflow-hidden">
                                     <img 
                                         :src="article.image" 
                                         :alt="article.title"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     >
                                 </div>
                                 <div class="p-5">
                                     <div class="flex items-center gap-2 text-xs mb-3">
                                         <span class="text-primary font-bold">{{ article.category }}</span>
                                         <span class="text-muted-foreground">•</span>
                                         <span class="text-muted-foreground">{{ article.readTime }}</span>
                                     </div>
                                     <h3 class="text-lg font-bold text-foreground mb-2 group-hover:text-primary transition-colors line-clamp-2">
                                         {{ article.title }}
                                     </h3>
                                     <p class="text-sm text-muted-foreground line-clamp-2">{{ article.excerpt }}</p>
                                 </div>
                             </article>
                         </div>
                     </div>
                 </div>
             </section>
        </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import BlockRenderer from '@/components/builder/blocks/BlockRenderer.vue'

const pageData = ref(null)
const loading = ref(true)

// Mock articles for fallback
const articles = [
    { id: 1, title: 'Getting Started with JA-CMS', category: 'TUTORIAL', readTime: '5 min', excerpt: 'Learn how to set up and configure JA-CMS for your next project.', image: '/images/fallback/blog-1.png' },
    { id: 2, title: 'Building Custom Themes', category: 'DEVELOPMENT', readTime: '8 min', excerpt: 'Create beautiful themes with the Janari theme system.', image: '/images/fallback/blog-2.png' },
    { id: 3, title: 'Page Builder Deep Dive', category: 'FEATURES', readTime: '6 min', excerpt: 'Master the drag-and-drop page builder for stunning pages.', image: '/images/fallback/blog-1.png' },
    { id: 4, title: 'SEO Best Practices', category: 'TIPS', readTime: '4 min', excerpt: 'Optimize your content for search engines with built-in SEO tools.', image: '/images/fallback/blog-2.png' },
    { id: 5, title: 'User Roles & Permissions', category: 'SECURITY', readTime: '7 min', excerpt: 'Configure granular access control for your team members.', image: '/images/fallback/blog-1.png' },
    { id: 6, title: 'API Integration Guide', category: 'DEVELOPMENT', readTime: '10 min', excerpt: 'Connect JA-CMS with external services using the REST API.', image: '/images/fallback/blog-2.png' }
]

onMounted(async () => {
  try {
    const response = await api.get('/cms/contents/blog')
    pageData.value = response.data.data
  } catch (error) {
    // Gracefully handle - fallback will render
    if (error.response?.status !== 404) {
      console.error('Failed to fetch blog page:', error)
    }
  } finally {
    loading.value = false
  }
})
</script>

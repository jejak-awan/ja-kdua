<template>
  <div class="min-h-screen flex flex-col">
    <!-- Hero Section -->
    <Hero 
      v-bind="{ settings, stats, isAuthenticated }"
    />
    
    <!-- Featured Content -->
    <section v-if="featuredPosts.length > 0" class="py-20 bg-background">
      <div class="container mx-auto px-4">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-foreground mb-4">Featured Stories</h2>
          <div class="w-20 h-1 bg-primary mx-auto rounded-full"></div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <PostCard 
            v-for="post in featuredPosts" 
            :key="post.id" 
            :post="post"
          />
        </div>
      </div>
    </section>
    
    <!-- Recent Posts -->
    <section v-if="recentPosts.length > 0" class="py-20 bg-muted">
      <div class="container mx-auto px-4">
        <div class="flex justify-between items-end mb-12">
          <div>
            <h2 class="text-3xl font-bold text-foreground mb-2">Latest Updates</h2>
            <p class="text-muted-foreground">Stay up to date with our newest articles</p>
          </div>
          <router-link to="/blog" class="hidden md:flex items-center gap-2 text-primary font-medium hover:text-primary/80 transition-colors">
            View All Posts
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
          </router-link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <PostCard 
            v-for="post in recentPosts" 
            :key="post.id" 
            :post="post"
          />
        </div>
        
        <div class="mt-12 text-center md:hidden">
          <router-link to="/blog" class="inline-flex items-center px-6 py-3 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors font-medium">
            View All Posts
          </router-link>
        </div>
      </div>
    </section>
    
    <!-- Newsletter Section -->
    <section class="py-20 bg-background border-t border-border">
      <div class="container mx-auto px-4">
        <NewsletterWidget
          variant="default"
          :title="t('features.frontend.newsletter.title')"
          :description="t('features.frontend.newsletter.description')"
          :button-text="t('features.frontend.newsletter.button')"
        />
      </div>
    </section>
    
    <!-- CTA Section -->
    <section class="py-24 bg-indigo-900 relative overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-10">
          <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
              <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white"/>
          </svg>
      </div>
      
      <div class="container mx-auto px-4 relative z-10 text-center">
        <h2 class="text-4xl md:text-5xl font-bold text-white mb-6 tracking-tight">
            Ready to Start Building?
        </h2>
        <p class="text-indigo-200 text-xl max-w-2xl mx-auto mb-10">
            Join thousands of developers and creators using JA-CMS to build modern web experiences.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <router-link to="/register" class="px-8 py-4 bg-white text-indigo-900 rounded-full font-bold text-lg hover:bg-indigo-50 transition-all shadow-xl hover:shadow-2xl transform hover:-translate-y-1">
                Get Started Free
            </router-link>
            <router-link to="/contact" class="px-8 py-4 bg-transparent border-2 border-white text-white rounded-full font-bold text-lg hover:bg-white hover:text-indigo-900 transition-all">
                Contact Sales
            </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useTheme } from '@/composables/useTheme'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import PostCard from './components/PostCard.vue'
import Hero from './components/Hero.vue'
import NewsletterWidget from '@/components/NewsletterWidget.vue'

const authStore = useAuthStore()
const { t } = useI18n()
const { themeSettings } = useTheme()

const featuredPosts = ref([])
const recentPosts = ref([])
const stats = ref({
  contents: 0,
  visitors: 0,
  categories: 0
})

const settings = computed(() => themeSettings.value || {})
const isAuthenticated = computed(() => authStore.isAuthenticated)

onMounted(async () => {
  await Promise.allSettled([
    fetchFeaturedPosts(),
    fetchRecentPosts(),
    fetchStats()
  ])
})

const fetchFeaturedPosts = async () => {
  try {
    const response = await api.get('/cms/contents', {
      params: {
        is_featured: true,
        status: 'published',
        type: 'post',
        limit: 3
      }
    })
    featuredPosts.value = response.data.data || []
  } catch (error) {
    console.error('Falied to fetch featured:', error)
  }
}

const fetchRecentPosts = async () => {
  try {
    const response = await api.get('/cms/contents', {
      params: {
        status: 'published',
        type: 'post',
        sort: '-published_at',
        limit: 6
      }
    })
    recentPosts.value = response.data.data || []
  } catch (error) {
    console.error('Failed to fetch recent:', error)
  }
}

const fetchStats = async () => {
  try {
    const [contentsRes, categoriesRes] = await Promise.all([
      api.get('/cms/contents', { params: { status: 'published', type: 'post', per_page: 1 } }),
      api.get('/cms/categories')
    ])
    
    let visitorsCount = 3789
    try {
      const visitorsRes = await api.get('/admin/cms/analytics/visitors/count')
      visitorsCount = visitorsRes.data.count || visitorsCount
    } catch (e) {}
    
    stats.value = {
      contents: contentsRes.data.meta?.total || contentsRes.data.data?.length || 0,
      visitors: visitorsCount,
      categories: categoriesRes.data.data?.length || 0
    }
  } catch (error) {
    console.error('Failed stats:', error)
  }
}
</script>

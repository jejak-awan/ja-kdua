<template>
  <div class="janari-home bg-white min-h-screen">
    <!-- Hero Section -->
    <Hero 
      :title="settings.site_title" 
      :subtitle="settings.site_tagline"
      :image="settings.hero_image"
    />
    
    <div class="container mx-auto px-4 py-12">
      <h2 class="text-3xl font-bold text-center mb-8">Latest Stories</h2>
      
      <!-- Posts Grid -->
      <div v-if="recentPosts.length" class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <PostCard 
          v-for="post in recentPosts" 
          :key="post.id" 
          :post="post" 
        />
      </div>
      <div v-else class="text-center text-gray-500">
        No posts found.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useTheme } from '@/composables/useTheme'
import api from '@/services/api'

// Import local theme components
import Hero from './components/Hero.vue'
import PostCard from './components/PostCard.vue'

const { themeSettings } = useTheme()
const settings = computed(() => themeSettings.value || {})
const recentPosts = ref([])

onMounted(async () => {
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
    console.error('Failed to fetch posts:', error)
  }
})
</script>

<style scoped>
/* Scoped styles */
</style>

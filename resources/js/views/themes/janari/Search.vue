<template>
  <div class="py-12 bg-background min-h-screen">
    <div class="container mx-auto px-4">
      <h1 class="text-4xl font-bold text-center mb-4 text-foreground">{{ t('features.frontend.search.title') }}</h1>
      <p class="text-center text-lg text-muted-foreground mb-12">
        {{ t('features.frontend.search.query') }} <strong class="text-foreground">{{ searchQuery }}</strong>
      </p>
      
      <div v-if="loading" class="text-center py-16 text-lg text-muted-foreground">{{ t('features.frontend.search.loading') }}</div>
      <div v-else-if="results.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <PostCard 
          v-for="post in results" 
          :key="post.id" 
          :post="post"
        />
      </div>
      <div v-else class="text-center py-16 text-lg text-muted-foreground">
        <p>{{ t('features.frontend.search.empty', { query: searchQuery }) }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import PostCard from './components/PostCard.vue'
import { useAnalytics } from '@/composables/useAnalytics'

const { t } = useI18n()
const { trackSearch } = useAnalytics()

const route = useRoute()
const searchQuery = ref('')
const results = ref([])
const loading = ref(false)

const search = async () => {
  if (!searchQuery.value) return
  
  loading.value = true
  try {
    const response = await api.get('/cms/search', {
      params: { q: searchQuery.value }
    })
    results.value = response.data.data || []
    
    // Track search analytics
    trackSearch(searchQuery.value, results.value.length)
  } catch (error) {
    console.error('Search failed:', error)
  } finally {
    loading.value = false
  }
}

watch(() => route.query.q, (newQuery) => {
  searchQuery.value = newQuery || ''
  if (newQuery) search()
})

onMounted(() => {
  searchQuery.value = route.query.q || ''
  if (searchQuery.value) search()
})
</script>



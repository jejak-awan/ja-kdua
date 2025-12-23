<template>
  <div class="search-page">
    <div class="container">
      <h1 class="page-title">{{ t('features.frontend.search.title') }}</h1>
      <p class="search-query">{{ t('features.frontend.search.query') }} <strong>{{ searchQuery }}</strong></p>
      
      <div v-if="loading" class="loading">{{ t('features.frontend.search.loading') }}</div>
      <div v-else-if="results.length > 0" class="posts-grid">
        <PostCard 
          v-for="post in results" 
          :key="post.id" 
          :post="post"
        />
      </div>
      <div v-else class="no-results">
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
import PostCard from '@/components/theme/PostCard.vue'

const { t } = useI18n()

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

<style scoped>
.search-page {
  padding: 3rem 0;
}

.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1rem;
}

.page-title {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 1rem;
  text-align: center;
}

.search-query {
  text-align: center;
  font-size: 1.125rem;
  color: #6b7280;
  margin-bottom: 3rem;
}

.loading,
.no-results {
  text-align: center;
  padding: 4rem 0;
  font-size: 1.125rem;
  color: #6b7280;
}

.posts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
}

@media (max-width: 768px) {
  .posts-grid {
    grid-template-columns: 1fr;
  }
}
</style>

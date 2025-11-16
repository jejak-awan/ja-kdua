<template>
  <div class="blog-page">
    <div class="container">
      <div class="blog-header">
        <h1 class="page-title">Blog</h1>
        <p class="page-subtitle">Latest articles and updates from our team</p>
      </div>
      
      <!-- Posts Grid -->
      <div v-if="loading" class="loading">Loading...</div>
      <div v-else-if="posts.length > 0" class="posts-grid">
        <PostCard 
          v-for="post in posts" 
          :key="post.id" 
          :post="post"
        />
      </div>
      <div v-else class="no-posts">
        <p>No posts available yet.</p>
      </div>
      
      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination">
        <button 
          @click="goToPage(currentPage - 1)" 
          :disabled="currentPage === 1"
          class="btn btn-outline"
        >
          Previous
        </button>
        <span class="page-info">Page {{ currentPage }} of {{ totalPages }}</span>
        <button 
          @click="goToPage(currentPage + 1)" 
          :disabled="currentPage === totalPages"
          class="btn btn-outline"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import PostCard from '@/components/theme/PostCard.vue'

const route = useRoute()
const router = useRouter()

const posts = ref([])
const loading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)
const perPage = ref(9)

const fetchPosts = async () => {
  loading.value = true
  try {
    const response = await api.get('/cms/contents', {
      params: {
        status: 'published',
        type: 'post',
        page: currentPage.value,
        per_page: perPage.value,
        sort: '-published_at'
      }
    })
    posts.value = response.data.data || []
    totalPages.value = response.data.meta?.last_page || 1
  } catch (error) {
    console.error('Failed to fetch posts:', error)
  } finally {
    loading.value = false
  }
}

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    router.push({ query: { ...route.query, page } })
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

watch(() => route.query.page, (newPage) => {
  currentPage.value = parseInt(newPage) || 1
  fetchPosts()
})

onMounted(() => {
  currentPage.value = parseInt(route.query.page) || 1
  fetchPosts()
})
</script>

<style scoped>
.blog-page {
  padding: 3rem 0;
}

.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1rem;
}

.blog-header {
  text-align: center;
  margin-bottom: 3rem;
}

.page-title {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.page-subtitle {
  font-size: 1.25rem;
  color: #6b7280;
}

.loading,
.no-posts {
  text-align: center;
  padding: 4rem 0;
  font-size: 1.125rem;
  color: #6b7280;
}

.posts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 2rem;
  padding: 2rem 0;
}

.page-info {
  font-weight: 600;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-outline {
  background: transparent;
  border: 2px solid var(--theme-primary-color, #2563eb);
  color: var(--theme-primary-color, #2563eb);
}

.btn-outline:hover:not(:disabled) {
  background-color: var(--theme-primary-color, #2563eb);
  color: white;
}

.btn-outline:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .posts-grid {
    grid-template-columns: 1fr;
  }
}
</style>

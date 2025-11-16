<template>
  <div class="post-page">
    <div v-if="loading" class="container loading">Loading...</div>
    <div v-else-if="post" class="container">
      <!-- Post Header -->
      <article class="post-content">
        <div class="post-header">
          <div v-if="post.category" class="post-category">
            {{ post.category.name }}
          </div>
          <h1 class="post-title">{{ post.title }}</h1>
          
          <!-- Meta -->
          <div class="post-meta">
            <span v-if="post.author" class="meta-item">
              <i class="bi bi-person"></i>
              {{ post.author.name }}
            </span>
            <span v-if="post.published_at" class="meta-item">
              <i class="bi bi-calendar"></i>
              {{ formatDate(post.published_at) }}
            </span>
            <span v-if="post.views" class="meta-item">
              <i class="bi bi-eye"></i>
              {{ post.views }} views
            </span>
          </div>
        </div>
        
        <!-- Featured Image -->
        <img v-if="post.featured_image" :src="post.featured_image" :alt="post.title" class="post-image">
        
        <!-- Content -->
        <div class="post-body" v-html="post.content"></div>
      </article>
    </div>
    <div v-else class="container no-post">
      <p>Post not found</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'

const route = useRoute()
const post = ref(null)
const loading = ref(true)

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

onMounted(async () => {
  try {
    const response = await api.get(`/cms/contents/${route.params.slug}`)
    post.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch post:', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.post-page {
  padding: 3rem 0;
}

.container {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 1rem;
}

.loading,
.no-post {
  text-align: center;
  padding: 4rem 0;
  font-size: 1.125rem;
  color: #6b7280;
}

.post-content {
  background: white;
  border-radius: 0.75rem;
  padding: 3rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.post-header {
  margin-bottom: 2rem;
}

.post-category {
  display: inline-block;
  background-color: var(--theme-primary-color, #2563eb);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.post-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  line-height: 1.2;
}

.post-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  color: #6b7280;
  font-size: 0.875rem;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.post-image {
  width: 100%;
  height: auto;
  border-radius: 0.75rem;
  margin-bottom: 2rem;
}

.post-body {
  font-size: 1.125rem;
  line-height: 1.8;
  color: #374151;
}

.post-body :deep(h2),
.post-body :deep(h3),
.post-body :deep(h4) {
  margin-top: 2rem;
  margin-bottom: 1rem;
  font-weight: 700;
}

.post-body :deep(p) {
  margin-bottom: 1rem;
}

.post-body :deep(a) {
  color: var(--theme-primary-color, #2563eb);
  text-decoration: underline;
}

.post-body :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 0.5rem;
  margin: 1.5rem 0;
}

@media (max-width: 768px) {
  .post-content {
    padding: 1.5rem;
  }
  
  .post-title {
    font-size: 1.875rem;
  }
}
</style>

<template>
  <div class="post-page">
    <div v-if="loading" class="container loading">{{ t('features.frontend.post.loading') }}</div>
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
              {{ post.views }} {{ t('features.frontend.post.views') }}
            </span>
          </div>
        </div>
        
        <!-- Featured Image -->
        <img v-if="post.featured_image" :src="post.featured_image" :alt="post.title" class="post-image">
        
        <!-- Content -->
        <div class="post-body" v-html="post.content"></div>
        
        <!-- Tags -->
        <div v-if="post.tags && post.tags.length > 0" class="post-tags">
          <span class="tags-label">{{ t('features.frontend.post.tags') }}</span>
          <span v-for="tag in post.tags" :key="tag.id" class="tag">
            {{ tag.name }}
          </span>
        </div>
        
        <!-- Social Share -->
        <div class="post-share-section">
          <SocialShare
            :url="postUrl"
            :title="post.title"
            :description="post.excerpt || post.meta_description"
            :hashtags="postHashtags"
            @shared="trackShare"
          />
        </div>
      </article>
      
      <!-- Related Posts -->
      <RelatedPosts
        v-if="post"
        :slug="post.slug"
        :limit="6"
        class="mt-8"
      />
    </div>
    <div v-else class="container no-post">
      <p>{{ t('features.frontend.post.notFound') }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import SocialShare from '@/components/SocialShare.vue'
import RelatedPosts from '@/components/RelatedPosts.vue'

const { t, locale } = useI18n()

const route = useRoute()
const post = ref(null)
const loading = ref(true)

// Computed properties for social share
const postUrl = computed(() => window.location.href)
const postHashtags = computed(() => {
  if (post.value?.tags) {
    return post.value.tags.map(tag => tag.name.replace(/\s+/g, ''))
  }
  return []
})

const formatDate = (date) => {
  return new Date(date).toLocaleDateString(locale.value, {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const trackShare = async (data) => {
  // Track share event (optional analytics)
  console.log('Shared on:', data.platform, data.url)
  
  // You can send this to your analytics endpoint
  try {
    await api.post('/analytics/share', {
      content_id: post.value?.id,
      platform: data.platform,
      url: data.url,
    })
  } catch (error) {
    console.error('Failed to track share:', error)
  }
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

.post-tags {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid #e5e7eb;
}

.tags-label {
  font-weight: 600;
  color: #6b7280;
  font-size: 0.875rem;
}

.tag {
  display: inline-block;
  background-color: #f3f4f6;
  color: #374151;
  padding: 0.375rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  transition: background-color 0.2s;
  cursor: pointer;
}

.tag:hover {
  background-color: #e5e7eb;
}

.post-share-section {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
  .post-content {
    padding: 1.5rem;
  }
  
  .post-title {
    font-size: 1.875rem;
  }
  
  .post-tags {
    gap: 0.5rem;
  }
}
</style>

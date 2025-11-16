<template>
  <article class="post-card" @click="navigateToPost">
    <!-- Featured Image -->
    <div v-if="post.featured_image" class="post-image">
      <img v-lazy="post.featured_image" :alt="post.title" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='200'%3E%3Crect width='400' height='200' fill='%23f3f4f6'/%3E%3C/svg%3E">
      <div v-if="post.category" class="post-category">
        {{ post.category.name }}
      </div>
    </div>
    
    <!-- Content -->
    <div class="post-content">
      <h3 class="post-title">{{ post.title }}</h3>
      <p class="post-excerpt">{{ excerpt }}</p>
      
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
          {{ post.views }}
        </span>
      </div>
      
      <!-- Read More -->
      <router-link :to="`/blog/${post.slug}`" class="read-more">
        Read More <i class="bi bi-arrow-right"></i>
      </router-link>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  post: {
    type: Object,
    required: true
  }
})

const router = useRouter()

const excerpt = computed(() => {
  if (props.post.excerpt) {
    return props.post.excerpt
  }
  if (props.post.content) {
    return props.post.content.substring(0, 150) + '...'
  }
  return ''
})

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const navigateToPost = () => {
  router.push(`/blog/${props.post.slug}`)
}
</script>

<style scoped>
.post-card {
  background: white;
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  cursor: pointer;
  display: flex;
  flex-direction: column;
}

.post-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}

.post-image {
  position: relative;
  width: 100%;
  height: 200px;
  overflow: hidden;
}

.post-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.post-image img.lazy-loading {
  filter: blur(5px);
  opacity: 0.6;
}

.post-image img.lazy-loaded {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.post-card:hover .post-image img {
  transform: scale(1.05);
}

.post-category {
  position: absolute;
  top: 1rem;
  left: 1rem;
  background-color: var(--theme-primary-color, #2563eb);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 0.25rem;
  font-size: 0.875rem;
  font-weight: 600;
}

.post-content {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  flex: 1;
}

.post-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
  color: var(--theme-text-color, #1f2937);
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.post-excerpt {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
  line-height: 1.6;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.post-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  font-size: 0.75rem;
  color: #9ca3af;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.read-more {
  color: var(--theme-primary-color, #2563eb);
  font-weight: 600;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  margin-top: auto;
  transition: gap 0.2s;
}

.read-more:hover {
  gap: 0.75rem;
}
</style>

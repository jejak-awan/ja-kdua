<template>
  <div v-if="related && related.length > 0" class="related-posts py-8 border-t border-border">
    <h2 class="text-2xl font-bold text-foreground mb-6">
      {{ title }}
    </h2>
    
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Loading skeleton -->
      <div v-for="i in 3" :key="i" class="animate-pulse">
        <div class="bg-muted h-48 rounded-lg mb-3"></div>
        <div class="h-4 bg-muted rounded w-3/4 mb-2"></div>
        <div class="h-3 bg-muted rounded w-1/2"></div>
      </div>
    </div>
    
    <div v-else-if="related && related.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <article
        v-for="post in related"
        :key="post.id"
        class="group bg-card rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200 border border-border"
      >
        <router-link :to="getPostUrl(post.slug)" class="block">
          <!-- Featured Image -->
          <div v-if="post.featured_image" class="relative h-48 overflow-hidden bg-secondary dark:bg-background">
            <img
              v-lazy="post.featured_image"
              :alt="post.title"
              src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='200'%3E%3Crect width='400' height='200' fill='%23e5e7eb'/%3E%3C/svg%3E"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
          </div>
          <div v-else class="relative h-48 bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
            <svg class="w-12 h-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          
          <!-- Content -->
          <div class="p-4">
            <!-- Category -->
            <div v-if="post.category" class="mb-2">
              <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-blue-500/20 text-blue-400 dark:bg-blue-900 dark:text-blue-200">
                {{ post.category.name }}
              </span>
            </div>
            
            <!-- Title -->
            <h3 class="text-lg font-semibold text-foreground mb-2 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
              {{ post.title }}
            </h3>
            
            <!-- Excerpt -->
            <p v-if="post.excerpt" class="text-sm text-muted-foreground mb-3 line-clamp-2">
              {{ post.excerpt }}
            </p>
            
            <!-- Meta -->
            <div class="flex items-center justify-between text-xs text-muted-foreground">
              <span v-if="post.author" class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
                {{ post.author.name }}
              </span>
              <span v-if="post.published_at" class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                </svg>
                {{ formatDate(post.published_at) }}
              </span>
            </div>
          </div>
        </router-link>
      </article>
    </div>
    
    <div v-else class="text-center py-8 text-muted-foreground">
      Tidak ada artikel terkait
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '@/services/api';

const props = defineProps({
  slug: {
    type: String,
    required: true,
  },
  title: {
    type: String,
    default: 'Artikel Terkait',
  },
  limit: {
    type: Number,
    default: 6,
  },
});

const related = ref([]);
const loading = ref(true);

const getPostUrl = (slug) => {
  return `/blog/${slug}`;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

const fetchRelated = async () => {
  loading.value = true;
  try {
    const response = await api.get(`/cms/contents/${props.slug}/related`);
    related.value = response.data.data.slice(0, props.limit);
  } catch (error) {
    console.error('Failed to fetch related posts:', error);
    related.value = [];
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchRelated();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>


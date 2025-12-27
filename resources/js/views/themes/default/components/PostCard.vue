<template>
  <article 
    class="bg-card rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 cursor-pointer flex flex-col h-full border border-border group"
    @click="navigateToPost"
  >
    <!-- Featured Image -->
    <div class="relative h-48 overflow-hidden bg-muted">
      <img 
        v-if="post.featured_image"
        v-lazy="post.featured_image" 
        :alt="post.title" 
        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
      >
      <div v-if="post.category" class="absolute top-4 left-4">
         <span class="px-3 py-1 bg-background/90 backdrop-blur-sm text-primary text-xs font-bold rounded-lg shadow-sm">
            {{ post.category.name }}
         </span>
      </div>
    </div>
    
    <!-- Content -->
    <div class="p-6 flex flex-col flex-1 gap-4">
      <div class="flex-1 space-y-2">
          <h3 class="text-xl font-bold text-gray-900 group-hover:text-indigo-600 transition-colors line-clamp-2">
            {{ post.title }}
          </h3>
          <p class="text-gray-500 text-sm line-clamp-3 leading-relaxed">
            {{ excerpt }}
          </p>
      </div>
      
      <!-- Meta -->
      <div class="border-t border-gray-100 pt-4 mt-auto flex items-center justify-between text-xs text-gray-500">
        <div class="flex items-center gap-2">
             <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold">
                {{ post.author?.name ? post.author.name[0] : 'A' }}
             </div>
             <span>{{ post.author?.name || 'Author' }}</span>
        </div>
        <span class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            {{ formatDate(post.published_at) }}
        </span>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'

const { locale } = useI18n()
const props = defineProps({
  post: {
    type: Object,
    required: true
  }
})

const router = useRouter()

const excerpt = computed(() => {
  if (props.post.excerpt) return props.post.excerpt
  if (props.post.content) return props.post.content.replace(/<[^>]*>/g, '').substring(0, 150) + '...'
  return ''
})

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString(locale.value, {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const navigateToPost = () => {
  router.push(`/blog/${props.post.slug}`)
}
</script>

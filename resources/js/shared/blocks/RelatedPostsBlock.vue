<template>
  <BaseBlock :module="module" :settings="settings" class="related-posts-block">
    <div class="container mx-auto">
      <h3 
        v-if="titleValue" 
        class="related-posts-title" 
        :style="titleStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateText('title', $event)"
      >{{ titleValue }}</h3>
      
      <div class="related-posts-grid grid gap-6" :style="gridStyles">
        <article v-for="(post, index) in displayPosts" :key="index" class="related-post flex flex-col h-full">
          <div v-if="showImage" class="post-image aspect-video bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden flex items-center justify-center mb-4">
             <img v-if="post.image" :src="post.image" :alt="post.title" class="w-full h-full object-cover" />
             <ImageIcon v-else class="w-8 h-8 text-gray-400" />
          </div>
          <div class="post-content flex-1">
            <span v-if="showMeta" class="post-meta block mb-1 text-sm opacity-60" :style="metaStyles">{{ post.date || 'Jan 10, 2026' }}</span>
            <h4 class="post-title mb-2 font-semibold" :style="postTitleStyles">{{ post.title }}</h4>
            <p v-if="showExcerpt" class="post-excerpt text-sm opacity-80 line-clamp-2" :style="excerptStyles">{{ post.excerpt }}</p>
          </div>
        </article>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Image as ImageIcon } from 'lucide-vue-next'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

// Dynamic data injection
const injectedRelatedPosts = inject('relatedPosts', [
    { title: 'Related Article Title 1', excerpt: 'A brief excerpt from the related article...', date: 'Jan 10, 2026', image: null },
    { title: 'Related Article Title 2', excerpt: 'Another interesting read for you...', date: 'Jan 9, 2026', image: null },
    { title: 'Related Article Title 3', excerpt: 'Deep dive into advanced topics...', date: 'Jan 8, 2026', image: null }
])

const titleValue = computed(() => props.mode === 'edit' ? (settings.value.title || 'Related Posts') : 'Related Posts')
const showImage = computed(() => getResponsiveValue(settings.value, 'showImage', device.value) !== false)
const showMeta = computed(() => getResponsiveValue(settings.value, 'showMeta', device.value) !== false)
const showExcerpt = computed(() => getResponsiveValue(settings.value, 'showExcerpt', device.value) !== false)

const displayPosts = computed(() => {
    const count = getResponsiveValue(settings.value, 'postsCount', device.value) || 3
    return injectedRelatedPosts.slice(0, count)
})

const updateText = (key, event) => {
    if (props.mode !== 'edit') return
    builder?.updateModuleSettings(props.module.id, { [key]: event.target.innerText })
}

const gridStyles = computed(() => {
  const columns = getResponsiveValue(settings.value, 'columns', device.value) || 3
  return { 
    gridTemplateColumns: `repeat(${columns}, 1fr)`
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', device.value))
const postTitleStyles = computed(() => getTypographyStyles(settings.value, 'post_title_', device.value))
const excerptStyles = computed(() => getTypographyStyles(settings.value, 'excerpt_', device.value))
</script>

<style scoped>
.related-posts-block { width: 100%; }
.related-posts-title { margin-bottom: 24px; outline: none; }
[contenteditable]:focus {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
}
</style>

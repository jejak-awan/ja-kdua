<template>
  <div class="related-posts-block" :style="wrapperStyles">
    <h3 v-if="titleValue" class="related-posts-title" :style="titleStyles">{{ titleValue }}</h3>
    <div class="related-posts-grid" :style="gridStyles">
      <article v-for="(post, index) in mockPosts" :key="index" class="related-post">
        <div v-if="showImage" class="post-image"><Image class="placeholder-icon" /></div>
        <div class="post-content">
          <span v-if="showMeta" class="post-meta" :style="metaStyles">Jan {{ 10 - index }}, 2026</span>
          <h4 class="post-title" :style="postTitleStyles">{{ post.title }}</h4>
          <p v-if="showExcerpt" class="post-excerpt" :style="excerptStyles">{{ post.excerpt }}</p>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Image } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getResponsiveValue,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({ module: { type: Object, required: true } })

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const titleValue = computed(() => getResponsiveValue(settings.value, 'title', device.value))
const showImage = computed(() => getResponsiveValue(settings.value, 'showImage', device.value) !== false)
const showMeta = computed(() => getResponsiveValue(settings.value, 'showMeta', device.value) !== false)
const showExcerpt = computed(() => getResponsiveValue(settings.value, 'showExcerpt', device.value) !== false)

const mockPosts = computed(() => {
  const count = getResponsiveValue(settings.value, 'postsCount', device.value) || 3
  return Array.from({ length: count }, (_, i) => ({
    title: `Related Article Title ${i + 1}`,
    excerpt: 'A brief excerpt from the related article...'
  }))
})

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  return styles
})

const gridStyles = computed(() => {
  const columns = getResponsiveValue(settings.value, 'columns', device.value) || 3
  return { 
    display: 'grid', 
    gridTemplateColumns: `repeat(${columns}, 1fr)`, 
    gap: '24px' 
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', device.value))
const postTitleStyles = computed(() => getTypographyStyles(settings.value, 'post_title_', device.value))
const excerptStyles = computed(() => getTypographyStyles(settings.value, 'excerpt_', device.value))
</script>

<style scoped>
.related-posts-block { width: 100%; }
.related-posts-title { margin-bottom: 24px; }
.post-image { aspect-ratio: 16/9; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
.placeholder-icon { width: 32px; height: 32px; color: #ccc; }
.post-meta { display: block; margin-bottom: 4px; }
.post-title { margin: 0 0 8px; }
.post-excerpt { margin: 0; }
</style>

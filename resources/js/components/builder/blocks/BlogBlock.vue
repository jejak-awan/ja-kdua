<template>
  <div class="blog-block" :style="wrapperStyles">
    <h2 
      v-if="settings.title || builder?.isEditing" 
      class="blog-title" 
      :style="titleStyles"
      contenteditable="true"
      @blur="e => builder.updateModuleSetting(module.id, 'title', e.target.innerText)"
    >
      {{ settings.title }}
    </h2>
    <div class="blog-grid" :style="gridStyles">
      <article 
        v-for="post in mockPosts" 
        :key="post.id"
        class="blog-post"
        :style="postStyles"
      >
        <!-- Featured Image -->
        <div v-if="settings.showImage !== false" class="post-image" :style="imageStyles">
          <div class="post-image-placeholder">
            <ImageIcon class="placeholder-icon" />
          </div>
        </div>
        
        <!-- Content -->
        <div class="post-content">
          <!-- Category -->
          <span v-if="settings.showCategory !== false" class="post-category" :style="categoryStyles">
            {{ post.category }}
          </span>
          
          <!-- Title -->
          <h3 class="post-title" :style="titleStyles">{{ post.title }}</h3>
          
          <!-- Excerpt -->
          <p v-if="settings.showExcerpt !== false" class="post-excerpt" :style="excerptStyles">
            {{ post.excerpt }}
          </p>
          
          <!-- Meta -->
          <div class="post-meta" :style="metaStyles">
            <span v-if="settings.showAuthor !== false">{{ post.author }}</span>
            <span v-if="settings.showAuthor !== false && settings.showDate !== false">•</span>
            <span v-if="settings.showDate !== false">{{ post.date }}</span>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Image as ImageIcon } from 'lucide-vue-next'
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

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

// Mock posts for preview
const mockPosts = computed(() => {
  const count = getResponsiveValue(settings.value, 'itemsPerPage', device.value) || 6
  return Array.from({ length: Math.min(count, 12) }, (_, i) => ({
    id: i + 1,
    title: `Blog Post Title ${i + 1}`,
    excerpt: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore.',
    category: 'Category',
    author: 'John Doe',
    date: 'Jan 10, 2026'
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
  const layout = getResponsiveValue(settings.value, 'layout', device.value) || 'grid'
  const columns = getResponsiveValue(settings.value, 'columns', device.value) || 3
  const gap = getResponsiveValue(settings.value, 'gap', device.value) || 24
  
  if (layout === 'list') {
    return { display: 'flex', flexDirection: 'column', gap: `${gap}px` }
  }
  return { 
    display: 'grid', 
    gridTemplateColumns: `repeat(${columns}, 1fr)`, 
    gap: `${gap}px` 
  }
})

const postStyles = computed(() => {
  const styles = {
    backgroundColor: settings.value.cardBackgroundColor || '#ffffff',
    overflow: 'hidden',
    borderRadius: 'var(--border-radius-lg)',
    transition: 'var(--transition-premium)',
    display: 'flex',
    flexDirection: 'column'
  }
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  return styles
})

const imageStyles = computed(() => {
  const ratio = settings.value.imageAspectRatio || '16:9'
  const ratioMap = { '16:9': '56.25%', '4:3': '75%', '1:1': '100%' }
  return { paddingTop: ratioMap[ratio] || '56.25%', position: 'relative' }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const categoryStyles = computed(() => getTypographyStyles(settings.value, 'category_', device.value))
const excerptStyles = computed(() => getTypographyStyles(settings.value, 'excerpt_', device.value))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', device.value))
</script>

<style scoped>
.blog-block { width: 100%; }
.blog-title {
    margin-bottom: 32px;
    width: 100%;
}
.blog-post { 
  background: white; 
}
.blog-post:hover {
  transform: translateY(-8px);
  box-shadow: var(--shadow-premium) !important;
}
.post-image { background: #f8fafc; overflow: hidden; }
.post-image-placeholder { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }
.placeholder-icon { width: 48px; height: 48px; color: #cbd5e1; }
.post-content { padding: 24px; flex: 1; display: flex; flex-direction: column; }
.post-category { 
  display: inline-block; 
  margin-bottom: 12px; 
  text-transform: uppercase; 
  font-size: 10px; 
  font-weight: 700; 
  letter-spacing: 0.1em;
  color: var(--builder-accent);
}
.post-title { 
  margin: 0 0 12px; 
  font-size: 1.25rem; 
  font-weight: 700; 
  line-height: 1.3;
  color: var(--builder-text-primary);
}
.post-excerpt { 
  margin: 0 0 20px; 
  font-size: 14px; 
  line-height: 1.6; 
  color: var(--builder-text-secondary);
  display: -webkit-box;
  overflow: hidden;
  line-clamp: 3;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
}
.post-meta { 
  display: flex; 
  gap: 8px; 
  font-size: 12px; 
  color: var(--builder-text-muted); 
  margin-top: auto;
  border-top: 1px solid var(--builder-border-light);
  padding-top: 16px;
}
</style>

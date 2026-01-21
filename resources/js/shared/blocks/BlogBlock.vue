<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperBaseStyles, settings }">
      <div class="blog-block" :style="blogBlockStyles">
        <h2 
          v-if="settings.title || mode === 'edit'" 
          class="blog-title" 
          :style="titleStyles"
          :contenteditable="mode === 'edit'"
          @blur="e => updateResponsiveField('title', e.target.innerText)"
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
                <LucideIcon name="Image" class="placeholder-icon" />
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
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder')
const settings = computed(() => props.module?.settings || {})

// Mock posts for preview
const mockPosts = computed(() => {
  const count = getVal(settings.value, 'itemsPerPage', props.device) || 6
  return Array.from({ length: Math.min(count, 12) }, (_, i) => ({
    id: i + 1,
    title: `Blog Post Title ${i + 1}`,
    excerpt: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore.',
    category: 'Category',
    author: 'John Doe',
    date: 'Jan 10, 2026'
  }))
})

const blogBlockStyles = computed(() => ({ width: '100%' }))

const gridStyles = computed(() => {
  const layout = getVal(settings.value, 'layout', props.device) || 'grid'
  const columns = getVal(settings.value, 'columns', props.device) || 3
  const gap = getVal(settings.value, 'gap', props.device) || 24
  
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
    borderRadius: '12px',
    transition: 'all 0.3s ease',
    display: 'flex',
    flexDirection: 'column'
  }
  return styles
})

const imageStyles = computed(() => {
  const ratio = settings.value.imageAspectRatio || '16:9'
  const ratioMap = { '16:9': '56.25%', '4:3': '75%', '1:1': '100%' }
  return { paddingTop: ratioMap[ratio] || '56.25%', position: 'relative' }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const categoryStyles = computed(() => getTypographyStyles(settings.value, 'category_', props.device))
const excerptStyles = computed(() => getTypographyStyles(settings.value, 'excerpt_', props.device))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', props.device))

const updateResponsiveField = (fieldName, value) => {
  if (props.mode !== 'edit') return
  const current = settings.value[fieldName]
  let newValue
  if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
    newValue = { ...current, [props.device]: value }
  } else {
    newValue = { [props.device]: value }
  }
  builder?.updateModuleSettings(props.module.id, { [fieldName]: newValue })
}
</script>

<style scoped>
.blog-block { width: 100%; }
.blog-title { margin-bottom: 32px; width: 100%; }
.blog-post { background: white; border: 1px solid #e2e8f0; }
.blog-post:hover { transform: translateY(-8px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
.post-image { background: #f8fafc; overflow: hidden; }
.post-image-placeholder { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }
.placeholder-icon { width: 48px; height: 48px; color: #cbd5e1; }
.post-content { padding: 24px; flex: 1; display: flex; flex-direction: column; }
.post-category { display: inline-block; margin-bottom: 12px; text-transform: uppercase; font-size: 10px; font-weight: 700; letter-spacing: 0.1em; color: var(--theme-primary-color, #2059ea); }
.post-title { margin: 0 0 12px; font-size: 1.25rem; font-weight: 700; line-height: 1.3; }
.post-excerpt { margin: 0 0 20px; font-size: 14px; line-height: 1.6; color: #64748b; display: -webkit-box; overflow: hidden; line-clamp: 3; -webkit-line-clamp: 3; -webkit-box-orient: vertical; }
.post-meta { display: flex; gap: 8px; font-size: 12px; color: #94a3b8; margin-top: auto; border-top: 1px solid #f1f5f9; padding-top: 16px; }
</style>

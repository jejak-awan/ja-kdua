<template>
  <header class="fullwidth-post-title-block" :style="containerStyles">
    <div v-if="settings.showFeaturedImage !== false" class="featured-bg" :style="featuredBgStyles" />
    <div class="title-overlay" :style="overlayStyles" />
    <div class="title-content" :style="contentStyles">
      <div v-if="settings.showMeta !== false" class="post-meta" :style="metaStyles">
        <span v-if="settings.showCategories !== false" class="meta-category">{{ postCategory }}</span>
        <span v-if="settings.showDate !== false" class="meta-date">{{ postDate }}</span>
        <span v-if="settings.showAuthor !== false" class="meta-author">by {{ postAuthor }}</span>
        <span v-if="settings.showCommentCount" class="meta-comments">{{ postComments }} Comments</span>
      </div>
      <component 
        :is="settings.tag || 'h1'" 
        class="post-title" 
        :style="titleStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateText('title', $event)"
      >
        {{ mode === 'edit' ? (settings.title || 'Amazing Post Title Goes Here') : postTitle }}
      </component>
    </div>
  </header>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
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

// Post data would normally come from context/prop
const postTitle = inject('postTitle', 'Amazing Post Title Goes Here')
const postCategory = inject('postCategory', 'Technology')
const postDate = inject('postDate', 'January 10, 2026')
const postAuthor = inject('postAuthor', 'John Doe')
const postComments = inject('postComments', 12)
const postFeaturedImage = inject('postFeaturedImage', '')

const updateText = (key, event) => {
    if (props.mode !== 'edit') return
    const value = event.target.innerText
    builder?.updateModuleSettings(props.module.id, { [key]: value })
}

const containerStyles = computed(() => {
  return { 
    position: 'relative', 
    width: '100%', 
    overflow: 'hidden',
    minHeight: `${getResponsiveValue(settings.value, 'height', device.value) || 400}px`
  }
})

const featuredBgStyles = computed(() => ({
    backgroundImage: postFeaturedImage ? `url(${postFeaturedImage})` : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    backgroundSize: 'cover',
    backgroundPosition: 'center',
    position: 'absolute',
    inset: 0,
    zIndex: 0
}))

const overlayStyles = computed(() => ({
  position: 'absolute',
  inset: 0,
  backgroundColor: settings.value.overlayColor || 'rgba(0,0,0,0.5)',
  zIndex: 1
}))

const contentStyles = computed(() => {
  const alignment = getResponsiveValue(settings.value, 'contentAlignment', device.value) || 'center'
  return {
    position: 'relative',
    zIndex: 10,
    display: 'flex',
    flexDirection: 'column',
    alignItems: alignment === 'left' ? 'flex-start' : alignment === 'right' ? 'flex-end' : 'center',
    padding: '40px',
    maxWidth: '1000px',
    width: '100%',
    height: '100%',
    textAlign: alignment,
    margin: '0 auto',
    justifyContent: settings.value.contentPosition === 'top' ? 'flex-start' : 
                    settings.value.contentPosition === 'bottom' ? 'flex-end' : 'center'
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', device.value))
</script>

<style scoped>
.fullwidth-post-title-block { display: flex; align-items: center; justify-content: center; width: 100%; }
.post-meta { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; color: inherit; justify-content: inherit; }
.meta-category { background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 4px; }
.post-title { max-width: 100%; margin: 0; }
[contenteditable]:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}
</style>

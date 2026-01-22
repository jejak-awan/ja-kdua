<template>
  <header class="fullwidth-post-title-block" :style="containerStyles">
    <div v-if="settings.showFeaturedImage !== false" class="featured-bg" />
    <div class="title-overlay" :style="overlayStyles" />
    <div class="title-content" :style="contentStyles">
      <div v-if="settings.showMeta !== false" class="post-meta" :style="metaStyles">
        <span v-if="settings.showCategories !== false" class="meta-category">Technology</span>
        <span v-if="settings.showDate !== false" class="meta-date">January 10, 2026</span>
        <span v-if="settings.showAuthor !== false" class="meta-author">by John Doe</span>
        <span v-if="settings.showCommentCount" class="meta-comments">12 Comments</span>
      </div>
      <component :is="settings.tag || 'h1'" class="post-title" :style="titleStyles">
        Amazing Post Title Goes Here
      </component>
    </div>
  </header>
</template>

<script setup>
import { computed, inject } from 'vue'
import { 
  getSpacingStyles, 
  getBackgroundStyles, 
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
const device = computed(() => builder?.device || 'desktop')

const containerStyles = computed(() => {
  const styles = { 
    position: 'relative', 
    width: '100%', 
    overflow: 'hidden',
    minHeight: `${getResponsiveValue(settings.value, 'height', device.value) || 400}px`
  }
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

const overlayStyles = computed(() => ({
  position: 'absolute',
  inset: 0,
  backgroundColor: settings.value.overlayColor || 'rgba(0,0,0,0.5)'
}))

const contentStyles = computed(() => {
  const alignment = getResponsiveValue(settings.value, 'contentAlignment', device.value) || 'center'
  return {
    position: 'relative',
    zIndex: 1,
    display: 'flex',
    flexDirection: 'column',
    alignItems: alignment === 'left' ? 'flex-start' : alignment === 'right' ? 'flex-end' : 'center',
    padding: '40px',
    maxWidth: '1000px',
    width: '100%',
    height: '100%',
    textAlign: alignment,
    justifyContent: settings.value.contentPosition === 'top' ? 'flex-start' : 
                    settings.value.contentPosition === 'bottom' ? 'flex-end' : 'center'
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', device.value))
</script>

<style scoped>
.fullwidth-post-title-block { display: flex; align-items: center; justify-content: center; }
.featured-bg {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  background-size: cover;
  background-position: center;
}
.title-overlay { position: absolute; inset: 0; }
.post-meta { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; }
.meta-category { background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 4px; }
.post-title { max-width: 100%; }
</style>

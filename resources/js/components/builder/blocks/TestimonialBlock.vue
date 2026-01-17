<template>
  <div class="testimonial-block" :style="wrapperStyles">
    <!-- Quote Icon -->
    <Quote 
      v-if="showQuoteIcon" 
      class="quote-icon"
      :style="quoteIconStyles"
    />
    
    <!-- Content -->
    <div class="testimonial-content" :style="contentStyles">
      {{ contentValue }}
    </div>
    
    <!-- Author -->
    <div class="testimonial-author">
      <img 
        v-if="settings.authorImage"
        :src="settings.authorImage"
        :alt="authorNameValue"
        class="author-image"
      />
      <div class="author-info">
        <div class="author-name" :style="authorNameStyles">{{ authorNameValue || 'Author Name' }}</div>
        <div v-if="authorTitleValue" class="author-title" :style="authorTitleStyles">{{ authorTitleValue }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Quote } from 'lucide-vue-next'
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
  module: {
    type: Object,
    required: true
  }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const contentValue = computed(() => getResponsiveValue(settings.value, 'content', device.value))
const authorNameValue = computed(() => getResponsiveValue(settings.value, 'authorName', device.value))
const authorTitleValue = computed(() => getResponsiveValue(settings.value, 'authorTitle', device.value))
const showQuoteIcon = computed(() => getResponsiveValue(settings.value, 'showQuoteIcon', device.value) !== false)

const wrapperStyles = computed(() => {
  const styles = { 
    textAlign: getResponsiveValue(settings.value, 'alignment', device.value) || 'center',
    width: '100%'
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

const quoteIconStyles = computed(() => {
  const size = getResponsiveValue(settings.value, 'quoteIconSize', device.value) || 48
  const color = getResponsiveValue(settings.value, 'quoteIconColor', device.value) || '#e0e0e0'
  return {
    width: `${size}px`,
    height: `${size}px`,
    color: color
  }
})

const contentStyles = computed(() => {
  return getTypographyStyles(settings.value, 'content_', device.value)
})

const authorNameStyles = computed(() => {
  return getTypographyStyles(settings.value, 'author_name_', device.value)
})

const authorTitleStyles = computed(() => {
  return getTypographyStyles(settings.value, 'author_title_', device.value)
})
</script>

<style scoped>
.testimonial-block { width: 100%; }
.quote-icon { margin-bottom: 16px; transform: rotate(180deg); }
.testimonial-content { margin-bottom: 24px; max-width: 700px; margin-left: auto; margin-right: auto; }
.testimonial-author { display: inline-flex; align-items: center; gap: 12px; }
.author-image { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; }
.author-title { margin-top: 2px; }
</style>

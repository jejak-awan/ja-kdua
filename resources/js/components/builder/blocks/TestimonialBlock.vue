<template>
  <div class="testimonial-block" :style="wrapperStyles">
    <!-- Quote Icon Overlay -->
    <Quote 
      v-if="showQuoteIcon" 
      class="quote-icon-bg"
      :style="{ color: quoteIconStyles.color }"
    />
    
    <!-- Content Wrapper -->
    <div class="testimonial-inner">
      <!-- Quote Icon Small -->
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
        <div class="author-image-wrapper">
          <img 
            v-if="settings.authorImage"
            :src="settings.authorImage"
            :alt="authorNameValue"
            class="author-image"
          />
        </div>
        <div class="author-info">
          <div class="author-name" :style="authorNameStyles">{{ authorNameValue || 'Author Name' }}</div>
          <div v-if="authorTitleValue" class="author-title" :style="authorTitleStyles">{{ authorTitleValue }}</div>
        </div>
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
const device = computed(() => builder?.device?.value || 'desktop')

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
.testimonial-block { 
  width: 100%; 
  position: relative; 
  background: white; 
  border-radius: 24px;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.testimonial-block:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-premium);
}

.testimonial-inner {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.quote-icon-bg {
  position: absolute;
  top: -20px;
  right: -20px;
  width: 120px;
  height: 120px;
  opacity: 0.05;
  transform: rotate(180deg);
  z-index: 1;
}

.quote-icon { 
  margin-bottom: 24px; 
  transform: rotate(180deg); 
  opacity: 0.8;
}

.testimonial-content { 
  margin-bottom: 32px; 
  max-width: 800px; 
  line-height: 1.8;
  font-size: 18px;
  font-style: italic;
  font-weight: 500;
  color: #1e293b;
}

.testimonial-author { 
  display: flex; 
  align-items: center; 
  gap: 16px; 
  margin-top: auto;
}

.author-image-wrapper {
  position: relative;
  width: 64px;
  height: 64px;
}

.author-image-wrapper::after {
  content: '';
  position: absolute;
  inset: -4px;
  border: 2px solid var(--builder-accent);
  border-radius: 50%;
  opacity: 0.2;
}

.author-image { 
  width: 100%; 
  height: 100%; 
  border-radius: 50%; 
  object-fit: cover; 
  box-shadow: var(--shadow-md);
}

.author-name {
  font-weight: 700;
  font-size: 16px;
  color: #0f172a;
}

.author-title { 
  margin-top: 2px; 
  font-size: 13px;
  font-weight: 500;
  opacity: 0.6;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
</style>

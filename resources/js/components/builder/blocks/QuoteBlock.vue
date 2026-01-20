<template>
  <blockquote class="quote-block" :class="`quote-block--${quoteStyle}`" :style="wrapperStyles">
    <QuoteIcon v-if="quoteStyle === 'classic'" class="quote-icon" :style="iconStyles" />
    <p class="quote-content" :style="contentStyles">{{ quoteContent }}</p>
    <footer v-if="quoteAuthor" class="quote-footer">
      <cite class="quote-author" :style="authorStyles">
        {{ quoteAuthor }}
        <span v-if="authorTitleValue" class="author-title">, {{ authorTitleValue }}</span>
      </cite>
    </footer>
  </blockquote>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Quote as QuoteIcon } from 'lucide-vue-next'
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
const device = computed(() => builder?.device?.value || 'desktop')

const quoteStyle = computed(() => getResponsiveValue(settings.value, 'quoteStyle', device.value) || 'modern')
const quoteContent = computed(() => getResponsiveValue(settings.value, 'content', device.value) || 'Your quote here...')
const quoteAuthor = computed(() => getResponsiveValue(settings.value, 'author', device.value))
const authorTitleValue = computed(() => getResponsiveValue(settings.value, 'authorTitle', device.value))

const wrapperStyles = computed(() => {
  const styles = { width: '100%', position: 'relative' }
  const alignment = getResponsiveValue(settings.value, 'alignment', device.value) || 'left'
  styles.textAlign = alignment
  
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

const iconStyles = computed(() => {
  const typography = getTypographyStyles(settings.value, 'quote_', device.value)
  return { 
    width: '48px', 
    height: '48px', 
    color: typography.color || '#2059ea',
    opacity: 0.3, 
    marginBottom: '16px' 
  }
})

const contentStyles = computed(() => getTypographyStyles(settings.value, 'quote_', device.value))
const authorStyles = computed(() => getTypographyStyles(settings.value, 'author_', device.value))
</script>

<style scoped>
.quote-block { width: 100%; position: relative; }
.quote-content { font-weight: 400; }
.quote-footer { margin-top: 16px; }
.quote-author { display: block; }
.author-title { opacity: 0.7; }
.quote-block--classic { border: none !important; }
.quote-block--minimal { border-left: none !important; padding-left: 0 !important; }
.quote-block--minimal::before { content: '"'; font-size: 4em; color: rgba(0,0,0,0.1); position: absolute; left: 0; top: -10px; }
</style>

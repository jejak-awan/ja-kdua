<template>
  <div class="post-meta-block" :style="wrapperStyles">
    <span v-if="settings.showAuthor !== false" class="meta-item">
      By <a href="#" :style="linkStyles">John Doe</a>
    </span>
    <span v-if="settings.showAuthor !== false && hasNextItem('author')" class="meta-separator">{{ sep }}</span>
    
    <span v-if="settings.showDate !== false" class="meta-item">January 10, 2026</span>
    <span v-if="settings.showDate !== false && hasNextItem('date')" class="meta-separator">{{ sep }}</span>
    
    <span v-if="settings.showCategory !== false" class="meta-item">
      <a href="#" :style="linkStyles">Technology</a>
    </span>
    <span v-if="settings.showCategory !== false && hasNextItem('category')" class="meta-separator">{{ sep }}</span>
    
    <span v-if="settings.showReadTime !== false" class="meta-item">5 min read</span>
    <span v-if="settings.showReadTime !== false && hasNextItem('readTime')" class="meta-separator">{{ sep }}</span>
    
    <span v-if="settings.showComments" class="meta-item">12 Comments</span>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
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

const sep = computed(() => settings.value.separator || '•')

const hasNextItem = (current) => {
  const items = ['author', 'date', 'category', 'readTime', 'comments']
  const idx = items.indexOf(current)
  const showMap = { author: settings.value.showAuthor, date: settings.value.showDate, category: settings.value.showCategory, readTime: settings.value.showReadTime, comments: settings.value.showComments }
  for (let i = idx + 1; i < items.length; i++) { if (showMap[items[i]] !== false && showMap[items[i]]) return true }
  return false
}

const wrapperStyles = computed(() => {
  const styles = { 
    display: 'flex', 
    alignItems: 'center', 
    gap: '8px', 
    flexWrap: 'wrap',
    width: '100%'
  }
  
  const alignment = getResponsiveValue(settings.value, 'alignment', device.value) || 'left' // Assuming inheritance doesn't apply directly to alignment if field isn't there, but let's check PostMeta.js. It doesn't have alignment field!
  // Wait, PostMeta.js doesn't have alignment field in design tab?
  // Let's assume text alignment comes from typography settings (standard typography has textAlign).
  // Standard typographySettings has text_align.
  // So getTypographyStyles handles text align if we pass it.
  
  Object.assign(styles, getTypographyStyles(settings.value, '', device.value))
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  // Override justify-content based on text-align from typography if present
  if (styles.textAlign === 'center') styles.justifyContent = 'center'
  else if (styles.textAlign === 'right') styles.justifyContent = 'flex-end'
  else styles.justifyContent = 'flex-start'

  return styles
})

const linkStyles = computed(() => {
    return getTypographyStyles(settings.value, 'link_', device.value)
})
</script>

<style scoped>
.post-meta-block { width: 100%; }
.meta-separator { opacity: 0.5; }
</style>

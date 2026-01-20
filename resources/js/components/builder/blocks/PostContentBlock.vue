<template>
  <div class="post-content-block" :style="contentStyles">
    <InlineRichtext 
      :model-value="settings.content"
      @update:modelValue="val => builder.updateModuleSetting(module.id, 'content', val)"
    />
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
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'
import InlineRichtext from '../canvas/InlineRichtext.vue'

const props = defineProps({ module: { type: Object, required: true } })

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const contentStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, '', device.value)
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  const linkColors = getTypographyStyles(settings.value, 'link_', device.value)
  styles['--link-color'] = linkColors.color || '#2059ea'
  styles['--link-font-weight'] = linkColors.fontWeight
  styles['--link-text-decoration'] = linkColors.textDecoration
  
  return styles
})
</script>

<style scoped>
.post-content-block { width: 100%; }
.post-content-block p { margin: 0 0 1.5em; }
.post-content-block h2, .post-content-block h3 { margin: 2em 0 1em; font-weight: 600; }
.post-content-block a { 
  color: var(--link-color); 
  font-weight: var(--link-font-weight); 
  text-decoration: var(--link-text-decoration); 
}
.post-content-block blockquote { margin: 1.5em 0; padding: 1em 1.5em; border-left: 4px solid #e0e0e0; font-style: italic; color: #666; }
</style>

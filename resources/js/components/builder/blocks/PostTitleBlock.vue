<template>
  <component 
    :is="settings.tag || 'h1'" 
    class="post-title-block" 
    :style="titleStyles"
    contenteditable="true"
    @blur="e => builder.updateModuleSetting(module.id, 'title', e.target.innerText)"
  >
    {{ settings.title || 'Sample Post Title' }}
  </component>
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

const props = defineProps({ module: { type: Object, required: true } })

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const postTitle = computed(() => 'Sample Post Title - Dynamic Content')

const titleStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, '', device.value)
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
</script>

<style scoped>
.post-title-block { width: 100%; }
</style>

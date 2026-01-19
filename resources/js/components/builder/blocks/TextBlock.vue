<template>
  <div 
    class="text-block"
    :style="textStyles"
    :class="cssClass"
    v-html="content"
  />
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

const builder = inject('builder')

const props = defineProps({
  module: {
    type: Object,
    required: true
  }
})

// Computed
const settings = computed(() => props.module?.settings || {})

const content = computed(() => 
  settings.value.content || '<p>Your text goes here. Edit this in the settings panel.</p>'
)

const device = computed(() => builder?.device || 'desktop')

const textStyles = computed(() => {
  const styles = { width: '100%' }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getTypographyStyles(settings.value, '', device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  return styles
})

const cssClass = computed(() => settings.value.cssClass || '')
</script>

<style scoped>
.text-block {
  word-wrap: break-word;
}

.text-block :deep(p) {
  margin: 0 0 1.5em;
  line-height: 1.7;
  color: inherit;
}

.text-block :deep(p:last-child) {
  margin-bottom: 0;
}

.text-block :deep(a) {
  color: var(--builder-accent, #2059ea);
}
</style>

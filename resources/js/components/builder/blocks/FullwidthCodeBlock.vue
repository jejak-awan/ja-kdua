<template>
  <div class="fullwidth-code-block" :style="wrapperStyles">
    <div v-if="settings.rawContent" class="code-content" v-html="settings.rawContent" />
    <div v-else class="code-placeholder">
      <Code class="placeholder-icon" />
      <span>Add custom HTML/CSS/JS</span>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Code } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles,
  getSizingStyles,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({ module: { type: Object, required: true } })
const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const wrapperStyles = computed(() => {
  const styles = { width: '100%', minHeight: '100px' }
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
.fullwidth-code-block { width: 100%; }
.code-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; padding: 48px; color: #666; }
.placeholder-icon { width: 32px; height: 32px; }
</style>

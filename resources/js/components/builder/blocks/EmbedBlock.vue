<template>
  <div class="embed-block" :style="wrapperStyles">
    <div v-if="hasContent" class="embed-container" :style="containerStyles">
      <iframe v-if="settings.embedType === 'url' && settings.embedUrl" :src="settings.embedUrl" frameborder="0" allowfullscreen class="embed-iframe" />
      <div v-else-if="settings.embedCode" class="embed-code" v-html="settings.embedCode" />
    </div>
    <div v-else class="embed-placeholder">
      <Code2 class="placeholder-icon" />
      <span>Add embed code or URL</span>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Code2 } from 'lucide-vue-next'
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
const settings = computed(() => props.module.settings || {})

const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')

const hasContent = computed(() => (settings.value.embedType === 'url' && settings.value.embedUrl) || settings.value.embedCode)

const aspectRatios = { '16:9': '56.25%', '4:3': '75%', '1:1': '100%' }

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  
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

const containerStyles = computed(() => {
  const ratio = getResponsiveValue(settings.value, 'aspectRatio', device.value) || '16:9'
  if (ratio === 'auto') return { position: 'relative' }
  return { 
    position: 'relative', 
    paddingTop: aspectRatios[ratio] || '56.25%', 
    overflow: 'hidden' 
  }
})
</script>

<style scoped>
.embed-block { width: 100%; }
.embed-iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
.embed-code { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
.embed-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; padding: 48px; background: #f0f0f0; border-radius: 8px; color: #999; }
.placeholder-icon { width: 32px; height: 32px; }
</style>

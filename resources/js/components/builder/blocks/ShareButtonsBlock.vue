<template>
  <div class="share-buttons-block" :style="wrapperStyles">
    <span v-if="labelValue" class="share-label" :style="labelStyles">{{ labelValue }}</span>
    <div class="share-buttons" :style="buttonListStyles">
      <button v-for="(item, index) in platformList" :key="index" class="share-button" :class="buttonClasses" :style="buttonStyles(item.network)">
        <component :is="getIcon(item.network)" class="share-icon" />
        <span v-if="showLabels" class="share-text">{{ item.label || item.network }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Facebook, Twitter, Linkedin, Mail, MessageCircle, Link2 } from 'lucide-vue-next'
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
const device = computed(() => builder?.device || 'desktop')

const labelValue = computed(() => getResponsiveValue(settings.value, 'label', device.value))
const showLabels = computed(() => getResponsiveValue(settings.value, 'showLabels', device.value))
const currentStyle = computed(() => getResponsiveValue(settings.value, 'style', device.value) || 'filled')
const currentShape = computed(() => getResponsiveValue(settings.value, 'shape', device.value) || 'rounded')
const currentSize = computed(() => getResponsiveValue(settings.value, 'size', device.value) || 'medium')

const platformList = computed(() => {
  return (props.module.children || []).map(child => ({
    network: child.settings.network || 'facebook',
    label: child.settings.customLabel || ''
  }))
})

const platformColors = { facebook: '#1877f2', twitter: '#1da1f2', linkedin: '#0077b5', whatsapp: '#25d366', email: '#ea4335' }
const getIcon = (p) => ({ facebook: Facebook, twitter: Twitter, linkedin: Linkedin, whatsapp: MessageCircle, email: Mail }[p] || Link2)

const wrapperStyles = computed(() => {
  const styles = { 
    display: 'flex', 
    alignItems: 'center', 
    gap: '12px',
    flexWrap: 'wrap'
  }
  
  const alignment = getResponsiveValue(settings.value, 'alignment', device.value) || 'left'
  if (alignment === 'center') styles.justifyContent = 'center'
  else if (alignment === 'right') styles.justifyContent = 'flex-end'
  else styles.justifyContent = 'flex-start'

  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  return styles
})

const buttonListStyles = computed(() => {
  const gap = getResponsiveValue(settings.value, 'gap', device.value) || 8
  return { 
    display: 'flex', 
    gap: `${gap}px`, 
    flexWrap: 'wrap' 
  }
})

const labelStyles = computed(() => getTypographyStyles(settings.value, 'label_', device.value))

const buttonClasses = computed(() => [
  `share-button--${currentStyle.value}`,
  `share-button--${currentShape.value}`,
  `share-button--${currentSize.value}`
])

const buttonStyles = (platform) => {
  const color = platformColors[platform] || '#666'
  if (currentStyle.value === 'filled') return { backgroundColor: color, color: '#fff' }
  if (currentStyle.value === 'outline') return { border: `2px solid ${color}`, color, backgroundColor: 'transparent' }
  return { color, backgroundColor: 'transparent' }
}
</script>

<style scoped>
.share-buttons-block { width: 100%; }
.share-button { display: flex; align-items: center; gap: 6px; border: none; cursor: pointer; transition: transform 0.2s, opacity 0.2s; }
.share-button:hover { transform: scale(1.05); opacity: 0.9; }
.share-button--rounded { border-radius: 6px; }
.share-button--circle { border-radius: 50%; }
.share-button--square { border-radius: 0; }
.share-button--small { padding: 6px 10px; }
.share-button--small .share-icon { width: 14px; height: 14px; }
.share-button--medium { padding: 8px 14px; }
.share-button--medium .share-icon { width: 18px; height: 18px; }
.share-button--large { padding: 12px 18px; }
.share-button--large .share-icon { width: 22px; height: 22px; }
.share-text { font-size: 13px; font-weight: 500; text-transform: capitalize; }
</style>

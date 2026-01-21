<template>
  <BaseBlock :module="module" :settings="settings" class="share-buttons-block">
    <div class="share-buttons-container flex flex-wrap items-center gap-4" :style="wrapperStyles">
        <span v-if="labelValue" class="share-label font-bold text-sm tracking-wide uppercase opacity-60" :style="labelStyles">{{ labelValue }}</span>
        <div class="share-buttons-list flex flex-wrap" :style="buttonListStyles">
          <button 
            v-for="(item, index) in platformList" 
            :key="index" 
            class="share-button-item flex items-center justify-center gap-2 transition-all hover:-translate-y-1 active:scale-95 shadow-sm"
            :class="buttonClasses" 
            :style="buttonStyles(item.network)"
            @click="handleShare(item.network)"
          >
            <component :is="getIcon(item.network)" class="share-icon" :class="iconSizeClass" />
            <span v-if="showLabels" class="share-text font-semibold text-sm">{{ item.label || item.network }}</span>
          </button>
        </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Facebook, Twitter, Linkedin, Mail, MessageCircle, Link2 } from 'lucide-vue-next'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const labelValue = computed(() => getResponsiveValue(settings.value, 'label', device.value))
const showLabels = computed(() => getResponsiveValue(settings.value, 'showLabels', device.value))
const currentStyle = computed(() => getResponsiveValue(settings.value, 'style', device.value) || 'filled')
const currentShape = computed(() => getResponsiveValue(settings.value, 'shape', device.value) || 'rounded')
const currentSize = computed(() => getResponsiveValue(settings.value, 'size', device.value) || 'medium')

const platformList = computed(() => {
  // In builder mode, children are managed by ModuleWrapper slot
  // But for the logic of style and platform, we might need a list
  // Let's assume children define the platforms
  return (props.module.children || []).map(child => ({
    network: child.settings.network || 'facebook',
    label: child.settings.customLabel || ''
  }))
})

const platformColors = { 
    facebook: '#1877f2', 
    twitter: '#1da1f2', 
    linkedin: '#0077b5', 
    whatsapp: '#25d366', 
    email: '#ea4335' 
}

const getIcon = (p) => ({ 
    facebook: Facebook, 
    twitter: Twitter, 
    linkedin: Linkedin, 
    whatsapp: MessageCircle, 
    email: Mail 
}[p] || Link2)

const handleShare = (network) => {
    if (props.mode === 'edit') return
    const url = window.location.href
    const text = document.title
    
    let shareUrl = ''
    switch(network) {
        case 'facebook': shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`; break;
        case 'twitter': shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`; break;
        case 'linkedin': shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`; break;
        case 'whatsapp': shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(text + ' ' + url)}`; break;
        case 'email': shareUrl = `mailto:?subject=${encodeURIComponent(text)}&body=${encodeURIComponent(url)}`; break;
    }
    
    if (shareUrl) window.open(shareUrl, '_blank')
}

const wrapperStyles = computed(() => {
  const styles = {}
  const alignment = getResponsiveValue(settings.value, 'alignment', device.value) || 'left'
  if (alignment === 'center') styles.justifyContent = 'center'
  else if (alignment === 'right') styles.justifyContent = 'flex-end'
  else styles.justifyContent = 'flex-start'
  return styles
})

const buttonListStyles = computed(() => {
  const gap = getResponsiveValue(settings.value, 'gap', device.value) || 12
  return { 
    gap: `${gap}px`
  }
})

const labelStyles = computed(() => getTypographyStyles(settings.value, 'label_', device.value))

const buttonClasses = computed(() => [
  currentShape.value === 'rounded' ? 'rounded-lg' : 
  currentShape.value === 'circle' ? 'rounded-full' : '',
  currentSize.value === 'small' ? 'px-3 py-1.5' : 
  currentSize.value === 'medium' ? 'px-4 py-2.5' : 
  'px-6 py-4'
])

const iconSizeClass = computed(() => [
    currentSize.value === 'small' ? 'w-4 h-4' : 
    currentSize.value === 'medium' ? 'w-5 h-5' : 
    'w-6 h-6'
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
.share-button-item { border: none; cursor: pointer; }
/* Circle shape should be square aspect ratio if no label */
.rounded-full:not(:has(.share-text)) { aspect-ratio: 1/1; padding: 0; width: 3rem; }
.share-button-item--small.rounded-full:not(:has(.share-text)) { width: 2.25rem; }
.share-button-item--large.rounded-full:not(:has(.share-text)) { width: 4rem; }
</style>

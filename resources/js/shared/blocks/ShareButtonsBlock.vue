<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :settings="settings"
    class="share-buttons-block transition-all duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Social Share'"
  >
    <div class="share-buttons-container flex flex-wrap items-center w-full" :style="wrapperStyles">
        <Badge v-if="labelValue" variant="secondary" class="share-label mr-6 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 font-black text-[10px] uppercase tracking-[0.2em] px-4 py-1.5 border-none" :style="labelStyles">{{ labelValue }}</Badge>
        
        <div class="share-buttons-list flex flex-wrap" :style="listStyles">
          <Button 
            v-for="(item, index) in platformList" 
            :key="index" 
            class="share-button-item group transition-all duration-500 hover:scale-[var(--hover-scale)] active:scale-95 shadow-md hover:shadow-xl rounded-full"
            :class="[
                showLabels ? 'px-6 h-12' : 'p-0 w-12 h-12',
                currentSize === 'small' ? 'h-10 w-10' : (currentSize === 'large' ? 'h-16 w-16' : 'h-12 w-12')
            ]"
            :style="buttonStyles(item.network)"
            variant="ghost"
            @click="handleShare(item.network)"
          >
            <component :is="getIcon(item.network)" class="share-icon transition-transform duration-500 group-hover:rotate-12" :class="iconSizeClass" />
            <span v-if="showLabels" class="share-text font-black text-[10px] uppercase tracking-widest ml-2">{{ item.label || item.network }}</span>
          </Button>
        </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Button, Badge } from '../ui'
import { Facebook, Twitter, Linkedin, Mail, MessageCircle, Link2 } from 'lucide-vue-next'
import { 
  getVal,
  getLayoutStyles,
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<any>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const labelValue = computed(() => getVal(settings.value, 'label', device.value))
const showLabels = computed(() => getVal(settings.value, 'showLabels', device.value))
const currentStyle = computed(() => getVal(settings.value, 'style', device.value) || 'filled')
const currentSize = computed(() => getVal(settings.value, 'size', device.value) || 'medium')

const platformList = computed(() => {
  const children = props.module.children || []
  if (children.length === 0) {
    return [
        { network: 'facebook', label: '' },
        { network: 'twitter', label: '' },
        { network: 'linkedin', label: '' },
        { network: 'whatsapp', label: '' }
    ]
  }
  return children.map(child => ({
    network: (child.settings as any).network || 'facebook',
    label: (child.settings as any).customLabel || ''
  }))
})

const platformColors: Record<string, string> = { 
    facebook: '#1877f2', 
    twitter: '#1da1f2', 
    linkedin: '#0077b5', 
    whatsapp: '#25d366', 
    email: '#ea4335' 
}

const getIcon = (p: string) => {
    const icons: Record<string, any> = { 
        facebook: Facebook, 
        twitter: Twitter, 
        linkedin: Linkedin, 
        whatsapp: MessageCircle, 
        email: Mail 
    }
    return icons[p] || Link2
}

const handleShare = (network: string) => {
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
  const align = getVal(settings.value, 'alignment', device.value) || 'left'
  return { 
    justifyContent: align === 'center' ? 'center' : (align === 'right' ? 'flex-end' : 'flex-start')
  }
})

const listStyles = computed(() => {
    const layout = getLayoutStyles(settings.value, device.value)
    const gap = getVal(settings.value, 'gap', device.value) ?? 12
    const hoverScale = getVal(settings.value, 'hover_scale', device.value) || 1.1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', device.value) || 110

    return {
        ...layout,
        gap: `${gap}px`,
        '--hover-scale': hoverScale,
        '--hover-brightness': `${hoverBrightness}%`
    }
})

const labelStyles = computed(() => getTypographyStyles(settings.value, 'label_', device.value))

const iconSizeClass = computed(() => [
    currentSize.value === 'small' ? 'w-4 h-4' : 
    currentSize.value === 'medium' ? 'w-5 h-5' : 
    'w-6 h-6'
])

const buttonStyles = (platform: string) => {
  const color = platformColors[platform] || '#666'
  const styles: Record<string, any> = {
      filter: 'brightness(var(--hover-brightness, 100%)) transition-all duration-300'
  }
  
  if (currentStyle.value === 'filled') {
      styles.backgroundColor = color
      styles.color = '#fff'
      styles.borderColor = 'transparent'
  } else if (currentStyle.value === 'outline') {
      styles.border = `2px solid ${color}`
      styles.color = color
      styles.backgroundColor = 'transparent'
  } else {
      styles.color = color
      styles.backgroundColor = 'transparent'
      styles.borderColor = 'transparent'
  }
  
  return styles
}
</script>

<style scoped>
.share-buttons-block { width: 100%; }
.share-button-item:hover {
    filter: brightness(var(--hover-brightness));
}
</style>


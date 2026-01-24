<template>
  <BaseBlock :module="module" :settings="settings" class="share-buttons-block">
    <div class="share-buttons-container flex flex-wrap items-center w-full" :style="wrapperStyles">
        <Badge v-if="labelValue" variant="secondary" class="share-label mr-6 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 font-black text-[10px] uppercase tracking-[0.2em] px-4 py-1.5 border-none" :style="labelStyles">{{ labelValue }}</Badge>
        
        <div class="share-buttons-list flex flex-wrap gap-3">
          <Button 
            v-for="(item, index) in platformList" 
            :key="index" 
            class="share-button-item group transition-all duration-500 hover:scale-110 active:scale-95 shadow-md hover:shadow-xl rounded-full"
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

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Button, Badge } from '../ui'
import { Facebook, Twitter, Linkedin, Mail, MessageCircle, Link2 } from 'lucide-vue-next'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const currentDevice = computed(() => builder?.device?.value || props.device || 'desktop')
const settings = computed(() => props.module.settings || {})

const labelValue = computed(() => getResponsiveValue(settings.value, 'label', currentDevice.value))
const showLabels = computed(() => getResponsiveValue(settings.value, 'showLabels', currentDevice.value))
const currentStyle = computed(() => getResponsiveValue(settings.value, 'style', currentDevice.value) || 'filled')
const currentSize = computed(() => getResponsiveValue(settings.value, 'size', currentDevice.value) || 'medium')

const platformList = computed(() => {
  return (props.module.children || []).map(child => ({
    network: child.settings.network || 'facebook',
    label: child.settings.customLabel || ''
  }))
})

if (platformList.value.length === 0) {
    // Fallback/Demo platforms if no children defined yet
    platformList.value = [
        { network: 'facebook' },
        { network: 'twitter' },
        { network: 'linkedin' },
        { network: 'whatsapp' }
    ]
}

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
  const alignment = getResponsiveValue(settings.value, 'alignment', currentDevice.value) || 'left'
  return { 
    justifyContent: alignment === 'center' ? 'center' : (alignment === 'right' ? 'flex-end' : 'flex-start')
  }
})

const labelStyles = computed(() => getTypographyStyles(settings.value, 'label_', currentDevice.value))

const iconSizeClass = computed(() => [
    currentSize.value === 'small' ? 'w-4 h-4' : 
    currentSize.value === 'medium' ? 'w-5 h-5' : 
    'w-6 h-6'
])

const buttonStyles = (platform) => {
  const color = platformColors[platform] || '#666'
  if (currentStyle.value === 'filled') return { backgroundColor: color, color: '#fff', borderColor: 'transparent' }
  if (currentStyle.value === 'outline') return { border: `2px solid ${color}`, color, backgroundColor: 'transparent' }
  return { color, backgroundColor: 'transparent', borderColor: 'transparent' }
}
</script>

<style scoped>
.share-buttons-block { width: 100%; }
</style>

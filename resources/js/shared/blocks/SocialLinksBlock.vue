<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings }">
      <div class="social-links-block w-full flex flex-wrap" :style="containerStyles">
        <Button 
          v-for="(link, index) in links" 
          :key="index"
          as="a"
          :href="mode === 'view' ? (link.url || '#') : null"
          target="_blank"
          rel="noopener noreferrer"
          class="social-link-btn group transition-all duration-300 hover:scale-110 active:scale-95 shadow-md hover:shadow-xl"
          :class="buttonClass(link)"
          :style="getButtonStyle(link)"
          variant="outline"
          @click="mode === 'edit' ? $event.preventDefault() : null"
        >
          <LucideIcon 
            :name="getIconName(link.network)" 
            class="transition-transform duration-500 group-hover:rotate-[360deg]" 
            :size="iconSize" 
          />
          <span v-if="showLabels" class="ml-2 font-black text-[10px] uppercase tracking-widest">
            {{ link.network }}
          </span>
        </Button>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Button } from '../ui'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const currentDevice = computed(() => builder?.device?.value || props.device || 'desktop')
const settings = computed(() => props.module?.settings || {})

const links = computed(() => settings.value.links || [
    { network: 'facebook', url: '#' },
    { network: 'twitter', url: '#' },
    { network: 'instagram', url: '#' },
    { network: 'linkedin', url: '#' }
])

const showLabels = computed(() => getVal(settings.value, 'showLabels', currentDevice.value))

const iconMap = {
  facebook: 'Facebook',
  twitter: 'Twitter',
  instagram: 'Instagram',
  linkedin: 'Linkedin',
  youtube: 'Youtube',
  github: 'Github',
  dribbble: 'Dribbble',
  whatsapp: 'MessageCircle',
  email: 'Mail',
  website: 'Globe'
}

const getIconName = (network) => iconMap[network] || 'Globe'

const containerStyles = computed(() => {
  const gap = parseInt(getVal(settings.value, 'gap', currentDevice.value)) || 16
  const align = getVal(settings.value, 'alignment', currentDevice.value) || 'center'
  
  return {
    gap: `${gap}px`,
    justifyContent: align === 'center' ? 'center' : align === 'right' ? 'flex-end' : 'flex-start',
  }
})

const iconSize = computed(() => parseInt(getVal(settings.value, 'iconSize', currentDevice.value)) || 20)

const buttonClass = (link) => {
    const style = getVal(settings.value, 'displayStyle', currentDevice.value) || 'icon-circle'
    const spv = iconSize.value
    const res = []
    
    if (style === 'icon-circle') res.push('rounded-full aspect-square p-0')
    else if (style === 'icon-square') res.push('rounded-2xl aspect-square p-0')
    else res.push('rounded-full px-6') // with label or default

    if (spv < 20) res.push('w-10 h-10')
    else if (spv < 26) res.push('w-12 h-12')
    else res.push('w-16 h-16')

    return res
}

const getButtonStyle = (link) => {
  const color = getVal(settings.value, 'color', currentDevice.value) || 'currentColor'
  const bgColor = getVal(settings.value, 'backgroundColor', currentDevice.value) || 'rgba(var(--primary), 0.05)'
  
  return {
    color: link.useCustomColor ? link.iconColor : color,
    backgroundColor: link.useCustomColor ? (link.backgroundColor || 'transparent') : bgColor,
    borderColor: 'transparent'
  }
}
</script>

<style scoped>
.social-links-block { width: 100%; }
</style>

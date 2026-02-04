<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :settings="settings"
    class="social-links-block transition-colors duration-300"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Social Links'"
  >
    <div class="social-links-container w-full flex flex-wrap" :style="containerStyles">
      <Button 
        v-for="(link, index) in displayLinks" 
        :key="index"
        as="a"
        :href="mode === 'view' ? (link.url || '#') : undefined"
        target="_blank"
        rel="noopener noreferrer"
        class="social-link-btn group transition-colors duration-300 hover:scale-[var(--hover-scale)] active:scale-95 shadow-md hover:shadow-xl"
        :class="buttonClass()"
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
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Button } from '../ui'
import { LucideIcon } from '@/components/ui';
import { 
  getVal,
  getLayoutStyles
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance, ModuleSettings } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<BuilderInstance | null>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

interface SocialLink {
    network: string;
    url?: string;
    useCustomColor?: boolean;
    iconColor?: string;
    backgroundColor?: string;
}

const displayLinks = computed(() => (settings.value.links as SocialLink[]) || [
    { network: 'facebook', url: '#' },
    { network: 'twitter', url: '#' },
    { network: 'instagram', url: '#' },
    { network: 'linkedin', url: '#' }
])

const showLabels = computed(() => getVal<boolean>(settings.value, 'showLabels', device.value))

const iconMap: Record<string, string> = {
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

const getIconName = (network: string) => iconMap[network] || 'Globe'

const containerStyles = computed(() => {
  const layout = getLayoutStyles(settings.value, device.value)
  const gapVal = getVal<string | number>(settings.value, 'gap', device.value)
  const gap = parseInt(gapVal as string) || 16
  const align = getVal<string>(settings.value, 'alignment', device.value) || 'center'
  
  const hoverScale = getVal<number>(settings.value, 'hover_scale', device.value) || 1.1
  const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', device.value) || 110

  const styles: Record<string, string | number> = {
    ...layout,
    gap: `${gap}px`,
    justifyContent: align === 'center' ? 'center' : align === 'right' ? 'flex-end' : 'flex-start',
    '--hover-scale': hoverScale,
    '--hover-brightness': `${hoverBrightness}%`
  }
  return styles
})

const iconSize = computed(() => {
    const val = getVal<string | number>(settings.value, 'iconSize', device.value)
    return parseInt(val as string) || 20
})

const buttonClass = () => {
    const style = getVal<string>(settings.value, 'displayStyle', device.value) || 'icon-circle'
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

const getButtonStyle = (link: {useCustomColor?: boolean, iconColor?: string, backgroundColor?: string}) => {
  const color = getVal<string>(settings.value, 'color', device.value) || 'currentColor'
  const bgColor = getVal<string>(settings.value, 'backgroundColor', device.value) || 'rgba(var(--primary), 0.05)'
  
  const styles: Record<string, string | number> = {
    color: link.useCustomColor ? (link.iconColor || color) : color,
    backgroundColor: link.useCustomColor ? (link.backgroundColor || 'transparent') : bgColor,
    borderColor: 'transparent',
    filter: 'brightness(var(--hover-brightness, 100%))'
  }
  return styles
}
</script>

<style scoped>
.social-links-block { width: 100%; }
.social-link-btn:hover {
    filter: brightness(var(--hover-brightness));
}
</style>


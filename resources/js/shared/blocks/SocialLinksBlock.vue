<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperBaseStyles, settings }">
      <div class="social-links-block" :style="socialLinksBlockStyles">
        <a 
          v-for="(link, index) in links" 
          :key="index"
          :href="link.url || '#'"
          target="_blank"
          rel="noopener noreferrer"
          class="social-link"
          :style="getLinkStyles(link)"
          @click="mode === 'edit' ? e => e.preventDefault() : undefined"
        >
          <LucideIcon 
            :name="getIconName(link.network)" 
            :size="iconSize" 
            :style="iconStyles" 
          />
        </a>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})

const links = computed(() => settings.value.links || [])

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

const socialLinksBlockStyles = computed(() => {
  const gap = parseInt(getVal(settings.value, 'gap', props.device)) || 16
  const align = getVal(settings.value, 'alignment', props.device) || 'center'
  
  return {
    display: 'flex',
    gap: `${gap}px`,
    flexWrap: 'wrap',
    justifyContent: align === 'center' ? 'center' : align === 'right' ? 'flex-end' : 'flex-start',
    width: '100%'
  }
})

const iconSize = computed(() => {
    const size = parseInt(getVal(settings.value, 'iconSize', props.device)) || 24
    return size
})

const getLinkStyles = (link) => {
  const color = getVal(settings.value, 'color', props.device) || 'currentColor'
  const hoverColor = getVal(settings.value, 'hoverColor', props.device) || 'var(--theme-primary-color, #2059ea)'
  const hoverBg = getVal(settings.value, 'hoverBackgroundColor', props.device) || ''
  
  const styles = {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    color: link.useCustomColor ? link.iconColor : color,
    textDecoration: 'none',
    transition: 'all 0.2s ease',
    '--hover-color': hoverColor,
    '--hover-bg': hoverBg
  }
  
  const style = getVal(settings.value, 'displayStyle', props.device) || 'icon-only'
  const size = iconSize.value
  
  if (style === 'icon-circle' || style === 'icon-square') {
    const dim = size * 1.8
    styles.width = `${dim}px`
    styles.height = `${dim}px`
    styles.borderRadius = style === 'icon-circle' ? '50%' : '8px'
    styles.backgroundColor = link.useCustomColor ? link.backgroundColor : 'rgba(0,0,0,0.05)'
  }
  
  return styles
}

const iconStyles = computed(() => {
  return {
    display: 'block'
  }
})
</script>

<style scoped>
.social-links-block { width: 100%; }
.social-link:hover { 
  color: var(--hover-color) !important; 
  background-color: var(--hover-bg); 
  transform: translateY(-3px); 
}
</style>

<template>
  <div class="social-links-block" :style="wrapperStyles">
    <a 
      v-for="(link, index) in links" 
      :key="index"
      :href="link.url || '#'"
      target="_blank"
      rel="noopener noreferrer"
      class="social-link"
      :class="linkClass"
      :style="getLinkStyles(link)"
      @click.prevent
    >
      <component :is="getIcon(link.network)" class="social-icon" :style="iconStyles" />
    </a>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { 
  Facebook, Twitter, Instagram, Linkedin, Youtube, 
  Github, Dribbble, MessageCircle, Mail, Globe
} from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getResponsiveValue,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const links = computed(() => settings.value.links || [])

const iconMap = {
  facebook: Facebook,
  twitter: Twitter,
  instagram: Instagram,
  linkedin: Linkedin,
  youtube: Youtube,
  github: Github,
  dribbble: Dribbble,
  whatsapp: MessageCircle,
  email: Mail,
  website: Globe
}

const getIcon = (network) => iconMap[network] || Globe

const linkClass = computed(() => {
  const style = getResponsiveValue(settings.value, 'displayStyle', device.value) || 'icon-only'
  return `social-link--${style}`
})

const wrapperStyles = computed(() => {
  const gap = parseInt(getResponsiveValue(settings.value, 'gap', device.value)) || 16
  const styles = {
    display: 'flex',
    gap: `${gap}px`,
    flexWrap: 'wrap'
  }
  
  const alignment = getResponsiveValue(settings.value, 'alignment', device.value) || 'center'
  if (alignment === 'center') {
    styles.justifyContent = 'center'
  } else if (alignment === 'right') {
    styles.justifyContent = 'flex-end'
  }
  
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

const getLinkStyles = (link) => {
  const color = getResponsiveValue(settings.value, 'color', device.value) || '#333333'
  const hoverColor = getResponsiveValue(settings.value, 'hoverColor', device.value) || '#2059ea'
  const hoverBg = getResponsiveValue(settings.value, 'hoverBackgroundColor', device.value) || ''
  
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
  
  const style = getResponsiveValue(settings.value, 'displayStyle', device.value) || 'icon-only'
  const size = parseInt(getResponsiveValue(settings.value, 'iconSize', device.value)) || 24
  
  if (style === 'icon-circle' || style === 'icon-square') {
    const dim = size * 1.8
    styles.width = `${dim}px`
    styles.height = `${dim}px`
    styles.borderRadius = style === 'icon-circle' ? '50%' : '8px'
    styles.backgroundColor = link.useCustomColor ? link.backgroundColor : '#f3f4f6'
  }
  
  return styles
}

const iconStyles = computed(() => {
  const size = parseInt(getResponsiveValue(settings.value, 'iconSize', device.value)) || 24
  return {
    width: `${size}px`,
    height: `${size}px`
  }
})
</script>

<style scoped>
.social-links-block { width: 100%; }
.social-link:hover { color: var(--hover-color) !important; background-color: var(--hover-bg); transform: translateY(-3px); }
.social-icon { display: block; }
</style>

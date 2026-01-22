<template>
  <div class="author-block" :style="wrapperStyles" :class="`author-block--${layout}`">
    <div class="author-image-wrapper">
      <div v-if="settings.image" class="author-image" :style="imageStyles"><img :src="settings.image" :alt="settings.name" /></div>
      <div v-else class="author-image author-image--placeholder" :style="imageStyles"><UserCircle class="placeholder-icon" /></div>
    </div>
    <div class="author-content">
      <h4 class="author-name" :style="nameStyles">{{ settings.name || 'Author Name' }}</h4>
      <p v-if="settings.title" class="author-title" :style="titleStyles">{{ settings.title }}</p>
      <p v-if="settings.bio" class="author-bio" :style="bioStyles">{{ settings.bio }}</p>
      <div v-if="settings.showSocial !== false && authorSocialLinks.length" class="author-social">
        <a v-for="(link, i) in authorSocialLinks" :key="i" :href="link.url" class="social-link" target="_blank"><component :is="getSocialIcon(link.platform)" class="social-icon" /></a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { UserCircle, Twitter, Linkedin, Facebook, Instagram, Github, Globe } from 'lucide-vue-next'
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

const layout = computed(() => getResponsiveValue(settings.value, 'layout', device.value) || 'horizontal')

const authorSocialLinks = computed(() => {
  const links = settings.value.socialLinks
  if (Array.isArray(links)) return links
  if (typeof links === 'string') { try { return JSON.parse(links) } catch { return [] } }
  return []
})

const getSocialIcon = (p) => {
  const icons = { twitter: Twitter, linkedin: Linkedin, facebook: Facebook, instagram: Instagram, github: Github }
  return icons[p?.toLowerCase()] || Globe
}

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

const imageStyles = computed(() => {
  const size = getResponsiveValue(settings.value, 'imageSize', device.value) || 100
  return { width: `${size}px`, height: `${size}px` }
})

const nameStyles = computed(() => getTypographyStyles(settings.value, 'name_', device.value))
const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const bioStyles = computed(() => getTypographyStyles(settings.value, 'bio_', device.value))
</script>

<style scoped>
.author-block { width: 100%; }
.author-block--horizontal { display: flex; gap: 24px; align-items: flex-start; }
.author-block--vertical { text-align: center; }
.author-block--vertical .author-image-wrapper { margin-bottom: 16px; }
.author-image { border-radius: 50%; overflow: hidden; flex-shrink: 0; }
.author-image img { width: 100%; height: 100%; object-fit: cover; }
.author-image--placeholder { display: flex; align-items: center; justify-content: center; background: #e0e0e0; }
.placeholder-icon { width: 60%; height: 60%; color: #999; }
.author-name { margin: 0 0 4px; }
.author-title { margin: 0 0 12px; }
.author-bio { margin: 0 0 16px; }
.author-social { display: flex; gap: 12px; justify-content: inherit; }
.author-block--vertical .author-social { justify-content: center; }
.social-link { display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: #e0e0e0; color: #666; transition: all 0.2s; }
.social-link:hover { background: #2059ea; color: white; }
.social-icon { width: 16px; height: 16px; }
</style>

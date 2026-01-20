<template>
  <div class="person-block" :class="positionClass" :style="wrapperStyles">
    <div class="person-image">
      <User v-if="!imageValue" class="placeholder-icon" />
      <img v-else :src="imageValue" :alt="nameValue" />
    </div>
    <div class="person-content" :style="contentStyles">
      <h3 class="person-name" :style="nameStyles">{{ nameValue }}</h3>
      <span class="person-position" :style="positionStyles">{{ positionValue }}</span>
      <p v-if="bioValue" class="person-bio" :style="bioStyles">{{ bioValue }}</p>
      <div v-if="hasSocial" class="person-social">
        <a v-if="facebookUrl" :href="facebookUrl" class="social-link"><Facebook /></a>
        <a v-if="twitterUrl" :href="twitterUrl" class="social-link"><Twitter /></a>
        <a v-if="linkedinUrl" :href="linkedinUrl" class="social-link"><Linkedin /></a>
        <a v-if="instagramUrl" :href="instagramUrl" class="social-link"><Instagram /></a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { User, Facebook, Twitter, Linkedin, Instagram } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getFilterStyles,
  getTransformStyles,
  getResponsiveValue 
} from '../core/styleUtils'

const props = defineProps({ module: { type: Object, required: true } })

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const nameValue = computed(() => getResponsiveValue(settings.value, 'name', device.value) || 'John Doe')
const positionValue = computed(() => getResponsiveValue(settings.value, 'position', device.value) || 'Position')
const bioValue = computed(() => getResponsiveValue(settings.value, 'bio', device.value))
const imageValue = computed(() => getResponsiveValue(settings.value, 'image', device.value))

const facebookUrl = computed(() => getResponsiveValue(settings.value, 'facebook', device.value))
const twitterUrl = computed(() => getResponsiveValue(settings.value, 'twitter', device.value))
const linkedinUrl = computed(() => getResponsiveValue(settings.value, 'linkedin', device.value))
const instagramUrl = computed(() => getResponsiveValue(settings.value, 'instagram', device.value))

const hasSocial = computed(() => facebookUrl.value || twitterUrl.value || linkedinUrl.value || instagramUrl.value)

const imagePosition = computed(() => getResponsiveValue(settings.value, 'imagePosition', device.value) || 'top')
const alignment = computed(() => getResponsiveValue(settings.value, 'alignment', device.value) || 'center')
const positionClass = computed(() => `person-block--${imagePosition.value}`)

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
  styles.textAlign = alignment.value
  return styles
})

const contentStyles = computed(() => {
  return { 
    textAlign: alignment.value,
    flex: imagePosition.value === 'top' ? 'none' : '1'
  }
})

const nameStyles = computed(() => getTypographyStyles(settings.value, 'name_', device.value))
const positionStyles = computed(() => getTypographyStyles(settings.value, 'position_', device.value))
const bioStyles = computed(() => getTypographyStyles(settings.value, 'bio_', device.value))
</script>

<style scoped>
.person-block { width: 100%; }
.person-block--top { display: flex; flex-direction: column; align-items: center; }
.person-block--left { display: flex; align-items: flex-start; gap: 20px; text-align: left !important; }
.person-block--right { display: flex; flex-direction: row-reverse; align-items: flex-start; gap: 20px; text-align: right !important; }
.person-image { width: 120px; height: 120px; border-radius: 50%; overflow: hidden; background: #f0f0f0; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-bottom: 16px; }
.person-image img { width: 100%; height: 100%; object-fit: cover; }
.placeholder-icon { width: 48px; height: 48px; color: #ccc; }
.person-name { margin: 0 0 4px; font-weight: 600; }
.person-position { display: block; margin-bottom: 12px; }
.person-bio { margin: 0 0 16px; line-height: 1.6; }
.person-social { display: flex; gap: 12px; justify-content: inherit; }
.social-link { color: #666; transition: color 0.2s; }
.social-link:hover { color: #2059ea; }
</style>

<template>
  <div class="team-member-block" :style="wrapperStyles" :class="layoutClass">
    <!-- Image -->
    <div class="member-image-wrapper">
      <div v-if="settings.image" class="member-image" :style="imageStyles">
        <img :src="settings.image" :alt="settings.name" />
      </div>
      <div v-else class="member-image member-image--placeholder" :style="imageStyles">
        <User class="placeholder-icon" />
      </div>
    </div>
    
    <!-- Info -->
    <div class="member-info">
      <h3 class="member-name" :style="nameStyles">{{ nameValue }}</h3>
      <p class="member-position" :style="positionStyles">{{ positionValue }}</p>
      <p v-if="bioValue" class="member-bio" :style="bioStyles">{{ bioValue }}</p>
      
      <!-- Social Links -->
      <div v-if="memberSocialLinks.length" class="member-social">
        <a 
          v-for="(link, index) in memberSocialLinks" 
          :key="index"
          :href="link.url"
          class="social-link"
        >
          <component :is="getSocialIcon(link.platform)" class="social-icon" />
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { User, Linkedin, Twitter, Facebook, Instagram, Github, Globe } from 'lucide-vue-next'
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

const props = defineProps({
  module: {
    type: Object,
    required: true
  }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const nameValue = computed(() => getResponsiveValue(settings.value, 'name', device.value) || 'Team Member')
const positionValue = computed(() => getResponsiveValue(settings.value, 'position', device.value) || 'Position')
const bioValue = computed(() => getResponsiveValue(settings.value, 'bio', device.value))
const layoutValue = computed(() => getResponsiveValue(settings.value, 'layout', device.value) || 'stacked')
const alignmentValue = computed(() => getResponsiveValue(settings.value, 'alignment', device.value) || 'center')
const imageSizeValue = computed(() => getResponsiveValue(settings.value, 'imageSize', device.value) || 150)
const imageBorderRadiusValue = computed(() => getResponsiveValue(settings.value, 'imageBorderRadius', device.value) || 50)

const layoutClass = computed(() => `team-member--${layoutValue.value}`)

const memberSocialLinks = computed(() => {
  return (props.module.children || []).map(child => ({
    platform: child.settings.network || 'globe',
    url: child.settings.url || '#'
  }))
})

const getSocialIcon = (platform) => {
  const icons = { linkedin: Linkedin, twitter: Twitter, facebook: Facebook, instagram: Instagram, github: Github }
  return icons[platform] || Globe
}

const wrapperStyles = computed(() => {
  const styles = {
    textAlign: alignmentValue.value
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
  
  return styles
})

const imageStyles = computed(() => {
  return {
    width: `${imageSizeValue.value}px`,
    height: `${imageSizeValue.value}px`,
    borderRadius: `${imageBorderRadiusValue.value}%`
  }
})

const nameStyles = computed(() => getTypographyStyles(settings.value, 'name_', device.value))
const positionStyles = computed(() => getTypographyStyles(settings.value, 'position_', device.value))
const bioStyles = computed(() => getTypographyStyles(settings.value, 'bio_', device.value))
</script>

<style scoped>
.team-member-block {
  width: 100%;
}

.team-member--stacked {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.team-member--horizontal {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 24px;
}

.member-image {
  overflow: hidden;
}

.member-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.member-image--placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f0f0f0;
}

.placeholder-icon {
  width: 40%;
  height: 40%;
  color: #ccc;
}

.member-name {
  margin: 0 0 4px;
}

.member-position {
  margin: 0 0 12px;
}

.member-bio {
  margin: 0 0 16px;
}

.member-social {
  display: flex;
  gap: 12px;
  justify-content: inherit;
}

.social-link {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #f0f0f0;
  color: #666;
  transition: all 0.2s;
}

.social-link:hover {
  background: #2059ea;
  color: white;
}

.social-icon {
  width: 18px;
  height: 18px;
}
</style>

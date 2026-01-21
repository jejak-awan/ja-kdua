<template>
  <BaseBlock :module="module" :settings="settings" class="author-block" :class="`author-block--${layout}`">
    <div class="author-image-wrapper">
      <div v-if="authorImage || mode === 'edit'" class="author-image" :style="imageStyles">
          <img v-if="authorImage" :src="authorImage" :alt="authorName" />
          <UserCircle v-else class="placeholder-icon" />
      </div>
    </div>
    <div class="author-content" :style="contentStyles">
      <h4 
        class="author-name" 
        :style="nameStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateText('name', $event)"
      >{{ authorName || (mode === 'edit' ? 'Author Name' : '') }}</h4>
      
      <p 
        v-if="authorTitle || mode === 'edit'" 
        class="author-title" 
        :style="titleStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateText('title', $event)"
      >{{ authorTitle || (mode === 'edit' ? 'Author Title/Role' : '') }}</p>
      
      <p 
        v-if="authorBio || mode === 'edit'" 
        class="author-bio" 
        :style="bioStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateText('bio', $event)"
      >{{ authorBio || (mode === 'edit' ? 'Author bio goes here...' : '') }}</p>
      
      <div v-if="settings.showSocial !== false && activeSocialLinks.length" class="author-social">
        <a 
            v-for="(link, i) in activeSocialLinks" 
            :key="i" 
            :href="mode === 'view' ? link.url : null" 
            class="social-link" 
            target="_blank"
            @click="handleLinkClick"
        >
            <component :is="getSocialIcon(link.platform)" class="social-icon" />
        </a>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { UserCircle, Twitter, Linkedin, Facebook, Instagram, Github, Globe } from 'lucide-vue-next'
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

// Dynamic data injection
const injectedAuthorName = inject('authorName', 'John Doe')
const injectedAuthorTitle = inject('authorTitle', 'Content Creator')
const injectedAuthorBio = inject('authorBio', 'Passionate about sharing knowledge and building community.')
const injectedAuthorImage = inject('authorImage', null)
const injectedAuthorSocial = inject('authorSocial', [])

const authorName = computed(() => props.mode === 'edit' ? (settings.value.name || injectedAuthorName) : injectedAuthorName)
const authorTitle = computed(() => props.mode === 'edit' ? (settings.value.title || injectedAuthorTitle) : injectedAuthorTitle)
const authorBio = computed(() => props.mode === 'edit' ? (settings.value.bio || injectedAuthorBio) : injectedAuthorBio)
const authorImage = computed(() => props.mode === 'edit' ? (settings.value.image || injectedAuthorImage) : injectedAuthorImage)

const layout = computed(() => getResponsiveValue(settings.value, 'layout', device.value) || 'horizontal')

const activeSocialLinks = computed(() => {
    const links = props.mode === 'edit' ? (settings.value.socialLinks || injectedAuthorSocial) : injectedAuthorSocial
    if (Array.isArray(links)) return links
    if (typeof links === 'string') { try { return JSON.parse(links) } catch { return [] } }
    return []
})

const getSocialIcon = (p) => {
  const icons = { twitter: Twitter, linkedin: Linkedin, facebook: Facebook, instagram: Instagram, github: Github }
  return icons[p?.toLowerCase()] || Globe
}

const updateText = (key, event) => {
    if (props.mode !== 'edit') return
    builder?.updateModuleSettings(props.module.id, { [key]: event.target.innerText })
}

const handleLinkClick = (event) => {
    if (props.mode === 'edit') event.preventDefault()
}

const imageStyles = computed(() => {
  const size = getResponsiveValue(settings.value, 'imageSize', device.value) || 100
  return { width: `${size}px`, height: `${size}px` }
})

const contentStyles = computed(() => {
    const styles = {}
    if (layout.value === 'vertical') styles.textAlign = 'center'
    return styles
})

const nameStyles = computed(() => getTypographyStyles(settings.value, 'name_', device.value))
const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const bioStyles = computed(() => getTypographyStyles(settings.value, 'bio_', device.value))
</script>

<style scoped>
.author-block { width: 100%; }
.author-block--horizontal { display: flex; gap: 24px; align-items: flex-start; }
.author-block--vertical { display: flex; flex-direction: column; align-items: center; text-align: center; }
.author-block--vertical .author-image-wrapper { margin-bottom: 16px; }
.author-image { border-radius: 50%; overflow: hidden; flex-shrink: 0; background: #f0f0f0; display: flex; align-items: center; justify-content: center; }
.author-image img { width: 100%; height: 100%; object-fit: cover; }
.placeholder-icon { width: 60%; height: 60%; color: #ccc; }
.author-name { margin: 0 0 4px; outline: none; }
.author-title { margin: 0 0 12px; outline: none; opacity: 0.8; }
.author-bio { margin: 0 0 16px; outline: none; }
.author-social { display: flex; gap: 12px; justify-content: inherit; }
.social-link { display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: #f0f0f0; color: #666; transition: all 0.2s; }
.social-link:hover { background: #3b82f6; color: white; }
.social-icon { width: 16px; height: 16px; }
[contenteditable]:focus {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
}
</style>

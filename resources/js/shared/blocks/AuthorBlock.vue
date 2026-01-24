<template>
  <BaseBlock :module="module" :settings="settings" class="author-block">
    <div :class="[
      'flex gap-6',
      layout === 'vertical' ? 'flex-col items-center text-center' : 'items-start text-left'
    ]">
      <Avatar :style="imageStyles" class="flex-shrink-0">
        <AvatarImage 
          v-if="authorImage" 
          :src="authorImage" 
          :alt="authorName" 
          class="object-cover"
        />
        <AvatarFallback class="bg-gray-100 text-gray-400">
          <UserCircle class="w-2/3 h-2/3" />
        </AvatarFallback>
      </Avatar>

      <div class="author-content flex-1 max-w-full">
        <h4 
          class="font-bold mb-1 outline-none" 
          :style="nameStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateText('name', $event)"
          v-text="authorName || (mode === 'edit' ? 'Author Name' : '')"
        ></h4>
        
        <p 
          v-if="authorTitle || mode === 'edit'" 
          class="text-sm opacity-80 mb-3 outline-none" 
          :style="titleStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateText('title', $event)"
          v-text="authorTitle || (mode === 'edit' ? 'Author Title/Role' : '')"
        ></p>
        
        <div 
          v-if="authorBio || mode === 'edit'" 
          class="mb-4 leading-relaxed outline-none" 
          :style="bioStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateText('bio', $event)"
          v-text="authorBio || (mode === 'edit' ? 'Author bio goes here...' : '')"
        ></div>
        
        <div v-if="settings.showSocial !== false && activeSocialLinks.length" class="flex gap-3" :class="layout === 'vertical' ? 'justify-center' : ''">
          <a 
              v-for="(link, i) in activeSocialLinks" 
              :key="i" 
              :href="mode === 'view' ? link.url : null" 
              class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center hover:bg-primary hover:text-white hover:-translate-y-0.5 transition-all"
              target="_blank"
              @click="handleLinkClick"
          >
              <component :is="getSocialIcon(link.platform)" class="w-4 h-4" />
          </a>
        </div>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Avatar, AvatarImage, AvatarFallback } from '../ui'
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

const nameStyles = computed(() => getTypographyStyles(settings.value, 'name_', device.value))
const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const bioStyles = computed(() => getTypographyStyles(settings.value, 'bio_', device.value))
</script>

<style scoped>
.author-block { width: 100%; }
/* Edit mode placeholder overrides */
[contenteditable="true"]:empty:before {
  content: 'Add details...';
  opacity: 0.3;
}
</style>

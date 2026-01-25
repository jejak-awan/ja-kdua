<template>
  <BaseBlock 
    :module="module" 
    :settings="settings" 
    :mode="mode"
    class="author-block transition-all duration-300"
    :style="cardStyles"
    :id="settings.html_id"
    :aria-label="settings.aria_label"
  >
    <div :class="[
      'author-container flex',
      layout === 'vertical' ? 'flex-col items-center text-center' : 'items-start text-left'
    ]" :style="containerStyles">
      <Avatar :style="imageStyles" class="flex-shrink-0 author-avatar">
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
          class="font-bold mb-1 outline-none author-name" 
          :style="nameStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateText('name', $event)"
          v-text="authorName || (mode === 'edit' ? 'Author Name' : '')"
        ></h4>
        
        <p 
          v-if="authorTitle || mode === 'edit'" 
          class="text-sm opacity-80 mb-3 outline-none author-title" 
          :style="titleStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateText('title', $event)"
          v-text="authorTitle || (mode === 'edit' ? 'Author Title/Role' : '')"
        ></p>
        
        <div 
          v-if="authorBio || mode === 'edit'" 
          class="mb-4 leading-relaxed outline-none author-bio" 
          :style="bioStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateText('bio', $event)"
          v-text="authorBio || (mode === 'edit' ? 'Author bio goes here...' : '')"
        ></div>
        
        <div v-if="settings.showSocial !== false && activeSocialLinks.length" class="flex gap-3 author-social" :class="layout === 'vertical' ? 'justify-center' : ''">
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

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Avatar, AvatarImage, AvatarFallback } from '../ui'
import { UserCircle, Twitter, Linkedin, Facebook, Instagram, Github, Globe } from 'lucide-vue-next'
import { 
  getVal,
  getLayoutStyles,
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

import type { BlockInstance } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<any>('builder', null)
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)
const device = computed(() => builder?.device?.value || 'desktop')

// Dynamic data injection
const injectedAuthorName = inject('authorName', 'John Doe')
const injectedAuthorTitle = inject('authorTitle', 'Content Creator')
const injectedAuthorBio = inject('authorBio', 'Passionate about sharing knowledge and building community.')
const injectedAuthorImage = inject('authorImage', null)
const injectedAuthorSocial = inject('authorSocial', [])

const authorName = computed(() => props.mode === 'edit' ? (getVal(settings.value, 'name', device.value) || injectedAuthorName) : injectedAuthorName)
const authorTitle = computed(() => props.mode === 'edit' ? (getVal(settings.value, 'title', device.value) || injectedAuthorTitle) : injectedAuthorTitle)
const authorBio = computed(() => props.mode === 'edit' ? (getVal(settings.value, 'bio', device.value) || injectedAuthorBio) : injectedAuthorBio)
const authorImage = computed(() => props.mode === 'edit' ? (getVal(settings.value, 'image', device.value) || injectedAuthorImage) : injectedAuthorImage)

const layout = computed(() => getResponsiveValue(settings.value, 'layout', device.value) || 'horizontal')

const activeSocialLinks = computed(() => {
    const links = props.mode === 'edit' ? (settings.value.socialLinks || injectedAuthorSocial) : injectedAuthorSocial
    if (Array.isArray(links)) return links
    if (typeof links === 'string') { try { return JSON.parse(links) } catch { return [] } }
    return []
})

const getSocialIcon = (p: string) => {
  const icons: Record<string, any> = { twitter: Twitter, linkedin: Linkedin, facebook: Facebook, instagram: Instagram, github: Github }
  return icons[p?.toLowerCase()] || Globe
}

const updateText = (key: string, event: Event) => {
    if (props.mode !== 'edit') return
    const target = event.target as HTMLElement
    builder?.updateModuleSettings(props.module.id, { [key]: target.innerText })
}

const handleLinkClick = (event: MouseEvent) => {
    if (props.mode === 'edit') event.preventDefault()
}

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    
    // Interactive states
    const hoverScale = getVal(settings.value, 'hover_scale', device.value) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', device.value) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => getLayoutStyles(settings.value, device.value))

const imageStyles = computed(() => {
  const size = getResponsiveValue(settings.value, 'imageSize', device.value) || 100
  return { width: `${size}px`, height: `${size}px` }
})

const nameStyles = computed(() => getTypographyStyles(settings.value, 'name_', device.value))
const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const bioStyles = computed(() => getTypographyStyles(settings.value, 'bio_', device.value))
</script>

<style scoped>
.author-block {
    width: 100%;
}

.author-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}

.author-container {
    transition: all 0.3s ease;
}

/* Edit mode placeholder overrides */
[contenteditable="true"]:empty:before {
  content: 'Add details...';
  opacity: 0.3;
}
</style>


<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :device="device"
    class="related-posts-block transition-all duration-500"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Related Posts'"
    :style="cardStyles"
  >
    <div class="related-wrapper w-full" :style="containerStyles">
      <h3 
        v-if="titleValue" 
        class="related-posts-main-title mb-12 font-black tracking-tighter outline-none" 
        :style="mainTitleStyles"
        :contenteditable="mode === 'edit'"
        @blur="(e: any) => updateField('title', (e.target as HTMLElement).innerText)"
      >{{ titleValue }}</h3>
      
      <div class="related-posts-grid grid transition-all duration-500" :style="gridStyles">
        <article 
            v-for="(post, index) in displayPosts" 
            :key="index" 
            class="related-post group flex flex-col h-full bg-white dark:bg-slate-900 border border-slate-50 dark:border-slate-800 rounded-[2.5rem] overflow-hidden shadow-2xl transition-all duration-700 hover:-translate-y-4"
        >
          <div v-if="showImage" class="post-image aspect-video relative bg-slate-50 dark:bg-slate-950 overflow-hidden flex items-center justify-center">
             <div class="absolute inset-0 bg-gradient-to-tr from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 z-10" />
             <img v-if="post.image" :src="post.image" :alt="post.title" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" />
             <LucideIcon v-else name="Image" class="w-12 h-12 text-slate-100 dark:text-slate-800 transition-transform duration-1000 group-hover:scale-110" />
          </div>
          
          <div class="post-content flex-1 p-8 relative z-20">
            <span v-if="showMeta" class="post-meta block mb-3 text-[10px] font-black uppercase tracking-[0.2em] text-primary/60" :style="metaStyles">
                {{ post.date || 'JAN 25, 2026' }}
            </span>
            <h4 class="post-title mb-4 text-xl font-black leading-tight tracking-tight group-hover:text-primary transition-colors" :style="postTitleStyles">
                {{ post.title }}
            </h4>
            <p v-if="showExcerpt" class="post-excerpt text-sm font-medium text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed" :style="excerptStyles">
                {{ post.excerpt }}
            </p>
          </div>
        </article>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { 
  getVal,
  getLayoutStyles,
  getTypographyStyles
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance;
  mode?: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile';
}>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<any>('builder', null)
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

// Mock dynamic data
const injectedRelatedPosts = inject<any[]>('relatedPosts', [
    { title: 'The Future of Agency Design: Dynamic Interfaces', excerpt: 'Deep dive into specialized layout controls and unmatched interactive aesthetics...', date: 'JAN 25, 2026', image: 'https://picsum.photos/600/400?random=41' },
    { title: 'Crafting Digital Excellence with Antigravity', excerpt: 'Explore the latest techniques in modern web design and performance...', date: 'JAN 22, 2026', image: 'https://picsum.photos/600/400?random=42' },
    { title: 'Luminous Branding Strategies', excerpt: 'How to build brands that resonate in the digital ecosystem...', date: 'JAN 20, 2026', image: 'https://picsum.photos/600/400?random=43' }
])

const titleValue = computed(() => props.mode === 'edit' ? (settings.value.title || 'Related Insights') : 'Related Insights')
const showImage = computed(() => getVal(settings.value, 'showImage', props.device) !== false)
const showMeta = computed(() => getVal(settings.value, 'showMeta', props.device) !== false)
const showExcerpt = computed(() => getVal(settings.value, 'showExcerpt', props.device) !== false)

const displayPosts = computed(() => {
    const count = getVal(settings.value, 'postsCount', props.device) || 3
    return injectedRelatedPosts.slice(0, count)
})

const updateField = (key: string, value: string) => {
    if (props.mode !== 'edit' || !builder) return
    builder.updateModuleSettings(props.module.id, { [key]: value })
}

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => getLayoutStyles(settings.value, props.device))

const gridStyles = computed(() => {
  const columns = getVal(settings.value, 'columns', props.device) || 3
  return { 
    display: 'grid',
    gridTemplateColumns: props.device === 'mobile' ? '1fr' : (props.device === 'tablet' ? 'repeat(2, 1fr)' : `repeat(${columns}, 1fr)`),
    gap: '2rem'
  }
})

const mainTitleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', props.device))
const postTitleStyles = computed(() => getTypographyStyles(settings.value, 'post_title_', props.device))
const excerptStyles = computed(() => getTypographyStyles(settings.value, 'excerpt_', props.device))
</script>

<style scoped>
.related-posts-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.related-posts-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.related-posts-main-title { outline: none; }
</style>

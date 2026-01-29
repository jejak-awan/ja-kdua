<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :device="device"
    class="post-meta-block transition-[width] duration-500"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Post Metadata'"
    :style="cardStyles"
  >
    <div 
        class="meta-inner flex flex-wrap items-center transition-[width] duration-500" 
        :class="alignmentClass"
        :style="containerStyles"
    >
        <template v-for="(item, index) in activeMetaItems" :key="item.key">
            <span class="meta-item flex items-center gap-2 group/meta">
                <component v-if="item.icon" :is="item.icon" class="w-4 h-4 text-slate-400 group-hover/meta:text-primary transition-colors" />
                <span v-if="item.key === 'author'" class="text-slate-400 dark:text-slate-500 font-bold text-[10px] uppercase">By </span>
                <a 
                    v-if="item.link" 
                    :href="mode === 'view' ? item.link : '#'" 
                    :style="linkStyles"
                    class="hover:text-primary transition-colors duration-300"
                    @click.prevent="mode === 'edit' ? null : null"
                >{{ item.value }}</a>
                <span v-else class="text-slate-600 dark:text-slate-300 font-bold text-xs" :style="textStyles">{{ item.value }}</span>
            </span>
            <span v-if="index < activeMetaItems.length - 1" class="meta-separator px-4 opacity-20 font-black text-slate-400">{{ separator }}</span>
        </template>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import Calendar from 'lucide-vue-next/dist/esm/icons/calendar.js';
import User from 'lucide-vue-next/dist/esm/icons/user.js';
import Tag from 'lucide-vue-next/dist/esm/icons/tag.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import MessageSquare from 'lucide-vue-next/dist/esm/icons/message-square.js';import { 
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

// Dynamic data injection
const postAuthor = inject<string>('postAuthor', 'John Doe')
const postAuthorUrl = inject<string>('postAuthorUrl', '#')
const postDate = inject<string>('postDate', 'January 10, 2026')
const postCategory = inject<string>('postCategory', 'Technology')
const postCategoryUrl = inject<string>('postCategoryUrl', '#')
const postReadTime = inject<string>('postReadTime', '5 min read')
const postCommentsCount = inject<number>('postCommentsCount', 12)

const separator = computed(() => getVal(settings.value, 'separator', props.device) || '•')

const metaItems = computed(() => [
    { key: 'author', value: postAuthor, link: postAuthorUrl, icon: User, show: settings.value.showAuthor !== false },
    { key: 'date', value: postDate, icon: Calendar, show: settings.value.showDate !== false },
    { key: 'category', value: postCategory, link: postCategoryUrl, icon: Tag, show: settings.value.showCategories !== false || settings.value.showCategory !== false },
    { key: 'readTime', value: postReadTime, icon: Clock, show: settings.value.showReadTime || settings.value.show_reading_time },
    { key: 'comments', value: `${postCommentsCount} Comments`, icon: MessageSquare, show: settings.value.showComments }
])

const activeMetaItems = computed(() => metaItems.value.filter(item => item.show))

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => {
    return getLayoutStyles(settings.value, props.device)
})

const alignmentClass = computed(() => {
    const align = getVal(settings.value, 'alignment', props.device) || 'left'
    if (align === 'center') return 'justify-center'
    if (align === 'right' || align === 'end') return 'justify-end'
    return 'justify-start'
})

const textStyles = computed(() => {
  return getTypographyStyles(settings.value, '', props.device)
})

const linkStyles = computed(() => {
    return getTypographyStyles(settings.value, 'link_', props.device)
})
</script>

<style scoped>
.post-meta-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.post-meta-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>

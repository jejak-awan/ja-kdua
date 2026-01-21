<template>
  <BaseBlock :module="module" :settings="settings" class="post-meta-block" :style="wrapperStyles">
    <div class="meta-inner flex flex-wrap items-center gap-x-2 gap-y-1">
        <template v-for="(item, index) in activeMetaItems" :key="item.key">
            <span class="meta-item flex items-center gap-1">
                <component v-if="item.icon" :is="item.icon" class="w-3.5 h-3.5 opacity-70" />
                <span v-if="item.key === 'author'">By </span>
                <a 
                    v-if="item.link" 
                    :href="mode === 'view' ? item.link : null" 
                    :style="linkStyles"
                    class="hover:opacity-70 transition-opacity"
                >{{ item.value }}</a>
                <span v-else>{{ item.value }}</span>
            </span>
            <span v-if="index < activeMetaItems.length - 1" class="meta-separator opacity-40">{{ separator }}</span>
        </template>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Calendar, User, Tag, Clock, MessageSquare } from 'lucide-vue-next'
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
const postAuthor = inject('postAuthor', 'John Doe')
const postAuthorUrl = inject('postAuthorUrl', '#')
const postDate = inject('postDate', 'January 10, 2026')
const postCategory = inject('postCategory', 'Technology')
const postCategoryUrl = inject('postCategoryUrl', '#')
const postReadTime = inject('postReadTime', '5 min read')
const postCommentsCount = inject('postCommentsCount', 12)

const separator = computed(() => settings.value.separator || '•')

const metaItems = computed(() => [
    { key: 'author', value: postAuthor, link: postAuthorUrl, icon: User, show: settings.value.showAuthor !== false },
    { key: 'date', value: postDate, icon: Calendar, show: settings.value.showDate !== false },
    { key: 'category', value: postCategory, link: postCategoryUrl, icon: Tag, show: settings.value.showCategories !== false || settings.value.showCategory !== false },
    { key: 'readTime', value: postReadTime, icon: Clock, show: settings.value.showReadTime || settings.value.show_reading_time },
    { key: 'comments', value: `${postCommentsCount} Comments`, icon: MessageSquare, show: settings.value.showComments }
])

const activeMetaItems = computed(() => metaItems.value.filter(item => item.show))

const wrapperStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, '', device.value)
  
  // Alignment handling
  const alignment = getResponsiveValue(settings.value, 'alignment', device.value)
  if (alignment) {
      if (alignment.includes('center')) styles.justifyContent = 'center'
      else if (alignment.includes('end') || alignment.includes('right')) styles.justifyContent = 'flex-end'
      else styles.justifyContent = 'flex-start'
  } else if (styles.textAlign) {
      if (styles.textAlign === 'center') styles.justifyContent = 'center'
      else if (styles.textAlign === 'right') styles.justifyContent = 'flex-end'
      else styles.justifyContent = 'flex-start'
  }

  return styles
})

const linkStyles = computed(() => {
    return getTypographyStyles(settings.value, 'link_', device.value)
})
</script>

<style scoped>
.post-meta-block { width: 100%; display: flex; }
.meta-inner { width: 100%; justify-content: inherit; }
</style>

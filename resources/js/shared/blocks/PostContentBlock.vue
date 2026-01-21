<template>
  <BaseBlock :module="module" :settings="settings" class="post-content-block" :style="contentStyles">
    <div v-if="mode === 'edit'" class="builder-content-container">
        <!-- In builder mode, we might want to show either real content or a placeholder -->
         <div v-if="!settings.content && !postContent" class="opacity-50 italic">
             Post content placeholder. In the actual post, this will display the full article content.
         </div>
         <div v-else class="post-content-inner" v-html="displayContent" />
    </div>
    <div v-else class="post-content-inner" v-html="postContent" />
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
  getTypographyStyles
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

// Dynamic content injection
const postContent = inject('postContent', '')

const displayContent = computed(() => {
    return settings.value.content || postContent || '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>'
})

const contentStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, '', device.value)
  
  const linkColors = getTypographyStyles(settings.value, 'link_', device.value)
  styles['--link-color'] = linkColors.color || 'inherit'
  styles['--link-font-weight'] = linkColors.fontWeight || 'inherit'
  styles['--link-text-decoration'] = linkColors.textDecoration || 'underline'
  
  return styles
})
</script>

<style scoped>
.post-content-block { width: 100%; }
.post-content-inner :deep(p) { margin: 0 0 1.5em; }
.post-content-inner :deep(h2), .post-content-inner :deep(h3) { margin: 2em 0 1em; font-weight: 600; }
.post-content-inner :deep(a) { 
  color: var(--link-color); 
  font-weight: var(--link-font-weight); 
  text-decoration: var(--link-text-decoration); 
}
.post-content-inner :deep(blockquote) { margin: 1.5em 0; padding: 1em 1.5em; border-left: 4px solid #e0e0e0; font-style: italic; color: #666; }
</style>

<template>
  <BaseBlock :module="module" :settings="settings" tag="article" class="fullwidth-post-content-block" :style="containerStyles">
    <div class="content-inner" :style="innerStyles">
      <div v-if="mode === 'edit'" class="edit-placeholder">
          <p :style="paragraphStyles">
            This is where the post content will be rendered. In the builder, we show this placeholder. 
            The actual post content is dynamically loaded in the live view.
          </p>
          <h2 :style="headingStyles">Section Heading</h2>
          <p :style="paragraphStyles">
            Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
          </p>
          <blockquote :style="blockquoteStyles">
            "Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit."
          </blockquote>
      </div>
      <div v-else class="post-content-render" v-html="postContent" />
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
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

// In a real scenario, this would come from a post provider or prop
const postContent = inject('postContent', '<p>Post content not found.</p>')

const containerStyles = computed(() => {
  return { width: '100%' }
})

const innerStyles = computed(() => {
  const maxWidth = getResponsiveValue(settings.value, 'maxWidth', device.value) || 1200
  return {
    maxWidth: settings.value.contentWidth === 'boxed' ? `${maxWidth}px` : '100%',
    margin: '0 auto',
    padding: '0 24px'
  }
})

const paragraphStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'text_', device.value)
  styles.marginBottom = `${settings.value.paragraphSpacing || 24}px`
  return styles
})

const headingStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'heading_', device.value)
  styles.marginTop = `${settings.value.headingSpacing || 32}px`
  styles.marginBottom = '16px'
  return styles
})

const linkStyles = computed(() => getTypographyStyles(settings.value, 'link_', device.value))

const blockquoteStyles = computed(() => ({
  borderLeft: `4px solid ${settings.value.blockquoteBorderColor || '#2059ea'}`,
  backgroundColor: settings.value.blockquoteBackgroundColor || '#f8f9fa',
  padding: '20px 24px',
  margin: '24px 0',
  fontStyle: 'italic'
}))
</script>

<style scoped>
.fullwidth-post-content-block { width: 100%; }
.content-inner:deep(a) { 
  color: v-bind('linkStyles.color || "#2059ea"'); 
  text-decoration: v-bind('linkStyles.textDecoration || "underline"');
}
.content-inner:deep(a:hover) { opacity: 0.8; }
.content-inner:deep(img) { 
  max-width: 100%; 
  height: auto; 
  border-radius: v-bind('(settings.imageBorderRadius || 8) + "px"');
}
.edit-placeholder { opacity: 0.7; }
</style>

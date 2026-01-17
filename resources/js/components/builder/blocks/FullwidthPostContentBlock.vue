<template>
  <article class="fullwidth-post-content-block" :style="containerStyles">
    <div class="content-inner" :style="innerStyles">
      <p :style="paragraphStyles">
        This is where the post content will be rendered. The actual content is dynamically loaded from your post data.
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
      </p>
      <h2 :style="headingStyles">Section Heading</h2>
      <p :style="paragraphStyles">
        Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
        Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
      </p>
      <blockquote :style="blockquoteStyles">
        "Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."
      </blockquote>
      <p :style="paragraphStyles">
        Curabitur pretium tincidunt lacus. Nulla gravida orci a odio. Nullam varius, turpis et commodo pharetra.
      </p>
    </div>
  </article>
</template>

<script setup>
import { computed, inject } from 'vue'
import { 
  getSpacingStyles, 
  getBackgroundStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles,
  getTypographyStyles,
  getResponsiveValue,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const containerStyles = computed(() => {
  const styles = { width: '100%' }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  return styles
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
.content-inner a { 
  color: v-bind('linkStyles.color || "#2059ea"'); 
  text-decoration: v-bind('linkStyles.textDecoration || "underline"');
}
.content-inner a:hover { opacity: 0.8; }
.content-inner img { 
  max-width: 100%; 
  height: auto; 
  border-radius: v-bind('(settings.imageBorderRadius || 8) + "px"');
}
</style>

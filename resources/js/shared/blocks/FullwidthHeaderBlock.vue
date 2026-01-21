<template>
  <BaseBlock :module="module" :settings="settings" class="fullwidth-header-block" :style="wrapperStyles">
    <div v-if="settings.overlayColor" class="header-overlay" :style="overlayStyles" />
    <div class="header-content" :style="contentStyles">
      <h1 
        class="header-title" 
        :style="titleStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateText('title', $event)"
      >{{ settings.title || 'Welcome' }}</h1>
      
      <p 
        v-if="settings.subtitle || mode === 'edit'" 
        class="header-subtitle" 
        :style="subtitleStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateText('subtitle', $event)"
      >{{ settings.subtitle || 'Your subtitle goes here' }}</p>
      
      <div 
        class="header-buttons" 
        :style="{ justifyContent: horizontalAlignment === 'right' ? 'flex-end' : horizontalAlignment === 'center' ? 'center' : 'flex-start' }"
      >
        <a 
          :href="settings.buttonUrl || '#'" 
          class="header-button header-button--primary" 
          :style="button1Styles"
          :contenteditable="mode === 'edit'"
          @blur="updateText('buttonText', $event)"
          @click.prevent="handleLinkClick(settings.buttonUrl)"
        >{{ settings.buttonText || 'Get Started' }}</a>
        
        <a 
          v-if="settings.showButton2 !== false || mode === 'edit'" 
          :href="settings.button2Url || '#'" 
          class="header-button header-button--secondary" 
          :style="button2Styles"
          :contenteditable="mode === 'edit'"
          @blur="updateText('button2Text', $event)"
          @click.prevent="handleLinkClick(settings.button2Url)"
        >{{ settings.button2Text || 'Learn More' }}</a>
      </div>
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

const horizontalAlignment = computed(() => getResponsiveValue(settings.value, 'textAlignment', device.value) || 'center')

const updateText = (key, event) => {
    if (props.mode !== 'edit') return
    const value = event.target.innerText
    builder?.updateModuleSettings(props.module.id, { [key]: value })
}

const handleLinkClick = (url) => {
    if (props.mode === 'edit') return
    if (url) window.location.href = url
}

const wrapperStyles = computed(() => {
  const styles = { 
    position: 'relative', 
    display: 'flex', 
    flexDirection: 'column',
    overflow: 'hidden',
    minHeight: `${getResponsiveValue(settings.value, 'height', device.value) || 400}px`,
    textAlign: horizontalAlignment.value
  }
  
  const vAlign = settings.value.contentAlignment || 'center'
  styles.justifyContent = vAlign === 'top' ? 'flex-start' : vAlign === 'bottom' ? 'flex-end' : 'center'
  return styles
})

const contentStyles = computed(() => {
  const alignment = horizontalAlignment.value
  return { 
    position: 'relative', 
    zIndex: 2, 
    width: '100%',
    maxWidth: '1200px',
    margin: alignment === 'center' ? '0 auto' : alignment === 'right' ? '0 0 0 auto' : '0 auto 0 0',
    padding: '40px 20px'
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const subtitleStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'subtitle_', device.value)
    return {
        ...styles,
        marginTop: '16px'
    }
})

const button1Styles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'button1_', device.value)
  return {
    ...styles,
    backgroundColor: settings.value.buttonBackgroundColor || styles.backgroundColor || '#3b82f6',
    color: settings.value.buttonTextColor || styles.color || '#ffffff'
  }
})

const button2Styles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'button2_', device.value)
  return {
    ...styles,
    backgroundColor: settings.value.button2BackgroundColor || styles.backgroundColor || 'transparent',
    color: settings.value.button2TextColor || styles.color || '#3b82f6',
    border: `1px solid ${settings.value.button2TextColor || styles.color || '#3b82f6'}`
  }
})

const overlayStyles = computed(() => ({ 
  position: 'absolute', 
  inset: 0, 
  backgroundColor: settings.value.overlayColor || 'transparent',
  zIndex: 1
}))
</script>

<style scoped>
.fullwidth-header-block { width: 100%; }
.header-overlay { position: absolute; inset: 0; pointer-events: none; }
.header-buttons { display: flex; gap: 16px; justify-content: inherit; flex-wrap: wrap; margin-top: 32px; }
.header-button { display: inline-block; padding: 14px 32px; text-decoration: none; border-radius: 6px; transition: transform 0.2s, opacity 0.2s; cursor: pointer; }
.header-button:hover { transform: translateY(-2px); opacity: 0.9; }
[contenteditable]:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}
</style>

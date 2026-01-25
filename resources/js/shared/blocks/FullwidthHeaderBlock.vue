<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="fullwidth-header-block transition-all duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Fullwidth Header'"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings }">
      <div v-if="blockSettings.overlayColor" class="header-overlay absolute inset-0 pointer-events-none z-1" :style="overlayStyles" />
      <div class="header-content relative z-[2] w-full max-w-[1200px] px-5 py-10" :style="contentStyles">
        <h1 
          class="header-title font-bold" 
          :style="titleStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateText('title', $event)"
        >{{ blockSettings.title || 'Welcome' }}</h1>
        
        <p 
          v-if="blockSettings.subtitle || mode === 'edit'" 
          class="header-subtitle mt-4" 
          :style="subtitleStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateText('subtitle', $event)"
        >{{ blockSettings.subtitle || 'Your subtitle goes here' }}</p>
        
        <div 
          class="header-buttons flex gap-4 flex-wrap mt-8" 
          :style="{ justifyContent: horizontalAlignment === 'right' ? 'flex-end' : horizontalAlignment === 'center' ? 'center' : 'flex-start' }"
        >
          <a 
            :href="blockSettings.buttonUrl || '#'" 
            class="header-button header-button--primary inline-block py-3.5 px-8 rounded-md transition-all duration-200 cursor-pointer hover:-translate-y-0.5 hover:opacity-90" 
            :style="button1Styles"
            :contenteditable="mode === 'edit'"
            @blur="updateText('buttonText', $event)"
            @click.prevent="handleLinkClick(blockSettings.buttonUrl)"
          >{{ blockSettings.buttonText || 'Get Started' }}</a>
          
          <a 
            v-if="blockSettings.showButton2 !== false || mode === 'edit'" 
            :href="blockSettings.button2Url || '#'" 
            class="header-button header-button--secondary inline-block py-3.5 px-8 rounded-md transition-all duration-200 cursor-pointer hover:-translate-y-0.5 hover:opacity-90" 
            :style="button2Styles"
            :contenteditable="mode === 'edit'"
            @blur="updateText('button2Text', $event)"
            @click.prevent="handleLinkClick(blockSettings.button2Url)"
          >{{ blockSettings.button2Text || 'Learn More' }}</a>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
    getVal,
    getTypographyStyles,
    getLayoutStyles,
    getResponsiveValue 
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const horizontalAlignment = computed(() => getVal(settings.value, 'textAlignment', props.device) || 'center')

const updateText = (key: string, event: FocusEvent) => {
    if (props.mode !== 'edit' || !event.target) return
    const value = (event.target as HTMLElement).innerText
    // @ts-ignore
    window.builder?.updateModuleSettings(props.module.id, { [key]: value })
}

const handleLinkClick = (url: string) => {
    if (props.mode === 'edit') return
    if (url) window.location.href = url
}

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const contentStyles = computed(() => {
    const layoutStyles = getLayoutStyles(settings.value, props.device)
    const alignment = horizontalAlignment.value
    const vAlign = settings.value.contentAlignment || 'center'
    
    return { 
        ...layoutStyles,
        display: 'flex',
        flexDirection: 'column',
        minHeight: `${getResponsiveValue(settings.value, 'height', props.device) || 400}px`,
        textAlign: alignment,
        justifyContent: vAlign === 'top' ? 'flex-start' : vAlign === 'bottom' ? 'flex-end' : 'center',
        margin: alignment === 'center' ? '0 auto' : alignment === 'right' ? '0 0 0 auto' : '0 auto 0 0',
    }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const subtitleStyles = computed(() => getTypographyStyles(settings.value, 'subtitle_', props.device))

const button1Styles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'button1_', props.device)
  return {
    ...styles,
    backgroundColor: settings.value.buttonBackgroundColor || styles.backgroundColor || '#3b82f6',
    color: settings.value.buttonTextColor || styles.color || '#ffffff'
  }
})

const button2Styles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'button2_', props.device)
  return {
    ...styles,
    backgroundColor: settings.value.button2BackgroundColor || styles.backgroundColor || 'transparent',
    color: settings.value.button2TextColor || styles.color || '#3b82f6',
    border: `1px solid ${settings.value.button2TextColor || styles.color || '#3b82f6'}`
  }
})

const overlayStyles = computed(() => ({ 
  backgroundColor: settings.value.overlayColor || 'transparent',
}))
</script>

<style scoped>
.fullwidth-header-block { width: 100%; }
.fullwidth-header-block {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.fullwidth-header-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
[contenteditable]:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}
</style>

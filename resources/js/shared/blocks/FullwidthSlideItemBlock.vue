<template>
  <div 
    class="fullwidth-slide-item-block" 
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Slide Item'"
    :class="{ 'slider-slide--active': isActive }"
    :style="slideStyles"
  >
    <div class="slide-overlay absolute inset-0 z-1" :style="overlayStyles" />
    <div class="slide-content relative z-[2] w-full max-w-[1200px] mx-auto px-5" :style="contentStyles">
      <h2 
        class="slide-title m-0" 
        :style="titleStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateText('title', $event)"
      >{{ settings.title || 'Slide Title' }}</h2>
      
      <p 
        v-if="settings.subtitle || mode === 'edit'" 
        class="slide-subtitle mt-4" 
        :style="subtitleStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateText('subtitle', $event)"
      >{{ settings.subtitle || 'Slide subtitle goes here' }}</p>
      
      <div v-if="settings.buttonText || mode === 'edit'" class="mt-8">
        <a 
          :href="settings.buttonUrl || '#'" 
          class="slide-button inline-block py-3.5 px-8 rounded-md no-underline font-semibold transition-transform duration-200 hover:-translate-y-0.5" 
          :style="buttonStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateText('buttonText', $event)"
          @click.prevent="handleLinkClick"
        >{{ settings.buttonText || 'Learn More' }}</a>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import { getTypographyStyles, getResponsiveValue } from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  index: number
}>()

// @ts-ignore
const builder = inject<any>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

// Injected from FullwidthSliderBlock
const fullwidthSliderState = inject<any>('fullwidthSliderState', {
    parentSettings: computed(() => ({})),
    currentSlide: computed(() => 0),
    mode: computed(() => 'view')
})

const parentSettings = computed(() => fullwidthSliderState.parentSettings.value || {})
const isActive = computed(() => fullwidthSliderState.currentSlide.value === props.index)
const mode = computed(() => fullwidthSliderState.mode.value)

const updateText = (key: string, event: FocusEvent) => {
    if (mode.value !== 'edit' || !event.target) return
    const value = (event.target as HTMLElement).innerText
    builder?.updateModuleSettings(props.module.id, { [key]: value })
}

const handleLinkClick = () => {
    if (mode.value === 'edit') return
    if (settings.value.buttonUrl) window.location.href = settings.value.buttonUrl
}

const slideStyles = computed(() => {
    const styles: Record<string, any> = {
        position: 'absolute',
        inset: 0,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        transition: 'opacity 0.5s ease-in-out',
        opacity: isActive.value || mode.value === 'edit' ? 1 : 0,
        zIndex: isActive.value ? 10 : 1,
        backgroundColor: '#333'
    }
    
    // Slide specific background
    if (settings.value.backgroundImage) {
        styles.backgroundImage = `url(${settings.value.backgroundImage})`
        styles.backgroundSize = 'cover'
        styles.backgroundPosition = 'center'
    } else if (settings.value.backgroundColor) {
        styles.backgroundColor = settings.value.backgroundColor
    }
    
    return styles
})

const overlayStyles = computed(() => ({ 
  backgroundColor: parentSettings.value.overlayColor || 'rgba(0,0,0,0.4)',
}))

const contentStyles = computed(() => ({ 
  textAlign: getResponsiveValue(parentSettings.value, 'contentAlignment', device.value) || 'center', 
}))

const titleStyles = computed(() => getTypographyStyles(parentSettings.value, 'title_', device.value))
const subtitleStyles = computed(() => getTypographyStyles(parentSettings.value, 'subtitle_', device.value))

const buttonStyles = computed(() => {
  const styles = getTypographyStyles(parentSettings.value, 'button_', device.value)
  return {
    ...styles,
    backgroundColor: parentSettings.value.buttonBackgroundColor || styles.backgroundColor || '#fff',
    color: parentSettings.value.buttonTextColor || styles.color || '#333',
  }
})
</script>

<style scoped>
.fullwidth-slide-item-block { width: 100%; height: 100%; }
[contenteditable]:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}
</style>

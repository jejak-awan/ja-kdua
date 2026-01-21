<template>
  <BaseBlock :module="module" :settings="settings" tag="figure" class="fullwidth-image-block" :style="wrapperStyles">
    <component 
      :is="settings.link ? 'a' : 'div'" 
      :href="mode === 'view' ? (settings.link || null) : null" 
      :target="settings.target || '_self'" 
      class="image-container"
      @click="handleLinkClick"
    >
      <ImageIcon v-if="!settings.image" class="placeholder-icon" />
      <img v-else :src="settings.image" :alt="settings.alt" :style="imageStyles" />
      
      <div v-if="settings.showOverlay || mode === 'edit'" class="image-overlay" :style="overlayStyles">
        <span 
          v-if="settings.overlayText || mode === 'edit'" 
          class="overlay-text" 
          :style="textStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateText('overlayText', $event)"
        >{{ settings.overlayText || (mode === 'edit' ? 'Overlay Text' : '') }}</span>
      </div>
    </component>
    
    <figcaption 
      v-if="settings.caption || mode === 'edit'" 
      class="image-caption" 
      :style="captionStyles"
      :contenteditable="mode === 'edit'"
      @blur="updateText('caption', $event)"
    >{{ settings.caption || (mode === 'edit' ? 'Image caption goes here' : '') }}</figcaption>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Image as ImageIcon } from 'lucide-vue-next'
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

const updateText = (key, event) => {
    if (props.mode !== 'edit') return
    const value = event.target.innerText
    builder?.updateModuleSettings(props.module.id, { [key]: value })
}

const handleLinkClick = (event) => {
    if (props.mode === 'edit' && settings.value.link) {
        event.preventDefault()
    }
}

const wrapperStyles = computed(() => {
  return { 
    margin: 0, 
    position: 'relative', 
    overflow: 'hidden', 
    display: 'flex', 
    flexDirection: 'column',
    alignItems: 'center', 
    justifyContent: 'center',
    height: `${getResponsiveValue(settings.value, 'height', device.value) || 500}px`
  }
})

const imageStyles = computed(() => ({ 
  width: '100%', 
  height: '100%', 
  objectFit: settings.value.objectFit || 'cover' 
}))

const overlayStyles = computed(() => ({ 
  position: 'absolute', 
  inset: 0, 
  backgroundColor: settings.value.overlayColor || 'rgba(0,0,0,0.4)', 
  display: 'flex', 
  alignItems: 'center', 
  justifyContent: 'center',
  opacity: (settings.showOverlay || props.mode === 'edit') ? 1 : 0,
  transition: 'opacity 0.3s'
}))

const textStyles = computed(() => getTypographyStyles(settings.value, 'overlay_', device.value))
const captionStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'caption_', device.value)
    return {
        ...styles,
        width: '100%',
        padding: '12px'
    }
})
</script>

<style scoped>
.fullwidth-image-block { width: 100%; }
.image-container { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; position: relative; }
.placeholder-icon { width: 64px; height: 64px; color: #ccc; }
[contenteditable]:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}
</style>

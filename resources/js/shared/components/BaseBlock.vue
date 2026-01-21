<template>
  <div 
    class="ja-block-wrapper" 
    :class="[
      mode === 'edit' ? 'ja-block-edit' : 'ja-block-view',
      isSelected ? 'ja-block-selected' : '',
      customClass
    ]"
    :style="wrapperStyles"
    :id="cssId"
    @click="onBlockClick"
  >
    <!-- Background Video Support -->
    <BackgroundMedia v-if="hasBackgroundVideo" :settings="settings" :device="device" />

    <!-- Slot for the actual block content -->
    <slot 
      :mode="mode" 
      :settings="settings" 
      :device="device"
      :styles="contentStyles"
    />

    <!-- Shared Overlay/Controls for Builder Mode -->
    <template v-if="mode === 'edit'">
      <div v-if="isSelected" class="ja-block-controls">
         <!-- Add block specific controls here if needed -->
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, inject, defineAsyncComponent } from 'vue'
const BackgroundMedia = defineAsyncComponent(() => import('./BackgroundMedia.vue'))
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles,
  getLayoutStyles,
  getVisibilityStyles,
  getPositioningStyles,
  getAnimationStyles,
  getColorVariables,
  getVal
} from '../utils/styleUtils'
import { useResponsiveDevice } from '../utils/useResponsiveDevice'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }, // 'edit' or 'view'
  device: { type: String, default: null } // Optional override
})

const builder = inject('builder', null)
const detectedDevice = useResponsiveDevice()
const device = computed(() => props.device || detectedDevice.value)

const settings = computed(() => props.module?.settings || {})
const isSelected = computed(() => props.mode === 'edit' && builder?.selectedModuleId === props.module.id)

const wrapperStyles = computed(() => {
  const s = {}
  
  Object.assign(s, getBackgroundStyles(settings.value, device.value))
  Object.assign(s, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(s, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(s, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(s, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(s, getSizingStyles(settings.value, device.value))
  Object.assign(s, getLayoutStyles(settings.value, device.value))
  
  // New Utils
  Object.assign(s, getVisibilityStyles(settings.value, device.value))
  Object.assign(s, getPositioningStyles(settings.value, device.value))
  Object.assign(s, getAnimationStyles(settings.value, device.value))
  
  // Color Variables for Hover
  Object.assign(s, getColorVariables(settings.value, props.module.hoverSettings || {}, device.value))
  
  return s
})

const contentStyles = computed(() => {
  // Styles passed to the child slot (e.g. typography)
  return {} 
})

const cssId = computed(() => getVal(settings.value, 'cssId', device.value) || getVal(settings.value, '_css_id', device.value))

const customClass = computed(() => {
    let classes = [getVal(settings.value, 'cssClass', device.value) || '']
    
    // Hover Support Classes (Tailwind arbitrary variants for CSS vars)
    // We assume the variable is set via inline style, so we just toggle it on hover
    // But wait, to toggle a variable on hover requires a class that redefines it on hover?
    // BlockRenderer used: hover:[--text-color:var(--hover-text-color)]
    
    const hover = props.module.hoverSettings || {}
    const hasHoverText = getVal(hover, 'textColor', device.value) || getVal(hover, 'color', device.value)
    const hasHoverBg = getVal(hover, 'backgroundColor', device.value) || getVal(hover, 'bgColor', device.value)
    
    if (hasHoverText) classes.push('hover:[--text-color:var(--hover-text-color)]')
    if (hasHoverBg) classes.push('hover:[--bg-color:var(--hover-bg-color)]')
    
    return classes.join(' ')
})

const hasBackgroundVideo = computed(() => {
  return getVal(settings.value, 'backgroundVideoMp4', device.value) || 
         getVal(settings.value, 'backgroundVideoWebm', device.value)
})

const onBlockClick = (e) => {
  if (props.mode === 'edit' && builder) {
    builder.selectModule(props.module.id)
    e.stopPropagation()
  }
}
</script>

<style scoped>
.ja-block-wrapper {
  position: relative;
  transition: all 0.2s ease;
}

.ja-block-edit {
  cursor: pointer;
}

.ja-block-edit:hover {
  outline: 1px dashed rgba(var(--primary), 0.5);
}

.ja-block-selected {
  outline: 2px solid rgb(var(--primary)) !important;
}

.ja-block-controls {
  position: absolute;
  top: -30px;
  right: 0;
  display: flex;
  gap: 4px;
}
</style>

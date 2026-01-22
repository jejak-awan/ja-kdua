<template>
  <div 
    class="ja-block-wrapper" 
    ref="blockRef"
    :class="[
      mode === 'edit' ? 'ja-block-edit' : 'ja-block-view',
      isSelected ? 'ja-block-selected' : '',
      customClass
    ]"
    :style="[wrapperStyles, interactionStyles]"
    :id="cssId"
    v-bind="customAttributes"
    @click="onBlockClick"
    @mouseenter="onInteractionEvent('mouseenter')"
    @mouseleave="onInteractionEvent('mouseleave')"
  >
    <component :is="'style'" v-if="customCssInternal">
      {{ customCssInternal }}
    </component>
    <!-- Background Video Support -->
    <BackgroundMedia v-if="hasBackgroundVideo" :settings="settings" :device="device" />

    <!-- Slot for the actual block content -->
    <slot 
      :mode="mode" 
      :settings="settings" 
      :device="device"
      :styles="contentStyles"
      :get-attributes="getAttributes"
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
import { computed, inject, defineAsyncComponent, onMounted, onUnmounted, ref } from 'vue'
const BackgroundMedia = defineAsyncComponent(() => import('./BackgroundMedia.vue'))
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles,
  getLayoutStyles,
  getFilterStyles,
  getTransformStyles,
  getVisibilityStyles,
  getVisibilityClasses,
  getPositioningStyles,
  getAnimationStyles,
  getAnimationClasses,
  getTransitionStyles,
  getColorVariables,
  getVal
} from '../utils/styleUtils'
import { interactionManager } from '../utils/InteractionManager'
import { useResponsiveDevice } from '../utils/useResponsiveDevice'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }, // 'edit' or 'view'
  device: { type: String, default: null } // Optional override
})

const blockRef = ref(null)
let observer = null

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
  Object.assign(s, getFilterStyles(settings.value, device.value))
  Object.assign(s, getTransformStyles(settings.value, device.value))
  
  // New Utils
  Object.assign(s, getVisibilityStyles(settings.value, device.value))
  Object.assign(s, getPositioningStyles(settings.value, device.value))
  Object.assign(s, getAnimationStyles(settings.value, device.value))
  Object.assign(s, getTransitionStyles(settings.value, device.value))
  
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
    
    // Advanced Classes
    classes.push(getVisibilityClasses(settings.value, device.value, props.mode === 'edit'))
    classes.push(getAnimationClasses(settings.value, device.value))
    
    // Hover Support Classes (Tailwind arbitrary variants for CSS vars)
    // We assume the variable is set via inline style, so we just toggle it on hover
    // But wait, to toggle a variable on hover requires a class that redefines it on hover?
    // BlockRenderer used: hover:[--text-color:var(--hover-text-color)]
    
    const hover = props.module.hoverSettings || {}
    const hasHoverText = getVal(hover, 'textColor', device.value) || getVal(hover, 'color', device.value)
    const hasHoverBg = getVal(hover, 'backgroundColor', device.value) || getVal(hover, 'bgColor', device.value)
    
    if (hasHoverText) classes.push('hover:[--text-color:var(--hover-text-color)]')
    if (hasHoverBg) classes.push('hover:[--bg-color:var(--hover-bg-color)]')
    
    // Interaction Classes
    const interactionState = interactionManager.getBlockState(props.module.id)
    if (interactionState.classes) {
        classes.push(...interactionState.classes)
    }
    
    return classes.join(' ')
})

const interactionStyles = computed(() => {
    const state = interactionManager.getBlockState(props.module.id)
    const s = {}
    if (state.activeAnimation) {
        s.animationName = state.activeAnimation
        s.animationDuration = '1s'
        s.animationIterationCount = '1'
    }
    return s
})

const customAttributes = computed(() => {
    const attrs = {}
    const list = getVal(settings.value, 'attributes', device.value) || []
    if (Array.isArray(list)) {
        list.forEach(attr => {
            if (attr.name && attr.name !== 'id' && attr.name !== 'class') {
                attrs[attr.name] = attr.value
            }
        })
    }
    return attrs
})

const customCssInternal = computed(() => {
    const css = getVal(settings.value, 'custom_css', device.value)
    if (!css) return ''
    
    // Replace 'selector' with the actual ID
    const id = cssId.value || `#${props.module.id}`
    const selector = id.startsWith('#') ? id : `#${id}`
    return css.replace(/selector/g, selector)
})

const getAttributes = (prefix = '') => {
    const key = prefix ? `${prefix}_attributes` : 'attributes'
    const list = getVal(settings.value, key, device.value) || []
    const attrs = {}
    if (Array.isArray(list)) {
        list.forEach(attr => {
            // We strip 'id' and 'class' as they are handled separately or could break things
            if (attr.name && attr.name !== 'id' && attr.name !== 'class') {
                attrs[attr.name] = attr.value
            }
        })
    }
    return attrs
}

const hasBackgroundVideo = computed(() => {
  return getVal(settings.value, 'backgroundVideoMp4', device.value) || 
         getVal(settings.value, 'backgroundVideoWebm', device.value)
})

const onBlockClick = (e) => {
  onInteractionEvent('click')
  if (props.mode === 'edit' && builder) {
    builder.selectModule(props.module.id)
    e.stopPropagation()
  }
}

const onInteractionEvent = (triggerType) => {
    const interactions = getVal(settings.value, 'interactions', device.value) || []
    if (Array.isArray(interactions)) {
        interactions.forEach(inter => {
            if (inter.trigger === triggerType && inter.action) {
                const action = inter.action
                const targetId = inter.targetId || props.module.id // Default to self
                const params = {
                    className: inter.className,
                    animationName: inter.animationName
                }

                interactionManager.triggerAction(triggerType, action, targetId, params)
            }
        })
    }
}

onMounted(() => {
    const interactions = getVal(settings.value, 'interactions', device.value) || []
    const hasViewportTriggers = Array.isArray(interactions) && interactions.some(i => i.trigger === 'viewportenter' || i.trigger === 'viewportexit')
    
    if (hasViewportTriggers && blockRef.value) {
        observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    onInteractionEvent('viewportenter')
                } else {
                    onInteractionEvent('viewportexit')
                }
            })
        }, { threshold: 0.1 })
        
        observer.observe(blockRef.value)
    }
})

onUnmounted(() => {
    if (observer) {
        observer.disconnect()
        observer = null
    }
})
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

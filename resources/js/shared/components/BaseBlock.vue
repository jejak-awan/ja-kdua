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

<script setup lang="ts">
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
import type { BlockInstance, BlockProps, BuilderInstance } from '../../types/builder'

const props = withDefaults(defineProps<BlockProps>(), {
  mode: 'view',
  device: null
})

const blockRef = ref<HTMLElement | null>(null)
let observer: IntersectionObserver | null = null

const builder = inject<BuilderInstance>('builder')
const detectedDevice = useResponsiveDevice()
const device = computed(() => props.device || detectedDevice.value)

const settings = computed(() => props.module?.settings || {})
const isSelected = computed(() => props.mode === 'edit' && builder?.selectedModuleId.value === props.module.id)

const wrapperStyles = computed(() => {
  const s: Record<string, any> = {}
  
  const background = getBackgroundStyles(settings.value, device.value)
  const padding = getSpacingStyles(settings.value, 'padding', device.value, 'padding')
  const margin = getSpacingStyles(settings.value, 'margin', device.value, 'margin')
  const border = getBorderStyles(settings.value, 'border', device.value)
  const boxShadow = getBoxShadowStyles(settings.value, 'boxShadow', device.value)
  const sizing = getSizingStyles(settings.value, device.value)
  const layout = getLayoutStyles(settings.value, device.value)
  const filters = getFilterStyles(settings.value, device.value)
  const transform = getTransformStyles(settings.value, device.value)
  const visibility = getVisibilityStyles(settings.value, device.value)
  const positioning = getPositioningStyles(settings.value, device.value)
  const animation = getAnimationStyles(settings.value, device.value)
  const transition = getTransitionStyles(settings.value, device.value)
  const colors = getColorVariables(settings.value, (props.module as any).hoverSettings || {}, device.value)

  if (props.manualStyles) {
      // Properties that define outer layout stays on wrapper
      Object.assign(s, visibility, positioning, margin, sizing, layout, animation, colors)
  } else {
      // Everything on wrapper
      Object.assign(s, background, padding, margin, border, boxShadow, sizing, layout, filters, transform, visibility, positioning, animation, transition, colors)
  }
  
  // Ensure structural blocks fill containers while keeping content blocks responsive
  const isStructural = ['section', 'row', 'column'].includes(props.module.type)
  if (isStructural) {
      s.width = '100%'
      s.height = '100%'
      if (props.mode === 'edit') {
          s.overflow = 'visible !important'
      }
  } else {
      s.width = s.width || '100%' // Content modules default to 100%
      if (props.mode === 'edit') {
          s.overflow = 'visible !important'
      }
  }
  
  return s
})

const contentStyles = computed(() => {
    if (!props.manualStyles) return {}
    
    return {
        ...getBackgroundStyles(settings.value, device.value),
        ...getSpacingStyles(settings.value, 'padding', device.value, 'padding'),
        ...getBorderStyles(settings.value, 'border', device.value),
        ...getBoxShadowStyles(settings.value, 'boxShadow', device.value),
        ...getFilterStyles(settings.value, device.value),
        ...getTransformStyles(settings.value, device.value),
        ...getTransitionStyles(settings.value, device.value),
    }
})

const cssId = computed(() => getVal(settings.value, 'cssId', device.value) || getVal(settings.value, '_css_id', device.value))

const customClass = computed(() => {
    let classes = [getVal(settings.value, 'cssClass', device.value) || '']
    
    classes.push(getVisibilityClasses(settings.value, device.value, props.mode === 'edit'))
    classes.push(getAnimationClasses(settings.value, device.value))
    
    const hover = (props.module as any).hoverSettings || {}
    const hasHoverText = getVal(hover, 'textColor', device.value) || getVal(hover, 'color', device.value)
    const hasHoverBg = getVal(hover, 'backgroundColor', device.value) || getVal(hover, 'bgColor', device.value)
    
    if (hasHoverText) classes.push('hover:[--text-color:var(--hover-text-color)]')
    if (hasHoverBg) classes.push('hover:[--bg-color:var(--hover-bg-color)]')
    
    const interactionState = interactionManager.getBlockState(props.module.id)
    if (interactionState.classes) {
        classes.push(...interactionState.classes)
    }
    
    return classes.join(' ')
})

const interactionStyles = computed(() => {
    const state = interactionManager.getBlockState(props.module.id)
    const s: Record<string, any> = {}
    if (state.activeAnimation) {
        s.animationName = state.activeAnimation
        s.animationDuration = '1s'
        s.animationIterationCount = '1'
    }
    return s
})

const customAttributes = computed(() => {
    const attrs: Record<string, any> = {}
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
    
    const id = cssId.value || `#${props.module.id}`
    const selector = id.startsWith('#') ? id : `#${id}`
    return css.replace(/selector/g, selector)
})

const getAttributes = (prefix = '') => {
    const key = prefix ? `${prefix}_attributes` : 'attributes'
    const list = getVal(settings.value, key, device.value) || []
    const attrs: Record<string, any> = {}
    if (Array.isArray(list)) {
        list.forEach(attr => {
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

const onBlockClick = (e: MouseEvent) => {
  onInteractionEvent('click')
  if (props.mode === 'edit' && builder) {
    builder.selectModule(props.module.id)
    e.stopPropagation()
  }
}

const onInteractionEvent = (triggerType: string) => {
    const interactions = getVal(settings.value, 'interactions', device.value) || []
    if (Array.isArray(interactions)) {
        interactions.forEach(inter => {
            if (inter.trigger === triggerType && inter.action) {
                const action: string = inter.action
                const targetId: string = inter.targetId || props.module.id
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
        
        observer.observe(blockRef.value);
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
  width: 100%;
}

.ja-block-edit {
  cursor: pointer;
}

.ja-block-edit:hover {
  outline: 1px solid rgba(var(--builder-accent-rgb, 32, 89, 234), 0.3);
  transition: outline 0.2s ease;
}

/* Ensure controls are always on top */
.ja-block-controls {
  position: absolute;
  top: -30px;
  right: 0;
  display: flex;
  gap: 4px;
  z-index: 50;
}

/* When selected in edit mode, ensure we can still interact with inner fields if any, 
   but usually we want to capture click for selection. 
   The issue might be the overlay in edit mode. */
</style>

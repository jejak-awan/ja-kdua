<template>
  <div class="button-block" :style="wrapperStyles">
    <a 
      :href="settings.url || '#'" 
      :target="settings.target || '_self'"
      class="button-element"
      :class="buttonClasses"
      :style="buttonStyles"
      @click.prevent
    >
      <component 
        v-if="settings.icon && settings.iconPosition === 'left'" 
        :is="iconComponent" 
        class="button-icon button-icon--left"
      />
      <span class="button-text">{{ settings.text || 'Click Here' }}</span>
      <component 
        v-if="settings.icon && settings.iconPosition === 'right'" 
        :is="iconComponent" 
        class="button-icon button-icon--right"
      />
    </a>
  </div>
</template>

<script setup>
import { computed, defineAsyncComponent, inject } from 'vue'
import { MousePointer } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getFilterStyles,
  getTransformStyles,
  getResponsiveValue 
} from '../core/styleUtils'

const props = defineProps({ module: { type: Object, required: true } })
const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const iconComponent = computed(() => {
  if (!settings.value.icon) return null
  try {
    return defineAsyncComponent(() => 
      import('lucide-vue-next').then(m => m[settings.value.icon] || MousePointer)
    )
  } catch { return MousePointer }
})

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  const alignment = getResponsiveValue(settings.value, 'alignment', device.value) || 'left'
  styles.textAlign = alignment
  
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  return styles
})

const buttonClasses = computed(() => ([
  `button--${getResponsiveValue(settings.value, 'size', device.value) || 'medium'}`,
  getResponsiveValue(settings.value, 'fullWidth', device.value) ? 'button--full-width' : ''
]))

const buttonStyles = computed(() => {
  const styles = {
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    gap: '8px',
    lineHeight: 1,
    textDecoration: 'none',
    cursor: 'pointer',
    transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
    position: 'relative',
    overflow: 'hidden'
  }
  
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getTypographyStyles(settings.value, '', device.value))
  
  const sizeMap = {
    small: { fontSize: '12px', padding: '8px 16px' },
    medium: { fontSize: '14px', padding: '12px 24px' },
    large: { fontSize: '16px', padding: '16px 32px' }
  }
  const sizeValue = getResponsiveValue(settings.value, 'size', device.value) || 'medium'
  const size = sizeMap[sizeValue] || sizeMap.medium
  
  if (!settings.value.padding) { styles.padding = size.padding } 
  else { Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding')) }
  
  if (!styles.fontSize) styles.fontSize = size.fontSize
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  if (getResponsiveValue(settings.value, 'fullWidth', device.value)) { styles.width = '100%' }
  
  return styles
})
</script>

<style scoped>
.button-block {
  width: 100%;
}

.button-element {
  font-weight: 500;
  font-family: inherit;
}

.button-element:hover {
  background-color: var(--hover-bg, inherit) !important;
  color: var(--hover-color, inherit) !important;
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.button-element:active {
  transform: translateY(0);
}

.button-icon {
  width: 1em;
  height: 1em;
}

.button-icon--left {
  margin-right: 4px;
}

.button-icon--right {
  margin-left: 4px;
}

.button--full-width {
  width: 100%;
}
</style>

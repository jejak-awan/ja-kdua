<template>
  <div class="icon-list-block" :style="containerStyles">
    <div class="icon-list-items" :style="listStyles">
        <div 
            v-for="(item, index) in items" 
            :key="index"
            class="icon-list-item"
            :style="itemStyles"
        >
            <div class="icon-wrapper" :style="iconWrapperStyles">
                <LucideIcon 
                    :name="item.icon || defaultIcon" 
                    :size="iconSize" 
                    :style="iconStyles"
                />
            </div>
            <div class="icon-list-content">
                <span class="icon-list-text" :style="textStyles">{{ item.text || item.title || 'List Item' }}</span>
                <p v-if="item.description" class="icon-list-desc">{{ item.description }}</p>
            </div>
        </div>
    </div>
    
    <!-- Empty State -->
    <div v-if="items.length === 0" class="empty-list-placeholder p-4 text-center text-gray-400 border dashed border-gray-300 rounded">
        <LucideIcon name="List" :size="24" class="mx-auto mb-2" />
        <span>Add items in settings</span>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import LucideIcon from '../../ui/LucideIcon.vue'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
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
const device = computed(() => builder?.device?.value || 'desktop')

const items = computed(() => settings.value.items || [])
const defaultIcon = computed(() => 'check')
const iconSize = computed(() => getResponsiveValue(settings.value, 'iconSize', device.value) || 20)

const containerStyles = computed(() => {
  const styles = {
    display: 'flex',
    flexDirection: 'column',
    width: '100%',
  }
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

const listStyles = computed(() => {
  return {
    display: 'flex',
    flexDirection: 'column',
    gap: `${getResponsiveValue(settings.value, 'gap', device.value) || 12}px`
  }
})

const itemStyles = computed(() => {
    const align = getResponsiveValue(settings.value, 'alignment', device.value) || 'left'
    return {
        display: 'flex',
        alignItems: 'flex-start', // Align to top (icon/text)
        gap: '12px',
        justifyContent: align === 'center' ? 'center' : 'flex-start',
        textAlign: align
    }
})

const iconWrapperStyles = computed(() => {
    const s = {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        flexShrink: 0
    }
    
    if (settings.value.iconBgColor) {
        s.backgroundColor = settings.value.iconBgColor
        s.padding = '8px'
        // Simple shape handling
        s.borderRadius = settings.value.iconBackgroundShape === 'circle' ? '50%' : settings.value.iconBackgroundShape === 'square' ? '4px' : '0'
    }
    
    return s
})

const iconStyles = computed(() => {
    return {
        color: settings.value.iconColor || 'inherit'
    }
})

const textStyles = computed(() => getTypographyStyles(settings.value, 'text_', device.value))

</script>

<style scoped>
.icon-list-block { width: 100%; }
</style>

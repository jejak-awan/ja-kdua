<template>
  <div class="icon-list-block" :style="containerStyles">
    <div 
        v-for="child in (module.children || [])" 
        :key="child.id" 
        class="icon-list-item"
        :style="itemStyles"
    >
        <!-- Icon -->
        <div class="icon-wrapper" :style="iconWrapperStyles">
            <component 
                :is="getIcon(child.settings.icon)" 
                :style="iconStyles" 
            />
        </div>
        
        <!-- Text -->
        <div class="text-content">
            <span v-if="child.settings.link_url" class="item-link">
                <a :href="child.settings.link_url" :target="child.settings.link_target">{{ child.settings.text }}</a>
            </span>
            <span v-else>{{ child.settings.text }}</span>
        </div>
    </div>
    
    <!-- Empty State -->
    <div v-if="!module.children || module.children.length === 0" class="empty-list-placeholder">
        <List :size="24" />
        <span>Add items in settings</span>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import * as LucideIcons from 'lucide-vue-next'
import { List } from 'lucide-vue-next'
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
const device = computed(() => builder?.device || 'desktop')

const getIcon = (name) => {
    if (!name) return LucideIcons.Check
    return LucideIcons[name] || LucideIcons.Check
}

const containerStyles = computed(() => {
  const styles = {
    display: 'flex',
    flexDirection: 'column',
    width: '100%',
    gap: `${getResponsiveValue(settings.value, 'gap', device.value) || 12}px`
  }
  
  const align = getResponsiveValue(settings.value, 'alignment', device.value) || 'left'
  if (align === 'center') styles.alignItems = 'center'
  
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

const itemStyles = computed(() => {
    const align = getResponsiveValue(settings.value, 'alignment', device.value) || 'left'
    const styles = {
        display: 'flex',
        alignItems: 'center',
        gap: '12px',
        justifyContent: align === 'center' ? 'center' : 'flex-start',
        width: align === 'center' ? 'auto' : '100%'
    }
    Object.assign(styles, getTypographyStyles(settings.value, 'text_', device.value))
    return styles
})

const iconWrapperStyles = computed(() => {
    const s = {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        flexShrink: 0
    }
    
    if (settings.value.iconBackgroundColor) {
        s.backgroundColor = settings.value.iconBackgroundColor
        s.padding = '8px'
        s.borderRadius = settings.value.iconBackgroundShape === 'circle' ? '50%' : settings.value.iconBackgroundShape === 'square' ? '4px' : '0'
    }
    
    return s
})

const iconStyles = computed(() => {
    const size = getResponsiveValue(settings.value, 'iconSize', device.value) || 20
    return {
        width: `${size}px`,
        height: `${size}px`,
        color: settings.value.iconColor || 'inherit'
    }
})
</script>

<style scoped>
.icon-list-block {
  width: 100%;
}
</style>

<template>
  <div class="icon-list-item-block" :style="itemStyles">
      <!-- Icon -->
      <div class="icon-wrapper" :style="iconWrapperStyles">
          <component 
              :is="getIcon(settings.icon)" 
              :style="iconStyles" 
          />
      </div>
      
      <!-- Text -->
      <div class="text-content">
          <span v-if="linkUrl" class="item-link">
              <a 
                :href="linkUrl" 
                :target="settings.link_target" 
                :style="linkStyles"
                :contenteditable="builder?.isEditing"
                @blur="onTextBlur"
                @click.prevent
              >{{ text }}</a>
          </span>
          <span 
            v-else
            :contenteditable="builder?.isEditing"
            @blur="onTextBlur"
          >{{ text }}</span>
      </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import * as LucideIcons from 'lucide-vue-next'
import { getTypographyStyles, getResponsiveValue } from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')
const settings = computed(() => props.module.settings || {})

// Injected from IconListBlock
const iconListState = inject('iconListState', {
    parentSettings: {}
})
const parentSettings = computed(() => iconListState.parentSettings.value || {})

const getIcon = (name) => {
    if (!name) return LucideIcons.Check
    return LucideIcons[name] || LucideIcons.Check
}

const text = computed(() => getResponsiveValue(settings.value, 'text', device.value) || 'List Item')
const linkUrl = computed(() => getResponsiveValue(settings.value, 'link_url', device.value))

const onTextBlur = (e) => {
    updateResponsiveField('text', e.target.innerText)
}

const updateResponsiveField = (fieldName, value) => {
    const current = settings.value[fieldName]
    let newValue
    if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
        newValue = { ...current, [device.value]: value }
    } else {
        newValue = { [device.value]: value }
    }
    builder?.updateModuleSettings(props.module.id, { [fieldName]: newValue })
}

const itemStyles = computed(() => {
    const align = getResponsiveValue(parentSettings.value, 'alignment', device.value) || 'left'
    const styles = {
        display: 'flex',
        alignItems: 'center',
        gap: '12px',
        justifyContent: align === 'center' ? 'center' : 'flex-start',
        width: '100%'
    }
    
    // Apply typography from parent settings to this item
    Object.assign(styles, getTypographyStyles(parentSettings.value, 'text_', device.value))
    
    return styles
})

const linkStyles = computed(() => {
    return {
        color: 'inherit',
        textDecoration: 'none'
    }
})

const iconWrapperStyles = computed(() => {
    const s = {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        flexShrink: 0
    }
    
    if (parentSettings.value.iconBackgroundColor) {
        s.backgroundColor = parentSettings.value.iconBackgroundColor
        s.padding = '8px'
        s.borderRadius = parentSettings.value.iconBackgroundShape === 'circle' ? '50%' : parentSettings.value.iconBackgroundShape === 'square' ? '4px' : '0'
    }
    
    return s
})

const iconStyles = computed(() => {
    const size = getResponsiveValue(parentSettings.value, 'iconSize', device.value) || 20
    return {
        width: `${size}px`,
        height: `${size}px`,
        color: parentSettings.value.iconColor || 'inherit'
    }
})
</script>

<style scoped>
.icon-list-item-block {
  width: 100%;
}
</style>

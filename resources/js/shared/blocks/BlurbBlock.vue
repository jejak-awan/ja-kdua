<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperBaseStyles, settings }">
      <div class="blurb-block" :style="blurbBlockStyles">
        <!-- Media (Icon/Image) -->
        <div v-if="mediaType !== 'none'" class="blurb-media" :style="mediaStyles">
          <LucideIcon 
            v-if="mediaType === 'icon'"
            :name="iconName" 
            :size="iconSize" 
            :style="iconStyles"
          />
          <img 
            v-else-if="mediaType === 'image' && settings.image"
            :src="settings.image" 
            alt=""
            class="blurb-image"
          />
        </div>
        
        <!-- Content -->
        <div class="blurb-content" :style="contentWrapperStyles">
          <h3 
            v-if="title || mode === 'edit'" 
            class="blurb-title" 
            :style="titleStyles"
            :contenteditable="mode === 'edit'"
            @blur="e => updateResponsiveField('title', e.target.innerText)"
          >
            {{ title }}
          </h3>
          <div 
            v-if="content || mode === 'edit'" 
            class="blurb-text" 
            :style="textStyles"
            :contenteditable="mode === 'edit'"
            @blur="e => updateResponsiveField('content', e.target.innerText)"
          >
            {{ content }}
          </div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder')
const settings = computed(() => props.module?.settings || {})

const mediaType = computed(() => settings.value.mediaType || 'icon')
const iconName = computed(() => settings.value.iconName || 'Star')
const iconSize = computed(() => {
    const size = getVal(settings.value, 'iconSize', props.device) || 48
    return typeof size === 'number' ? size : parseInt(size) || 48
})

const title = computed(() => getVal(settings.value, 'title', props.device) || '')
const content = computed(() => getVal(settings.value, 'content', props.device) || '')

const blurbBlockStyles = computed(() => {
  const position = getVal(settings.value, 'iconPosition', props.device) || 'top'
  const alignment = getVal(settings.value, 'alignment', props.device) || 'center'
  
  return { 
    width: '100%',
    display: 'flex',
    gap: position === 'top' ? '16px' : '20px',
    flexDirection: position === 'top' ? 'column' : (position === 'right' ? 'row-reverse' : 'row'),
    alignItems: position === 'top' ? (alignment === 'center' ? 'center' : (alignment === 'right' ? 'flex-end' : 'flex-start')) : 'flex-start'
  }
})

const contentWrapperStyles = computed(() => ({
  flex: 1,
  textAlign: getVal(settings.value, 'alignment', props.device) || 'center'
}))

const mediaStyles = computed(() => {
  const styles = {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    flexShrink: 0
  }
  
  const bgColor = getVal(settings.value, 'iconBackgroundColor', props.device)
  const shape = getVal(settings.value, 'iconBackgroundShape', props.device)
  
  if (bgColor && shape !== 'none') {
    styles.backgroundColor = bgColor
    styles.padding = '16px'
    
    if (shape === 'circle') {
      styles.borderRadius = '50%'
    } else if (shape === 'rounded') {
      styles.borderRadius = '12px'
    }
  }
  
  return styles
})

const iconStyles = computed(() => {
  return {
    color: getVal(settings.value, 'iconColor', props.device) || '#2059ea'
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const textStyles = computed(() => getTypographyStyles(settings.value, 'content_', props.device))

const updateResponsiveField = (fieldName, value) => {
  if (props.mode !== 'edit') return
  const current = settings.value[fieldName]
  let newValue
  if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
    newValue = { ...current, [props.device]: value }
  } else {
    newValue = { [props.device]: value }
  }
  builder?.updateModuleSettings(props.module.id, { [fieldName]: newValue })
}
</script>

<style scoped>
.blurb-block { width: 100%; }
.blurb-image { max-width: 80px; height: auto; border-radius: 4px; }
.blurb-media { transition: all 0.3s ease; }
</style>

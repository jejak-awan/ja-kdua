<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <div 
        class="blurb-block transition-all duration-300 group" 
        :style="blurbContainerStyles(settings, blockDevice)"
      >
        <!-- Media (Icon/Image) -->
        <div 
            v-if="mediaType(settings) !== 'none'" 
            class="blurb-media flex items-center justify-center shrink-0 transition-transform group-hover:scale-110 duration-500" 
            :style="mediaWrapperStyles(settings)"
        >
          <LucideIcon 
            v-if="mediaType(settings) === 'icon'"
            :name="getVal(settings, 'iconName') || 'Zap'" 
            :size="getVal(settings, 'iconSize') || 48" 
            :style="{ color: getVal(settings, 'iconColor') || '#4f46e5' }"
          />
          <img 
            v-else-if="mediaType(settings) === 'image' && getVal(settings, 'image')"
            :src="getVal(settings, 'image')" 
            alt="Feature"
            class="blurb-image object-cover w-full h-full rounded-lg"
          />
        </div>
        
        <!-- Content -->
        <div class="blurb-content flex flex-col" :style="contentStyles(settings, blockDevice)">
          <h3 
            v-if="getVal(settings, 'title')" 
            class="blurb-title font-bold text-xl mb-3 text-slate-900 leading-tight" 
            :contenteditable="mode === 'edit'"
            @blur="e => updateField('title', e.target.innerText)"
          >
            {{ getVal(settings, 'title') }}
          </h3>
          <div 
            v-if="getVal(settings, 'content')" 
            class="blurb-text text-slate-500 font-medium leading-relaxed" 
            :contenteditable="mode === 'edit'"
            @blur="e => updateField('content', e.target.innerText)"
          >
            {{ getVal(settings, 'content') }}
          </div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder')

const mediaType = (settings) => getVal(settings, 'mediaType') || 'icon'

const blurbContainerStyles = (settings, device) => {
    const pos = getVal(settings, 'iconPosition', device) || 'top'
    const align = getVal(settings, 'alignment', device) || 'center'
    
    return {
        width: '100%',
        display: 'flex',
        flexDirection: pos === 'top' ? 'column' : 'row',
        alignItems: pos === 'top' 
            ? (align === 'center' ? 'center' : (align === 'right' ? 'flex-end' : 'flex-start')) 
            : 'flex-start',
        gap: '24px',
        textAlign: align
    }
}

const mediaWrapperStyles = (settings) => {
    const shape = getVal(settings, 'iconShape') || 'rounded'
    const bgColor = getVal(settings, 'iconBgColor') || 'rgba(79, 70, 229, 0.08)'
    const size = (getVal(settings, 'iconSize') || 48) + 32 // Add padding to wrapper

    const style = {
        backgroundColor: bgColor,
        width: `${size}px`,
        height: `${size}px`
    }

    if (shape === 'circle') style.borderRadius = '50%'
    if (shape === 'rounded') style.borderRadius = '1.25rem'
    
    return style
}

const contentStyles = (settings, device) => {
    const align = getVal(settings, 'alignment', device) || 'center'
    return {
        alignItems: align === 'center' ? 'center' : (align === 'right' ? 'flex-end' : 'flex-start')
    }
}

const updateField = (key, value) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.module.id, { [key]: value })
}
</script>

<style scoped>
.blurb-title { letter-spacing: -0.01em; }
</style>

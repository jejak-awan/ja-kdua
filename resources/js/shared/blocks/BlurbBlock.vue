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
            class="blurb-media flex items-center justify-center shrink-0 transition-all duration-500 group-hover:scale-110" 
            :style="mediaWrapperStyles(settings)"
        >
          <LucideIcon 
            v-if="mediaType(settings) === 'icon'"
            :name="getVal(settings, 'iconName') || 'Zap'" 
            :size="getVal(settings, 'iconSize') || 48" 
            :style="{ color: getVal(settings, 'iconColor') || '#4f46e5' }"
          />
          <Avatar v-else-if="mediaType(settings) === 'image' && getVal(settings, 'image')" class="w-full h-full rounded-none">
            <AvatarImage 
              :src="getVal(settings, 'image')" 
              alt="Feature"
              class="object-cover"
            />
            <AvatarFallback class="bg-slate-100 rounded-none">
              <Layers class="w-1/2 h-1/2 opacity-20" />
            </AvatarFallback>
          </Avatar>
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
            v-text="getVal(settings, 'content')"
          >
          </div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Avatar, AvatarImage, AvatarFallback } from '../ui'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { Layers } from 'lucide-vue-next'
import { getVal } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)

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
        height: `${size}px`,
        overflow: 'hidden'
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

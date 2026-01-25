<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <div 
        class="blurb-block-container"
        :id="getVal(settings, 'html_id', blockDevice)"
        :style="containerStyles(settings, blockDevice)"
      >
        <div 
            class="blurb-content-wrapper transition-all duration-300 group" 
            :style="blurbContentWrapperStyles(settings, blockDevice)"
        >
            <!-- Media (Icon/Image) -->
            <div 
                v-if="mediaType(settings, blockDevice) !== 'none'" 
                class="blurb-media flex items-center justify-center shrink-0 transition-all duration-500 ease-out" 
                :style="mediaWrapperStyles(settings, blockDevice)"
            >
                <LucideIcon 
                    v-if="mediaType(settings, blockDevice) === 'icon'"
                    :name="getVal(settings, 'iconName', blockDevice) || 'Zap'" 
                    :size="getVal(settings, 'iconSize', blockDevice) || 48" 
                    :style="{ color: getVal(settings, 'iconColor') || '#4f46e5' }"
                    class="transition-transform duration-500 group-hover:scale-110"
                />
                <Avatar v-else-if="mediaType(settings, blockDevice) === 'image' && getVal(settings, 'image', blockDevice)" class="w-full h-full rounded-none">
                    <AvatarImage 
                        :src="getVal(settings, 'image', blockDevice)" 
                        alt="Feature"
                        class="object-cover w-full h-full transition-transform duration-500 group-hover:scale-110"
                    />
                    <AvatarFallback class="bg-slate-100 rounded-none">
                        <Layers class="w-1/2 h-1/2 opacity-20" />
                    </AvatarFallback>
                </Avatar>
            </div>
            
            <!-- Text Content -->
            <div 
                class="blurb-text-content flex flex-col" 
                :style="textContentStyles(settings, blockDevice)"
            >
                <h3 
                    v-if="mode === 'edit' || getVal(settings, 'title', blockDevice)" 
                    class="blurb-title font-bold text-xl mb-3 leading-tight transition-all duration-300" 
                    :contenteditable="mode === 'edit'"
                    @blur="e => updateField('title', (e.target as HTMLElement).innerText)"
                    :style="getTypographyStyles(settings, 'title_', blockDevice)"
                >
                    {{ getVal(settings, 'title', blockDevice) }}
                </h3>
                <div 
                    v-if="mode === 'edit' || getVal(settings, 'content', blockDevice)" 
                    class="blurb-description font-medium leading-relaxed transition-all duration-300" 
                    :contenteditable="mode === 'edit'"
                    @blur="e => updateField('content', (e.target as HTMLElement).innerText)"
                    v-text="getVal(settings, 'content', blockDevice)"
                    :style="getTypographyStyles(settings, 'content_', blockDevice)"
                >
                </div>
            </div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Avatar, AvatarImage, AvatarFallback } from '../ui'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { Layers } from 'lucide-vue-next'
import { 
    getVal, 
    getTypographyStyles,
    getLayoutStyles,
    toCSS
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance } from '../../types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance;
  mode?: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile' | null;
}>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<BuilderInstance>('builder', null as any)

const mediaType = (settings: any, device: string) => getVal(settings, 'mediaType', device) || 'icon'

const containerStyles = (settings: any, device: string) => {
    return {
        width: '100%',
        ...getLayoutStyles(settings, device)
    }
}

const blurbContentWrapperStyles = (settings: any, device: string) => {
    const pos = getVal(settings, 'iconPosition', device) || 'top'
    const align = getVal(settings, 'alignment', device) || 'center'
    
    const style: Record<string, any> = {
        width: '100%',
        display: 'flex',
        flexDirection: pos === 'top' ? 'column' : (pos === 'right' ? 'row-reverse' : 'row'),
        alignItems: pos === 'top' 
            ? (align === 'center' ? 'center' : (align === 'right' ? 'flex-end' : 'flex-start')) 
            : 'flex-start',
        gap: '24px',
        textAlign: align as any,
        transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
    }

    // Interactive Variables
    style['--hover-scale'] = `scale(${getVal(settings, 'hover_scale', device) || 1.05})`;
    style['--hover-brightness'] = `brightness(${getVal(settings, 'hover_brightness', device) || 100}%)`;

    return style
}

const mediaWrapperStyles = (settings: any, device: string) => {
    const shape = getVal(settings, 'iconBackgroundShape', device) || 'rounded'
    const bgColor = getVal(settings, 'iconBackgroundColor', device) || 'rgba(79, 70, 229, 0.08)'
    const iconSize = getVal(settings, 'iconSize', device) || 48
    const wrapperSize = iconSize + 32 

    const style: Record<string, any> = {
        backgroundColor: bgColor,
        width: toCSS(wrapperSize),
        height: toCSS(wrapperSize),
        overflow: 'hidden'
    }

    if (shape === 'circle') style.borderRadius = '50%'
    if (shape === 'rounded') style.borderRadius = '1.25rem'
    if (shape === 'square') style.borderRadius = '0'
    
    return style
}

const textContentStyles = (settings: any, device: string) => {
    const align = getVal(settings, 'alignment', device) || 'center'
    return {
        flex: '1',
        minWidth: '0',
        alignItems: align === 'center' ? 'center' : (align === 'right' ? 'flex-end' : 'flex-start')
    }
}

const updateField = (key: string, value: string) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.module.id, { [key]: value })
}
</script>

<style scoped>
.blurb-block-container { 
    width: 100%; 
}

.blurb-content-wrapper {
    will-change: transform, filter;
}

.blurb-content-wrapper:hover {
    transform: var(--hover-scale);
    filter: var(--hover-brightness);
}

.blurb-title { letter-spacing: -0.01em; }
</style>

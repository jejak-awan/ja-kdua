<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <div 
        class="feature-block-container"
        :id="getVal(settings, 'html_id', blockDevice)"
        :style="containerStyles(settings, blockDevice)"
      >
        <Card 
            class="feature-card transition-[width] duration-500 group overflow-hidden" 
            :class="cardClasses(settings, blockDevice)" 
            :style="cardStyles(settings, blockDevice)"
            :aria-label="getVal(settings, 'aria_label', blockDevice) || undefined"
        >
            <div 
                class="feature-inner flex transition-[width] duration-500" 
                :class="innerClasses(settings, blockDevice)"
            >
                <div class="feature-icon-wrap relative flex-shrink-0" :style="iconWrapperStyles(settings, blockDevice)">
                    <div class="icon-glow absolute inset-0 opacity-20 blur-xl group-hover:opacity-40 transition-opacity" :style="{ backgroundColor: getVal(settings, 'iconColor', blockDevice) || '#3b82f6' }"></div>
                    <div class="icon-container relative z-10 flex items-center justify-center transition-transform duration-500 group-hover:scale-110" :style="iconStyles(settings, blockDevice)">
                        <component :is="iconComponent(settings, blockDevice)" class="w-full h-full" />
                    </div>
                </div>
                
                <div class="feature-content flex-1 pt-1 min-w-0">
                    <CardTitle 
                        v-if="mode === 'edit' || getVal(settings, 'title', blockDevice)"
                        class="text-xl font-bold mb-3 tracking-tight transition-colors duration-300" 
                        :style="getTypographyStyles(settings, 'title_', blockDevice)"
                        :contenteditable="mode === 'edit'"
                        @blur="(e: any) => updateField('title', (e.target as HTMLElement).innerText)"
                    >
                        {{ getVal(settings, 'title', blockDevice) || 'Feature Title' }}
                    </CardTitle>
                    <CardDescription 
                        v-if="mode === 'edit' || getVal(settings, 'description', blockDevice)"
                        class="opacity-70 leading-relaxed text-sm transition-colors duration-300" 
                        :style="getTypographyStyles(settings, 'description_', blockDevice)"
                        :contenteditable="mode === 'edit'"
                        @blur="(e: any) => updateField('description', (e.target as HTMLElement).innerText)"
                    >
                        {{ getVal(settings, 'description', blockDevice) || 'Describe the unique value of this feature or service here.' }}
                    </CardDescription>
                </div>
            </div>
        </Card>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, CardTitle, CardDescription } from '../ui'
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import Layers from 'lucide-vue-next/dist/esm/icons/layers.js';
import Palette from 'lucide-vue-next/dist/esm/icons/palette.js';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import Code2 from 'lucide-vue-next/dist/esm/icons/code-xml.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import Star from 'lucide-vue-next/dist/esm/icons/star.js';
import Shield from 'lucide-vue-next/dist/esm/icons/shield.js';
import HelpCircle from 'lucide-vue-next/dist/esm/icons/circle-question-mark.js';
import Info from 'lucide-vue-next/dist/esm/icons/info.js';
import Heart from 'lucide-vue-next/dist/esm/icons/heart.js';
import Bell from 'lucide-vue-next/dist/esm/icons/bell.js';import type { Component } from 'vue'

const iconMap: Record<string, Component> = {
    Zap, Layers, Palette, Globe, Code2, Check, Star, Shield, HelpCircle, Info, Heart, Bell
}
import { 
    getVal, 
    getTypographyStyles,
    getLayoutStyles,
    toCSS
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance, BlockProps } from '../../types/builder'

const props = withDefaults(defineProps<BlockProps>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<BuilderInstance>('builder', null as any)

const iconComponent = (settings: any, device: string) => {
  const iconName = getVal(settings, 'icon', device) || 'Zap'
  return iconMap[iconName] || iconMap.Zap
}

const containerStyles = (settings: any, device: string) => {
    return {
        width: '100%',
        ...getLayoutStyles(settings, device)
    }
}

const innerClasses = (settings: any, device: string) => {
    const layout = getVal(settings, 'layout', device) || 'top'
    const align = getVal(settings, 'alignment', device) || 'center'
    
    return [
        layout === 'top' ? 'flex-col items-center text-center gap-6' : 
        layout === 'right' ? 'flex-row-reverse items-start gap-6' : 
        'flex-row items-start gap-6',
        align === 'left' ? 'text-left' : (align === 'right' ? 'text-right' : 'text-center')
    ]
}

const iconWrapperStyles = (settings: any, device: string) => {
  const size = getVal(settings, 'iconSize', device) || 48
  const bgColor = getVal(settings, 'iconBackgroundColor', device) || 'rgba(59, 130, 246, 0.1)'
  const borderRadius = getVal(settings, 'iconBorderRadius', device) || 24
  
  return {
    width: toCSS(size + 32),
    height: toCSS(size + 32),
    backgroundColor: bgColor,
    borderRadius: typeof borderRadius === 'string' ? borderRadius : toCSS(borderRadius),
    display: 'flex', 
    alignItems: 'center', 
    justifyContent: 'center'
  }
}

const iconStyles = (settings: any, device: string) => {
  const size = getVal(settings, 'iconSize', device) || 48
  const color = getVal(settings, 'iconColor', device) || '#3b82f6'
  return { 
    width: toCSS(size),
    height: toCSS(size),
    color: color
  }
}

const cardClasses = (settings: any, device: string) => {
    return getVal(settings, 'variant', device) === 'boxed' 
        ? 'p-6 border bg-card text-card-foreground shadow-sm' 
        : 'border-none shadow-none bg-transparent p-0'
}

const cardStyles = (settings: any, device: string) => {
    const style: Record<string, any> = {
        willChange: 'transform, filter',
        transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
    }

    style['--hover-scale'] = `scale(${getVal(settings, 'hover_scale', device) || 1.05})`;
    style['--hover-brightness'] = `brightness(${getVal(settings, 'hover_brightness', device) || 100}%)`;

    return style
}

const updateField = (key: string, value: string) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.module.id, { [key]: value })
}
</script>

<style scoped>
.feature-block-container { width: 100%; }

.feature-card:hover {
    transform: var(--hover-scale);
    filter: var(--hover-brightness);
}
</style>

<template>
  <BaseBlock :module="module" :mode="mode" :device="device" v-slot="{ mode: blockMode, settings, device: blockDevice, getAttributes }">
      <div class="heading-container">
        <component 
          :is="tag(settings)"
          class="heading-block transition-all duration-300"
          :style="headingStyles(settings, blockDevice)"
          :class="[sizeClass(settings)]"
          v-bind="getAttributes('title')"
        >
          <template v-if="blockMode === 'edit' && !isDynamic(settings)">
            <div 
              ref="editableRef"
              contenteditable="true"
              @blur="onTextBlur($event, settings)"
              v-html="displayText(settings)"
              style="display: block; width: 100%; outline: none;"
            ></div>
          </template>
          <template v-else>
            {{ displayText(settings) }}
          </template>
        </component>

        <div v-if="subtitle(settings) || blockMode === 'edit'" class="heading-subtitle" :class="subtitleSizeClass(settings)" :style="subtitleStyles(settings, blockDevice)" v-bind="getAttributes('subtitle')">
          <div 
            :contenteditable="blockMode === 'edit'"
            @blur="onSubtitleBlur($event, settings)"
            v-html="subtitle(settings) || (blockMode === 'edit' ? 'Add a subtitle...' : '')"
            style="display: block; width: 100%; outline: none;"
          ></div>
        </div>
        
      </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
    getTypographyStyles, 
    getVal,
    getTextGradientStyles,
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance } from '../../types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance;
  mode: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile' | null;
}>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<BuilderInstance>('builder', null as any)

const tag = (settings: any) => getVal(settings, 'tag') || 'h2'
const subtitle = (settings: any) => getVal(settings, 'subtitle') || ''
const isDynamic = (settings: any) => {
    const text = getVal(settings, 'text')
    return typeof text === 'string' && text.startsWith('{{')
}

const displayText = (settings: any) => {
    return getVal(settings, 'text') || 'Heading Text'
}

const headingStyles = (settings: any, device: string) => {
    const styles: Record<string, any> = { width: '100%' }
    
    // 1. Core Typography
    Object.assign(styles, getTypographyStyles(settings, '', device))

    // 2. Gradients
    if (getVal(settings, 'use_gradient', device)) {
        Object.assign(styles, getTextGradientStyles(settings, '', device))
    }

    // 3. Stroke
    if (getVal(settings, 'use_stroke', device)) {
        const width = getVal(settings, 'stroke_width', device) || 1
        const color = getVal(settings, 'stroke_color', device) || '#000000'
        styles.webkitTextStroke = `${width}px ${color}`
    }

    // 4. Shadow Presets
    const shadowPreset = getVal(settings, 'shadow_preset', device)
    if (shadowPreset && shadowPreset !== 'none') {
        const presets: Record<string, string> = {
            soft: '0 2px 10px rgba(0,0,0,0.1)',
            hard: '4px 4px 0px rgba(0,0,0,0.2)',
            glow: '0 0 20px rgba(var(--primary-rgb, 32, 89, 234), 0.5)'
        }
        styles.textShadow = presets[shadowPreset]
    }

    const alignment = getVal(settings, 'alignment', device)
    if (alignment) styles.textAlign = alignment
    
    return styles
}

const sizeClass = (settings: any) => {
    const size = getVal(settings, 'size') || 'large'
    
    const sizes: Record<string, { mobile: string; desktop: string }> = {
        small: { mobile: 'text-lg', desktop: 'text-2xl' },
        medium: { mobile: 'text-xl', desktop: 'text-3xl' },
        large: { mobile: 'text-2xl', desktop: 'text-4xl' },
        xlarge: { mobile: 'text-3xl', desktop: 'text-5xl' },
        display: { mobile: 'text-4xl', desktop: 'text-7xl' }
    }
    
    const s = sizes[size] || sizes.large

    if (props.mode === 'edit') {
        if (props.device === 'mobile') return s.mobile
        if (props.device === 'tablet') return s.mobile 
        return s.desktop
    }

    return `${s.mobile} md:${s.desktop}`
}

const subtitleSizeClass = (settings: any) => {
    const size = getVal(settings, 'size') || 'large'
    
    const sizes: Record<string, { mobile: string; desktop: string }> = {
        small: { mobile: 'text-sm', desktop: 'text-sm' },
        medium: { mobile: 'text-sm', desktop: 'text-base' },
        large: { mobile: 'text-base', desktop: 'text-lg' },
        xlarge: { mobile: 'text-lg', desktop: 'text-xl' },
        display: { mobile: 'text-xl', desktop: 'text-2xl' }
    }

    const s = sizes[size] || sizes.large

    if (props.mode === 'edit') {
        if (props.device === 'mobile') return s.mobile
        if (props.device === 'tablet') return s.mobile
        return s.desktop
    }

    if (s.mobile === s.desktop) return s.desktop
    return `${s.mobile} md:${s.desktop}`
}

const subtitleStyles = (settings: any, device: string) => {
    const s = getTypographyStyles(settings, '', device)
    return {
        textAlign: s.textAlign || 'left',
        opacity: 0.8,
        marginTop: '0.5em',
        fontWeight: 'normal'
    }
}

const onTextBlur = (e: any, settings: any) => {
  if (props.mode !== 'edit' || !builder) return
  const newText = e.target.innerText
  if (newText !== getVal(settings, 'text')) {
    builder.updateModule(props.module.id, {
      settings: { ...settings, text: newText }
    })
  }
}

const onSubtitleBlur = (e: any, settings: any) => {
  if (props.mode !== 'edit' || !builder) return
  const newSubtitle = e.target.innerText
  if (newSubtitle !== getVal(settings, 'subtitle') && newSubtitle !== 'Add a subtitle...') {
    builder.updateModule(props.module.id, {
      settings: { ...settings, subtitle: newSubtitle }
    })
  }
}
</script>

<style scoped>
.heading-container {
  width: 100%;
}
.heading-block {
  margin: 0;
  padding: 0;
  line-height: 1.2;
  letter-spacing: -0.02em;
  font-weight: 700;
}
</style>

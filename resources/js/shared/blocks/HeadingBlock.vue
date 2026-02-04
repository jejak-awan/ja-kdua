<template>
  <BaseBlock :module="module" :mode="mode" :device="device" v-slot="{ mode: blockMode, settings, device: blockDevice, getAttributes }">
      <div class="heading-container" :style="getLayoutStyles(settings as ModuleSettings, blockDevice)">
        <component 
          :is="tag(settings as ModuleSettings)"
          :id="getVal(settings as ModuleSettings, 'html_id') || undefined"
          class="heading-block transition-colors duration-300"
          :style="(headingStyles(settings as ModuleSettings, blockDevice) as CSSProperties)"
          :class="[sizeClass(settings as ModuleSettings)]"
          :aria-label="getVal<string>(settings as ModuleSettings, 'aria_label') || undefined"
          v-bind="(getAttributes('title') as Record<string, any>)"
        >
          <template v-if="getVal(settings as ModuleSettings, 'use_link')">
             <a 
               :href="getVal(settings as ModuleSettings, 'link_url') || '#'" 
               :target="getVal(settings as ModuleSettings, 'link_target') || '_self'"
                :rel="Array.isArray(getVal<string[]>(settings as ModuleSettings, 'link_rel')) ? (getVal<string[]>(settings as ModuleSettings, 'link_rel') as string[]).join(' ') : undefined"
             >
                <template v-if="blockMode === 'edit' && !isDynamic(settings as ModuleSettings)">
                  <div 
                    ref="editableRef"
                    contenteditable="true"
                    @blur="onTextBlur($event, settings as ModuleSettings)"
                    v-html="(displayText(settings as ModuleSettings) as string)"
                    style="display: inline-block; width: 100%; outline: none;"
                  ></div>
                </template>
                <template v-else>
                  {{ displayText(settings as ModuleSettings) }}
                </template>
             </a>
          </template>
          <template v-else>
            <template v-if="blockMode === 'edit' && !isDynamic(settings as ModuleSettings)">
              <div 
                ref="editableRef"
                contenteditable="true"
                @blur="onTextBlur($event, settings as ModuleSettings)"
                v-html="(displayText(settings as ModuleSettings) as string)"
                style="display: block; width: 100%; outline: none;"
              ></div>
            </template>
            <template v-else>
              {{ displayText(settings as ModuleSettings) }}
            </template>
          </template>
        </component>

        <component 
          :is="subtitleTag(settings as ModuleSettings)"
          v-if="subtitle(settings as ModuleSettings) || blockMode === 'edit'" 
          class="heading-subtitle" 
          :class="subtitleSizeClass(settings as ModuleSettings)" 
          :style="(subtitleStyles(settings as ModuleSettings, blockDevice) as CSSProperties)" 
          v-bind="(getAttributes('subtitle') as Record<string, any>)"
        >
          <div 
            :contenteditable="blockMode === 'edit'"
            @blur="onSubtitleBlur($event, settings as ModuleSettings)"
            v-html="(subtitle(settings as ModuleSettings) as string) || (blockMode === 'edit' ? 'Add a subtitle...' : '')"
            style="display: block; width: 100%; outline: none;"
          ></div>
        </component>
      </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { inject, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
    getTypographyStyles, 
    getVal,
    getTextGradientStyles,
    generateGradientCSS,
    getLayoutStyles,
    type Gradient
} from '../utils/styleUtils'
import type { BuilderInstance, BlockProps, ModuleSettings } from '@/types/builder'


const props = withDefaults(defineProps<BlockProps>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<BuilderInstance | null>('builder', null)

const tag = (settings: ModuleSettings) => getVal<string>(settings, 'tag') || 'h2'
const subtitleTag = (settings: ModuleSettings) => getVal<string>(settings, 'subtitle_tag') || 'div'
const subtitle = (settings: ModuleSettings) => getVal<string>(settings, 'subtitle') || ''
const isDynamic = (settings: ModuleSettings) => {
    const text = getVal<string>(settings, 'text')
    return typeof text === 'string' && text.startsWith('{{')
}

const displayText = (settings: ModuleSettings) => {
    return getVal<string>(settings, 'text') || 'Heading Text'
}

const headingStyles = (settings: ModuleSettings, device: string) => {
    const styles: Record<string, string | number> = { width: '100%' }
    
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
        styles.textShadow = presets[shadowPreset as string]
    }

    const alignment = getVal<string>(settings, 'alignment', device) as string | undefined
    if (alignment) styles.textAlign = alignment

    // 5. Background Clip Text (Uses module background as text fill)
    if (getVal(settings, 'background_clip_text', device)) {
        styles.webkitBackgroundClip = 'text'
        styles.backgroundClip = 'text'
        styles.webkitTextFillColor = 'transparent'
        styles.color = 'transparent'
    }

    // 6. Hover States (Via CSS Variables)
    const hoverColor = getVal<string>(settings, 'hover_text_color', device) as string | undefined
    if (hoverColor) styles['--hover-color'] = hoverColor

    if (getVal<boolean>(settings, 'hover_use_gradient', device)) {
        const hg = getVal<unknown>(settings, 'hover_gradient', device) as Gradient | undefined
        if (hg && Array.isArray(hg.stops) && hg.stops.length >= 2) {
            styles['--hover-gradient'] = generateGradientCSS(hg)
        }
    }
    
    return styles as CSSProperties
}

const sizeClass = (settings: ModuleSettings) => {
    const size = getVal<string>(settings, 'size') || 'large'
    
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

const subtitleSizeClass = (settings: ModuleSettings) => {
    const size = getVal<string>(settings, 'size') || 'large'
    
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

const subtitleStyles = (settings: ModuleSettings, device: string) => {
    const s = getTypographyStyles(settings, '', device) as Record<string, string | number>
    return {
        textAlign: s.textAlign || 'left',
        opacity: 0.8,
        marginTop: '0.5em',
        fontWeight: 'normal'
    }
}

const onTextBlur = (e: FocusEvent, settings: ModuleSettings) => {
  if (props.mode !== 'edit' || !builder) return
  const target = e.target as HTMLElement
  const newText = target.innerText
  if (newText !== getVal<string>(settings, 'text')) {
    builder.updateModule(props.module.id, {
      settings: { ...settings, text: newText }
    })
  }
}

const onSubtitleBlur = (e: FocusEvent, settings: ModuleSettings) => {
  if (props.mode !== 'edit' || !builder) return
  const target = e.target as HTMLElement
  const newSubtitle = target.innerText
  if (newSubtitle !== getVal<string>(settings, 'subtitle') && newSubtitle !== 'Add a subtitle...') {
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

/* Custom Hover Effects */
.heading-block {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.heading-block:hover {
    color: var(--hover-color) !important;
    -webkit-text-fill-color: var(--hover-color) !important;
}

/* Hover Gradient Support */
.heading-block[style*="--hover-gradient"]:hover {
    background-image: var(--hover-gradient) !important;
    -webkit-background-clip: text !important;
    background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
    color: transparent !important;
}

/* Background Clip Text Fixes */
.heading-block[style*="background-clip: text"] {
    background-color: transparent !important;
}
</style>

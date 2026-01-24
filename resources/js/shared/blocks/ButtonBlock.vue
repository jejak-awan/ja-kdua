<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device" 
    :manual-styles="true"
    v-slot="{ mode: blockMode, settings, device: blockDevice, getAttributes, styles: baseStyles }"
  >
    <div 
      class="button-block-container w-full" 
      :class="alignmentClass(settings, blockDevice)"
    >
      <Button
        variant="none"
        :class="cn(
            'button-element group relative',
            `btn-variant-${getVal(settings, 'variant', blockDevice) || 'solid'}`,
            `btn-hover-${getVal(settings, 'hover_effect', blockDevice) || 'lift'}`,
            { 'overflow-hidden': getVal(settings, 'hover_effect', blockDevice) === 'shine' || getVal(settings, 'hover_effect', blockDevice) === 'sweep' }
        )"
        :style="buttonStyles(settings, blockDevice, baseStyles)"
        :as="mode === 'edit' ? 'button' : 'a'"
        :href="mode === 'edit' ? undefined : (getVal(settings, 'url', blockDevice) || getVal(settings, 'link_url', blockDevice) || '#')"
        :target="getVal(settings, 'openNewTab', blockDevice) || getVal(settings, 'link_target', blockDevice) ? '_blank' : '_self'"
        :rel="Array.isArray(getVal(settings, 'link_rel', blockDevice)) ? getVal(settings, 'link_rel', blockDevice).join(' ') : undefined"
        :download="getVal(settings, 'download', blockDevice) ? '' : undefined"
        :aria-label="getVal(settings, 'aria_label', blockDevice)"
        :type="getVal(settings, 'button_type', blockDevice) || 'button'"
        @click="onButtonClick"
        v-bind="getAttributes('button')"
      >
        <!-- Shine Element -->
        <div v-if="getVal(settings, 'hover_effect', blockDevice) === 'shine'" class="btn-shine-overlay"></div>
        
        <!-- Sweep Element -->
        <div v-if="getVal(settings, 'hover_effect', blockDevice) === 'sweep'" class="btn-sweep-overlay"></div>

        <!-- Icon Left -->
        <component 
          v-if="getVal(settings, 'use_icon', blockDevice) && getVal(settings, 'iconName', blockDevice) && getVal(settings, 'iconPosition', blockDevice) === 'left'"
          :is="getIconComponent(getVal(settings, 'iconName', blockDevice))"
          class="btn-icon-left transition-transform duration-300 group-hover:-translate-x-1"
          :style="iconStyles(settings, blockDevice)"
        />

        <!-- Editable Text -->
        <span 
          :contenteditable="blockMode === 'edit'"
          @blur="onTextBlur($event, settings)"
          class="outline-none relative z-10"
          v-html="getVal(settings, 'text', blockDevice) || 'Button Text'"
        ></span>

        <!-- Icon Right -->
        <component 
          v-if="getVal(settings, 'use_icon', blockDevice) && getVal(settings, 'iconName', blockDevice) && getVal(settings, 'iconPosition', blockDevice) === 'right'"
          :is="getIconComponent(getVal(settings, 'iconName', blockDevice))"
          class="btn-icon-right transition-transform duration-300 group-hover:translate-x-1"
          :style="iconStyles(settings, blockDevice)"
        />
      </Button>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Button } from '../ui'
import { cn } from '../../lib/utils'
import { 
    getTypographyStyles, 
    getVal, 
    getGlassStyles, 
    getTextGradientStyles,
    generateGradientCSS
} from '../utils/styleUtils'
import * as LucideIcons from 'lucide-vue-next'
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

const getIconComponent = (name: string) => {
    return (LucideIcons as any)[name] || LucideIcons.ArrowRight
}

const alignmentClass = (settings: any, device: string) => {
  const align = getVal(settings, 'alignment', device) || 'left'
  return {
    'flex justify-start text-left': align === 'left',
    'flex justify-center text-center': align === 'center',
    'flex justify-end text-right': align === 'right'
  }
}

const buttonStyles = (settings: any, device: string, baseStyles: any) => {
    let styles: Record<string, any> = {}
    const variant = getVal(settings, 'variant', device) || 'solid'
    const useCustom = getVal(settings, 'use_custom_styles', device)

    // 1. Inherit all design properties from BaseBlock if custom styles are enabled
    if (useCustom) {
        Object.assign(styles, baseStyles)
    } else {
        // Only inherit spacing and basic visibility
        if (baseStyles.margin) styles.margin = baseStyles.margin
        if (baseStyles.padding) styles.padding = baseStyles.padding
        if (baseStyles.width) styles.width = baseStyles.width
    }

    // 2. Typography
    const typography = getTypographyStyles(settings, '', device)
    Object.assign(styles, typography)

    // 3. Variant Overrides & Color Defaults
    const defaultWhite = '#ffffff'
    const defaultDark = '#111827'

    if (variant === 'glass') {
        const glass = getGlassStyles(settings, '', device)
        if (Object.keys(glass).length === 0) {
            // Premium Default Glass
            styles.backdropFilter = 'blur(12px)'
            styles.webkitBackdropFilter = 'blur(12px)'
            styles.backgroundColor = 'rgba(0, 0, 0, 0.1)' // Darken slightly for better visibility
            styles.border = '1px solid rgba(255, 255, 255, 0.2)'
            styles.color = styles.color || defaultWhite
        } else {
            Object.assign(styles, glass)
        }
    } else if (variant === 'gradient') {
        const gradient = getVal(settings, 'gradient', device)
        if (gradient && gradient.stops) {
            styles.backgroundImage = generateGradientCSS(gradient)
        } else {
            // Premium Default Gradient (Indigo to Purple)
            styles.backgroundImage = 'linear-gradient(135deg, #6366f1 0%, #a855f7 100%)'
        }
        styles.border = 'none'
        styles.color = styles.color || defaultWhite
    } else if (variant === 'outline' || variant === 'ghost') {
        styles.backgroundColor = 'transparent'
        
        // If color is the default white, change it to dark for visibility on transparent variants
        if (!styles.color || styles.color === defaultWhite) {
            styles.color = defaultDark
        }

        if (variant === 'outline') {
            styles.borderWidth = styles.borderWidth || '2px'
            styles.borderStyle = 'solid'
            styles.borderColor = styles.color
        } else {
            styles.border = 'none'
        }
    } else {
        // Solid
        if (!useCustom) {
            styles.backgroundColor = styles.backgroundColor || defaultDark
            styles.color = styles.color || defaultWhite
            styles.border = 'none'
        }
    }

    // Default transition for all buttons
    styles.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'

    // 4. Hover Colors (Via CSS Variables)
    const hoverBg = getVal(settings, 'hover_background_color', device)
    const hoverText = getVal(settings, 'hover_text_color', device)
    if (hoverBg) styles['--hover-bg'] = hoverBg
    if (hoverText) styles['--hover-color'] = hoverText

    return styles
}

const iconStyles = (settings: any, device: string) => {
  const size = getVal(settings, 'iconSize', device) || 16
  return {
    width: `${size}px`,
    height: `${size}px`,
    marginLeft: getVal(settings, 'iconPosition', device) === 'right' ? '8px' : '0',
    marginRight: getVal(settings, 'iconPosition', device) === 'left' ? '8px' : '0',
  }
}

const onButtonClick = (e: MouseEvent) => {
  if (props.mode === 'edit') e.preventDefault()
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
</script>

<style scoped>
.button-element {
  text-decoration: none;
  cursor: pointer;
  min-width: 120px;
}

/* Hover Animations */
.btn-hover-lift:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.btn-hover-zoom:hover {
    transform: scale(1.05);
}

.btn-hover-pulse:hover {
    animation: btn-pulse 1.5s infinite;
}

@keyframes btn-pulse {
    0% { box-shadow: 0 0 0 0 rgba(var(--primary-rgb, 32, 89, 234), 0.4); }
    70% { box-shadow: 0 0 0 15px rgba(var(--primary-rgb, 32, 89, 234), 0); }
    100% { box-shadow: 0 0 0 0 rgba(var(--primary-rgb, 32, 89, 234), 0); }
}

/* Shine Effect */
.btn-shine-overlay {
    position: absolute;
    top: 0;
    left: -100%;
    width: 50%;
    height: 100%;
    background: linear-gradient(to right, transparent, rgba(255,255,255,0.3), transparent);
    transform: skewX(-25deg);
    transition: none;
}
.btn-hover-shine:hover .btn-shine-overlay {
    animation: btn-shine 0.8s forwards;
}

@keyframes btn-shine {
    100% { left: 150%; }
}

/* Sweep Effect */
.btn-sweep-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 0;
    height: 100%;
    background: rgba(0,0,0,0.1);
    transition: width 0.3s ease;
    z-index: 0;
}
.btn-hover-sweep:hover .btn-sweep-overlay {
    width: 100%;
}

/* Variant Specific Hover Fixes */
.button-element {
    transition: all 0.3s ease;
}

/* Custom Hover Colors */
.button-element:hover {
    background-color: var(--hover-bg) !important;
    color: var(--hover-color) !important;
}

/* Outline hover: fill background slightly (if no custom hover bg) */
.btn-variant-outline:not([style*="--hover-bg"]):hover {
    background-color: rgba(var(--primary-rgb, 17, 24, 39), 0.05) !important;
}

/* Ghost hover: fill background slightly (if no custom hover bg) */
.btn-variant-ghost:not([style*="--hover-bg"]):hover {
    background-color: rgba(var(--primary-rgb, 17, 24, 39), 0.08) !important;
}

/* Glass hover: increase brightness (if no custom hover bg) */
.btn-variant-glass:not([style*="--hover-bg"]):hover {
    filter: brightness(1.1);
}
</style>

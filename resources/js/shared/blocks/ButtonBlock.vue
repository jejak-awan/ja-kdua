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
            `btn-hover-${getVal(settings, 'hover_effect', blockDevice) || 'lift'}`,
            { 'overflow-hidden': getVal(settings, 'hover_effect', blockDevice) === 'shine' || getVal(settings, 'hover_effect', blockDevice) === 'sweep' }
        )"
        :style="buttonStyles(settings, blockDevice, baseStyles)"
        :as="mode === 'edit' ? 'button' : 'a'"
        :href="mode === 'edit' ? undefined : (getVal(settings, 'url') || '#')"
        :target="getVal(settings, 'openNewTab') ? '_blank' : '_self'"
        @click="onButtonClick"
        v-bind="getAttributes('button')"
      >
        <!-- Shine Element -->
        <div v-if="getVal(settings, 'hover_effect', blockDevice) === 'shine'" class="btn-shine-overlay"></div>
        
        <!-- Sweep Element -->
        <div v-if="getVal(settings, 'hover_effect', blockDevice) === 'sweep'" class="btn-sweep-overlay"></div>

        <!-- Icon Left -->
        <component 
          v-if="getVal(settings, 'iconName') && getVal(settings, 'iconPosition') === 'left'"
          :is="getIconComponent(getVal(settings, 'iconName'))"
          class="btn-icon-left transition-transform duration-300 group-hover:-translate-x-1"
          :style="iconStyles(settings)"
        />

        <!-- Editable Text -->
        <span 
          :contenteditable="blockMode === 'edit'"
          @blur="onTextBlur($event, settings)"
          class="outline-none relative z-10"
          v-html="getVal(settings, 'text') || 'Button Text'"
        ></span>

        <!-- Icon Right -->
        <component 
          v-if="getVal(settings, 'iconName') && getVal(settings, 'iconPosition') === 'right'"
          :is="getIconComponent(getVal(settings, 'iconName'))"
          class="btn-icon-right transition-transform duration-300 group-hover:translate-x-1"
          :style="iconStyles(settings)"
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

    // 1. Inherit all design properties from BaseBlock (background, spacing, border, shadow, etc.)
    Object.assign(styles, baseStyles)

    // 2. Typography
    const typography = getTypographyStyles(settings, '', device)
    Object.assign(styles, typography)

    // 3. Variant Overrides & Color Defaults
    if (variant === 'glass') {
        Object.assign(styles, getGlassStyles(settings, '', device))
        styles.color = styles.color || '#ffffff'
    } else if (variant === 'gradient') {
        const gradient = getVal(settings, 'gradient', device)
        if (gradient) {
            styles.backgroundImage = generateGradientCSS(gradient)
            styles.border = 'none'
            styles.color = styles.color || '#ffffff'
        }
    } else if (variant === 'outline') {
        styles.backgroundColor = 'transparent'
        styles.borderWidth = styles.borderWidth || '2px'
        styles.borderStyle = 'solid'
        styles.borderColor = styles.color || styles.backgroundColor || '#111827'
    } else if (variant === 'ghost') {
        styles.backgroundColor = 'transparent'
        styles.border = 'none'
    } else {
        // Solid
        styles.color = styles.color || '#ffffff'
    }

    return styles
}

const iconStyles = (settings: any) => {
  const size = getVal(settings, 'iconSize') || 16
  return {
    width: `${size}px`,
    height: `${size}px`,
    marginLeft: getVal(settings, 'iconPosition') === 'right' ? '8px' : '0',
    marginRight: getVal(settings, 'iconPosition') === 'left' ? '8px' : '0',
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
</style>

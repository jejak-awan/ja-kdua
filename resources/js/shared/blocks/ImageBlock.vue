<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ mode: blockMode, settings, device: blockDevice }">
      <div 
        class="image-block-container" 
        :id="getVal(settings, 'html_id', blockDevice)"
        :style="containerStyles(settings, blockDevice)"
      >
        <figure 
          ref="figureRef"
          class="image-figure relative transition-all duration-500"
          :class="[
            shadowClass(settings, blockDevice),
            hoverShadowClass(settings, blockDevice),
            hoverEffectClass(settings)
          ]"
          :style="figureStyles(settings, blockDevice)"
          @mousemove="handleMouseMove"
          @mouseleave="handleMouseLeave"
        >
          <!-- The Link or Div -->
          <component
            :is="getVal(settings, 'link_url') ? 'a' : 'div'"
            class="image-link-wrapper block relative h-full w-full overflow-hidden"
            :href="getVal(settings, 'link_url') || undefined"
            :target="getVal(settings, 'link_target') || '_self'"
            :aria-label="getVal(settings, 'aria_label', blockDevice) || undefined"
            :role="getVal(settings, 'aria_label', blockDevice) ? 'img' : undefined"
            @click="onLinkClick"
            :style="maskStyles(settings, blockDevice)"
          >
            <!-- Image Element -->
            <img 
              v-if="getVal(settings, 'src', blockDevice)"
              :src="getVal(settings, 'src', blockDevice)"
              :alt="getVal(settings, 'alt') || 'Image'"
              :title="getVal(settings, 'title') || undefined"
              class="block w-full h-full transition-all duration-700 ease-out image-element"
              :class="[objectFitClass(settings), filterClasses(settings)]"
              :style="imageElementStyles(settings, blockDevice)"
              :loading="getVal(settings, 'lazyLoad') !== false ? 'lazy' : 'eager'"
            />
            
            <!-- Placeholder -->
            <div 
              v-else 
              class="image-placeholder bg-gray-100 border-2 border-dashed border-gray-200 flex flex-col items-center justify-center p-8 text-center"
              :style="{ height: getVal(settings, 'height', blockDevice) || '300px' }"
            >
              <div class="p-4 rounded-full bg-white shadow-sm mb-4">
                <ImageIcon class="w-8 h-8 text-gray-300" />
              </div>
              <p class="text-sm font-medium text-gray-400">
                {{ blockMode === 'edit' ? 'Select an image' : '' }}
              </p>
            </div>

            <!-- Hover Overlay -->
            <div 
              v-if="hasOverlay(settings)"
              class="absolute inset-0 transition-opacity duration-300 opacity-0 group-hover:opacity-100"
              :style="overlayStyles(settings, blockDevice)"
            >
              <div v-if="getVal(settings, 'showZoomIcon')" class="flex items-center justify-center h-full">
                <ZoomIn class="w-8 h-8 text-white drop-shadow-md" />
              </div>
            </div>
          </component>

          <!-- Caption -->
          <figcaption 
            v-if="getVal(settings, 'showCaption') && getVal(settings, 'caption')" 
            class="image-caption-container transition-all duration-300"
            :class="captionStaticClass(settings)"
            :style="captionStyles(settings, blockDevice)"
          >
            {{ getVal(settings, 'caption') }}
          </figcaption>
        </figure>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
    getTypographyStyles, 
    getVal,
    getMaskStyles,
    getLayoutStyles,
    toCSS
} from '../utils/styleUtils'
import { Image as ImageIcon, ZoomIn } from 'lucide-vue-next'
import type { BlockInstance } from '../../types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance;
  mode: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile' | null;
}>(), {
  mode: 'view',
  device: 'desktop'
})

const figureRef = ref<HTMLElement | null>(null)
const tiltStyle = ref({ transform: 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)' })

const handleMouseMove = (e: MouseEvent) => {
    const effect = getVal(props.module.settings, 'hover_effect')
    if (effect !== 'tilt' || !figureRef.value) return

    const rect = figureRef.value.getBoundingClientRect()
    const x = e.clientX - rect.left
    const y = e.clientY - rect.top
    
    const centerX = rect.width / 2
    const centerY = rect.height / 2
    
    const rotateX = ((y - centerY) / centerY) * -10 // Max 10 degrees
    const rotateY = ((x - centerX) / centerX) * 10 // Max 10 degrees
    
    tiltStyle.value.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.05, 1.05, 1.05)`
}

const handleMouseLeave = () => {
    tiltStyle.value.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)'
}

const containerStyles = (settings: any, device: string) => {
  const align = getVal(settings, 'alignment', device) || 'center'
  return {
    width: '100%',
    display: 'flex',
    justifyContent: align === 'left' ? 'flex-start' : align === 'right' ? 'flex-end' : 'center',
    textAlign: align as any,
    ...getLayoutStyles(settings, device)
  }
}

const figureStyles = (settings: any, device: string) => {
  const width = getVal(settings, 'width', device)
  const height = getVal(settings, 'height', device)
  const aspectRatio = getVal(settings, 'aspectRatio', device) || 'auto'
  
  const style: Record<string, any> = {
    maxWidth: width > 0 && width !== '100%' ? toCSS(width) : '100%',
    width: getVal(settings, 'forceFullwidth', device) ? '100%' : 'auto',
    position: 'relative',
    display: 'inline-block',
    transition: 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)'
  }
  
  if (aspectRatio !== 'auto') style.aspectRatio = aspectRatio
  else if (height) style.height = toCSS(height)

  // Hover Variables
  style['--hover-scale'] = `scale(${getVal(settings, 'hover_scale', device) || 1.05})`;
  style['--hover-brightness'] = `brightness(${getVal(settings, 'hover_brightness', device) || 100}%)`;

  // Add 3D Tilt transform if active
  if (getVal(settings, 'hover_effect') === 'tilt') {
      style.transform = tiltStyle.value.transform
      style.transformStyle = 'preserve-3d'
  }

  return style
}

const maskStyles = (settings: any, device: string) => {
    return getMaskStyles(settings, '', device)
}

const imageElementStyles = (settings: any, device: string) => {
  const filters = []
  const b = getVal(settings, 'brightness', device)
  const c = getVal(settings, 'contrast', device)
  const s = getVal(settings, 'saturate', device)
  const bl = getVal(settings, 'blur', device)
  const g = getVal(settings, 'grayscale', device)

  if (bl > 0) filters.push(`blur(${bl}px)`)
  if (b !== undefined && b !== 100) filters.push(`brightness(${b}%)`)
  if (c !== undefined && c !== 100) filters.push(`contrast(${c}%)`)
  if (s !== undefined && s !== 100) filters.push(`saturate(${s}%)`)
  if (g !== undefined && g > 0) filters.push(`grayscale(${g}%)`)

  return {
    height: '100%',
    objectPosition: getVal(settings, 'objectPosition', device) || 'center',
    filter: filters.length ? filters.join(' ') : undefined,
    transition: 'all 0.5s ease-in-out'
  }
}

const objectFitClass = (settings: any) => {
  const fit = getVal(settings, 'objectFit') || 'cover'
  return {
    'object-cover': fit === 'cover',
    'object-contain': fit === 'contain',
    'object-fill': fit === 'fill',
    'object-none': fit === 'none'
  }
}

const shadowClass = (settings: any, device: string) => {
  const s = getVal(settings, 'shadow', device)
  return s && s !== 'none' ? s : ''
}

const hoverShadowClass = (settings: any, device: string) => {
  const s = getVal(settings, 'hover_shadow', device)
  return s && s !== 'none' ? `group-hover:${s}` : ''
}

const hoverEffectClass = (settings: any) => {
  const effect = getVal(settings, 'hover_effect') || 'none'
  return {
    'group': true,
    'hover:scale-custom': effect === 'zoom',
    'hover:-translate-y-2': effect === 'lift',
  }
}

const filterClasses = (settings: any) => {
  const effect = getVal(settings, 'hover_effect') || 'none'
  const classes = []
  if (effect === 'reveal') classes.push('grayscale group-hover:grayscale-0')
  if (effect === 'colorize') classes.push('grayscale group-hover:grayscale-0')
  if (effect === 'desaturate') classes.push('group-hover:grayscale')
  if (effect === 'brighten') classes.push('group-hover:brightness-110')
  
  // Custom interactive brightness
  classes.push('group-hover:brightness-custom')
  
  return classes.join(' ')
}

const hasOverlay = (settings: any) => {
  return getVal(settings, 'overlayEnabled')
}

const overlayStyles = (settings: any, device: string) => {
  const color = getVal(settings, 'overlayColor', device) || 'rgba(0,0,0,0.3)'
  return {
    backgroundColor: color
  }
}

const captionStyles = (settings: any, device: string) => {
  return getTypographyStyles(settings, 'caption_', device)
}

const captionStaticClass = (settings: any) => {
  return 'absolute bottom-0 left-0 right-0 bg-black/60 backdrop-blur-sm text-white p-4 text-sm text-center transform translate-y-full group-hover:translate-y-0'
}

const onLinkClick = (e: MouseEvent) => {
  if (props.mode === 'edit') {
    e.preventDefault()
  }
}

</script>

<style scoped>
.image-block-container {
  width: 100%;
}
.image-figure {
  margin: 0;
  vertical-align: top;
  will-change: transform, filter, box-shadow;
}

.image-element {
  will-change: transform, filter;
}

.hover\:scale-custom:hover {
  transform: var(--hover-scale);
}

.group:hover .group-hover\:brightness-custom {
  filter: var(--hover-brightness);
}
</style>

<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ mode: blockMode, settings, device: blockDevice }">
      <div 
        class="image-block-container" 
        :class="alignmentClass(settings, blockDevice)"
      >
        <figure 
          class="image-figure relative overflow-hidden group transition-all duration-300"
          :class="[
            radiusClass(settings),
            shadowClass(settings),
            hoverEffectClass(settings)
          ]"
          :style="figureStyles(settings, blockDevice)"
        >
          <!-- The Link or Div -->
          <component
            :is="getVal(settings, 'linkUrl') ? 'a' : 'div'"
            class="image-link-wrapper block relative h-full"
            :href="getVal(settings, 'linkUrl') || undefined"
            :target="getVal(settings, 'linkNewTab') ? '_blank' : '_self'"
            @click="onLinkClick"
          >
            <!-- Image Element -->
            <img 
              v-if="getVal(settings, 'url')"
              :src="getVal(settings, 'url')"
              :alt="getVal(settings, 'alt') || 'Image'"
              class="block w-full transition-all duration-500"
              :class="[objectFitClass(settings), filterClasses(settings)]"
              :style="imageElementStyles(settings, blockDevice)"
              :loading="getVal(settings, 'lazyLoad') !== false ? 'lazy' : 'eager'"
            />
            
            <!-- Placeholder -->
            <div 
              v-else 
              class="image-placeholder bg-muted/20 border-2 border-dashed border-primary/10 flex flex-col items-center justify-center p-8 text-center"
              :style="{ height: getVal(settings, 'height', blockDevice) || '300px' }"
            >
              <div class="p-4 rounded-full bg-background shadow-sm mb-4">
                <ImageIcon class="w-8 h-8 text-muted-foreground/50" />
              </div>
              <p class="text-sm font-medium text-muted-foreground">
                {{ blockMode === 'edit' ? 'Select an image' : '' }}
              </p>
            </div>

            <!-- Hover Overlay (Unified) -->
            <div 
              v-if="hasOverlay(settings)"
              class="absolute inset-0 transition-opacity duration-300"
              :class="blockMode === 'edit' ? 'opacity-0' : 'opacity-0 group-hover:opacity-100'"
              :style="overlayStyles(settings, blockDevice)"
            >
              <div v-if="getVal(settings, 'showZoomIcon')" class="flex items-center justify-center h-full">
                <ZoomIn class="w-8 h-8 text-white drop-shadow-md" />
              </div>
            </div>
          </component>

          <!-- Caption -->
          <figcaption 
            v-if="getVal(settings, 'caption')" 
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

<script setup>
import { computed } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getTypographyStyles, getVal, getResponsiveValue } from '../utils/styleUtils'
import { Image as ImageIcon, ZoomIn } from 'lucide-vue-next'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

// Logic derived from both builder and renderer
const alignmentClass = (settings, device) => {
  const align = getVal(settings, 'alignment', device) || 'center'
  return {
    'flex justify-start text-left': align === 'left',
    'flex justify-center text-center': align === 'center',
    'flex justify-end text-right': align === 'right'
  }
}

const figureStyles = (settings, device) => {
  const width = getVal(settings, 'width', device)
  const height = getVal(settings, 'height', device)
  const aspectRatio = getVal(settings, 'aspectRatio', device) || 'auto'
  
  const style = {
    maxWidth: width > 0 ? `min(${width}px, 100%)` : '100%',
    width: '100%',
    position: 'relative'
  }
  
  if (aspectRatio !== 'auto') style.aspectRatio = aspectRatio
  else if (height) style.height = height

  return style
}

const imageElementStyles = (settings, device) => {
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
    filter: filters.length ? filters.join(' ') : undefined
  }
}

const objectFitClass = (settings) => {
  const fit = getVal(settings, 'objectFit') || 'cover'
  return {
    'object-cover': fit === 'cover',
    'object-contain': fit === 'contain',
    'object-fill': fit === 'fill',
    'object-none': fit === 'none'
  }
}

const radiusClass = (settings) => getVal(settings, 'radius') || 'rounded-lg'
const shadowClass = (settings) => {
  const s = getVal(settings, 'shadow')
  return s && s !== 'none' ? s : ''
}

const hoverEffectClass = (settings) => {
  const effect = getVal(settings, 'hoverEffect') || 'none'
  return {
    'hover:scale-[1.02]': effect === 'zoom',
    'overflow-hidden': true
  }
}

const filterClasses = (settings) => {
  const effect = getVal(settings, 'hoverEffect') || 'none'
  const classes = []
  if (effect === 'colorize') classes.push('grayscale group-hover:grayscale-0')
  if (effect === 'desaturate') classes.push('group-hover:grayscale')
  if (effect === 'brighten') classes.push('group-hover:brightness-110')
  return classes.join(' ')
}

const hasOverlay = (settings) => {
  const effect = getVal(settings, 'hoverEffect')
  return effect === 'overlay' || effect === 'brighten' || getVal(settings, 'overlayEnabled')
}

const overlayStyles = (settings, device) => {
  const color = getVal(settings, 'overlayColor', device) || 'rgba(0,0,0,0.3)'
  return {
    backgroundColor: color
  }
}

const captionStyles = (settings, device) => {
  return getTypographyStyles(settings, 'caption_', device)
}

const captionStaticClass = (settings) => {
  return 'absolute bottom-0 left-0 right-0 bg-black/60 backdrop-blur-sm text-white p-4 text-sm text-center transform translate-y-full group-hover:translate-y-0'
}

const onLinkClick = (e) => {
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
  display: inline-block;
  vertical-align: top;
}
</style>

<template>
    <section 
        class="section relative transition-[background-position] duration-200 ease-out" 
        :style="styles" 
        :id="id" 
        ref="sectionRef"
        @mousemove="handleMouseMove"
        @mouseleave="handleMouseLeave"
    >
         <BackgroundMedia :settings="settings" />
         <div class="relative z-10" :class="[ fullWidth ? 'w-full' : 'container mx-auto px-4' ]">
              <BlockRenderer 
                 :blocks="nestedBlocks" 
                 :context="context"
                 :is-preview="isPreview"
              />
         </div>
    </section>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import BlockRenderer from '../BlockRenderer.vue'
import BackgroundMedia from './BackgroundMedia.vue'
import { getBorderStyles, getSpacingStyles, getBoxShadowStyles, getBackgroundStyles, getSizingStyles, getTransformStyles } from '../utils'

const props = defineProps({
  id: String,
  fullWidth: Boolean,
  backgroundColor: String,
  backgroundImage: String,
  padding: Object,
  margin: Object,
  border: Object,
  boxShadow: Object,
  settings: { type: Object, default: () => ({}) },
  nestedBlocks: { type: Array, default: () => [] },
  context: Object,
  isPreview: Boolean
})

// Parallax State
const scrollY = ref(0)
const mouseX = ref(0)
const mouseY = ref(0)
const sectionRef = ref(null)

const handleMouseMove = (e) => {
    if (!props.isPreview) return
    const rect = sectionRef.value.getBoundingClientRect()
    // Normalise mouse position to -1 to 1 range
    mouseX.value = ((e.clientX - rect.left) / rect.width) * 2 - 1
    mouseY.value = ((e.clientY - rect.top) / rect.height) * 2 - 1
}

const handleMouseLeave = () => {
    mouseX.value = 0
    mouseY.value = 0
}

const handleScroll = (e) => {
    // If event is provided (target is specific element), use its scrollTop
    if (e && e.target && e.target.scrollTop !== undefined) {
        scrollY.value = e.target.scrollTop
    } else {
        scrollY.value = window.scrollY
    }
}

onMounted(() => {
    // In builder, we need to listen to the canvas-frame's scroll
    const frame = document.querySelector('.canvas-frame')
    if (frame) {
        frame.addEventListener('scroll', handleScroll)
        // Initial sync
        scrollY.value = frame.scrollTop
    } else {
        window.addEventListener('scroll', handleScroll)
    }
})

onUnmounted(() => {
    const frame = document.querySelector('.canvas-frame')
    if (frame) frame.removeEventListener('scroll', handleScroll)
    window.removeEventListener('scroll', handleScroll)
})

const styles = computed(() => {
  const s = {}
  
  // Base background color as fallback
  if(props.backgroundColor) s.backgroundColor = props.backgroundColor
  
  // Use robust background logic from utils
  const settings = props.settings || {}
  if (settings) {
      const bgStyles = getBackgroundStyles(settings)
      Object.assign(s, bgStyles)

      // Handle "True Parallax" (JS-based)
      const isParallax = settings.parallax === true || settings.parallax === 'true'
      const isTrueParallax = isParallax && (settings.parallaxMethod === 'true' || settings.parallaxMethod === true)
      
      if (isTrueParallax && sectionRef.value) {
          const speed = 0.5
          const itemTop = sectionRef.value.offsetTop || 0
          const scrollOffset = (scrollY.value - itemTop) * speed
          
          // More noticeable mouse nudge (max 30px)
          const mouseNudgeX = mouseX.value * 30
          const mouseNudgeY = mouseY.value * 30
          
          const basePos = bgStyles.backgroundPosition || 'center center'
          const parts = basePos.split(',').map(pos => {
              const p = pos.trim().split(/\s+/)
              let x = p[0] || 'center'
              let y = p[1] || 'center'
              
              // Normalize keywords to % for calc
              const xNorm = x === 'left' ? '0%' : x === 'right' ? '100%' : x === 'center' ? '50%' : x
              const yNorm = y === 'top' ? '0%' : y === 'bottom' ? '100%' : y === 'center' ? '50%' : y
              
              const xFinal = xNorm.includes('%') || xNorm.includes('px') ? `calc(${xNorm} + ${mouseNudgeX}px)` : xNorm
              const yFinal = `calc(${yNorm} + ${scrollOffset + mouseNudgeY}px)`
              
              return `${xFinal} ${yFinal}`
          })
          s.backgroundPosition = parts.join(', ')
          s.backgroundAttachment = 'scroll'
      }
  } else if(props.backgroundImage) {
      s.backgroundImage = `url(${props.backgroundImage})`
      s.backgroundSize = 'cover'
      s.backgroundPosition = 'center'
  }
  
  if(props.padding) Object.assign(s, getSpacingStyles(props.padding, 'padding'))
  if(props.margin) Object.assign(s, getSpacingStyles(props.margin, 'margin'))
  if(props.border) Object.assign(s, getBorderStyles(props.border))
  if(props.boxShadow) Object.assign(s, getBoxShadowStyles(props.boxShadow))
  
  // Apply Sizing (Height, MinHeight, Overflow, ZIndex)
  if(settings) {
      Object.assign(s, getSizingStyles(settings))
      Object.assign(s, getTransformStyles(settings))

      // Vertical Alignment Logic
      const vAlign = settings.verticalAlign || 'start'
      s.display = 'flex'
      s.flexDirection = 'column'
      if (vAlign === 'center') s.justifyContent = 'center'
      else if (vAlign === 'end') s.justifyContent = 'flex-end'
      else s.justifyContent = 'flex-start'
  }
  
  return s
})
</script>

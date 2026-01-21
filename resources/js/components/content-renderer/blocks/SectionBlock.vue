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
         <div class="relative z-10" :class="[ isFullWidth ? 'w-full' : 'container mx-auto px-4' ]">
              <BlockRenderer 
                 :blocks="props.nestedBlocks" 
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
import { getBorderStyles, getSpacingStyles, getBoxShadowStyles, getBackgroundStyles, getSizingStyles, getTransformStyles, getVal } from '../utils'

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

const sectionRef = ref(null)
const mouseX = ref(0)
const mouseY = ref(0)

const isFullWidth = computed(() => {
    const settings = props.settings || {}
    return getVal(settings, 'fullWidth') ?? props.fullWidth ?? false
})

const handleMouseMove = (e) => {
    if (!props.isPreview || !sectionRef.value) return
    const rect = sectionRef.value.getBoundingClientRect()
    mouseX.value = ((e.clientX - rect.left) / rect.width) * 2 - 1
    mouseY.value = ((e.clientY - rect.top) / rect.height) * 2 - 1
}

const handleMouseLeave = () => {
    mouseX.value = 0
    mouseY.value = 0
}

const styles = computed(() => {
  const s = {}
  const settings = props.settings || {}

  if (props.backgroundColor) s.backgroundColor = props.backgroundColor
  
  if (settings) {
      Object.assign(s, getBackgroundStyles(settings))
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

      // Layout Logic (Responsive)
      const layoutDisplay = getVal(settings, 'display')
      const layoutFlexDir = getVal(settings, 'flexDirection') || getVal(settings, 'direction')
      const vAlign = getVal(settings, 'verticalAlign') || 'start'

      if (layoutDisplay) {
          s.display = layoutDisplay
          if (layoutFlexDir) s.flexDirection = layoutFlexDir
      } else {
          // Default to Flex Column
          s.display = 'flex'
          s.flexDirection = 'column'
          
          if (vAlign === 'center') s.justifyContent = 'center'
          else if (vAlign === 'end') s.justifyContent = 'flex-end'
          else s.justifyContent = 'flex-start'
      }
  }
  
  return s
})
</script>

<style scoped>
.section {
    width: 100%;
}
</style>

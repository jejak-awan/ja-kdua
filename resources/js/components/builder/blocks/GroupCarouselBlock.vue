<template>
  <div class="group-carousel-block" :style="containerStyles">
    <div class="carousel-viewport" :style="viewportStyles">
      <div class="carousel-track" :style="trackStyles">
        <!-- Render actual children as slides -->
        <template v-if="module.children && module.children.length > 0">
          <div 
            v-for="(child, idx) in module.children" 
            :key="child.id" 
            class="carousel-slide" 
            :style="slideStyles"
          >
            <!-- Render the child module via slot provided by ModuleWrapper -->
            <slot :name="`slide-${idx}`">
               <!-- ModuleRenderer will be injected here via slot when using ModuleWrapper -->
               <component 
                 :is="'ModuleWrapper'" 
                 v-if="false"
                 :module="child" 
               />
               <!-- Fallback if slot not used (though ModuleWrapper handles this) -->
               <div class="internal-slot-placeholder">
                  <slot :child="child" />
               </div>
            </slot>
          </div>
        </template>
        
        <!-- Empty state placeholders if no children -->
        <template v-else>
          <div v-for="i in 3" :key="'placeholder-'+i" class="carousel-slide" :style="slideStyles">
            <div class="slide-placeholder">
              <Layers class="placeholder-icon" />
              <span>Slide {{ i }}</span>
              <small>Add modules to this carousel</small>
            </div>
          </div>
        </template>
      </div>
    </div>
    
    <!-- Arrows -->
    <template v-if="showControls">
      <button v-if="settings.showArrows !== false" class="carousel-arrow carousel-arrow--prev" :style="arrowStyles" @click="prev">
        <ChevronLeft />
      </button>
      <button v-if="settings.showArrows !== false" class="carousel-arrow carousel-arrow--next" :style="arrowStyles" @click="next">
        <ChevronRight />
      </button>
      
      <!-- Dots -->
      <div v-if="settings.showDots !== false && totalPages > 1" class="carousel-dots">
        <button v-for="i in totalPages" :key="i" class="carousel-dot" :style="dotStyles(i - 1)" @click="currentPage = i - 1" />
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, watch } from 'vue'
import { Layers, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { getSpacingStyles, getBackgroundStyles, getBorderStyles, getBoxShadowStyles, getSizingStyles, getResponsiveValue, getFilterStyles, getTransformStyles } from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const currentPage = ref(0)
let interval = null

const slidesPerView = computed(() => {
  const val = getResponsiveValue(settings.value, 'slidesPerView', device.value)
  return parseInt(val) || 1
})

const totalSlides = computed(() => props.module.children?.length || 0)
const totalPages = computed(() => totalSlides.value > 0 ? Math.ceil(totalSlides.value / slidesPerView.value) : 1)

const showControls = computed(() => totalSlides.value > 0)

const next = () => { 
  if (totalPages.value <= 1) return
  currentPage.value = settings.value.loop ? (currentPage.value + 1) % totalPages.value : Math.min(currentPage.value + 1, totalPages.value - 1) 
}

const prev = () => { 
  if (totalPages.value <= 1) return
  currentPage.value = settings.value.loop ? (currentPage.value - 1 + totalPages.value) % totalPages.value : Math.max(currentPage.value - 1, 0) 
}

// Reset page if children count changes or slidesPerView changes
watch([totalSlides, slidesPerView], () => {
  if (currentPage.value >= totalPages.value) {
    currentPage.value = Math.max(0, totalPages.value - 1)
  }
})

const startAutoplay = () => {
    stopAutoplay()
    if (settings.value.autoplay && totalPages.value > 1) {
        interval = setInterval(next, parseInt(settings.value.autoplaySpeed) || 5000)
    }
}

const stopAutoplay = () => {
    if (interval) {
        clearInterval(interval)
        interval = null
    }
}

onMounted(startAutoplay)
onUnmounted(stopAutoplay)

// Restart autoplay if settings change
watch(() => [settings.value.autoplay, settings.value.autoplaySpeed, totalPages.value], startAutoplay)

const containerStyles = computed(() => {
  const styles = { position: 'relative', width: '100%' }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  
  if (!styles.minHeight) styles.minHeight = '300px'
  
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))

  return styles
})

const viewportStyles = computed(() => ({ overflow: 'hidden', width: '100%' }))

const trackStyles = computed(() => ({
  display: 'flex',
  gap: `${getResponsiveValue(settings.value, 'gap', device.value) || 20}px`,
  transform: `translateX(-${currentPage.value * 100}%)`,
  transition: `transform ${settings.value.speed || 500}ms ease`,
  width: '100%'
}))

const slideStyles = computed(() => {
  const gap = parseInt(getResponsiveValue(settings.value, 'gap', device.value)) || 20
  const spv = slidesPerView.value
  return {
    flex: `0 0 calc(${100 / spv}% - ${(gap * (spv - 1)) / spv}px)`,
    minWidth: 0,
    flexShrink: 0,
    position: 'relative'
  }
})

const arrowStyles = computed(() => ({
  backgroundColor: settings.value.arrowBackgroundColor || '#2059ea',
  color: settings.value.arrowColor || '#ffffff',
  borderRadius: settings.value.arrowStyle === 'square' ? '4px' : settings.value.arrowStyle === 'minimal' ? '0' : '50%',
  border: settings.value.arrowStyle === 'minimal' ? 'none' : undefined,
  background: settings.value.arrowStyle === 'minimal' ? 'transparent' : undefined
}))

const dotStyles = (index) => ({
  backgroundColor: currentPage.value === index ? (settings.value.dotsActiveColor || '#1a47b8') : (settings.value.dotsColor || '#2059ea'),
  opacity: currentPage.value === index ? 1 : 0.4
})
</script>

<style scoped>
.group-carousel-block { width: 100%; }
.slide-placeholder {
  height: 200px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 2px dashed #ddd;
  border-radius: 8px;
  color: #999;
  gap: 8px;
}
.placeholder-icon { width: 32px; height: 32px; opacity: 0.5; }
.slide-placeholder span { font-weight: 500; }
.slide-placeholder small { font-size: 12px; opacity: 0.7; }
.carousel-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 10;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  cursor: pointer;
  transition: opacity 0.2s;
}
.carousel-arrow:hover { opacity: 0.9; }
.carousel-arrow--prev { left: 12px; }
.carousel-arrow--next { right: 12px; }
.carousel-dots { display: flex; justify-content: center; gap: 8px; margin-top: 20px; }
.carousel-dot { width: 10px; height: 10px; border-radius: 50%; border: none; cursor: pointer; transition: opacity 0.2s; }
.internal-slot-placeholder { pointer-events: auto; }
</style>

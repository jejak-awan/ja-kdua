<template>
  <BaseBlock :module="module" :settings="settings" class="fullwidth-slider-block" :style="containerStyles">
    <div class="slider-slides-container" :style="{ height: '100%', width: '100%', position: 'relative' }">
      <slot v-if="mode === 'edit'"></slot>
      <template v-else>
        <div 
          v-for="(child, index) in module.children" 
          :key="child.id"
          class="fullwidth-slide-wrapper"
          :style="{ 
            position: 'absolute', 
            inset: 0, 
            zIndex: currentSlide === index ? 10 : 1, 
            opacity: currentSlide === index ? 1 : 0,
            transition: 'opacity 0.5s ease-in-out',
            pointerEvents: currentSlide === index ? 'auto' : 'none' 
          }"
        >
          <component 
            :is="getChildComponent(child.type)" 
            :module="child" 
            :index="index"
          />
        </div>
      </template>
    </div>
    
    <button v-if="settings.showArrows !== false" class="slider-arrow slider-arrow--prev" @click="prev" aria-label="Previous slide">
      <ChevronLeft />
    </button>
    <button v-if="settings.showArrows !== false" class="slider-arrow slider-arrow--next" @click="next" aria-label="Next slide">
      <ChevronRight />
    </button>
    
    <div v-if="settings.showDots !== false" class="slider-dots">
      <button 
        v-for="i in (module.children?.length || 0)" 
        :key="i" 
        class="slider-dot" 
        :class="{ 'slider-dot--active': currentSlide === i - 1 }" 
        @click="currentSlide = i - 1"
        :aria-label="`Go to slide ${i}`"
      />
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, inject, provide } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { getResponsiveValue } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const currentSlide = ref(0)
let interval = null

const next = () => { 
    const count = props.module.children?.length || 0
    if (count > 0) currentSlide.value = (currentSlide.value + 1) % count 
}
const prev = () => { 
    const count = props.module.children?.length || 0
    if (count > 0) currentSlide.value = currentSlide.value === 0 ? count - 1 : currentSlide.value - 1 
}

const startAutoplay = () => {
  stopAutoplay()
  if (settings.value.autoplay !== false && (props.module.children?.length || 0) > 1) {
    interval = setInterval(next, settings.value.autoplaySpeed || 5000)
  }
}

const stopAutoplay = () => {
  if (interval) {
    clearInterval(interval)
    interval = null
  }
}

onMounted(() => startAutoplay())
onUnmounted(() => stopAutoplay())

// Provide state to FullwidthSlideItemBlock
provide('fullwidthSliderState', {
    parentSettings: settings,
    currentSlide,
    mode: computed(() => props.mode)
})

const containerStyles = computed(() => {
  return { 
    position: 'relative', 
    overflow: 'hidden', 
    width: '100%',
    height: `${getResponsiveValue(settings.value, 'height', device.value) || 600}px`
  }
})

const getChildComponent = (type) => {
  // This logic should match how BlockRenderer gets components
  // For now, assuming FullwidthSlideItemBlock is registered globally or via a registry
  if (builder?.getComponent) return builder.getComponent(type)
  // Fallback for renderer if needed, but usually renderer handles this via slots or recursion
  return 'FullwidthSlideItemBlock' 
}
</script>

<style scoped>
.fullwidth-slider-block { width: 100%; }
.slider-arrow { 
  position: absolute; 
  top: 50%; 
  transform: translateY(-50%); 
  z-index: 20; 
  width: 48px; 
  height: 48px; 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  background: rgba(255,255,255,0.2); 
  border: none; 
  border-radius: 50%; 
  color: white; 
  cursor: pointer; 
  transition: background 0.3s;
}
.slider-arrow:hover { background: rgba(255,255,255,0.4); }
.slider-arrow--prev { left: 24px; }
.slider-arrow--next { right: 24px; }

.slider-dots { 
  position: absolute; 
  bottom: 24px; 
  left: 50%; 
  transform: translateX(-50%); 
  z-index: 20; 
  display: flex; 
  gap: 8px; 
}
.slider-dot { 
  width: 12px; 
  height: 12px; 
  border-radius: 50%; 
  border: none; 
  background: rgba(255,255,255,0.4); 
  cursor: pointer; 
  transition: background 0.3s;
}
.slider-dot--active { background: white; }
</style>

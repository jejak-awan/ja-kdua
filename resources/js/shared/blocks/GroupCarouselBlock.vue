<template>
  <BaseBlock :module="module" :settings="settings" class="group-carousel-block">
    <div class="carousel-container relative group" :style="containerStyles">
        <div class="carousel-viewport overflow-hidden" :style="viewportStyles">
            <div 
                class="carousel-track flex transition-transform duration-500 ease-in-out" 
                :style="trackStyles"
            >
                <!-- Builder Mode -->
                <template v-if="mode === 'edit'">
                    <slot />
                    <div v-if="!module.children?.length" v-for="i in 3" :key="i" class="carousel-slide flex-shrink-0 min-w-0 px-2" :style="slideStyles">
                        <div class="slide-placeholder h-[250px] flex flex-col items-center justify-center p-10 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-2xl text-gray-400 gap-3">
                            <Layers class="w-8 h-8 opacity-30" />
                            <span class="font-bold">Slide {{ i }}</span>
                        </div>
                    </div>
                </template>

                <!-- Renderer Mode -->
                <template v-else>
                    <div 
                        v-for="child in nestedBlocks" 
                        :key="child.id" 
                        class="carousel-slide flex-shrink-0 min-w-0 px-2"
                        :style="slideStyles"
                    >
                        <BlockRenderer
                          :block="child"
                          :mode="mode"
                        />
                    </div>
                </template>
            </div>
        </div>

        <!-- Navigation -->
        <template v-if="showControls">
            <button 
                v-if="settings.showArrows !== false" 
                class="carousel-arrow prev absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 w-12 h-12 flex items-center justify-center bg-white dark:bg-gray-800 rounded-full shadow-xl border border-gray-100 dark:border-gray-700 z-10 transition-all hover:scale-110 active:scale-95 opacity-0 group-hover:opacity-100" 
                :style="arrowStyles"
                @click="prev"
            >
                <ChevronLeft class="w-6 h-6" />
            </button>
            <button 
                v-if="settings.showArrows !== false" 
                class="carousel-arrow next absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 w-12 h-12 flex items-center justify-center bg-white dark:bg-gray-800 rounded-full shadow-xl border border-gray-100 dark:border-gray-700 z-10 transition-all hover:scale-110 active:scale-95 opacity-0 group-hover:opacity-100" 
                :style="arrowStyles"
                @click="next"
            >
                <ChevronRight class="w-6 h-6" />
            </button>
            
            <div v-if="settings.showDots !== false && totalPages > 1" class="carousel-dots flex justify-center gap-3 mt-8">
                <button 
                    v-for="i in totalPages" 
                    :key="i" 
                    class="carousel-dot h-2 rounded-full transition-all duration-300" 
                    :class="currentPage === i - 1 ? 'w-8 bg-black dark:bg-white' : 'w-2 bg-gray-300 dark:bg-gray-700'" 
                    :style="dotStyles(i - 1)"
                    @click="currentPage = i - 1" 
                />
            </div>
        </template>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, watch, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Layers, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { 
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  nestedBlocks: { type: Array, default: () => [] }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const BlockRenderer = inject('BlockRenderer', null)

const currentPage = ref(0)
let interval = null

const slidesPerView = computed(() => {
  const val = getResponsiveValue(settings.value, 'slidesPerView', device.value)
  return parseInt(val) || 1
})

const totalSlides = computed(() => props.mode === 'edit' ? (props.module.children?.length || 0) : props.nestedBlocks.length)
const totalPages = computed(() => totalSlides.value > 0 ? Math.ceil(totalSlides.value / slidesPerView.value) : 1)
const showControls = computed(() => totalSlides.value > (props.mode === 'edit' ? 0 : slidesPerView.value))

const next = () => { 
  if (totalPages.value <= 1) return
  currentPage.value = (currentPage.value + 1) % totalPages.value
}

const prev = () => { 
  if (totalPages.value <= 1) return
  currentPage.value = (currentPage.value - 1 + totalPages.value) % totalPages.value
}

const startAutoplay = () => {
    stopAutoplay()
    if (settings.value.autoplay && totalPages.value > 1 && props.mode === 'view') {
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

const containerStyles = computed(() => ({}))
const viewportStyles = computed(() => ({}))

const trackStyles = computed(() => ({
  transform: `translateX(-${currentPage.value * 100}%)`,
  transition: `transform ${settings.value.speed || 500}ms ease`
}))

const slideStyles = computed(() => ({
    flex: `0 0 ${100 / slidesPerView.value}%`
}))

const arrowStyles = computed(() => ({
  backgroundColor: settings.value.arrowBackgroundColor,
  color: settings.value.arrowColor
}))

const dotStyles = (index) => ({
  backgroundColor: currentPage.value === index ? settings.value.dotsActiveColor : settings.value.dotsColor
})
</script>

<style scoped>
.group-carousel-block { width: 100%; }
</style>

<template>
  <BaseBlock
    :module="module"
    :mode="mode"
    :settings="settings"
    class="star-rating-block transition-all duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Star Rating'"
  >
    <div 
      class="star-rating-container flex items-center gap-4 flex-wrap"
      :style="containerStyles"
    >
      <div class="stars-group flex gap-1">
        <div 
          v-for="i in maxRating" 
          :key="i" 
          class="relative"
          :style="{ width: starSize + 'px', height: starSize + 'px' }"
        >
          <!-- Base / Empty Star -->
          <Star 
            class="absolute inset-0" 
            :style="{ color: emptyStarColor, width: starSize + 'px', height: starSize + 'px', fill: 'currentColor' }" 
          />
          <!-- Filled Star -->
          <div 
            class="absolute inset-0 overflow-hidden text-amber-400" 
            :style="{ 
                width: getStarFillWidth(i), 
                color: starColor,
                transition: 'width 0.3s ease'
            }"
          >
            <Star 
                class="shrink-0" 
                :style="{ width: starSize + 'px', height: starSize + 'px', fill: 'currentColor' }" 
            />
          </div>
        </div>
      </div>
      
      <div class="rating-info flex items-center gap-3">
        <Badge 
          v-if="getVal(settings, 'showNumber', device) !== false" 
          variant="secondary"
          class="rating-number font-black text-lg h-9 px-4 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white border-none" 
          :style="textStyles"
          v-text="ratingValue.toFixed(1)"
        ></Badge>
        
        <div v-if="getVal(settings, 'showReviewCount', device) !== false" class="review-count-wrapper flex items-center gap-1.5 text-slate-400 dark:text-slate-500 font-medium text-sm">
          <span class="opacity-30 font-bold ml-1">(</span>
          <span 
            class="review-number outline-none"
            :style="reviewStyles"
            v-text="reviewCount"
          ></span>
          <span 
            class="review-label outline-none"
            :style="reviewStyles"
            v-text="reviewText"
          ></span>
          <span class="opacity-30 font-bold">)</span>
        </div>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import { Star } from 'lucide-vue-next'
import { Badge } from '../ui'
import BaseBlock from '../components/BaseBlock.vue'
import { 
  getVal, 
  getLayoutStyles,
  getTypographyStyles 
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<any>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const ratingValue = computed(() => parseFloat(getVal(settings.value, 'rating', device.value)) || 4.5)
const maxRating = computed(() => parseInt(getVal(settings.value, 'maxRating', device.value)) || 5)
const reviewCount = computed(() => getVal(settings.value, 'reviewCount', device.value) || 0)
const reviewText = computed(() => getVal(settings.value, 'reviewText', device.value) || 'reviews')

const starSize = computed(() => parseInt(getVal(settings.value, 'starSize', device.value)) || 24)
const starColor = computed(() => getVal(settings.value, 'starColor', device.value) || '#f59e0b')
const emptyStarColor = computed(() => getVal(settings.value, 'emptyStarColor', device.value) || '#e2e8f0')

const getStarFillWidth = (i: number) => {
  const rating = ratingValue.value
  if (i <= Math.floor(rating)) return '100%'
  if (i === Math.ceil(rating)) return `${(rating % 1) * 100}%`
  return '0%'
}

const containerStyles = computed(() => {
  const layout = getLayoutStyles(settings.value, device.value)
  const align = getVal(settings.value, 'alignment', device.value) || 'center'
  
  return {
    ...layout,
    justifyContent: (align === 'center' ? 'center' : (align === 'right' ? 'flex-end' : 'flex-start')) as any,
    textAlign: (align === 'center' ? 'center' : (align === 'right' ? 'right' : 'left')) as any
  }
})

const textStyles = computed(() => getTypographyStyles(settings.value, 'text_', device.value))
const reviewStyles = computed(() => getTypographyStyles(settings.value, 'review_', device.value))

</script>

<style scoped>
.star-rating-block { width: 100%; }
.rating-number { transition: transform 0.2s ease; }
.rating-number:hover { transform: scale(1.05); }
</style>


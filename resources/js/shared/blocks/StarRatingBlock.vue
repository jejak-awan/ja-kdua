<template>
  <BaseBlock
    :module="module"
    :mode="mode"
    :settings="settings"
    class="star-rating-block transition-colors duration-300"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Star Rating'"
  >
    <div 
      class="star-rating-container flex items-center gap-4 flex-wrap"
      :style="(containerStyles as any)"
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
            :style="{ color: (emptyStarColor as string), width: starSize + 'px', height: starSize + 'px', fill: 'currentColor' }" 
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
          v-if="getVal<boolean>(settings, 'showNumber', device) !== false" 
          variant="secondary"
          class="rating-number font-black text-lg h-9 px-4 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white border-none" 
          :style="(textStyles as any)"
        >
{{ ratingValue.toFixed(1) }}
</Badge>
        
        <div v-if="getVal<boolean>(settings, 'showReviewCount', device) !== false" class="review-count-wrapper flex items-center gap-1.5 text-slate-400 dark:text-slate-500 font-medium text-sm">
          <span class="opacity-30 font-bold ml-1">(</span>
          <span 
            class="review-number outline-none"
            :style="(reviewStyles as any)"
          >{{ reviewCount }}</span>
          <span 
            class="review-label outline-none"
            :style="(reviewStyles as any)"
          >{{ reviewText }}</span>
          <span class="opacity-30 font-bold">)</span>
        </div>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import Star from 'lucide-vue-next/dist/esm/icons/star.js';
import { Badge } from '../ui'
import BaseBlock from '../components/BaseBlock.vue'
import { 
  getVal, 
  getLayoutStyles,
  getTypographyStyles 
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance, ModuleSettings } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<BuilderInstance | null>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

const ratingValue = computed(() => {
    const val = getVal<string | number>(settings.value, 'rating', device.value)
    return parseFloat(val as string) || 4.5
})
const maxRating = computed(() => {
    const val = getVal<string | number>(settings.value, 'maxRating', device.value)
    return parseInt(val as string) || 5
})
const reviewCount = computed(() => getVal<number>(settings.value, 'reviewCount', device.value) || 0)
const reviewText = computed(() => getVal<string>(settings.value, 'reviewText', device.value) || 'reviews')

const starSize = computed(() => {
    const val = getVal<string | number>(settings.value, 'starSize', device.value)
    return parseInt(val as string) || 24
})
const starColor = computed(() => getVal<string>(settings.value, 'starColor', device.value) || '#f59e0b')
const emptyStarColor = computed(() => getVal<string>(settings.value, 'emptyStarColor', device.value) || '#e2e8f0')

const getStarFillWidth = (i: number) => {
  const rating = ratingValue.value
  if (i <= Math.floor(rating)) return '100%'
  if (i === Math.ceil(rating)) return `${(rating % 1) * 100}%`
  return '0%'
}

const containerStyles = computed(() => {
  const layout = getLayoutStyles(settings.value, device.value)
  const align = getVal<string>(settings.value, 'alignment', device.value) || 'center'
  
  const styles: Record<string, string | number> = {
    ...layout,
    justifyContent: (align === 'center' ? 'center' : (align === 'right' ? 'flex-end' : 'flex-start')),
    textAlign: (align === 'center' ? 'center' : (align === 'right' ? 'right' : 'left'))
  }
  return styles
})

const textStyles = computed(() => (getTypographyStyles(settings.value, 'text_', device.value) || {}) as Record<string, string | number>)
const reviewStyles = computed(() => (getTypographyStyles(settings.value, 'review_', device.value) || {}) as Record<string, string | number>)

</script>

<style scoped>
.star-rating-block { width: 100%; }
.rating-number { transition: transform 0.2s ease; }
.rating-number:hover { transform: scale(1.05); }
</style>


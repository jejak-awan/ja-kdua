<template>
  <BaseBlock
    :id="id"
    :mode="mode"
    :settings="settings"
    :is-preview="isPreview"
    class="star-rating-block-wrapper"
  >
    <div 
      class="star-rating-container flex items-center gap-4 flex-wrap"
      :class="containerClasses"
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
          v-if="getVal(settings, 'showNumber') !== false" 
          variant="secondary"
          class="rating-number font-black text-lg h-9 px-4 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white border-none" 
          :style="textStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateField('rating', $event.target.innerText)"
          v-text="ratingValue.toFixed(1)"
        ></Badge>
        
        <div v-if="getVal(settings, 'showReviewCount') !== false" class="review-count-wrapper flex items-center gap-1.5 text-slate-400 font-medium text-sm">
          <span class="opacity-30 font-bold ml-1">(</span>
          <span 
            class="review-number outline-none"
            :style="reviewStyles"
            :contenteditable="mode === 'edit'"
            @blur="updateField('reviewCount', $event.target.innerText)"
            v-text="reviewCount"
          ></span>
          <span 
            class="review-label outline-none"
            :style="reviewStyles"
            :contenteditable="mode === 'edit'"
            @blur="updateField('reviewText', $event.target.innerText)"
            v-text="reviewText"
          ></span>
          <span class="opacity-30 font-bold">)</span>
        </div>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Star } from 'lucide-vue-next'
import { Badge } from '../ui'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  id: String,
  mode: { type: String, default: 'view' },
  settings: { type: Object, default: () => ({}) },
  isPreview: Boolean,
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const currentDevice = computed(() => builder?.device?.value || props.device || 'desktop')

const ratingValue = computed(() => parseFloat(getVal(props.settings, 'rating', currentDevice.value)) || 4.5)
const maxRating = computed(() => parseInt(getVal(props.settings, 'maxRating', currentDevice.value)) || 5)
const reviewCount = computed(() => getVal(props.settings, 'reviewCount', currentDevice.value) || 0)
const reviewText = computed(() => getVal(props.settings, 'reviewText', currentDevice.value) || 'reviews')

const starSize = computed(() => parseInt(getVal(props.settings, 'starSize', currentDevice.value)) || 24)
const starColor = computed(() => getVal(props.settings, 'starColor', currentDevice.value) || '#f59e0b')
const emptyStarColor = computed(() => getVal(props.settings, 'emptyStarColor', currentDevice.value) || '#e2e8f0')

const getStarFillWidth = (i) => {
  const rating = ratingValue.value
  if (i <= Math.floor(rating)) return '100%'
  if (i === Math.ceil(rating)) return `${(rating % 1) * 100}%`
  return '0%'
}

const textStyles = computed(() => getTypographyStyles(props.settings, 'text_', currentDevice.value))
const reviewStyles = computed(() => getTypographyStyles(props.settings, 'review_', currentDevice.value))

const containerClasses = computed(() => {
    const align = getVal(props.settings, 'alignment', currentDevice.value) || 'center'
    if (align === 'center') return 'justify-center text-center'
    if (align === 'right') return 'justify-end text-right'
    return 'justify-start text-left'
})

const updateField = (key, value) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.id, { [key]: value })
}
</script>

<style scoped>
.star-rating-block-wrapper { width: 100%; }
.rating-number { transition: transform 0.2s ease; }
.rating-number:hover { transform: scale(1.05); }
</style>

<template>
  <BaseBlock
    :id="id"
    :mode="mode"
    :settings="settings"
    :is-preview="isPreview"
    class="star-rating-block-wrapper"
  >
    <div class="star-rating-container">
      <div class="stars-group">
        <Star 
          v-for="i in maxRating" 
          :key="i" 
          class="star-icon" 
          :class="getStarClass(i)" 
          :style="getStarStyles(i)" 
        />
      </div>
      
      <div class="rating-info">
        <span 
          v-if="getVal(settings, 'showNumber') !== false" 
          class="rating-number" 
          :style="textStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateField('rating', $event.target.innerText)"
          v-text="ratingValue.toFixed(1)"
        ></span>
        
        <div v-if="getVal(settings, 'showReviewCount') !== false" class="review-count-wrapper">
          <span class="review-bracket">(</span>
          <span 
            class="review-number"
            :style="reviewStyles"
            :contenteditable="mode === 'edit'"
            @blur="updateField('reviewCount', $event.target.innerText)"
            v-text="reviewCount"
          ></span>
          <span 
            class="review-label"
            :style="reviewStyles"
            :contenteditable="mode === 'edit'"
            @blur="updateField('reviewText', $event.target.innerText)"
            v-text="reviewText"
          ></span>
          <span class="review-bracket">)</span>
        </div>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Star } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  id: String,
  mode: { type: String, default: 'view' },
  settings: { type: Object, default: () => ({}) },
  isPreview: Boolean
})

const builder = inject('builder', null)

const ratingValue = computed(() => parseFloat(getVal(props.settings, 'rating')) || 4.5)
const maxRating = computed(() => parseInt(getVal(props.settings, 'maxRating')) || 5)
const reviewCount = computed(() => getVal(props.settings, 'reviewCount') || 0)
const reviewText = computed(() => getVal(props.settings, 'reviewText') || 'reviews')

const getStarClass = (i) => {
  const rating = ratingValue.value
  if (i <= Math.floor(rating)) return 'star--full'
  if (i === Math.ceil(rating) && rating % 1 !== 0) return 'star--half'
  return 'star--empty'
}

const getStarStyles = (i) => {
  const size = parseInt(getVal(props.settings, 'starSize')) || 24
  const color = getVal(props.settings, 'starColor') || '#f59e0b'
  const emptyColor = getVal(props.settings, 'emptyStarColor') || '#e2e8f0'
  const isFullOrHalf = i <= Math.ceil(ratingValue.value)
  
  return {
    width: `${size}px`,
    height: `${size}px`,
    fill: i <= Math.floor(ratingValue.value) ? color : 'transparent',
    color: isFullOrHalf ? color : emptyColor
  }
}

const textStyles = computed(() => getTypographyStyles(props.settings, 'text_'))
const reviewStyles = computed(() => getTypographyStyles(props.settings, 'review_'))

const updateField = (key, value) => {
  if (props.mode !== 'edit' || !builder) return
  
  const current = props.settings[key]
  let newValue
  if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
    newValue = { ...current, [builder.device.value]: value }
  } else {
    newValue = { [builder.device.value]: value }
  }
  
  builder.updateModuleSettings(props.id, { [key]: newValue })
}
</script>

<style scoped>
.star-rating-block-wrapper {
  width: 100%;
}

.star-rating-container {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.stars-group {
  display: flex;
  gap: 2px;
}

.rating-info {
  display: flex;
  align-items: center;
  gap: 6px;
}

.rating-number {
  font-weight: 700;
  outline: none;
}

.review-count-wrapper {
  display: flex;
  gap: 2px;
  opacity: 0.8;
}

.review-number, .review-label {
  outline: none;
}

.review-bracket {
  opacity: 0.5;
}

.star--half {
  /* Half star logic usually requires an SVG clip path or dual layers */
  /* For simplicity in this hybrid, we use a gradient or opacity mask if needed */
  position: relative;
}

.star--half:after {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 50%;
  background: white; /* Overlay to fake half star */
  opacity: 0.5;
  pointer-events: none;
}
</style>

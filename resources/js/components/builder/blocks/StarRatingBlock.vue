<template>
  <div class="star-rating-block" :style="wrapperStyles">
    <div class="stars-container">
      <Star v-for="i in maxRatingValue" :key="i" class="star" :class="getStarClass(i)" :style="starStyles(i)" />
    </div>
    <span v-if="showNumberValue" class="rating-number" :style="textStyles">{{ ratingValue.toFixed(1) }}</span>
    <span v-if="showReviewCountValue" class="review-count" :style="reviewStyles">({{ reviewCountValue }} {{ reviewTextValue }})</span>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Star } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getResponsiveValue,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({ module: { type: Object, required: true } })

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const ratingValue = computed(() => parseFloat(getResponsiveValue(settings.value, 'rating', device.value)) || 4.5)
const maxRatingValue = computed(() => parseInt(getResponsiveValue(settings.value, 'maxRating', device.value)) || 5)
const showNumberValue = computed(() => getResponsiveValue(settings.value, 'showNumber', device.value) !== false)
const showReviewCountValue = computed(() => getResponsiveValue(settings.value, 'showReviewCount', device.value) !== false)
const reviewCountValue = computed(() => getResponsiveValue(settings.value, 'reviewCount', device.value) || 0)
const reviewTextValue = computed(() => getResponsiveValue(settings.value, 'reviewText', device.value) || 'reviews')

const getStarClass = (i) => {
  if (i <= Math.floor(ratingValue.value)) return 'star--full'
  if (i === Math.ceil(ratingValue.value) && ratingValue.value % 1 !== 0) return 'star--half'
  return 'star--empty'
}

const wrapperStyles = computed(() => {
  const styles = { display: 'flex', alignItems: 'center', gap: '8px', flexWrap: 'wrap' }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  return styles
})

const starStyles = (i) => {
  const size = getResponsiveValue(settings.value, 'starSize', device.value) || 24
  const color = getResponsiveValue(settings.value, 'starColor', device.value) || '#f59e0b'
  const emptyColor = getResponsiveValue(settings.value, 'emptyStarColor', device.value) || '#e0e0e0'
  
  return {
    width: `${size}px`,
    height: `${size}px`,
    fill: i <= ratingValue.value ? color : 'transparent',
    color: i <= ratingValue.value ? color : emptyColor
  }
}

const textStyles = computed(() => getTypographyStyles(settings.value, 'text_', device.value))
const reviewStyles = computed(() => getTypographyStyles(settings.value, 'review_', device.value))
</script>

<style scoped>
.star-rating-block { width: 100%; }
.stars-container { display: flex; gap: 2px; }
.star--half { clip-path: inset(0 50% 0 0); }
</style>

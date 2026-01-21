<template>
  <BaseBlock :module="module" :settings="settings" class="fullwidth-post-slider-block" :style="containerStyles">
    <div 
      v-for="(post, index) in displayPosts" 
      :key="index" 
      class="post-slide" 
      :class="{ 'post-slide--active': currentSlide === index }"
      :style="{ zIndex: currentSlide === index ? 10 : 1 }"
    >
      <div class="slide-image" :style="{ backgroundImage: `url(${post.image})` }" />
      <div class="slide-overlay" :style="overlayStyles" />
      <div class="slide-content" :style="contentStyles">
        <div v-if="settings.showMeta !== false" class="post-meta" :style="metaStyles">
          <span v-if="settings.showCategories !== false" class="meta-category">{{ post.category }}</span>
          <span v-if="settings.showDate !== false" class="meta-date">{{ post.date }}</span>
          <span v-if="settings.showAuthor !== false" class="meta-author">by {{ post.author }}</span>
        </div>
        <h2 class="post-title" :style="titleStyles">{{ post.title }}</h2>
        <p v-if="settings.showExcerpt !== false" class="post-excerpt" :style="excerptStyles">{{ post.excerpt }}</p>
        <a v-if="settings.showReadMore !== false" :href="post.url || '#'" class="read-more-btn" :style="buttonStyles" @click.prevent="handleLinkClick(post.url)">
          {{ settings.readMoreText || 'Read More' }}
        </a>
      </div>
    </div>
    
    <button v-if="settings.showArrows !== false" class="slider-arrow slider-arrow--prev" @click="prev"><ChevronLeft /></button>
    <button v-if="settings.showArrows !== false" class="slider-arrow slider-arrow--next" @click="next"><ChevronRight /></button>
    
    <div v-if="settings.showDots !== false" class="slider-dots">
      <button 
        v-for="(_, i) in displayPosts" 
        :key="i" 
        class="slider-dot" 
        :class="{ 'slider-dot--active': currentSlide === i }" 
        @click="currentSlide = i" 
      />
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const currentSlide = ref(0)
let interval = null

const mockPosts = [
  { title: 'Amazing Blog Post Title One', category: 'Technology', excerpt: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt.', date: 'Jan 10, 2026', author: 'John Doe', image: 'https://picsum.photos/1920/1080?random=1', url: '#' },
  { title: 'Another Great Article Here', category: 'Design', excerpt: 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip.', date: 'Jan 9, 2026', author: 'Jane Smith', image: 'https://picsum.photos/1920/1080?random=2', url: '#' },
  { title: 'Third Post With Long Title Example', category: 'Marketing', excerpt: 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat.', date: 'Jan 8, 2026', author: 'Mike Johnson', image: 'https://picsum.photos/1920/1080?random=3', url: '#' }
]

const displayPosts = computed(() => {
    // In a real app, we'd fetch actual posts based on settings (category, count, etc.)
    return mockPosts
})

const next = () => { currentSlide.value = (currentSlide.value + 1) % displayPosts.value.length }
const prev = () => { currentSlide.value = currentSlide.value === 0 ? displayPosts.value.length - 1 : currentSlide.value - 1 }

const handleLinkClick = (url) => {
    if (props.mode === 'edit') return
    if (url) window.location.href = url
}

onMounted(() => { 
    if (settings.value.autoplay !== false) {
        interval = setInterval(next, settings.value.autoplaySpeed || 5000) 
    }
})
onUnmounted(() => { if (interval) clearInterval(interval) })

const containerStyles = computed(() => {
  return { 
    position: 'relative', 
    overflow: 'hidden', 
    width: '100%',
    height: `${getResponsiveValue(settings.value, 'height', device.value) || 600}px`
  }
})

const overlayStyles = computed(() => ({ 
  background: settings.value.overlayGradient !== false 
    ? `linear-gradient(to top, ${settings.value.overlayColor || 'rgba(0,0,0,0.7)'}, transparent)` 
    : settings.value.overlayColor || 'rgba(0,0,0,0.5)',
  position: 'absolute',
  inset: 0
}))

const contentStyles = computed(() => ({ 
  position: 'relative',
  zIndex: 10,
  height: '100%',
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  padding: '60px 40px',
  maxWidth: '900px',
  margin: '0 auto',
  textAlign: settings.value.contentAlignment || 'center', 
  justifyContent: settings.value.contentPosition === 'top' ? 'flex-start' : settings.value.contentPosition === 'bottom' ? 'flex-end' : 'center' 
}))

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const excerptStyles = computed(() => getTypographyStyles(settings.value, 'excerpt_', device.value))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', device.value))

const buttonStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'button_', device.value)
  return {
    ...styles,
    backgroundColor: settings.value.buttonStyle === 'outline' ? 'transparent' : settings.value.buttonBackgroundColor || styles.backgroundColor || '#3b82f6',
    border: settings.value.buttonStyle === 'outline' ? `2px solid ${styles.color || 'currentColor'}` : 'none',
    display: 'inline-block',
    padding: '14px 32px',
    textDecoration: 'none',
    borderRadius: '6px',
    transition: 'transform 0.2s',
    cursor: 'pointer'
  }
})
</script>

<style scoped>
.fullwidth-post-slider-block { width: 100%; }
.post-slide { position: absolute; inset: 0; opacity: 0; transition: opacity 0.5s ease-in-out; }
.post-slide--active { opacity: 1; }
.slide-image { position: absolute; inset: 0; background-size: cover; background-position: center; }
.post-meta { display: flex; gap: 16px; margin-bottom: 16px; flex-wrap: wrap; justify-content: inherit; }
.meta-category { background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 4px; }
.post-title { margin: 0 0 16px; }
.post-excerpt { margin: 0 0 24px; }
.read-more-btn:hover { transform: translateY(-2px); opacity: 0.9; }
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
  transition: all 0.2s; 
}
.slider-arrow:hover { background: rgba(255,255,255,0.4); }
.slider-arrow--prev { left: 24px; }
.slider-arrow--next { right: 24px; }
.slider-dots { position: absolute; bottom: 24px; left: 50%; transform: translateX(-50%); z-index: 20; display: flex; gap: 8px; }
.slider-dot { width: 12px; height: 12px; border-radius: 50%; border: none; background: rgba(255,255,255,0.4); cursor: pointer; transition: background 0.2s; }
.slider-dot--active { background: white; }
</style>

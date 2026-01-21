<template>
  <BaseBlock :module="module" :settings="settings" class="post-slider-block">
    <h2 
      v-if="settings.title || mode === 'edit'" 
      class="slider-title-main" 
      :style="titleStyles"
      :contenteditable="mode === 'edit'"
      @blur="updateText('title', $event)"
    >
      {{ settings.title || (mode === 'edit' ? 'Featured Posts' : '') }}
    </h2>
    <div class="slider-container" :style="containerStyles">
      <div 
        v-for="(post, index) in displayPosts" 
        :key="index" 
        class="slider-slide" 
        :class="{ 'slider-slide--active': currentSlide === index }"
        :style="{ zIndex: currentSlide === index ? 10 : 1 }"
      >
        <div v-if="post.image" class="slide-image-bg" :style="{ backgroundImage: `url(${post.image})` }" />
        <div v-if="overlayEnabled" class="slide-overlay" :style="overlayStyles" />
        <div class="slide-content" :style="contentStyles">
          <span v-if="settings.showMeta !== false" class="slide-meta mb-4 block" :style="metaStyles">{{ post.date }} • {{ post.readTime }}</span>
          <h2 class="slide-title mb-6" :style="titleStyles">{{ post.title }}</h2>
          <p v-if="settings.showExcerpt !== false" class="slide-excerpt mb-8" :style="excerptStyles">{{ post.excerpt }}</p>
          <a v-if="settings.showButton !== false" :href="mode === 'view' ? post.url : null" class="slide-button" :style="buttonStyles" @click="handleLinkClick">
            {{ settings.buttonText || 'Read More' }}
          </a>
        </div>
      </div>
      
      <button v-if="settings.showArrows !== false" class="slider-arrow slider-arrow--prev" @click="prevSlide"><ChevronLeft /></button>
      <button v-if="settings.showArrows !== false" class="slider-arrow slider-arrow--next" @click="nextSlide"><ChevronRight /></button>
      
      <div v-if="settings.showDots !== false" class="slider-dots">
        <button 
            v-for="(_, index) in displayPosts" 
            :key="index" 
            class="slider-dot" 
            :class="{ 'slider-dot--active': currentSlide === index }" 
            @click="currentSlide = index" 
        />
      </div>
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

const overlayEnabled = computed(() => settings.value.overlayEnabled !== false)

// Dynamic data injection
const injectedPosts = inject('injectedPosts', [
    { title: 'Amazing Blog Post One', excerpt: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore.', date: 'Jan 10, 2026', readTime: '5 min read', image: 'https://picsum.photos/1200/600?random=1', url: '#' },
    { title: 'The Future of Web Design', excerpt: 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat.', date: 'Jan 12, 2026', readTime: '8 min read', image: 'https://picsum.photos/1200/600?random=2', url: '#' },
    { title: 'Mastering the Builder', excerpt: 'Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit.', date: 'Jan 15, 2026', readTime: '12 min read', image: 'https://picsum.photos/1200/600?random=3', url: '#' }
])

const displayPosts = computed(() => {
    const count = getResponsiveValue(settings.value, 'totalPosts', device.value) || 5
    return injectedPosts.slice(0, count)
})

const nextSlide = () => { currentSlide.value = (currentSlide.value + 1) % displayPosts.value.length }
const prevSlide = () => { currentSlide.value = currentSlide.value === 0 ? displayPosts.value.length - 1 : currentSlide.value - 1 }

onMounted(() => { 
    if (settings.value.autoplay !== false) {
        interval = setInterval(nextSlide, settings.value.autoplaySpeed || 5000) 
    }
})
onUnmounted(() => { if (interval) clearInterval(interval) })

const updateText = (key, event) => {
    if (props.mode !== 'edit') return
    builder?.updateModuleSettings(props.module.id, { [key]: event.target.innerText })
}

const handleLinkClick = (event) => {
    if (props.mode === 'edit') event.preventDefault()
}

const containerStyles = computed(() => {
    const height = getResponsiveValue(settings.value, 'height', device.value) || 500
    return {
        position: 'relative',
        height: typeof height === 'number' ? `${height}px` : height,
        borderRadius: '12px',
        overflow: 'hidden',
        background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
    }
})

const overlayStyles = computed(() => ({ 
    position: 'absolute',
    inset: 0,
    backgroundColor: settings.value.overlayColor || 'rgba(0,0,0,0.4)',
    zIndex: 2
}))

const contentStyles = computed(() => ({ 
    position: 'relative',
    zIndex: 10,
    padding: '60px',
    maxWidth: '800px',
    margin: '0 auto',
    height: '100%',
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'center',
    textAlign: 'center',
    color: '#ffffff'
}))

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', device.value))
const excerptStyles = computed(() => getTypographyStyles(settings.value, 'excerpt_', device.value))

const buttonStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'button_', device.value)
    return {
        ...styles,
        backgroundColor: settings.value.buttonBackgroundColor || '#ffffff',
        color: settings.value.buttonTextColor || '#333333',
        display: 'inline-block',
        padding: '12px 32px',
        borderRadius: '6px',
        textDecoration: 'none',
        alignSelf: 'center',
        fontWeight: '600'
    }
})
</script>

<style scoped>
.post-slider-block { width: 100%; }
.slider-title-main { margin-bottom: 24px; text-align: center; outline: none; }
.slider-container { width: 100%; }
.slider-slide { position: absolute; inset: 0; opacity: 0; transition: opacity 0.5s ease-in-out; }
.slider-slide--active { opacity: 1; }
.slide-image-bg { position: absolute; inset: 0; background-size: cover; background-position: center; z-index: 1; }
.slider-arrow { position: absolute; top: 50%; transform: translateY(-50%); z-index: 20; width: 44px; height: 44px; border-radius: 50%; background: rgba(255,255,255,0.2); border: none; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s; }
.slider-arrow:hover { background: rgba(255,255,255,0.4); }
.slider-arrow--prev { left: 24px; }
.slider-arrow--next { right: 24px; }
.slider-dots { position: absolute; bottom: 24px; left: 50%; transform: translateX(-50%); z-index: 20; display: flex; gap: 8px; }
.slider-dot { width: 10px; height: 10px; border-radius: 50%; background: rgba(255,255,255,0.3); border: none; cursor: pointer; transition: background 0.2s; }
.slider-dot--active { background: white; width: 24px; border-radius: 5px; }
[contenteditable]:focus {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
}
</style>

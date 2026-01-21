<template>
  <BaseBlock :module="module" :settings="settings" class="post-nav-block" :style="wrapperStyles">
    <!-- Previous Post -->
    <a 
        v-if="prevPost || mode === 'edit'" 
        :href="mode === 'view' ? (prevPost?.url || '#') : null" 
        class="post-nav-item post-nav-item--prev" 
        :style="itemStyles"
        @click="handleLinkClick"
    >
      <ChevronLeft :style="iconStyles" />
      <div class="post-nav-content">
        <span 
          v-if="settings.showLabels !== false" 
          class="post-nav-label" 
          :style="labelStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateText('prevLabel', $event)"
        >{{ settings.prevLabel || 'Previous Post' }}</span>
        <span class="post-nav-title" :style="titleStyles">{{ prevPost?.title || (mode === 'edit' ? 'Previous Post Title' : '') }}</span>
      </div>
    </a>
    <div v-else class="flex-1"></div>

    <!-- Next Post -->
    <a 
        v-if="nextPost || mode === 'edit'" 
        :href="mode === 'view' ? (nextPost?.url || '#') : null" 
        class="post-nav-item post-nav-item--next" 
        :style="itemStyles"
        @click="handleLinkClick"
    >
      <div class="post-nav-content" style="text-align: right">
        <span 
          v-if="settings.showLabels !== false" 
          class="post-nav-label" 
          :style="labelStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateText('nextLabel', $event)"
        >{{ settings.nextLabel || 'Next Post' }}</span>
        <span class="post-nav-title" :style="titleStyles">{{ nextPost?.title || (mode === 'edit' ? 'Next Post Title' : '') }}</span>
      </div>
      <ChevronRight :style="iconStyles" />
    </a>
    <div v-else class="flex-1"></div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
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

// Dynamic data injection
const prevPost = inject('prevPost', { title: 'How to Build a Website', url: '#' })
const nextPost = inject('nextPost', { title: '10 Tips for SEO Success', url: '#' })

const updateText = (key, event) => {
    if (props.mode !== 'edit') return
    builder?.updateModuleSettings(props.module.id, { [key]: event.target.innerText })
}

const handleLinkClick = (event) => {
    if (props.mode === 'edit') event.preventDefault()
}

const wrapperStyles = computed(() => {
  return { 
    display: 'flex', 
    justifyContent: 'space-between', 
    gap: '20px', 
    width: '100%'
  }
})

const itemStyles = computed(() => {
    return { 
        display: 'flex', 
        alignItems: 'center', 
        gap: '12px', 
        textDecoration: 'none', 
        flex: 1,
        color: 'inherit'
    }
})

const iconStyles = computed(() => ({ 
    width: '24px', 
    height: '24px', 
    color: settings.value.label_color || '#3b82f6',
    flexShrink: 0 
}))

const labelStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'label_', device.value)
    return {
        ...styles,
        textTransform: 'uppercase',
        fontSize: '0.75rem',
        letterSpacing: '0.05em',
        opacity: 0.7
    }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
</script>

<style scoped>
.post-nav-block { width: 100%; }
.post-nav-item { transition: opacity 0.2s; }
.post-nav-item:hover { opacity: 0.7; }
.post-nav-content { display: flex; flex-direction: column; gap: 4px; flex: 1; }
.post-nav-label { outline: none; }
[contenteditable]:focus {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
}
</style>

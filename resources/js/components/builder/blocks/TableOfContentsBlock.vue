<template>
  <div class="toc-block" :style="wrapperStyles">
    <div v-if="collapsibleValue" class="toc-header" @click="expanded = !expanded">
      <span class="toc-title" :style="titleStyles">{{ titleValue }}</span>
      <ChevronDown class="toc-toggle" :class="{ 'toc-toggle--expanded': expanded }" />
    </div>
    <h4 v-else class="toc-title" :style="titleStyles">{{ titleValue }}</h4>
    
    <nav v-show="!collapsibleValue || expanded" class="toc-nav">
      <ol v-if="showNumbersValue" class="toc-list toc-list--numbered">
        <li v-for="(item, index) in mockItems" :key="index" :class="`toc-item toc-item--${item.level}`">
          <a :href="`#${item.id}`" class="toc-link" :style="linkStyles">{{ item.text }}</a>
        </li>
      </ol>
      <ul v-else class="toc-list">
        <li v-for="(item, index) in mockItems" :key="index" :class="`toc-item toc-item--${item.level}`">
          <a :href="`#${item.id}`" class="toc-link" :style="linkStyles">{{ item.text }}</a>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import { ChevronDown } from 'lucide-vue-next'
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

const titleValue = computed(() => getResponsiveValue(settings.value, 'title', device.value) || 'Table of Contents')
const showNumbersValue = computed(() => getResponsiveValue(settings.value, 'showNumbers', device.value) !== false)
const collapsibleValue = computed(() => getResponsiveValue(settings.value, 'collapsible', device.value))
const expanded = ref(getResponsiveValue(settings.value, 'defaultExpanded', device.value) !== false)

const mockItems = [
  { id: 'intro', text: 'Introduction', level: 'h2' },
  { id: 'getting-started', text: 'Getting Started', level: 'h2' },
  { id: 'requirements', text: 'Requirements', level: 'h3' },
  { id: 'installation', text: 'Installation', level: 'h3' },
  { id: 'features', text: 'Features', level: 'h2' },
  { id: 'conclusion', text: 'Conclusion', level: 'h2' }
]

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
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

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const linkStyles = computed(() => getTypographyStyles(settings.value, 'link_', device.value))
</script>

<style scoped>
.toc-block { width: 100%; }
.toc-header { display: flex; justify-content: space-between; align-items: center; cursor: pointer; }
.toc-title { margin: 0 0 16px; }
.toc-header .toc-title { margin: 0; }
.toc-toggle { width: 20px; height: 20px; transition: transform 0.2s; }
.toc-toggle--expanded { transform: rotate(180deg); }
.toc-nav { margin-top: 16px; }
.toc-list { margin: 0; padding-left: 20px; }
.toc-list--numbered { counter-reset: toc; list-style: none; }
.toc-list--numbered .toc-item::before { counter-increment: toc; content: counters(toc, ".") ". "; }
.toc-item { margin: 8px 0; line-height: 1.4; }
.toc-item--h3 { margin-left: 20px; }
.toc-link { text-decoration: none; }
.toc-link:hover { text-decoration: underline; }
</style>

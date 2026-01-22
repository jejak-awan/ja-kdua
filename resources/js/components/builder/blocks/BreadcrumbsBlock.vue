<template>
  <nav class="breadcrumbs-block" :style="wrapperStyles" aria-label="Breadcrumb">
    <ol class="breadcrumbs-list">
      <li v-for="(item, index) in breadcrumbItems" :key="index" class="breadcrumb-item">
        <a v-if="item.url && index < breadcrumbItems.length - 1" :href="item.url" class="breadcrumb-link" :style="linkStyles">
          <Home v-if="index === 0 && settings.homeIcon !== false && settings.showHome !== false" class="home-icon" />
          <span v-else>{{ item.text }}</span>
        </a>
        <span v-else class="breadcrumb-current" :style="currentStyles">{{ item.text }}</span>
        <span v-if="index < breadcrumbItems.length - 1" class="breadcrumb-separator" :style="separatorStyles">{{ settings.separator || '/' }}</span>
      </li>
    </ol>
  </nav>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Home } from 'lucide-vue-next'
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
const settings = computed(() => props.module.settings || {})

const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')

const breadcrumbItems = computed(() => {
  const items = settings.value.items
  if (Array.isArray(items)) return items
  if (typeof items === 'string') { try { return JSON.parse(items) } catch { return [] } }
  return [{ text: 'Home', url: '/' }, { text: 'Page', url: '' }]
})

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

const linkStyles = computed(() => {
  return getTypographyStyles(settings.value, 'links_', device.value)
})

const currentStyles = computed(() => {
  return getTypographyStyles(settings.value, 'active_', device.value)
})

const separatorStyles = computed(() => ({ 
  color: settings.value.separatorColor || '#999999',
  fontSize: getTypographyStyles(settings.value, 'links_', device.value).fontSize
}))
</script>

<style scoped>
.breadcrumbs-block { width: 100%; }
.breadcrumbs-list { display: flex; flex-wrap: wrap; list-style: none; margin: 0; padding: 0; gap: 8px; }
.breadcrumb-item { display: flex; align-items: center; gap: 8px; }
.breadcrumb-link { text-decoration: none; display: flex; align-items: center; }
.home-icon { width: 16px; height: 16px; }
</style>

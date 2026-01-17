<template>
  <aside class="sidebar-block" :style="wrapperStyles">
    <!-- Dynamic Widgets -->
    <div v-for="(widget, index) in widgetList" :key="index" class="sidebar-widget">
      <!-- Search Widget -->
      <div v-if="widget.type === 'search'">
        <h4 v-if="showTitle" class="widget-title" :style="titleStyles">{{ widget.title || 'Search' }}</h4>
        <div class="search-widget">
          <input type="text" placeholder="Search..." class="search-input" :style="widgetStyles" />
          <SearchIcon class="search-icon" />
        </div>
      </div>

      <!-- Categories Widget -->
      <div v-else-if="widget.type === 'categories'">
        <h4 v-if="showTitle" class="widget-title" :style="titleStyles">{{ widget.title || 'Categories' }}</h4>
        <ul class="widget-list">
          <li v-for="cat in categories" :key="cat"><a href="#" :style="widgetStyles">{{ cat }}</a></li>
        </ul>
      </div>

      <!-- Recent Posts Widget -->
      <div v-else-if="widget.type === 'recentposts'">
        <h4 v-if="showTitle" class="widget-title" :style="titleStyles">{{ widget.title || 'Recent Posts' }}</h4>
        <ul class="widget-list">
          <li v-for="i in widget.count || 3" :key="i"><a href="#" :style="widgetStyles">Sample Post Title {{ i }}</a></li>
        </ul>
      </div>

      <!-- Tags Widget -->
      <div v-else-if="widget.type === 'tags'">
        <h4 v-if="showTitle" class="widget-title" :style="titleStyles">{{ widget.title || 'Tags' }}</h4>
        <div class="tags-cloud">
          <a v-for="tag in tags" :key="tag" href="#" class="tag" :style="widgetStyles">{{ tag }}</a>
        </div>
      </div>
      
      <!-- Text/Generic Widget -->
      <div v-else>
         <h4 v-if="showTitle && widget.title" class="widget-title" :style="titleStyles">{{ widget.title }}</h4>
         <p :style="widgetStyles">Widget content...</p>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Search as SearchIcon } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getFilterStyles,
  getTransformStyles,
  getResponsiveValue
} from '../core/styleUtils'

const props = defineProps({ module: { type: Object, required: true } })

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const showTitle = computed(() => getResponsiveValue(settings.value, 'showTitle', device.value) !== false)

const widgetList = computed(() => {
  return (props.module.children || []).map(child => ({
    type: getResponsiveValue(child.settings, 'widgetType', device.value) || 'text',
    title: getResponsiveValue(child.settings, 'title', device.value) || '',
    count: getResponsiveValue(child.settings, 'count', device.value) || 5
  }))
})

const categories = ['Technology', 'Design', 'Business', 'Lifestyle']
const tags = ['Design', 'Tech', 'News', 'Updates', 'Tips']

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
const widgetStyles = computed(() => getTypographyStyles(settings.value, 'widget_', device.value))
</script>

<style scoped>
.sidebar-block { width: 100%; }
.sidebar-widget { margin-bottom: 24px; }
.sidebar-widget:last-child { margin-bottom: 0; }
.widget-title { margin: 0 0 12px; }
.search-widget { position: relative; }
.search-input { width: 100%; padding: 10px 40px 10px 14px; border: 1px solid #e0e0e0; border-radius: 6px; box-sizing: border-box; }
.search-icon { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: #999; }
.widget-list { list-style: none; margin: 0; padding: 0; }
.widget-list li { padding: 8px 0; border-bottom: 1px solid #e0e0e0; }
.widget-list li:last-child { border-bottom: none; }
.widget-list a { text-decoration: none; color: inherit; }
.tags-cloud { display: flex; flex-wrap: wrap; gap: 8px; }
.tag { display: inline-block; padding: 4px 12px; background: #e0e0e0; border-radius: 4px; text-decoration: none; color: inherit; }
</style>

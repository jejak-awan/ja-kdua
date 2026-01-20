<template>
  <div class="sidebar-widget-block">
      <!-- Search Widget -->
      <div v-if="widgetType === 'search'">
        <h4 v-if="showTitle" class="widget-title" :style="titleStyles">{{ settings.title || 'Search' }}</h4>
        <div class="search-widget">
          <input type="text" placeholder="Search..." class="search-input" :style="widgetStyles" />
          <SearchIcon class="search-icon" />
        </div>
      </div>

      <!-- Categories Widget -->
      <div v-else-if="widgetType === 'categories'">
        <h4 v-if="showTitle" class="widget-title" :style="titleStyles">{{ settings.title || 'Categories' }}</h4>
        <ul class="widget-list">
          <li v-for="cat in categories" :key="cat"><a href="#" :style="widgetStyles">{{ cat }}</a></li>
        </ul>
      </div>

      <!-- Recent Posts Widget -->
      <div v-else-if="widgetType === 'recentposts'">
        <h4 v-if="showTitle" class="widget-title" :style="titleStyles">{{ settings.title || 'Recent Posts' }}</h4>
        <ul class="widget-list">
          <li v-for="i in settings.count || 3" :key="i"><a href="#" :style="widgetStyles">Sample Post Title {{ i }}</a></li>
        </ul>
      </div>

      <!-- Tags Widget -->
      <div v-else-if="widgetType === 'tags'">
        <h4 v-if="showTitle" class="widget-title" :style="titleStyles">{{ settings.title || 'Tags' }}</h4>
        <div class="tags-cloud">
          <a v-for="tag in tags" :key="tag" href="#" class="tag" :style="widgetStyles">{{ tag }}</a>
        </div>
      </div>
      
      <!-- Text/Generic Widget -->
      <div v-else>
         <h4 v-if="showTitle && settings.title" class="widget-title" :style="titleStyles">{{ settings.title }}</h4>
         <p :style="widgetStyles">Widget content...</p>
      </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Search as SearchIcon } from 'lucide-vue-next'
import { getTypographyStyles, getResponsiveValue } from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => props.module.settings || {})

const widgetType = computed(() => getResponsiveValue(settings.value, 'widgetType', device.value) || 'text')

// Injected from SidebarBlock
const sidebarState = inject('sidebarState', {
    parentSettings: {}
})
const parentSettings = computed(() => sidebarState.parentSettings.value || {})

const showTitle = computed(() => getResponsiveValue(parentSettings.value, 'showTitle', device.value) !== false)

const titleStyles = computed(() => getTypographyStyles(parentSettings.value, 'title_', device.value))
const widgetStyles = computed(() => getTypographyStyles(parentSettings.value, 'widget_', device.value))

// Mock data
const categories = ['Technology', 'Design', 'Business', 'Lifestyle']
const tags = ['Design', 'Tech', 'News', 'Updates', 'Tips']

</script>

<style scoped>
.sidebar-widget-block { margin-bottom: 24px; }
.sidebar-widget-block:last-child { margin-bottom: 0; }
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

<template>
  <BaseBlock :module="module" :settings="settings" class="breadcrumbs-block">
    <nav aria-label="Breadcrumb">
      <ol class="breadcrumbs-list flex flex-wrap items-center gap-2 list-none p-0 m-0">
        <li v-for="(item, index) in breadcrumbItems" :key="index" class="breadcrumb-item flex items-center gap-2">
          <template v-if="index < breadcrumbItems.length - 1">
            <a 
              v-if="item.url" 
              :href="mode === 'view' ? item.url : null" 
              class="breadcrumb-link flex items-center transition-opacity hover:opacity-80" 
              :style="linkStyles"
              @click="handleLinkClick"
            >
              <Home v-if="index === 0 && settings.homeIcon !== false && settings.showHome !== false" class="home-icon w-4 h-4 mr-1.5" />
              <span>{{ item.text }}</span>
            </a>
            <span v-else class="breadcrumb-text opacity-60" :style="linkStyles">{{ item.text }}</span>
            
            <span class="breadcrumb-separator font-normal opacity-40 px-1" :style="separatorStyles">
              {{ settings.separator || '/' }}
            </span>
          </template>
          
          <span v-else class="breadcrumb-current font-semibold" :style="currentStyles">
            {{ item.text }}
          </span>
        </li>
      </ol>
    </nav>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Home } from 'lucide-vue-next'
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

const breadcrumbItems = computed(() => {
  const items = settings.value.items
  if (Array.isArray(items)) return items
  if (typeof items === 'string') { try { return JSON.parse(items) } catch { return [] } }
  // Fallback for demonstration
  return [
    { text: 'Home', url: '/' },
    { text: 'Resource', url: '/resource' },
    { text: 'Current Page', url: '' }
  ]
})

const handleLinkClick = (event) => {
    if (props.mode === 'edit') event.preventDefault()
}

const linkStyles = computed(() => getTypographyStyles(settings.value, 'links_', device.value))
const currentStyles = computed(() => getTypographyStyles(settings.value, 'active_', device.value))

const separatorStyles = computed(() => ({ 
  color: settings.value.separatorColor || 'currentColor',
  fontSize: getTypographyStyles(settings.value, 'links_', device.value).fontSize
}))
</script>

<style scoped>
.breadcrumbs-block { width: 100%; }
</style>

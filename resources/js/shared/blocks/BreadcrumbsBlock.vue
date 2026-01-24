<template>
  <BaseBlock :module="module" :settings="settings" class="breadcrumbs-block">
    <Breadcrumb>
      <BreadcrumbList>
        <template v-for="(item, index) in breadcrumbItems" :key="index">
          <BreadcrumbItem>
            <template v-if="index < breadcrumbItems.length - 1">
              <BreadcrumbLink 
                v-if="item.url" 
                :href="mode === 'view' ? item.url : null" 
                class="flex items-center" 
                :style="linkStyles"
                @click="handleLinkClick"
              >
                <LucideIcon v-if="index === 0 && settings.homeIcon !== false && settings.showHome !== false" name="Home" class="w-4 h-4 mr-1.5" />
                <span>{{ item.text }}</span>
              </BreadcrumbLink>
              <span v-else class="opacity-60" :style="linkStyles">{{ item.text }}</span>
            </template>
            <BreadcrumbPage v-else :style="currentStyles">
              {{ item.text }}
            </BreadcrumbPage>
          </BreadcrumbItem>
          
          <BreadcrumbSeparator v-if="index < breadcrumbItems.length - 1" :style="separatorStyles">
            <template #default v-if="settings.separator">
               <span class="text-xs">{{ settings.separator }}</span>
            </template>
          </BreadcrumbSeparator>
        </template>
      </BreadcrumbList>
    </Breadcrumb>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Breadcrumb, BreadcrumbList, BreadcrumbItem, BreadcrumbLink, BreadcrumbPage, BreadcrumbSeparator } from '../ui'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || props.device)

const breadcrumbItems = computed(() => {
  const items = settings.value.items
  if (Array.isArray(items)) return items
  if (typeof items === 'string') { try { return JSON.parse(items) } catch { return [] } }
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
}))
</script>

<style scoped>
.breadcrumbs-block { width: 100%; }
</style>

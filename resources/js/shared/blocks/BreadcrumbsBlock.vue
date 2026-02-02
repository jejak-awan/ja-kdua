<template>
  <BaseBlock 
    :module="module" 
    :settings="settings" 
    :mode="mode"
    class="breadcrumbs-block"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Breadcrumb'"
  >
    <div :style="containerStyles">
      <Breadcrumb>
        <BreadcrumbList>
          <template v-for="(item, index) in breadcrumbItems" :key="index">
            <BreadcrumbItem>
              <template v-if="(index as number) < breadcrumbItems.length - 1">
                <BreadcrumbLink 
                  v-if="item.url" 
                  :href="mode === 'view' ? item.url : null" 
                  class="flex items-center transition-colors hover:text-primary" 
                  :style="linkStyles"
                  @click="handleLinkClick"
                >
                  <LucideIcon v-if="(index as number) === 0 && settings.homeIcon !== false && settings.showHome !== false" name="Home" class="w-4 h-4 mr-1.5" />
                  <span>{{ item.text }}</span>
                </BreadcrumbLink>
                <span v-else class="opacity-60" :style="linkStyles">{{ item.text }}</span>
              </template>
              <BreadcrumbPage v-else :style="currentStyles" class="font-medium">
                {{ item.text }}
              </BreadcrumbPage>
            </BreadcrumbItem>
            
            <BreadcrumbSeparator v-if="(index as number) < breadcrumbItems.length - 1" :style="separatorStyles">
              <template #default v-if="settings.separator">
                 <span class="text-xs">{{ settings.separator }}</span>
              </template>
            </BreadcrumbSeparator>
          </template>
        </BreadcrumbList>
      </Breadcrumb>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Breadcrumb, BreadcrumbList, BreadcrumbItem, BreadcrumbLink, BreadcrumbPage, BreadcrumbSeparator } from '../ui'
import { LucideIcon } from '@/components/ui';
import { 
  getVal,
  getLayoutStyles,
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<any>('builder', null)
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)
const device = computed(() => builder?.device?.value || 'desktop')

const breadcrumbItems = computed(() => {
  const items = getVal(settings.value, 'items', device.value)
  if (Array.isArray(items)) return items
  if (typeof items === 'string') { try { return JSON.parse(items) } catch { return [] } }
  return [
    { text: 'Home', url: '/' },
    { text: 'Resource', url: '/resource' },
    { text: 'Current Page', url: '' }
  ]
})

const handleLinkClick = (event: MouseEvent) => {
    if (props.mode === 'edit') event.preventDefault()
}

const containerStyles = computed(() => getLayoutStyles(settings.value, device.value))
const linkStyles = computed(() => getTypographyStyles(settings.value, 'links_', device.value))
const currentStyles = computed(() => getTypographyStyles(settings.value, 'active_', device.value))

const separatorStyles = computed(() => ({ 
  color: getVal(settings.value, 'separatorColor', device.value) || 'currentColor',
}))
</script>

<style scoped>
.breadcrumbs-block { 
  width: 100%; 
}
</style>


<template>
  <BaseBlock
    :module="module"
    :mode="mode"
    :settings="settings"
    class="logo-grid-wrapper transition-all duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Partner Logos'"
  >
    <div 
      v-if="hasTitle" 
      class="logo-grid-header mb-8 text-center" 
      :style="titleStyles"
    >
      <div
        :contenteditable="mode === 'edit'"
        @blur="updateField('title', ($event.target as HTMLElement).innerText)"
        v-text="titleText"
        class="outline-none"
      ></div>
    </div>

    <div class="logo-grid-container w-full" :style="gridStyles">
      <div 
        v-for="(item, index) in items" 
        :key="index"
        class="logo-card flex justify-center w-full transition-all duration-300 hover:z-10"
        :style="getItemStyles(index as number)"
      >
        <div class="logo-inner w-full flex items-center justify-center transition-all duration-300" :style="logoContainerStyles">
          <img 
            v-if="item.image" 
            :src="item.image" 
            :alt="item.name" 
            class="logo-image w-full h-auto object-contain max-h-[100px]"
            :style="logoImgStyles"
          />
          <div v-else class="logo-placeholder w-full aspect-[3/2] bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-center text-slate-300" :style="placeholderStyles">
            <Building class="w-1/3 h-1/3 opacity-50" />
          </div>
        </div>
      </div>
      
      <!-- Builder specific empty state -->
      <div v-if="items.length === 0 && mode === 'edit'" class="empty-logos-placeholder col-span-full p-10 text-center border-2 border-dashed border-slate-200 rounded-2xl flex flex-col items-center gap-3 text-slate-400">
        <Building :size="32" class="opacity-30" />
        <span class="font-bold">Add logos in settings</span>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import { Building } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
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

const items = computed(() => settings.value.items || [])
const titleText = computed(() => getVal(settings.value, 'title', device.value) || '')
const hasTitle = computed(() => props.mode === 'edit' || (getVal(settings.value, 'showTitle', device.value) !== false && titleText.value))

const gridStyles = computed(() => {
  const containerStyles = getLayoutStyles(settings.value, device.value)
  const cols = parseInt(getVal(settings.value, 'columns', device.value)) || 4
  const gap = parseInt(getVal(settings.value, 'gap', device.value)) || 40
  
  return {
    ...containerStyles,
    display: 'grid',
    gridTemplateColumns: `repeat(${getResponsiveValue(settings.value, 'columns', device.value) || 4}, 1fr)`,
    gap: `${getResponsiveValue(settings.value, 'gap', device.value) || 40}px`,
    alignItems: 'center',
    justifyItems: 'center',
    width: '100%'
  }
})

const getItemStyles = (index: number) => {
    const styles: Record<string, any> = {}
    
    // Interactive states
    const hoverScale = getVal(settings.value, 'hover_scale', device.value) || 1.05
    const hoverBrightness = getVal(settings.value, 'hover_brightness', device.value) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    // Grayscale / Opacity
    const isGrayscale = getVal(settings.value, 'grayscale', device.value) !== false
    const hoverColor = getVal(settings.value, 'hoverColor', device.value) !== false
    const opacity = getVal(settings.value, 'logoOpacity', device.value) ?? 0.6
    
    styles['--logo-opacity'] = opacity
    styles['--logo-filter'] = isGrayscale ? 'grayscale(100%)' : 'none'
    styles['--logo-hover-filter'] = (isGrayscale && hoverColor) ? 'grayscale(0%)' : (isGrayscale ? 'grayscale(100%)' : 'none')
    styles['--logo-hover-opacity'] = hoverColor ? '1' : opacity
    
    return styles
}

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))

const logoContainerStyles = computed(() => {
  const size = parseInt(getVal(settings.value, 'logoSize', device.value)) || 140
  return {
    maxWidth: `${size}px`,
  }
})

const logoImgStyles = computed(() => ({}))

const placeholderStyles = computed(() => ({}))

const updateField = (key: string, value: string) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.module.id, { [key]: value })
}
</script>

<style scoped>
.logo-grid-wrapper {
  width: 100%;
}

.logo-inner {
  opacity: var(--logo-opacity, 0.6);
  filter: var(--logo-filter, none);
}

.logo-card:hover .logo-inner {
  opacity: var(--logo-hover-opacity, 1);
  filter: var(--logo-hover-filter, none);
  transform: scale(var(--hover-scale, 1.05));
}

@media (max-width: 640px) {
  .logo-grid-container {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 20px !important;
  }
}
</style>


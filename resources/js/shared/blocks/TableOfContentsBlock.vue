<template>
  <BaseBlock 
    :module="module" 
    :settings="settings" 
    :mode="mode"
    class="toc-block transition-colors duration-300"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Table of Contents'"
    :style="(cardStyles as any)"
  >
    <div class="toc-container bg-gray-50 dark:bg-gray-900 rounded-2xl p-6 border border-gray-100 dark:border-gray-800 shadow-sm" :style="(containerStyles as any)">
        <div v-if="collapsibleValue" class="toc-header flex justify-between items-center cursor-pointer group" @click="expanded = !expanded">
          <span class="toc-title font-bold text-lg" :style="(titleStyles as any)">{{ titleValue }}</span>
          <div class="toc-toggle-icon w-8 h-8 flex items-center justify-center rounded-full bg-white dark:bg-gray-800 shadow-sm transition-shadow group-hover:scale-110">
            <ChevronDown class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': expanded }" />
          </div>
        </div>
        <h4 v-else class="toc-title font-bold text-lg mb-6" :style="(titleStyles as any)">{{ titleValue }}</h4>
        
        <transition name="toc-slide">
            <nav v-show="!collapsibleValue || expanded" class="toc-nav pt-4">
              <component :is="showNumbersValue ? 'ol' : 'ul'" class="toc-list" :class="{ 'toc-list--numbered': showNumbersValue }">
                <li v-for="(item, index) in displayItems" :key="index" :class="[`toc-item toc-item--${item.level}`, item.level === 'h3' ? 'ml-6' : '']" class="my-3">
                  <a :href="`#${item.id}`" class="toc-link block py-1 hover:text-blue-500 transition-colors" :style="(linkStyles as any)">{{ item.text }}</a>
                </li>
              </component>
            </nav>
        </transition>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import { 
  getVal,
  getLayoutStyles,
  getTypographyStyles
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance, ModuleSettings } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<BuilderInstance | null>('builder', null)
const settings = computed(() => (props.module.settings || {}) as ModuleSettings)
const device = computed(() => builder?.device?.value || 'desktop')

const titleValue = computed(() => getVal<string>(settings.value, 'title', device.value) || 'Table of Contents')
const showNumbersValue = computed(() => getVal<boolean>(settings.value, 'showNumbers', device.value) !== false)
const collapsibleValue = computed(() => getVal<boolean>(settings.value, 'collapsible', device.value))
const expanded = ref(getVal<boolean>(settings.value, 'defaultExpanded', device.value) !== false)

const displayItems = computed(() => {
    // In builder mode, we show mock items
    if (props.mode === 'edit') {
        return [
          { id: 'intro', text: 'Introduction', level: 'h2' },
          { id: 'getting-started', text: 'Getting Started', level: 'h2' },
          { id: 'requirements', text: 'Requirements', level: 'h3' },
          { id: 'installation', text: 'Installation', level: 'h3' },
          { id: 'features', text: 'Features', level: 'h2' },
          { id: 'conclusion', text: 'Conclusion', level: 'h2' }
        ]
    }
    // In view mode, this would ideally be populated dynamically from the DOM headers
    // For now we use the same mock items as a placeholder
    return [
      { id: 'intro', text: 'Introduction', level: 'h2' },
      { id: 'getting-started', text: 'Getting Started', level: 'h2' },
      { id: 'features', text: 'Key Features', level: 'h2' }
    ]
})

const cardStyles = computed(() => {
    const styles: Record<string, string | number> = {}
    
    // Interactive states
    const hoverScale = getVal<number>(settings.value, 'hover_scale', device.value) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', device.value) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => (getLayoutStyles(settings.value, device.value) || {}) as Record<string, string | number>)
const titleStyles = computed(() => (getTypographyStyles(settings.value, 'title_', device.value) || {}) as Record<string, string | number>)
const linkStyles = computed(() => (getTypographyStyles(settings.value, 'link_', device.value) || {}) as Record<string, string | number>)
</script>

<style scoped>
.toc-block { width: 100%; }

.toc-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}

.toc-list { list-style: none; padding: 0; margin: 0; }
.toc-list--numbered { counter-reset: toc; }
.toc-list--numbered .toc-item { counter-increment: toc; }
.toc-list--numbered .toc-item::before { content: counters(toc, ".") ". "; font-weight: bold; opacity: 0.5; margin-right: 8px; font-size: 0.9em; }

.toc-slide-enter-active, .toc-slide-leave-active {
  transition: all 0.3s ease-in-out;
  max-height: 1000px;
  opacity: 1;
}
.toc-slide-enter-from, .toc-slide-leave-to {
  max-height: 0;
  opacity: 0;
  overflow: hidden;
}
</style>


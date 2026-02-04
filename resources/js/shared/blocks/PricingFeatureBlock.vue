<template>
  <div 
    class="pricing-feature-item flex items-center py-5 border-b border-slate-50 dark:border-slate-800 last:border-0 group transition-colors duration-300"
    :class="[
        !isIncluded ? 'opacity-30 line-through grayscale' : '',
        settings.strikeThrough ? 'line-through opacity-40' : ''
    ]"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Pricing Feature'"
    :style="(itemStyles as any)"
  >
      <div 
        class="feature-icon-wrap w-7 h-7 flex items-center justify-center rounded-xl mr-5 transition-[width] duration-500 group-hover:scale-110 group-hover:rotate-6 shadow-sm border border-slate-100 dark:border-slate-800"
        :class="isIncluded ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'"
        :style="(iconStyles as any)"
      >
        <Check v-if="isIncluded" class="w-4 h-4" stroke-width="3" />
        <X v-else class="w-4 h-4" stroke-width="3" />
      </div>
      <span class="feature-text font-bold text-sm md:text-base tracking-tight text-slate-700 dark:text-slate-300 transition-colors group-hover:text-primary" :style="(textStyles as any)">
        {{ settings.text || 'Premium Feature' }}
      </span>
  </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
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
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

const isIncluded = computed(() => getVal<boolean>(settings.value, 'included', device.value) !== false)

const itemStyles = computed(() => {
    const layout = getLayoutStyles(settings.value, device.value)
    const styles: Record<string, string | number> = {
        ...(layout as Record<string, string | number>)
    }
    return styles
})

const textStyles = computed(() => {
    return (getTypographyStyles(settings.value, 'text_', device.value) || {}) as Record<string, string | number>
})

const iconStyles = computed(() => ({} as Record<string, string | number>))
</script>

<style scoped>
.pricing-feature-item { width: 100%; }
</style>


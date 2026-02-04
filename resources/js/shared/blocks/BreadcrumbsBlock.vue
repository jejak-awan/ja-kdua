<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="breadcrumbs-block py-6"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Breadcrumbs'"
    :style="cardStyles"
  >
    <div class="breadcrumbs-container flex items-center gap-2 overflow-x-auto whitespace-nowrap scrollbar-hide" :style="containerStyles">
        <template v-for="(crumb, index) in crumbs" :key="index">
            <div class="flex items-center gap-2">
                <a 
                    :href="(crumb as Record<string, any>).url || '#'" 
                    class="text-xs font-black uppercase tracking-widest transition-colors"
                    :class="[
                        index === crumbs.length - 1 
                            ? 'text-primary' 
                            : 'text-slate-400 hover:text-slate-900 dark:hover:text-white'
                    ]"
                >
                    {{ (crumb as Record<string, any>).label }}
                </a>
                <span v-if="index < crumbs.length - 1" class="text-slate-300 dark:text-slate-700 font-bold">/</span>
            </div>
        </template>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
    getVal, 
    getLayoutStyles
} from '../utils/styleUtils'
import type { BlockInstance, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

const crumbs = computed(() => (settings.value.items as unknown[]) || [
  { label: 'Home', url: '/' },
  { label: 'Products', url: '/products' },
  { label: 'Professional Tier', url: '/products/pro' }
])

const cardStyles = computed((): CSSProperties => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles as CSSProperties
})

const containerStyles = computed((): CSSProperties => {
    const layoutStyles = getLayoutStyles(settings.value, props.device)
    return { 
        ...layoutStyles,
        width: '100%'
    } as CSSProperties
})
</script>

<style scoped>
.breadcrumbs-block { width: 100%; position: relative; }
</style>

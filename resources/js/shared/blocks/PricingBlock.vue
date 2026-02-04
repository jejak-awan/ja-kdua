<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :settings="settings"
    class="pricing-block transition-colors duration-300"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Pricing Tables'"
  >
    <div 
        class="pricing-container mx-auto w-full transition-colors duration-300" 
        :style="containerStyles"
    >
        <div class="pricing-grid grid items-stretch" :style="gridStyles">
          <Card 
            v-for="(item, index) in items" 
            :key="index"
            class="flex flex-col transition-[width] duration-500 group rounded-[3rem] p-12 relative overflow-visible border-none shadow-2xl pricing-card"
            :class="[
                item.isFeatured ? 'z-10' : 'bg-white',
            ]"
            :style="getCardStyle(item)"
          >
            <!-- Featured Badge -->
            <Badge 
              v-if="item.isFeatured" 
              class="absolute -top-5 left-1/2 -translate-x-1/2 px-8 py-2.5 uppercase tracking-[0.2em] font-black text-[10px] shadow-xl border-none"
              :style="{ backgroundColor: (getVal<string>(settings, 'accentColor', device) || 'var(--primary)'), color: '#fff' }"
            >
              Most Popular
            </Badge>

            <CardHeader class="p-0 mb-12 text-center">
              <CardTitle class="text-2xl font-black text-slate-900 mb-8 tracking-tighter" :style="titleStyles">{{ item.title || 'Starter' }}</CardTitle>
              <div class="flex items-baseline justify-center gap-1 group-hover:scale-110 transition-transform duration-500">
                <span class="text-3xl font-black opacity-30 tracking-tighter">{{ item.currency || '$' }}</span>
                <span class="text-7xl font-black tracking-tighter" :style="(priceStyles as any)">{{ item.price || '0' }}</span>
                <span class="text-slate-400 font-bold uppercase text-[10px] tracking-widest ml-1">{{ item.period || '/mo' }}</span>
              </div>
            </CardHeader>

            <CardContent class="p-0 flex flex-col flex-grow">
                <div class="w-full h-px bg-slate-100 dark:bg-slate-800 mb-10"></div>
                <ul class="flex flex-col gap-6 mb-14">
                    <li 
                      v-for="(feature, fIndex) in parseFeatures(item.features || [])" 
                      :key="fIndex"
                      class="flex items-center gap-4 text-slate-600 dark:text-slate-400 font-bold text-sm text-left"
                    >
                      <div class="flex-shrink-0 w-7 h-7 bg-slate-50 dark:bg-slate-900 rounded-xl flex items-center justify-center shadow-sm border border-slate-100 dark:border-slate-800" :style="{ color: (getVal<string>(settings, 'accentColor', device) || 'var(--primary)') }">
                          <CheckIcon class="w-4 h-4" stroke-width="3" />
                      </div>
                      <span class="tracking-tight leading-tight">{{ feature }}</span>
                    </li>
                </ul>
            </CardContent>

            <CardFooter class="p-0 mt-auto">
                <Button 
                  class="w-full py-8 rounded-[1.5rem] font-black uppercase tracking-[0.15em] text-xs shadow-2xl transition-[width] duration-500 hover:scale-[1.02] active:scale-95 border-none"
                  :variant="item.isFeatured ? 'default' : 'secondary'"
                  :style="item.isFeatured ? { backgroundColor: (getVal<string>(settings, 'accentColor', device) || 'var(--primary)'), color: '#fff' } : {}"
                >
                  {{ item.buttonText || 'Join Program' }}
                </Button>
            </CardFooter>
          </Card>
        </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, CardHeader, CardTitle, CardContent, CardFooter, Button, Badge } from '../ui'
import CheckIcon from 'lucide-vue-next/dist/esm/icons/check.js';
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

interface PricingItem {
    title?: string;
    price?: string;
    currency?: string;
    period?: string;
    features?: string | string[];
    isFeatured?: boolean;
    buttonText?: string;
}

const items = computed<PricingItem[]>(() => (settings.value.items as PricingItem[]) || [])

const parseFeatures = (features: string | string[]) => {
  if (!features) return []
  if (Array.isArray(features)) return features
  return features.split('\n').filter(f => f.trim() !== '')
}

const containerStyles = computed(() => {
    return getLayoutStyles(settings.value, device.value)
})

const gridStyles = computed(() => {
    const colsVal = getVal<string | number>(settings.value, 'columns', device.value)
    const cols = parseInt(colsVal as string) || 3
    const gapVal = getVal<string | number>(settings.value, 'gap', device.value)
    const gap = parseInt(gapVal as string) || 32
    const styles: Record<string, string | number> = {
        gridTemplateColumns: device.value === 'mobile' ? '1fr' : (device.value === 'tablet' ? 'repeat(2, 1fr)' : `repeat(${cols}, minmax(0, 1fr))`),
        gap: `${gap}px`
    }
    return styles
})

const getCardStyle = (item: {isFeatured?: boolean}) => {
    const bgColor = item.isFeatured 
        ? (getVal<string>(settings.value, 'featuredCardBackgroundColor', device.value) || '#ffffff')
        : (getVal<string>(settings.value, 'cardBackgroundColor', device.value) || '#ffffff')
    
    const hoverScale = getVal<number>(settings.value, 'hover_scale', device.value) || 1.02
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', device.value) || 100

    const styles: Record<string, string | number> = {
        backgroundColor: bgColor,
        '--hover-scale': hoverScale,
        '--hover-brightness': `${hoverBrightness}%`
    }
    return styles
}

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const priceStyles = computed(() => getTypographyStyles(settings.value, 'price_', device.value))
</script>

<style scoped>
.pricing-block { width: 100%; }
.pricing-card {
  transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.pricing-card:hover {
  transform: scale(var(--hover-scale, 1.02));
  filter: brightness(var(--hover-brightness, 100%));
}
</style>


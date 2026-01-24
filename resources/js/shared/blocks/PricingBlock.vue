<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings }">
      <div 
        class="pricing-block mx-auto w-full px-6" 
        :class="[getVal(settings, 'width') || 'max-w-6xl', getVal(settings, 'padding') || 'py-20']"
      >
        <div class="pricing-grid grid items-stretch" :style="gridStyles">
          <Card 
            v-for="(item, index) in items" 
            :key="index"
            class="flex flex-col transition-all duration-500 hover:-translate-y-4 group rounded-[2.5rem] p-10 relative overflow-visible"
            :class="[
                item.isFeatured ? 'shadow-2xl z-10 border-primary' : 'bg-white shadow-xl border-slate-100',
            ]"
            :style="getCardStyle(item)"
          >
            <!-- Featured Badge -->
            <Badge 
              v-if="item.isFeatured" 
              class="absolute -top-4 left-1/2 -translate-x-1/2 px-6 py-1.5 uppercase tracking-widest"
              variant="default"
            >
              Most Popular
            </Badge>

            <CardHeader class="p-0 mb-10 text-center">
              <CardTitle class="text-xl font-bold text-slate-900 mb-6" :style="titleStyles">{{ item.title || item.name || 'Plan Name' }}</CardTitle>
              <div class="flex items-baseline justify-center gap-1">
                <span class="text-2xl font-bold opacity-40">$</span>
                <span class="text-6xl font-black tracking-tighter" :style="priceStyles">{{ item.price || '0' }}</span>
                <span class="text-slate-400 font-medium">{{ item.period || '/mo' }}</span>
              </div>
            </CardHeader>

            <CardContent class="p-0 flex flex-col flex-grow">
              <ul class="flex flex-col gap-5 mb-12">
                <li 
                  v-for="(feature, fIndex) in parseFeatures(item.features)" 
                  :key="fIndex"
                  class="flex items-center gap-4 text-slate-600 font-medium text-left"
                >
                  <div class="flex-shrink-0 w-6 h-6 bg-slate-100 rounded-full flex items-center justify-center" :style="{ backgroundColor: getVal(settings, 'accentColor') + '10' }">
                      <CheckIcon class="w-4 h-4" :style="{ color: getVal(settings, 'accentColor') || 'currentColor' }" />
                  </div>
                  <span>{{ feature }}</span>
                </li>
              </ul>
            </CardContent>

            <CardFooter class="p-0">
                <Button 
                  class="w-full py-8 rounded-2xl font-bold shadow-lg"
                  :variant="item.isFeatured ? 'default' : 'secondary'"
                  :style="item.isFeatured ? { backgroundColor: getVal(settings, 'accentColor') } : {}"
                >
                  {{ item.buttonText || 'Get Started' }}
                </Button>
            </CardFooter>
          </Card>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, CardHeader, CardTitle, CardContent, CardFooter, Button, Badge } from '../ui'
import { Check as CheckIcon } from 'lucide-vue-next'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})
const items = computed(() => settings.value.items || [])

const parseFeatures = (features) => {
  if (!features) return []
  if (Array.isArray(features)) return features
  return features.split('\n').filter(f => f.trim() !== '')
}

const gridStyles = computed(() => {
    const cols = getVal(settings.value, 'columns', props.device) || 3
    const gap = getVal(settings.value, 'gap', props.device) || 32
    return {
        gridTemplateColumns: props.device === 'mobile' ? '1fr' : `repeat(${cols}, minmax(0, 1fr))`,
        gap: `${gap}px`
    }
})

const getCardStyle = (item) => {
    const bgColor = item.isFeatured 
        ? (getVal(settings.value, 'featuredCardBackgroundColor', props.device) || '#ffffff')
        : (getVal(settings.value, 'cardBackgroundColor', props.device) || '#ffffff')
    
    return {
        backgroundColor: bgColor
    }
}

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const priceStyles = computed(() => getTypographyStyles(settings.value, 'price_', props.device))
</script>

<style scoped>
.pricing-block { width: 100%; }
</style>

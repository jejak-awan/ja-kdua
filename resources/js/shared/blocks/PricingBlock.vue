<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <div 
        class="pricing-block mx-auto w-full px-6" 
        :class="[getVal(settings, 'width') || 'max-w-6xl', getVal(settings, 'padding') || 'py-20']"
      >
        <div class="pricing-grid grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
          <div 
            v-for="(item, index) in items" 
            :key="index"
            class="pricing-card flex flex-col transition-all duration-500 hover:-translate-y-4 group"
            :class="[
                item.isFeatured ? 'featured-card shadow-2xl relative z-10' : 'bg-white shadow-xl',
                'rounded-[2.5rem] p-10 border border-slate-100'
            ]"
            :style="item.isFeatured ? featuredCardStyle : {}"
          >
            <!-- Featured Badge -->
            <div 
              v-if="item.isFeatured" 
              class="absolute -top-4 left-1/2 -translate-x-1/2 px-6 py-1.5 bg-indigo-600 text-white text-xs font-black uppercase tracking-widest rounded-full shadow-lg"
            >
              Most Popular
            </div>

            <div class="mb-10 text-center">
              <h3 class="text-xl font-bold text-slate-900 mb-6">{{ item.name || 'Plan Name' }}</h3>
              <div class="flex items-baseline justify-center gap-1">
                <span class="text-2xl font-bold opacity-40">$</span>
                <span class="text-6xl font-black tracking-tighter">{{ item.price || '0' }}</span>
                <span class="text-slate-400 font-medium">/mo</span>
              </div>
            </div>

            <ul class="flex flex-col gap-5 mb-12 flex-grow">
              <li 
                v-for="(feature, fIndex) in parseFeatures(item.features)" 
                :key="fIndex"
                class="flex items-center gap-4 text-slate-600 font-medium"
              >
                <div class="flex-shrink-0 w-6 h-6 bg-slate-100 rounded-full flex items-center justify-center">
                    <CheckIcon class="w-4 h-4 text-indigo-600" />
                </div>
                <span>{{ feature }}</span>
              </li>
            </ul>

            <button 
              class="w-full py-5 rounded-2xl font-bold transition-all duration-300 transform active:scale-95 shadow-lg active:shadow-none"
              :class="item.isFeatured ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-slate-900 text-white hover:bg-slate-800'"
            >
              {{ item.buttonText || 'Get Started' }}
            </button>
          </div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Check as CheckIcon } from 'lucide-vue-next'
import { getVal } from '../utils/styleUtils'

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

const featuredCardStyle = computed(() => {
    return {
        background: 'linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%)',
        borderColor: '#4f46e5',
        borderWidth: '2px'
    }
})
</script>

<style scoped>
.pricing-card { transform-style: preserve-3d; }
.featured-card { 
    border-color: #4f46e5;
    background: white;
}
</style>

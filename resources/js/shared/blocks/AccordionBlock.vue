<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <div 
        class="accordion-block mx-auto" 
        :class="[
            getVal(settings, 'width', blockDevice) || 'max-w-4xl',
            getVal(settings, 'padding', blockDevice) || 'py-20'
        ]"
      >
        <div class="accordion-list flex flex-col gap-5">
          <div 
            v-for="(item, index) in items" 
            :key="index"
            class="accordion-item transition-all duration-500 ease-in-out"
            :class="[
                getItemClasses(settings, index),
                { 'is-open shadow-lg': openIndices.includes(index) && getVal(settings, 'variant') === 'boxed' }
            ]"
            :style="getItemStyles(settings, index)"
          >
            <!-- Header -->
            <button 
              class="accordion-header w-full flex items-center justify-between text-left focus:outline-none group"
              :class="headerPadding(settings)"
              @click="toggle(index)"
            >
              <span 
                class="accordion-title font-bold text-lg"
                :class="{ 'text-primary': openIndices.includes(index) }"
              >
                {{ item.question || 'Question' }}
              </span>
              
              <div 
                class="accordion-icon ml-4 transition-transform duration-300 shrink-0"
                :class="{ 'rotate-180': openIndices.includes(index) }"
              >
                <component :is="getIcon(settings)" class="w-5 h-5 opacity-60 group-hover:opacity-100" />
              </div>
            </button>

            <!-- Content -->
            <div 
                class="accordion-content overflow-hidden transition-all duration-300"
                :style="{ maxHeight: openIndices.includes(index) ? '500px' : '0px' }"
            >
                <div class="p-6 pt-0 text-slate-600 leading-relaxed">
                    {{ item.answer || 'Answer goes here...' }}
                </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { ChevronDown, Plus, ArrowDown } from 'lucide-vue-next'
import { getVal } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})
const items = computed(() => settings.value.items || [])
const openIndices = ref([])

const toggle = (index) => {
    // Single open mode by default, unless we add allowMultiple setting back
    if (openIndices.value.includes(index)) {
        openIndices.value = []
    } else {
        openIndices.value = [index]
    }
}

const getIcon = (settings) => {
    const style = getVal(settings, 'iconStyle') || 'chevron-down'
    if (style === 'plus') return Plus
    if (style === 'arrow-down') return ArrowDown
    return ChevronDown
}

const getItemClasses = (settings, index) => {
    const variant = getVal(settings, 'variant') || 'simple'
    const isOpen = openIndices.value.includes(index)
    
    if (variant === 'boxed') {
        return 'border rounded-xl bg-white overflow-hidden ' + (isOpen ? 'shadow-sm border-gray-200' : 'border-transparent shadow-sm')
    }
    if (variant === 'minimal') {
        return 'border-b border-gray-100 last:border-0'
    }
    // simple
    return 'border-b border-gray-200 last:border-0'
}

const getItemStyles = (settings, index) => {
    const variant = getVal(settings, 'variant') || 'simple'
    if (variant === 'boxed' && openIndices.value.includes(index)) {
        return {
            backgroundColor: getVal(settings, 'activeBgColor') || '#f8fafc'
        }
    }
    return {}
}

const headerPadding = (settings) => {
    const variant = getVal(settings, 'variant') || 'simple'
    return variant === 'boxed' ? 'p-6' : 'py-5'
}

</script>

<style scoped>
.accordion-block { width: 100%; }
.accordion-content { transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
</style>

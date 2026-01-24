<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <div 
        class="faq-block mx-auto w-full transition-all duration-300" 
        :class="getVal(settings, 'padding') || 'py-12'"
      >
        <div 
            class="faq-list flex flex-col" 
            :class="[getVal(settings, 'variant') === 'boxed' ? 'gap-4' : 'gap-0']"
        >
          <div 
            v-for="(item, index) in items" 
            :key="index"
            class="faq-item transition-all duration-300 overflow-hidden"
            :class="[
                getItemClasses(settings, index),
                { 'is-open': openIndices.includes(index) }
            ]"
            :style="getItemStyles(settings, index)"
          >
            <!-- Header/Question -->
            <button 
              class="faq-header w-full flex items-center justify-between text-left focus:outline-none group"
              :class="headerPadding(settings)"
              :disabled="layout(settings) === 'list'"
              @click="toggle(index)"
            >
              <span 
                class="faq-question font-bold text-lg text-slate-900 transition-colors group-hover:text-indigo-600"
                :class="{ 'text-indigo-600': openIndices.includes(index) && layout(settings) === 'accordion' }"
              >
                {{ item.question || 'Question text...' }}
              </span>
              
              <div 
                v-if="layout(settings) === 'accordion'"
                class="faq-icon ml-4 transition-transform duration-300 shrink-0"
                :class="{ 'rotate-180': openIndices.includes(index) }"
              >
                <ChevronDown 
                    class="w-5 h-5 opacity-60 group-hover:opacity-100" 
                    :style="{ color: getVal(settings, 'iconColor') || '#4f46e5' }"
                />
              </div>
            </button>

            <!-- Content/Answer -->
            <div 
                class="faq-content-wrapper overflow-hidden transition-all duration-500 ease-in-out"
                :style="contentStyles(settings, index)"
            >
                <div 
                    class="p-6 pt-0 text-slate-600 font-medium leading-relaxed max-w-4xl"
                    :contenteditable="mode === 'edit'"
                    @blur="e => updateItemField(index, 'answer', e.target.innerText)"
                >
                    {{ item.answer || 'Answer text goes here...' }}
                </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { ChevronDown } from 'lucide-vue-next'
import { getVal } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})
const items = computed(() => settings.value.items || [])
const openIndices = ref([0]) // Open first by default

const layout = (settings) => getVal(settings, 'layout') || 'accordion'

const toggle = (index) => {
    if (layout(settings.value) === 'list') return
    
    if (getVal(settings.value, 'allowMultiple')) {
        if (openIndices.value.includes(index)) {
            openIndices.value = openIndices.value.filter(i => i !== index)
        } else {
            openIndices.value.push(index)
        }
    } else {
        openIndices.value = openIndices.value.includes(index) ? [] : [index]
    }
}

const getItemClasses = (settings, index) => {
    const variant = getVal(settings, 'variant') || 'boxed'
    const isOpen = openIndices.value.includes(index)
    const isList = layout(settings) === 'list'
    
    if (variant === 'boxed') {
        const border = getVal(settings, 'itemBorderColor') || 'transparent'
        return 'rounded-3xl bg-white shadow-xl transition-shadow duration-500 ' + (isOpen || isList ? 'shadow-2xl border-indigo-100' : 'border-transparent')
    }
    if (variant === 'minimal') {
        return 'border-b border-gray-100 last:border-0'
    }
    // simple
    return 'border-b border-gray-200 last:border-0'
}

const getItemStyles = (settings, index) => {
    const variant = getVal(settings, 'variant') || 'boxed'
    const isOpen = openIndices.value.includes(index)
    
    if (variant === 'boxed' && (isOpen || layout(settings) === 'list')) {
        return {
            backgroundColor: getVal(settings, 'activeBgColor') || 'white',
            border: `1px solid ${getVal(settings, 'itemBorderColor') || 'rgba(79, 70, 229, 0.1)'}`
        }
    }
    return {}
}

const headerPadding = (settings) => {
    const variant = getVal(settings, 'variant') || 'boxed'
    return variant === 'boxed' ? 'p-6' : 'py-6'
}

const contentStyles = (settings, index) => {
    if (layout(settings) === 'list') return { maxHeight: 'none', opacity: 1 }
    return {
        maxHeight: openIndices.value.includes(index) ? '1000px' : '0px',
        opacity: openIndices.value.includes(index) ? 1 : 0
    }
}

const updateItemField = (index, key, value) => {
    // Handling update logic
}
</script>

<style scoped>
.faq-block { width: 100%; }
.faq-content-wrapper { transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease; }
</style>

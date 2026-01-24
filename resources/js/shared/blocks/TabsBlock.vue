<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <div 
        class="tabs-block" 
        :class="[
            getVal(settings, 'padding', blockDevice) || 'py-12'
        ]"
      >
        <Tabs 
          v-model="activeTabValue" 
          :orientation="getVal(settings, 'orientation', blockDevice) === 'vertical' && blockDevice !== 'mobile' ? 'vertical' : 'horizontal'"
          :class="layoutClasses(settings, blockDevice)"
        >
          <!-- Tab Headers -->
          <TabsList 
              class="tabs-header shrink-0 no-scrollbar overflow-x-auto bg-transparent border-none p-0 h-auto" 
              :class="headerClasses(settings, blockDevice)"
              :style="headerContainerStyles(settings)"
          >
            <TabsTrigger 
              v-for="(tab, index) in items" 
              :key="index"
              :value="`tab-${index}`"
              class="tab-button group whitespace-nowrap transition-all duration-300 relative shadow-none"
              :class="[
                  buttonClasses(settings, activeTabIndex === index),
              ]"
              :style="getTabStyles(settings, activeTabIndex === index)"
              @click="activeTabIndex = index"
            >
               <span class="flex items-center gap-2 relative z-10">
                 <component 
                  v-if="tab.icon" 
                  :is="getIcon(tab.icon)" 
                  class="w-4 h-4 transition-colors"
                 />
                 {{ tab.title || 'Tab Title' }}
               </span>
               
               <!-- Active Indicator for Underline style -->
               <div 
                  v-if="isUnderline(settings) && activeTabIndex === index" 
                  class="absolute bottom-0 left-0 w-full h-[2px] bg-primary"
                  :style="{ backgroundColor: getVal(settings, 'activeColor') || '#4f46e5' }"
               ></div>
            </TabsTrigger>
          </TabsList>
          
          <!-- Tab Content -->
          <div class="tabs-content grow pt-8 md:pt-0" :style="contentContainerStyles(settings)">
            <TabsContent 
               v-for="(tab, index) in items" 
               :key="index"
               :value="`tab-${index}`"
               class="tab-pane animate-in fade-in slide-in-from-bottom-3 duration-500 mt-0"
            >
               <div class="prose max-w-none text-slate-600 leading-relaxed font-medium" v-html="tab.content || 'Content goes here...'"></div>
            </TabsContent>
          </div>
        </Tabs>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '../ui'
import * as LucideIcons from 'lucide-vue-next'
import { getVal } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})
// ... (rest unchanged)


const settings = computed(() => props.module?.settings || {})
const items = computed(() => settings.value.tabs || [])
const activeTabIndex = ref(0)
const activeTabValue = computed({
    get: () => `tab-${activeTabIndex.value}`,
    set: (val) => {
        const index = parseInt(val.split('-')[1])
        if (!isNaN(index)) activeTabIndex.value = index
    }
})

const getIcon = (name) => {
    const cleanName = typeof name === 'string' ? name.replace('lucide:', '') : name
    return LucideIcons[cleanName] || null
}

const isVertical = (settings, device) => {
    return getVal(settings, 'orientation', device) === 'vertical' && device !== 'mobile'
}

const isUnderline = (settings) => {
    return (getVal(settings, 'style') || 'underline') === 'underline'
}

const layoutClasses = (settings, device) => {
    return isVertical(settings, device) 
        ? 'flex flex-row gap-8 items-start' 
        : 'flex flex-col gap-6'
}

const headerClasses = (settings, device) => {
    if (isVertical(settings, device)) {
        return 'flex flex-col w-48 lg:w-64 border-r border-gray-100'
    }
    return 'flex w-full border-b border-gray-200'
}

const headerContainerStyles = (settings) => {
    // Optional customization
    return {}
}

const buttonClasses = (settings, isActive) => {
    const style = getVal(settings, 'style') || 'underline'
    
    let base = 'px-6 py-4 font-medium text-sm focus:outline-none '
    
    if (style === 'underline') {
        return base + (isActive ? 'text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50')
    }
    
    if (style === 'pills') {
        return base + ' rounded-lg mb-1 mr-1 ' + (isActive ? 'text-white shadow-md' : 'text-gray-600 hover:bg-gray-100')
    }
    
    if (style === 'cards') {
        return base + ' border-t border-l border-r rounded-t-lg -mb-px ' + (isActive ? 'bg-white border-gray-200 text-gray-900' : 'bg-gray-50 border-transparent text-gray-500 hover:text-gray-700')
    }

    return base
}

const getTabStyles = (settings, isActive) => {
    const style = getVal(settings, 'style') || 'underline'
    const activeColor = getVal(settings, 'activeColor') || '#4f46e5'
    
    if (style === 'pills' && isActive) {
        return { backgroundColor: activeColor }
    }
    if (style === 'underline' && isActive) {
        return { color: activeColor } // text color
    }
    
    return {}
}

const contentContainerStyles = (settings) => {
    const style = getVal(settings, 'style') || 'underline'
    if (style === 'cards') {
        return { 
            border: '1px solid #e5e7eb',
            borderTop: 'none',
            borderRadius: '0 0 0.5rem 0.5rem',
            padding: '2rem',
            backgroundColor: '#ffffff'
        }
    }
    return {}
}

</script>

<style scoped>
.tabs-block { width: 100%; }
.animate-fade-in { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
/* Hide scrollbar */
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

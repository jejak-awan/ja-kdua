<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="tabs-block transition-colors duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Tabs'"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings, device: blockDevice }">
      <div 
        class="tabs-container" 
        :style="containerStyles"
      >
        <Tabs 
          v-model="activeTabValue" 
          :orientation="getVal(blockSettings, 'orientation', blockDevice) === 'vertical' && blockDevice !== 'mobile' ? 'vertical' : 'horizontal'"
          :class="layoutClasses(blockSettings, blockDevice)"
        >
          <!-- Tab Headers -->
          <TabsList 
              class="tabs-header shrink-0 no-scrollbar overflow-x-auto bg-transparent border-none p-0 h-auto" 
              :class="headerClasses(blockSettings, blockDevice)"
              :style="headerContainerStyles(blockSettings)"
          >
            <TabsTrigger 
              v-for="(tab, index) in items" 
              :key="index"
              :value="`tab-${index}`"
              class="tab-button group whitespace-nowrap transition-colors duration-300 relative shadow-none"
              :class="[
                  buttonClasses(blockSettings, activeTabIndex === index),
              ]"
              :style="getTabStyles(blockSettings, activeTabIndex === index)"
              @click="activeTabIndex = (index as number)"
            >
               <span class="flex items-center gap-2 relative z-10" :style="tabTypographyStyles(blockSettings, activeTabIndex === index)">
                 <component 
                  v-if="tab.icon" 
                  :is="getIcon(tab.icon)" 
                  class="w-4 h-4 transition-colors"
                 />
                 {{ tab.title || 'Tab Title' }}
               </span>
               
               <!-- Active Indicator for Underline style -->
               <div 
                  v-if="isUnderline(blockSettings) && activeTabIndex === index" 
                  class="absolute bottom-0 left-0 w-full h-[2px] bg-primary"
                  :style="{ backgroundColor: getVal(blockSettings, 'activeColor') || '#4f46e5' }"
               ></div>
            </TabsTrigger>
          </TabsList>
          
          <!-- Tab Content -->
          <div class="tabs-content grow pt-8 md:pt-0" :style="contentContainerStyles(blockSettings)">
            <TabsContent 
               v-for="(tab, index) in items" 
               :key="index"
               :value="`tab-${index}`"
               class="tab-pane animate-in fade-in slide-in-from-bottom-3 duration-500 mt-0"
            >
               <div class="prose max-w-none text-slate-600 leading-relaxed font-medium" :style="contentTypographyStyles(blockSettings)" v-html="tab.content || 'Content goes here...'"></div>
            </TabsContent>
          </div>
        </Tabs>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '../ui'
import Layers from 'lucide-vue-next/dist/esm/icons/layers.js';
import Layout from 'lucide-vue-next/dist/esm/icons/layout-dashboard.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import User from 'lucide-vue-next/dist/esm/icons/user.js';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Mail from 'lucide-vue-next/dist/esm/icons/mail.js';
import Bell from 'lucide-vue-next/dist/esm/icons/bell.js';
import Calendar from 'lucide-vue-next/dist/esm/icons/calendar.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import Star from 'lucide-vue-next/dist/esm/icons/star.js';
import Heart from 'lucide-vue-next/dist/esm/icons/heart.js';
import HelpCircle from 'lucide-vue-next/dist/esm/icons/circle-question-mark.js';
import Info from 'lucide-vue-next/dist/esm/icons/info.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';import { getVal, getLayoutStyles, getTypographyStyles } from '../utils/styleUtils'
import type { Component } from 'vue'

const iconMap: Record<string, Component> = {
  Layers, Layout, Settings, User, Globe, FileText, Mail, Bell, 
  Calendar, Zap, Star, Heart, HelpCircle, Info, Check, X
}
import type { BlockInstance } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module?.settings || {}) as Record<string, any>)
const items = computed(() => settings.value.items || [])
const activeTabIndex = ref(0)
const activeTabValue = computed({
    get: () => `tab-${activeTabIndex.value}`,
    set: (val: string) => {
        const index = parseInt(val.split('-')[1])
        if (!isNaN(index)) activeTabIndex.value = index
    }
})

const getIcon = (name: any) => {
    const cleanName = typeof name === 'string' ? name.replace('lucide:', '') : name
    return iconMap[cleanName] || iconMap.Zap
}

const isVertical = (settings: any, device: string) => {
    return getVal(settings, 'orientation', device) === 'vertical' && device !== 'mobile'
}

const isUnderline = (settings: any) => {
    return (getVal(settings, 'style') || 'underline') === 'underline'
}

const layoutClasses = (settings: any, device: string) => {
    return isVertical(settings, device) 
        ? 'flex flex-row gap-8 items-start' 
        : 'flex flex-col gap-6'
}

const headerClasses = (settings: any, device: string) => {
    if (isVertical(settings, device)) {
        return 'flex flex-col w-48 lg:w-64 border-r border-gray-100'
    }
    return 'flex w-full border-b border-gray-200'
}

const headerContainerStyles = (settings: any) => {
    return {}
}

const buttonClasses = (settings: any, isActive: any) => {
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

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => {
    return getLayoutStyles(settings.value, props.device)
})

const getTabStyles = (settings: any, isActive: any) => {
    const style = getVal(settings, 'style') || 'underline'
    const activeColor = getVal(settings, 'activeColor') || '#4f46e5'
    
    if (style === 'pills' && isActive) {
        return { backgroundColor: activeColor }
    }
    if (style === 'underline' && isActive) {
        return { color: activeColor } 
    }
    return {}
}

const tabTypographyStyles = (settings: any, isActive: boolean) => {
    return getTypographyStyles(settings, isActive ? 'tab_active_' : 'tab_', props.device)
}

const contentTypographyStyles = (settings: any) => {
    return getTypographyStyles(settings, 'content_', props.device)
}

const contentContainerStyles = (settings: any) => {
    const style = getVal(settings, 'style') || 'underline'
    const bgColor = getVal(settings, 'contentBackgroundColor') || 'transparent'
    const padding = getVal(settings, 'contentPadding')
    
    const styles: any = {
        backgroundColor: bgColor
    }
    
    if (style === 'cards') {
        styles.border = '1px solid #e5e7eb'
        styles.borderTop = 'none'
        styles.borderRadius = '0 0 0.5rem 0.5rem'
        styles.padding = padding || '2rem'
    } else if (padding) {
        styles.padding = padding
    }
    
    return styles
}

</script>

<style scoped>
.tabs-block { width: 100%; }
.tabs-item {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.tabs-item:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

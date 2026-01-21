<template>
  <BaseBlock :module="module" :settings="settings" class="embed-block">
    <div v-if="hasContent" class="embed-container bg-black rounded-2xl overflow-hidden shadow-2xl transition-all hover:shadow-blue-500/10" :style="containerStyles">
      <iframe 
        v-if="settings.embedType === 'url' && settings.embedUrl" 
        :src="settings.embedUrl" 
        frameborder="0" 
        allowfullscreen 
        class="embed-iframe absolute inset-0 w-full h-full" 
      />
      <div v-else-if="settings.embedCode" class="embed-code absolute inset-0 w-full h-full" v-html="settings.embedCode" />
    </div>
    <div v-else class="embed-placeholder min-h-[300px] flex flex-col items-center justify-center gap-4 p-12 bg-gray-50 dark:bg-gray-900 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-2xl text-gray-400">
      <div class="icon-wrap bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm">
        <Code2 class="w-10 h-10 opacity-50" />
      </div>
      <span class="font-bold">Third-Party Embed</span>
      <p class="text-xs opacity-60 max-w-xs text-center">Add an IFrame URL or custom HTML code to display external content</p>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Code2 } from 'lucide-vue-next'
import { 
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const hasContent = computed(() => (settings.value.embedType === 'url' && settings.value.embedUrl) || settings.value.embedCode)

const aspectRatios = { '16:9': '56.25%', '4:3': '75%', '1:1': '100%' }

const containerStyles = computed(() => {
  const ratio = getResponsiveValue(settings.value, 'aspectRatio', device.value) || '16:9'
  if (ratio === 'auto') {
      const height = getResponsiveValue(settings.value, 'height', device.value) || 450
      return { height: typeof height === 'number' ? `${height}px` : height }
  }
  return { 
    paddingTop: aspectRatios[ratio] || '56.25%'
  }
})
</script>

<style scoped>
.embed-block { width: 100%; }
</style>

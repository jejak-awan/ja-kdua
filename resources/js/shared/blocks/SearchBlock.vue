<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :settings="settings"
    class="search-block transition-colors duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Search'"
  >
    <div class="search-form-wrapper mx-auto w-full" :style="containerStyles">
      <form 
        @submit.prevent="handleSearch" 
        class="search-form group flex items-stretch bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl border border-slate-100 dark:border-slate-800/50 overflow-hidden focus-within:ring-4 focus-within:ring-primary/10 transition-[width] duration-500 hover:shadow-primary/5"
        :style="formStyles"
      >
        <label for="search-input" class="sr-only">Search</label>
        <div class="relative flex-1 flex items-center h-full">
            <LucideIcon 
              v-if="currentButtonStyle === 'text'" 
              name="Search"
              class="absolute left-6 w-5 h-5 text-slate-400 group-focus-within:text-primary transition-colors z-10 pointer-events-none" 
            />
            <Input 
              id="search-input"
              type="text" 
              class="w-full bg-transparent border-none shadow-none focus-visible:ring-0 h-full text-slate-900 dark:text-white font-medium"
              :class="currentButtonStyle === 'text' ? 'pl-14 pr-6' : 'px-8'"
              :placeholder="placeholderValue"
              :style="inputStyles"
              v-model="searchQuery"
            />
        </div>
        <Button 
            v-if="showButton" 
            type="submit"
            class="h-auto px-10 font-black uppercase tracking-widest text-[10px] transition-[width] duration-500 hover:brightness-110 active:scale-95 whitespace-nowrap rounded-none bg-primary text-white border-none shadow-xl group-focus-within:px-12" 
            :style="buttonStyles"
        >
          <LucideIcon v-if="currentButtonStyle !== 'text'" name="Search" class="w-5 h-5" :class="currentButtonStyle === 'both' ? 'mr-3' : ''" />
          <span v-if="currentButtonStyle !== 'icon'">{{ buttonTextValue }}</span>
        </Button>
      </form>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Input, Button } from '../ui'
import { LucideIcon } from '@/components/ui';
import { 
  getVal,
  getLayoutStyles,
  getTypographyStyles
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<any>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const searchQuery = ref('')

const handleSearch = () => {
    if (props.mode === 'edit') return
    if (!searchQuery.value.trim()) return
    window.location.href = `/search?q=${encodeURIComponent(searchQuery.value)}`
}

const currentButtonStyle = computed(() => getVal(settings.value, 'buttonStyle', device.value) || 'icon')
const placeholderValue = computed(() => getVal(settings.value, 'placeholder', device.value) || 'Search...')
const buttonTextValue = computed(() => getVal(settings.value, 'buttonText', device.value) || 'Search')
const showButton = computed(() => getVal(settings.value, 'showButton', device.value) !== false)

const containerStyles = computed(() => getLayoutStyles(settings.value, device.value))

const formStyles = computed(() => {
  const h = getVal(settings.value, 'height', device.value) || 64
  return {
    height: typeof h === 'number' ? `${h}px` : h
  }
})

const inputStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'input_', device.value)
  return {
    ...styles,
    height: '100%'
  }
})

const buttonStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'button_', device.value)
  const bgColor = getVal(settings.value, 'buttonBackgroundColor', device.value) || ''
  const textColor = getVal(settings.value, 'buttonTextColor', device.value) || ''
  
  return {
    ...styles,
    backgroundColor: bgColor,
    color: textColor
  }
})
</script>

<style scoped>
.search-block { width: 100%; }
.search-form { width: 100%; height: 100%; }
</style>

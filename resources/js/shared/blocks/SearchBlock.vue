<template>
  <BaseBlock :module="module" :settings="settings" class="search-block">
    <div class="search-form-container mx-auto" :style="formStyles">
      <form 
        @submit.prevent="handleSearch" 
        class="search-form flex items-stretch bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-100 dark:border-gray-800 overflow-hidden focus-within:ring-2 focus-within:ring-primary/20 transition-all"
      >
        <label for="search-input" class="sr-only">Search</label>
        <div class="relative flex-1 flex items-center h-full">
            <SearchIcon 
              v-if="currentButtonStyle === 'text'" 
              class="absolute left-4 w-5 h-5 text-muted-foreground z-10 pointer-events-none" 
            />
            <Input 
              id="search-input"
              type="text" 
              class="w-full bg-transparent border-none shadow-none focus-visible:ring-0 h-full"
              :class="currentButtonStyle === 'text' ? 'pl-11 pr-4' : 'px-6'"
              :placeholder="placeholderValue"
              :style="inputStyles"
              v-model="searchQuery"
            />
        </div>
        <Button 
            v-if="showButton" 
            type="submit"
            class="h-full px-8 font-bold transition-all hover:brightness-110 active:scale-95 whitespace-nowrap rounded-none" 
            :style="buttonStyles"
        >
          <SearchIcon v-if="currentButtonStyle !== 'text'" class="w-5 h-5 mr-2" />
          <span v-if="currentButtonStyle !== 'icon'">{{ buttonTextValue }}</span>
        </Button>
      </form>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Input, Button } from '../ui'
import { Search as SearchIcon } from 'lucide-vue-next'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || props.device)

const searchQuery = ref('')

const handleSearch = () => {
    if (props.mode === 'edit') return
    if (!searchQuery.value.trim()) return
    window.location.href = `/search?q=${encodeURIComponent(searchQuery.value)}`
}

const currentButtonStyle = computed(() => getResponsiveValue(settings.value, 'buttonStyle', device.value) || 'icon')
const placeholderValue = computed(() => getResponsiveValue(settings.value, 'placeholder', device.value) || 'Search...')
const buttonTextValue = computed(() => getResponsiveValue(settings.value, 'buttonText', device.value) || 'Search')
const showButton = computed(() => getResponsiveValue(settings.value, 'showButton', device.value) !== false)

const formStyles = computed(() => {
  const styles = {}
  const height = getResponsiveValue(settings.value, 'height', device.value) || 56
  styles.height = `${height}px`
  return styles
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
  const bgColor = getResponsiveValue(settings.value, 'buttonBackgroundColor', device.value) || ''
  const textColor = getResponsiveValue(settings.value, 'buttonTextColor', device.value) || ''
  
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

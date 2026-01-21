<template>
  <div class="contact-field" :class="{ 'contact-field--full': isFullWidth }" :style="fieldStyles">
    <label v-if="settings.label" class="field-label block text-sm font-medium mb-2" :style="labelStyles">
      {{ settings.label }} 
      <span v-if="settings.required" class="text-red-500">*</span>
    </label>

    <!-- Text / Email / Password / Tel -->
    <input 
      v-if="['text', 'email', 'password', 'tel'].includes(settings.type || 'text')"
      :type="settings.type || 'text'"
      :placeholder="settings.placeholder"
      :required="settings.required"
      class="field-input w-full px-4 py-3 rounded-lg border bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
      :style="inputStyles"
    />

    <!-- Textarea -->
    <textarea 
      v-else-if="settings.type === 'textarea'"
      :placeholder="settings.placeholder"
      :required="settings.required"
      rows="4"
      class="field-input w-full px-4 py-3 rounded-lg border bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all resize-y"
      :style="inputStyles"
    ></textarea>

    <!-- Select -->
    <select 
      v-else-if="settings.type === 'select'"
      :required="settings.required"
      class="field-input w-full px-4 py-3 rounded-lg border bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all appearance-none"
      :style="inputStyles"
    >
      <option value="" disabled selected>{{ settings.placeholder || 'Select an option' }}</option>
      <option 
        v-for="opt in optionsList" 
        :key="opt" 
        :value="opt"
      >
        {{ opt }}
      </option>
    </select>

    <!-- Checkbox -->
    <div v-else-if="settings.type === 'checkbox'" class="flex items-center gap-3">
      <input 
        type="checkbox" 
        :required="settings.required"
        class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
      />
      <span class="text-sm cursor-pointer opacity-80" :style="labelStyles">
        {{ settings.placeholder || 'Yes, I agree' }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const isFullWidth = computed(() => getResponsiveValue(settings.value, 'fullWidth', device.value) === true)

const optionsList = computed(() => {
    const opts = settings.value.options
    if (!opts) return []
    if (Array.isArray(opts)) return opts
    return opts.split('\n').filter(Boolean)
})

const fieldStyles = computed(() => {
    const styles = {}
    if (isFullWidth.value) styles.gridColumn = '1 / -1'
    return styles
})

const labelStyles = computed(() => getTypographyStyles(settings.value, 'label_', device.value))
const inputStyles = computed(() => getTypographyStyles(settings.value, 'input_', device.value))
</script>

<style scoped>
.contact-field { width: 100%; }
</style>

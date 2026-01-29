<template>
  <div class="contact-field" :class="{ 'contact-field--full': isFullWidth }" :style="fieldStyles">
    <Label v-if="settings.label" class="mb-2 block" :style="labelStyles">
      {{ settings.label }} 
      <span v-if="settings.required" class="text-destructive font-bold ml-0.5">*</span>
    </Label>

    <!-- Text / Email / Password / Tel -->
    <Input 
      v-if="['text', 'email', 'password', 'tel'].includes(settings.type || 'text')"
      :type="settings.type || 'text'"
      :placeholder="settings.placeholder"
      :required="settings.required"
      class="h-12"
      :style="inputStyles"
    />

    <!-- Textarea -->
    <Textarea 
      v-else-if="settings.type === 'textarea'"
      :placeholder="settings.placeholder"
      :required="settings.required"
      rows="4"
      class="min-h-[120px] resize-y"
      :style="inputStyles"
    />

    <!-- Select -->
    <Select v-else-if="settings.type === 'select'" :required="settings.required">
      <SelectTrigger class="h-12 w-full" :style="inputStyles">
        <SelectValue :placeholder="settings.placeholder || 'Select an option'" />
      </SelectTrigger>
      <SelectContent>
        <SelectItem 
          v-for="opt in optionsList" 
          :key="opt" 
          :value="opt"
        >
          {{ opt }}
        </SelectItem>
      </SelectContent>
    </Select>

    <!-- Checkbox -->
    <div v-else-if="settings.type === 'checkbox'" class="flex items-center space-x-3 mt-1">
      <Checkbox 
        :id="`field-${module.id}`"
        :required="settings.required"
      />
      <Label :for="`field-${module.id}`" class="text-sm cursor-pointer font-normal opacity-80" :style="labelStyles">
        {{ settings.placeholder || 'Yes, I agree' }}
      </Label>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import { Label, Input, Textarea, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, Checkbox } from '../ui'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'
import type { BlockProps, BuilderInstance } from '@/types/builder'

const props = withDefaults(defineProps<BlockProps>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<BuilderInstance>('builder', null as any)
const settings = computed(() => props.settings || props.module?.settings || {})
const currentDevice = computed(() => props.device || builder?.device?.value || 'desktop')

const isFullWidth = computed(() => getResponsiveValue(settings.value, 'fullWidth', currentDevice.value) === true)

const optionsList = computed<string[]>(() => {
    const opts = settings.value.options
    if (!opts) return []
    if (Array.isArray(opts)) return opts
    if (typeof opts === 'string') return opts.split('\n').filter(Boolean)
    return []
})

const fieldStyles = computed(() => {
    const styles: Record<string, any> = {}
    if (isFullWidth.value) styles.gridColumn = '1 / -1'
    return styles
})

const labelStyles = computed(() => getTypographyStyles(settings.value, 'label_', currentDevice.value))
const inputStyles = computed(() => getTypographyStyles(settings.value, 'input_', currentDevice.value))
</script>

<style scoped>
.contact-field { width: 100%; }
</style>

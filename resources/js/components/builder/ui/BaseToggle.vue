<template>
  <div 
    class="flex items-center gap-3 cursor-pointer select-none group py-1"
    @click="toggle"
    :class="{ 'opacity-50 pointer-events-none': disabled }"
  >
    <Switch 
      :checked="modelValue" 
      :disabled="disabled"
      class="pointer-events-none"
    />
    <Label v-if="label" class="cursor-pointer font-medium text-[13px] text-slate-700 dark:text-slate-300 group-hover:text-primary transition-colors">
      {{ label }}
    </Label>
  </div>
</template>

<script setup>
import Switch from './Switch.vue'
import Label from './Label.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  label: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

const toggle = () => {
  if (props.disabled) return
  const newValue = !props.modelValue
  emit('update:modelValue', newValue)
  emit('change', newValue)
}
</script>


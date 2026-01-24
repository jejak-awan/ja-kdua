<template>
  <div class="flex items-center space-x-2 group cursor-pointer" @click="toggle">
    <Checkbox 
      :id="id" 
      :checked="modelValue" 
      :disabled="disabled"
      class="transition-all duration-200"
    />
    <Label 
      v-if="label" 
      :for="id"
      class="text-[13px] font-medium leading-none cursor-pointer group-hover:text-primary transition-colors"
    >
      {{ label }}
    </Label>
  </div>
</template>

<script setup>
import Checkbox from './Checkbox.vue'
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

const id = `checkbox-${Math.random().toString(36).substring(2, 9)}`

const emit = defineEmits(['update:modelValue'])

const toggle = () => {
    if (props.disabled) return
    emit('update:modelValue', !props.modelValue)
}
</script>

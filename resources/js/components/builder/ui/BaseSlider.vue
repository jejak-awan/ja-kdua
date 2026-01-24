<template>
  <div class="flex items-center w-full py-2">
    <input
      type="range"
      class="w-full h-1.5 bg-slate-200 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer accent-primary hover:accent-primary/80 transition-all"
      :value="modelValue"
      :min="min"
      :max="max"
      :step="step"
      @input="$emit('update:modelValue', Number($event.target.value))"
    />
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: Number,
    default: 0
  },
  min: {
    type: Number,
    default: 0
  },
  max: {
    type: Number,
    default: 100
  },
  step: {
    type: Number,
    default: 1
  }
})

defineEmits(['update:modelValue'])
</script>

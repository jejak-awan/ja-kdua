<template>
  <div class="base-slider">
    <input
      type="range"
      class="base-slider-native"
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

<style scoped>
.base-slider {
  display: flex;
  align-items: center;
  width: 100%;
}

.base-slider-native {
  -webkit-appearance: none;
  appearance: none;
  width: 100%;
  height: 4px;
  background: var(--builder-border);
  border-radius: 2px;
  outline: none;
  transition: opacity 0.2s;
}

.base-slider-native::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: var(--builder-accent);
  cursor: pointer;
  border: 2px solid var(--builder-bg-primary);
  box-shadow: var(--shadow-sm);
  transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.base-slider-native::-webkit-slider-thumb:hover {
  transform: scale(1.2);
}

.base-slider-native::-moz-range-thumb {
  width: 14px;
  height: 14px;
  border: 2px solid var(--builder-bg-primary);
  border-radius: 50%;
  background: var(--builder-accent);
  cursor: pointer;
  box-shadow: var(--shadow-sm);
}
</style>

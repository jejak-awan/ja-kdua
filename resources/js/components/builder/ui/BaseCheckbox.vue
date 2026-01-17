<template>
  <label class="base-checkbox" :class="{ 'is-disabled': disabled }">
    <input
      type="checkbox"
      class="base-checkbox-native"
      :checked="modelValue"
      :disabled="disabled"
      @change="$emit('update:modelValue', $event.target.checked)"
    />
    <div class="base-checkbox-box">
      <Check v-if="modelValue" :size="12" stroke-width="3" />
    </div>
    <span v-if="label" class="base-checkbox-label">{{ label }}</span>
  </label>
</template>

<script setup>
import { Check } from 'lucide-vue-next'

defineProps({
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

defineEmits(['update:modelValue'])
</script>

<style scoped>
.base-checkbox {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  user-select: none;
}

.base-checkbox.is-disabled {
  cursor: not-allowed;
  opacity: 0.5;
}

.base-checkbox-native {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

.base-checkbox-box {
  width: 16px;
  height: 16px;
  background: var(--builder-bg-tertiary);
  border: 1px solid var(--builder-border);
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  color: white;
}

.base-checkbox-native:checked ~ .base-checkbox-box {
  background: var(--builder-accent);
  border-color: var(--builder-accent);
}

.base-checkbox-native:focus ~ .base-checkbox-box {
  box-shadow: 0 0 0 2px rgba(var(--builder-accent-rgb), 0.2);
}

.base-checkbox-label {
  font-size: 13px;
  color: var(--builder-text-primary);
}
</style>

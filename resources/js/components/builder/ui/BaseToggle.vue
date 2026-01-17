<template>
  <button
    class="base-toggle"
    :class="{ 
      'base-toggle--active': modelValue, 
      'base-toggle--disabled': disabled,
      'base-toggle--placeholder-active': !modelValue && placeholderValue === true 
    }"
    @click="toggle"
    :disabled="disabled"
    type="button"
  >
    <span class="toggle-track">
      <span class="toggle-thumb" />
    </span>
    <span v-if="label" class="toggle-label">{{ label }}</span>
  </button>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  placeholderValue: {
    type: Boolean,
    default: null
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

<style scoped>
.base-toggle {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  user-select: none;
  outline: none;
}

.toggle-track {
  position: relative;
  width: 36px;
  height: 20px;
  background-color: var(--builder-toggle-track);
  border: 1px solid var(--builder-border);
  border-radius: 20px;
  transition: var(--transition-fast);
}

.toggle-thumb {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 14px;
  height: 14px;
  background-color: white;
  border-radius: 50%;
  transition: transform var(--transition-fast);
  box-shadow: var(--shadow-sm);
}

.base-toggle--active .toggle-track {
  background-color: var(--builder-accent);
  border-color: var(--builder-accent);
}

.base-toggle--active .toggle-thumb {
  transform: translateX(16px);
}

.base-toggle--placeholder-active .toggle-track {
  background-color: var(--builder-bg-secondary);
  border-color: var(--builder-border);
  opacity: 0.6;
}

.base-toggle--placeholder-active .toggle-thumb {
  transform: translateX(16px);
  background-color: var(--builder-text-muted);
  opacity: 0.5;
}

.base-toggle--disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.toggle-label {
  font-size: 13px;
  color: var(--builder-text-primary);
}
</style>

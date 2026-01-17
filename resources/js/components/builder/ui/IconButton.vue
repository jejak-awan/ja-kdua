<template>
  <button
    class="icon-button"
    :class="[
      `icon-button--${size}`,
      `icon-button--${variant}`,
      { 'is-active': active, 'is-disabled': disabled }
    ]"
    :title="title"
    :disabled="disabled"
    @click="$emit('click', $event)"
  >
    <component :is="icon" :size="iconSize" />
    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  icon: {
    type: [Object, Function, String],
    required: true
  },
  variant: {
    type: String,
    default: 'secondary', // ghost, secondary, primary
    validator: (value) => ['ghost', 'secondary', 'primary'].includes(value)
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  active: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

defineEmits(['click'])

const iconSize = computed(() => {
  switch (props.size) {
    case 'sm': return 14
    case 'lg': return 20
    default: return 16
  }
})
</script>

<style scoped>
.icon-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid transparent;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
  padding: 0;
  color: var(--builder-text-primary);
}

/* Variants */
.icon-button--secondary {
  background-color: var(--builder-bg-primary);
  border-color: var(--builder-border);
}

.icon-button--secondary:hover:not(:disabled) {
  background-color: var(--builder-bg-tertiary);
  border-color: var(--builder-accent);
}

.icon-button--ghost {
  background-color: transparent;
  color: var(--builder-text-secondary);
}

.icon-button--ghost:hover:not(:disabled) {
  background-color: var(--builder-bg-tertiary);
  color: var(--builder-text-primary);
}

.icon-button--primary {
  background-color: var(--builder-accent);
  color: white;
}

.icon-button--primary:hover:not(:disabled) {
  background-color: var(--builder-accent-hover);
}

.icon-button.is-active {
  background-color: var(--builder-bg-primary);
  color: var(--builder-accent);
  border-color: var(--builder-accent);
}

.icon-button.is-disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Sizes */
.icon-button--sm {
  width: 24px;
  height: 24px;
}

.icon-button--md {
  width: 32px;
  height: 32px;
}

.icon-button--lg {
  width: 40px;
  height: 40px;
}
</style>

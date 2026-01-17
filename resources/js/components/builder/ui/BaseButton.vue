<template>
  <button
    class="base-button"
    :class="[
      `base-button--${variant}`,
      `base-button--${size}`,
      { 'base-button--disabled': disabled || loading },
      { 'base-button--active': active }
    ]"
    :disabled="disabled || loading"
    v-bind="$attrs"
  >
    <template v-if="loading">
      <span class="button-loader"></span>
    </template>
    <slot v-else />
  </button>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: 'secondary', // primary, secondary, danger, ghost
    validator: (value) => ['primary', 'secondary', 'danger', 'ghost'].includes(value)
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  active: {
    type: Boolean,
    default: false
  }
})
</script>

<style scoped>
.base-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid transparent;
  white-space: nowrap;
  user-select: none;
}

/* Variants */
.base-button--primary {
  background-color: var(--builder-accent);
  color: white;
  border-color: var(--builder-accent);
}

.base-button--primary:hover:not(:disabled) {
  background-color: var(--builder-accent-hover);
  border-color: var(--builder-accent-hover);
}

.base-button--secondary {
  background-color: var(--builder-bg-primary);
  color: var(--builder-text-primary);
  border-color: var(--builder-border);
}

.base-button--secondary:hover:not(:disabled) {
  background-color: var(--builder-bg-tertiary);
  border-color: var(--builder-accent);
}

.base-button--danger {
  background-color: var(--builder-error);
  color: white;
  border-color: var(--builder-error);
}

.base-button--danger:hover:not(:disabled) {
  opacity: 0.9;
  filter: brightness(1.1);
}

.base-button--ghost {
  background: transparent;
  color: var(--builder-text-secondary);
  border-color: transparent;
}

.base-button--ghost:hover:not(:disabled),
.base-button--ghost.base-button--active {
  background-color: var(--builder-bg-primary);
  color: var(--builder-text-primary);
}

/* Sizes */
.base-button--sm {
  padding: 4px 8px;
  font-size: 11px;
}

.base-button--md {
  padding: 8px 16px;
  font-size: 13px;
}

.base-button--lg {
  padding: 10px 20px;
  font-size: 14px;
}

/* States */
.base-button--disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.button-loader {
  width: 14px;
  height: 14px;
  border: 2px solid currentColor;
  border-bottom-color: transparent;
  border-radius: 50%;
  display: inline-block;
  animation: rotation 1s linear infinite;
}

@keyframes rotation {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>

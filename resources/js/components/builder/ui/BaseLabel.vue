<template>
  <div class="base-label" :class="{ 'is-muted': muted, 'is-uppercase': uppercase }">
    <slot name="prefix"></slot>
    <span><slot></slot></span>
    <slot name="suffix"></slot>
  </div>
</template>

<script setup>
defineProps({
  muted: {
    type: Boolean,
    default: true
  },
  uppercase: {
    type: Boolean,
    default: false
  }
})
</script>

<style scoped>
.base-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.05em;
  color: var(--builder-text-primary);
  margin-bottom: 4px;
}

.base-label.is-muted {
  color: var(--builder-text-muted);
}

.base-label.is-uppercase {
  text-transform: uppercase;
}
</style>

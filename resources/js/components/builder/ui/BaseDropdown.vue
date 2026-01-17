<template>
  <div class="base-dropdown-wrapper" ref="wrapperRef">
    <div class="dropdown-trigger" @click="toggle">
      <slot name="trigger" :open="isOpen" :toggle="toggle" />
    </div>

    <BasePopover
      :is-open="isOpen"
      :trigger-rect="triggerRect"
      :align="align"
      :width="width"
      :backdrop="false"
      :show-close="false"
      :no-padding="noPadding"
      @close="close"
    >
      <div class="dropdown-menu" :class="{ 'no-padding': noPadding }">
        <slot :close="close" />
      </div>
    </BasePopover>
  </div>
</template>

<script setup>
import { ref, watch, onUnmounted } from 'vue'
import BasePopover from './BasePopover.vue'

const props = defineProps({
  align: {
    type: String,
    default: 'left'
  },
  width: {
    type: [String, Number],
    default: 200
  },
  noPadding: {
    type: Boolean,
    default: false
  }
})

const isOpen = ref(false)
const wrapperRef = ref(null)
const triggerRect = ref(null)

const toggle = (e) => {
  if (e) e.stopPropagation()
  if (isOpen.value) {
    close()
  } else {
    open()
  }
}

const open = () => {
  // Dispatch event to close other open dropdowns
  window.dispatchEvent(new CustomEvent('builder:dropdown-open', { detail: { id: wrapperRef.value } }))

  const el = wrapperRef.value.querySelector('.dropdown-trigger')
  if (el) {
    triggerRect.value = el.getBoundingClientRect()
  }
  isOpen.value = true
  window.addEventListener('scroll', updatePosition, true)
  window.addEventListener('resize', updatePosition)
}

const close = () => {
  isOpen.value = false
  window.removeEventListener('scroll', updatePosition, true)
  window.removeEventListener('resize', updatePosition)
}

// Global listener for other dropdowns opening
const handleOtherOpen = (e) => {
    if (isOpen.value && e.detail.id !== wrapperRef.value) {
        close()
    }
}

// Add/Remove dedicated listener
watch(isOpen, (val) => {
    if (val) {
        window.addEventListener('builder:dropdown-open', handleOtherOpen)
    } else {
        window.removeEventListener('builder:dropdown-open', handleOtherOpen)
    }
})

const updatePosition = () => {
  if (!isOpen.value) return
  const el = wrapperRef.value.querySelector('.dropdown-trigger')
  if (el) {
    triggerRect.value = el.getBoundingClientRect()
  }
}

onUnmounted(() => {
  window.removeEventListener('scroll', updatePosition, true)
  window.removeEventListener('resize', updatePosition)
})

defineExpose({ open, close, toggle })
</script>

<style scoped>
.base-dropdown-wrapper {
  display: inline-block;
}

.dropdown-trigger {
  cursor: pointer;
}

.dropdown-menu {
  display: flex;
  flex-direction: column;
  padding: 4px;
  min-width: 140px;
  max-width: 320px;
}

.dropdown-menu.no-padding {
  padding: 0;
}

/* Global menu item style intended to be used with slot */
:deep(.dropdown-item) {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  width: 100%;
  padding: 8px 12px;
  background: transparent;
  border: none;
  color: var(--builder-text-secondary);
  font-size: var(--font-size-md);
  cursor: pointer;
  border-radius: var(--border-radius-sm);
  transition: var(--transition-fast);
  text-align: left;
  white-space: nowrap;
}

:deep(.dropdown-item:hover) {
  background: var(--builder-bg-tertiary);
  color: var(--builder-text-primary);
}

:deep(.dropdown-item.active) {
  background: var(--builder-accent);
  color: white;
}

:deep(.dropdown-divider) {
  height: 1px;
  background: var(--builder-border);
  margin: 4px 0;
}
</style>

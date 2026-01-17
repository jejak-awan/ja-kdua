<template>
  <div class="base-collapsible" :class="{ 'is-open': isOpen }">
    <div class="collapsible-header" @click="toggle">
      <div class="header-content" :class="[`icon-${iconPosition}`]">
        <ChevronRight :size="14" class="chevron-icon" />
        <slot name="title">
          <span class="default-title">{{ title }}</span>
        </slot>
      </div>
      <div class="header-right">
        <slot name="actions"></slot>
      </div>
    </div>
    
    <transition name="collapse">
      <div v-if="isOpen" class="collapsible-content">
        <slot></slot>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { ChevronRight } from 'lucide-vue-next'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: undefined
  },
  title: {
    type: String,
    default: ''
  },
  defaultOpen: {
    type: Boolean,
    default: false
  },
  iconPosition: {
    type: String,
    default: 'left',
    validator: (val) => ['left', 'right'].includes(val)
  }
})

const emit = defineEmits(['update:modelValue'])

const internalOpen = ref(props.defaultOpen)
const isOpen = ref(props.modelValue !== undefined ? props.modelValue : internalOpen.value)

watch(() => props.modelValue, (newVal) => {
  if (newVal !== undefined) {
    isOpen.value = newVal
  }
})

const toggle = () => {
  if (props.modelValue !== undefined) {
    emit('update:modelValue', !props.modelValue)
  } else {
    internalOpen.value = !internalOpen.value
    isOpen.value = internalOpen.value
  }
}
</script>

<style scoped>
.base-collapsible {
  display: flex;
  flex-direction: column;
}

.collapsible-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  background: transparent;
  cursor: pointer;
  user-select: none;
  border-radius: var(--border-radius-sm);
  transition: background 0.2s;
}

.collapsible-header:hover {
  background: var(--builder-bg-tertiary);
}

.header-content {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
}

.header-content.icon-right {
  flex-direction: row-reverse;
  justify-content: space-between;
}

.chevron-icon {
  color: var(--builder-text-muted);
  transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  flex-shrink: 0;
}

.is-open .chevron-icon {
  transform: rotate(90deg);
}

.default-title {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.05em;
  color: var(--builder-text-secondary);
}

.collapsible-content {
  padding: 12px;
}

/* Transition */
.collapse-enter-active,
.collapse-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.collapse-enter-from,
.collapse-leave-to {
  max-height: 0;
  opacity: 0;
  padding-top: 0;
  padding-bottom: 0;
}
</style>

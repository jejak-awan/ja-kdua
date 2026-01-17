<template>
  <Teleport to="body">
    <transition name="popover-fade">
      <div 
        v-if="isOpen" 
        ref="popoverRef"
        class="base-popover"
        :style="popoverStyle"
        @click.stop
      >
        <div v-if="$slots.header || title" class="popover-header">
          <slot name="header">
            <h3 class="popover-title">{{ title }}</h3>
          </slot>
          <button v-if="showClose" class="popover-close" @click="close">
            <X :size="16" />
          </button>
        </div>
        
        <div class="popover-body" :class="{ 'no-padding': noPadding }">
          <slot />
        </div>
        
        <div v-if="$slots.footer" class="popover-footer">
          <slot name="footer" />
        </div>
      </div>
    </transition>
    
    <!-- Click outside backdrop (optional) -->
    <div v-if="isOpen && backdrop" class="popover-backdrop" @click="close"></div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onUnmounted, nextTick } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  triggerRect: {
    type: Object,
    default: null
  },
  title: {
    type: String,
    default: ''
  },
  showClose: {
    type: Boolean,
    default: true
  },
  backdrop: {
    type: Boolean,
    default: true
  },
  width: {
    type: [String, Number],
    default: 320
  },
  offset: {
    type: Number,
    default: 8
  },
  noPadding: {
    type: Boolean,
    default: false
  },
  align: {
    type: String,
    default: 'right', // left, right, center
    validator: (value) => ['left', 'right', 'center'].includes(value)
  }
})

const emit = defineEmits(['close'])

const popoverRef = ref(null)

const popoverStyle = computed(() => {
  if (!props.triggerRect) {
    return {
      position: 'fixed',
      top: '50%',
      left: '50%',
      transform: 'translate(-50%, -50%)',
        zIndex: 100100,
      width: typeof props.width === 'number' ? `${props.width}px` : props.width
    }
  }

  const rect = props.triggerRect
  let popWidth = 0
  
  if (props.width === '100%') {
    popWidth = rect.width
  } else if (props.width === 'auto') {
    popWidth = 200 // Default estimate for positioning before real width is known
  } else {
    popWidth = typeof props.width === 'number' ? props.width : parseInt(props.width)
  }
  
  let left = rect.left
  if (props.align === 'right') {
    left = rect.right - popWidth
  } else if (props.align === 'center') {
    left = rect.left + (rect.width / 2) - (popWidth / 2)
  }

  // Boundary checks
  if (left < 10) left = 10
  if (left + popWidth > window.innerWidth - 10) {
    left = window.innerWidth - popWidth - 10
  }


  // Vertical positioning with adaptive flip
  const viewportHeight = window.innerHeight
  const spaceBelow = viewportHeight - rect.bottom
  const spaceAbove = rect.top
  
  // Estimate height (can be refined with refs but 300px is a safe check for flip decision if unknown)
  // If we don't know the height yet, we prefer down unless space is very tight (< 200px)
  let verticalPos = {}
  let maxHeight = '90vh'
  
  // Check if we should flip
  if (spaceBelow < 300 && spaceAbove > spaceBelow) {
    // Flip UP
    verticalPos = {
        bottom: `${viewportHeight - rect.top + props.offset}px`,
        top: 'auto',
        transformOrigin: 'bottom'
    }
    maxHeight = `${spaceAbove - 20}px`
  } else {
    // Default DOWN
    verticalPos = {
        top: `${rect.bottom + props.offset}px`, // No 'auto' for top to avoid issues, just overwrite
        bottom: 'auto',
        transformOrigin: 'top'
    }
    maxHeight = `${spaceBelow - 20}px`
  }

  const styles = {
    position: 'fixed',
    ...verticalPos,
    width: props.width === 'auto' ? 'max-content' : `${popWidth}px`,
    minWidth: props.width === 'auto' ? `${rect.width}px` : undefined,
      zIndex: 100100,
    maxWidth: '90vw',
    maxHeight: maxHeight
  }

  if (props.align === 'right') {
    styles.right = `${window.innerWidth - rect.right}px`
    styles.left = 'auto'
  } else if (props.align === 'center') {
    styles.left = `${rect.left + (rect.width / 2) - (popWidth / 2)}px`
  } else {
    // left
    styles.left = `${rect.left}px`
  }
  
  // Boundary check only for left/center alignment as right is anchored safely
  if (props.align !== 'right') {
    if (parseInt(styles.left) < 10) styles.left = '10px'
    // Also check right edge for left align
    if (parseInt(styles.left) + popWidth > window.innerWidth) {
        styles.left = 'auto'
        styles.right = '10px'
    }
  }

  return styles
})

const close = () => {
  emit('close')
}

// Handle click outside if backdrop is disabled
const handleExternalClick = (e) => {
  if (props.isOpen && !props.backdrop && popoverRef.value && !popoverRef.value.contains(e.target)) {
    close()
  }
}

watch(() => props.isOpen, (val) => {
  if (val && !props.backdrop) {
    nextTick(() => {
      window.addEventListener('click', handleExternalClick)
    })
  } else {
    window.removeEventListener('click', handleExternalClick)
  }
})

onUnmounted(() => {
  window.removeEventListener('click', handleExternalClick)
})
</script>

<style scoped>
.base-popover {
  background: var(--builder-bg-popover);
  border: 1px solid var(--builder-border);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-lg);
  overflow: hidden;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.popover-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid var(--builder-border);
}

.popover-title {
  margin: 0;
  font-size: var(--font-size-md);
  font-weight: 600;
  color: var(--builder-text-primary);
}

.popover-close {
  padding: 4px;
  background: none;
  border: none;
  color: var(--builder-text-muted, #9ca3af);
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.popover-close:hover {
  background: var(--builder-bg-tertiary);
  color: var(--builder-text-primary);
}

.popover-body {
  padding: 12px;
  overflow-y: auto;
  flex: 1;
}

.popover-body.no-padding {
  padding: 0;
}

.popover-footer {
  padding: 12px 16px;
  border-top: 1px solid var(--builder-border);
  background: transparent;
}

.popover-backdrop {
  position: fixed;
  inset: 0;
  z-index: 100099;
}

/* Animations */
.popover-fade-enter-active,
.popover-fade-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}

.popover-fade-enter-from,
.popover-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>

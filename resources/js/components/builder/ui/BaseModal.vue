<template>
  <Teleport to="body">
    <transition name="modal-fade">
      <div 
        v-if="isOpen" 
        class="base-modal-overlay" 
        :class="{ 
          'no-backdrop': noBackdrop,
          ['is-placed-' + placement]: placement !== 'center'
        }"
        @click.self="onBackdropClick"
      >
        <div 
          ref="modalContainer"
          class="base-modal-container"
          :class="{ 'is-draggable': draggable }"
          :style="modalStyle"
        >
          <header 
            class="base-modal-header"
            @mousedown="onMouseDown"
          >
            <slot name="header">
              <h3 class="base-modal-title">{{ title }}</h3>
            </slot>
            <button v-if="showClose" class="base-modal-close" @click="close">
              <X :size="20" />
            </button>
          </header>
          
          <div class="base-modal-body">
            <slot />
          </div>
          
          <footer v-if="$slots.footer" class="base-modal-footer">
            <slot name="footer" />
          </footer>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  width: {
    type: [String, Number],
    default: 500
  },
  showClose: {
    type: Boolean,
    default: true
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true
  },
  draggable: {
    type: Boolean,
    default: false
  },
  noBackdrop: {
    type: Boolean,
    default: false
  },
  placement: {
    type: String,
    default: 'center', // center, top-left, top-right, bottom-left, bottom-right, right-sidebar
    validator: (val) => ['center', 'top-left', 'top-right', 'bottom-left', 'bottom-right', 'right-sidebar', 'right-start'].includes(val)
  }
})

const emit = defineEmits(['close'])

const modalContainer = ref(null)
const position = ref({ x: 0, y: 0 })
const isDragging = ref(false)
const dragOffset = ref({ x: 0, y: 0 })

const modalStyle = computed(() => {
  const widthStyle = typeof props.width === 'number' ? `${props.width}px` : props.width
  const styles = { width: widthStyle }
  
  // Transform only for dragging
  if (props.draggable) {
      styles.transform = `translate(${position.value.x}px, ${position.value.y}px)`
  }

  // Handle Placement Styles (absolute positioning override)
  if (props.placement !== 'center') {
      styles.position = 'absolute'
      styles.margin = '0' // Reset any auto margins
      
      const gap = '20px'
      
      switch (props.placement) {
          case 'top-left':
              styles.top = gap
              styles.left = gap
              break
          case 'top-right':
              styles.top = gap
              styles.right = gap
              break
          case 'bottom-left':
              styles.bottom = gap
              styles.left = gap
              break
          case 'bottom-right':
              styles.bottom = gap
              styles.right = gap
              break
          case 'right-sidebar':
          case 'right-start':
              styles.top = '60px'
              styles.right = 'calc(var(--panel-width, 320px) + 20px)'
              break
      }
  }

  return styles
})

// ... drag logic ...

const onMouseDown = (e) => {
  if (!props.draggable) return
  
  // Don't drag if clicking buttons or specific interactive elements
  if (e.target.closest('button') || e.target.closest('input')) return

  isDragging.value = true
  dragOffset.value = {
    x: e.clientX - position.value.x,
    y: e.clientY - position.value.y
  }

  window.addEventListener('mousemove', onMouseMove)
  window.addEventListener('mouseup', onMouseUp)
}

const onMouseMove = (e) => {
  if (!isDragging.value) return
  
  position.value = {
    x: e.clientX - dragOffset.value.x,
    y: e.clientY - dragOffset.value.y
  }
}

const onMouseUp = () => {
  isDragging.value = false
  window.removeEventListener('mousemove', onMouseMove)
  window.removeEventListener('mouseup', onMouseUp)
}

const close = () => {
  emit('close')
}

const onBackdropClick = () => {
  if (props.closeOnBackdrop) {
    close()
  }
}
</script>

<style scoped>
.base-modal-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100000;
  padding: 20px;
}

/* Placed modals should not enforce flex centering if they are absolutely positioned */
.base-modal-overlay[class*="is-placed-"] {
    display: block; /* Allows absolute children to position relative to viewport */
    padding: 0;
}

.base-modal-overlay.no-backdrop {
  background-color: transparent;
  pointer-events: none;
}

/* If user wants to click through, they need pointer-events: none on overlay and auto on container. 
   But then closeOnBackdrop won't work. 
   For now, just transparent background.*/

.base-modal-container {
  background-color: var(--builder-bg-popover, #1f2937);
  border: 1px solid var(--builder-border, #374151);
  border-radius: 12px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
  max-width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  pointer-events: auto;
}

.base-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--builder-border);
  user-select: none;
}

.base-modal-container.is-draggable .base-modal-header {
  cursor: move;
}

.base-modal-title {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: var(--builder-text-primary, #e5e7eb);
}

.base-modal-close {
  background: none;
  border: none;
  color: var(--builder-text-muted, #9ca3af);
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.base-modal-close:hover {
  background-color: var(--builder-bg-tertiary, #374151);
  color: var(--builder-text-primary, #e5e7eb);
}

.base-modal-body {
  padding: 24px;
  overflow-y: auto;
  flex: 1;
}

.base-modal-footer {
  padding: 16px 24px;
  border-top: 1px solid var(--builder-border, #374151);
  background-color: transparent;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

/* Animations */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-active .base-modal-container {
  animation: modal-slide-up 0.3s ease-out;
}

.modal-fade-leave-active .base-modal-container {
  animation: modal-slide-down 0.2s ease-in forwards;
}

@keyframes modal-slide-up {
  from { transform: translateY(20px) scale(0.98); opacity: 0; }
  to { transform: translateY(0) scale(1); opacity: 1; }
}

@keyframes modal-slide-down {
  from { transform: translateY(0) scale(1); opacity: 1; }
  to { transform: translateY(10px) scale(0.98); opacity: 0; }
}
</style>

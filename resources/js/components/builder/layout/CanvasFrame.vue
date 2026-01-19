<template>
  <div class="canvas-frame" :class="[`canvas-frame--${device}`]">
    <div 
      class="canvas-frame__viewport"
      :style="viewportStyle"
    >
      <!-- Grid Overlay -->
      <div v-if="showGridOverlay" class="canvas-grid-overlay"></div>
      <slot />
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { DEVICE_MODES } from '../core/constants'

const props = defineProps({
  device: {
    type: String,
    default: 'desktop',
    validator: (v) => ['desktop', 'tablet', 'mobile'].includes(v)
  },
  zoom: {
    type: Number,
    default: 100
  },
  width: {
    type: [Number, String],
    default: null
  }
})

const builder = inject('builder', null)
const showGridOverlay = computed(() => builder?.showGrid?.value ?? false)

const viewportStyle = computed(() => {
  // Mobile-first approach or explicit mapping to avoid import issues
  const widths = {
    desktop: null,
    tablet: 768,
    mobile: 375
  }

  const width = widths[props.device]
  
  const styles = {
    transform: `scale(${props.zoom / 100})`,
    transformOrigin: 'top center',
    transition: 'width 0.3s ease, max-width 0.3s ease'
  }
  
  if (props.width) {
    styles.width = `${props.width}px`
    styles.maxWidth = `${props.width}px`
  } else if (width) {
    styles.width = `${width}px`
    styles.maxWidth = `${width}px`
  } else {
    styles.width = '100%'
    styles.maxWidth = '100%'
  }
  
  return styles
})
</script>

<style scoped>
.canvas-frame {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  width: 100%;
  height: 100%;
  overflow-y: auto;
  padding: var(--spacing-lg) 0;
}

.canvas-frame__viewport {
  background: var(--builder-bg-canvas);
  border-radius: var(--border-radius-md);
  box-shadow: var(--shadow-lg);
  min-height: 400px;
  height: fit-content;
  transition: width var(--transition-normal);
  overflow: visible;
}

/* Device-specific frames */
.canvas-frame--tablet .canvas-frame__viewport {
  border-radius: var(--border-radius-lg);
  border: 8px solid #333;
}

.canvas-frame--mobile .canvas-frame__viewport {
  border-radius: 24px;
  border: 8px solid #333;
}

/* Grid Overlay */
.canvas-grid-overlay {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 999;
  background-image: 
    linear-gradient(to right, rgba(100, 100, 255, 0.1) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(100, 100, 255, 0.1) 1px, transparent 1px);
  background-size: 20px 20px;
}
</style>

<template>
  <div class="canvas-controls" :class="{ 'canvas-controls--mobile': isMobile }">
      <!-- Actions -->
      <div class="canvas-actions">
        <!-- Undo/Redo -->
        <IconButton 
          :icon="Undo2" 
          :disabled="!canUndo" 
          @click="builder?.undo()" 
          :title="$t('builder.toolbar.undo')"
          class="canvas-btn"
        />
        <IconButton 
          :icon="Redo2" 
          :disabled="!canRedo" 
          @click="builder?.redo()" 
          :title="$t('builder.toolbar.redo')"
          class="canvas-btn"
        />
      </div>
  </div>
</template>

<script setup>
import { computed, inject, ref, onMounted, onUnmounted } from 'vue'
import { Undo2, Redo2 } from 'lucide-vue-next'
import { IconButton } from '../ui'

// Props & Emits
const emit = defineEmits(['save'])

// Inject Builder
const builder = inject('builder')
const isMobile = ref(false)

// State
const canUndo = computed(() => builder?.canUndo || false)
const canRedo = computed(() => builder?.canRedo || false)

// Window resize listener for responsive class
const checkMobile = () => {
    isMobile.value = window.innerWidth <= 768
}

onMounted(() => {
    checkMobile()
    window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile)
})
</script>

<style scoped>
.canvas-controls {
  /* Position in the gutter area above the canvas */
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 8px 0;
  z-index: 10;
  /* No background - blends with canvas area background */
}

.canvas-actions {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px;
    background: var(--builder-bg-secondary, #242830);
    border: 1px solid var(--builder-border, #4a5568);
    border-radius: var(--border-radius-md, 8px);
}

.canvas-btn {
    width: 32px;
    height: 32px;
}

/* Dark mode overrides (default for builder) */
.ja-builder--dark .canvas-actions {
    background: #2d323b;
    border-color: #4a5568;
}

/* Light mode overrides */
.ja-builder--light .canvas-actions {
    background: #f8fafc;
    border-color: #e2e8f0;
}
.ja-builder--light .canvas-btn {
    color: #374151;
}

/* On mobile, span full width */
.canvas-controls--mobile {
    justify-content: center;
    padding: 6px 16px;
}

/* Hide divider (not used anymore) */
.canvas-divider {
    display: none;
}
</style>

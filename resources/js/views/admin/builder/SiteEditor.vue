<template>
  <div class="site-editor-view" :class="{ 'is-modal': !isFullscreen }">
    <div v-if="!isFullscreen" class="site-editor-overlay" @click="handleClose"></div>
    <div class="site-editor-container">
      <Builder 
        mode="site" 
        @close="handleClose" 
        @update:fullscreen="handleFullscreenUpdate"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, provide } from 'vue'
import { useRouter } from 'vue-router'
import Builder from '@/components/builder/Builder.vue'

const router = useRouter()
const isFullscreen = ref(false)

const handleClose = () => {
  router.push({ name: 'dashboard' })
}

const handleFullscreenUpdate = (val) => {
  isFullscreen.value = val
}

// Provide a way for the builder to communicate fullscreen state if needed, 
// though we usually rely on builder internals and teleport.
// For SiteEditor, we watch the builder's state if we can, or just let it teleport.

// Handle escape key to close modal if not fullscreen
const handleEsc = (e) => {
  if (e.key === 'Escape' && !isFullscreen.value) {
    handleClose()
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleEsc)
  // We can't easily listen to builder internal state here without props/emits
  // Let's ensure Builder emits fullscreen changes (we'll need to add that)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleEsc)
})
</script>

<style scoped>
.site-editor-view {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.site-editor-view.is-modal {
  padding: 40px;
}

.site-editor-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  z-index: -1;
}

.site-editor-container {
  width: 100%;
  height: 100%;
  background: var(--background);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
}

.is-modal .site-editor-container {
  max-width: 1600px;
  max-height: 900px;
  border: 1px solid var(--border);
}

.site-editor-container :deep(.ja-builder) {
  border: none !important;
  border-radius: 0 !important;
  box-shadow: none !important;
  height: 100% !important;
}

/* When builder is fullscreen, it teleports to body. 
   We should hide our container to avoid ghost elements? 
   No, Teleport moves the DOM nodes, so the container will be empty anyway.
*/
</style>

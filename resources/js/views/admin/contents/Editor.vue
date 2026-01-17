<template>
  <div class="fullscreen-editor h-screen w-screen overflow-hidden bg-background">
    <!-- Builder -->
    <Builder
      ref="builderComponent"
      :content-id="contentId"
      @close="handleClose"
    />
  </div>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router'
import Builder from '@/components/builder/Builder.vue'

const route = useRoute()
const router = useRouter()

const contentId = route.params.id

const handleClose = () => {
  router.push({ name: 'contents' })
}
</script>

<style scoped>
.fullscreen-editor {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1000;
}
</style>

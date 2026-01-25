<template>
  <div 
    class="map-pin-block" 
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Map Pin'"
    :style="pinStyles"
  >
     <MapPin class="marker-icon" />
     <span>{{ settings.title || 'Pin' }}</span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { MapPin } from 'lucide-vue-next'
import type { BlockInstance } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  index?: number
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  index: 0,
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

// Just for simulation, we offset pins visually so they don't overlap in the simulation
const pinStyles = computed(() => ({
    top: `${50 + props.index * 40}px`, 
    left: '20px'
}))
</script>

<style scoped>
.map-pin-block {
  position: absolute;
  background: white;
  padding: 6px 12px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  gap: 6px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  font-weight: 500;
  font-size: 13px;
  z-index: 10;
}
.marker-icon { width: 16px; height: 16px; color: #e53935; }
</style>

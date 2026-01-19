<template>
  <div 
    class="map-pin-block" 
    :style="pinStyles"
  >
     <MapPin class="marker-icon" />
     <span>{{ settings.title || 'Pin' }}</span>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { MapPin } from 'lucide-vue-next'

const props = defineProps({
  module: { type: Object, required: true },
  index: { type: Number, required: true }
})

const settings = computed(() => props.module.settings || {})

// Injected from MapBlock
const mapState = inject('mapState', {
    parentSettings: {}
})

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

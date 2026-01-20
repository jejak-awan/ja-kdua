<template>
  <div class="logo-item-block" :class="{ 'logo-item--grayscale': parentSettings.grayscale !== false, 'logo-item--hover-color': parentSettings.hoverColor !== false }">
    <a v-if="settings.url" :href="settings.url" target="_blank" class="logo-link">
      <img v-if="settings.image" :src="settings.image" :alt="settings.name" :style="logoStyles" />
      <div v-else class="logo-placeholder" :style="placeholderStyles"><Building /></div>
    </a>
    <template v-else>
      <img v-if="settings.image" :src="settings.image" :alt="settings.name" :style="logoStyles" />
      <div v-else class="logo-placeholder" :style="placeholderStyles"><Building /></div>
    </template>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Building } from 'lucide-vue-next'
import { getResponsiveValue } from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => props.module.settings || {})

// Injected from LogoGridBlock
const logoGridState = inject('logoGridState', {
    parentSettings: {}
})
const parentSettings = computed(() => logoGridState.parentSettings.value || {})

const logoStyles = computed(() => {
  const logoSize = getResponsiveValue(parentSettings.value, 'logoSize', device.value) || 120
  return { 
    maxWidth: `${logoSize}px`, 
    maxHeight: `${logoSize * 0.5}px`, 
    objectFit: 'contain' 
  }
})

const placeholderStyles = computed(() => {
  const logoSize = getResponsiveValue(parentSettings.value, 'logoSize', device.value) || 120
  return { 
    width: `${logoSize}px`, 
    height: `${logoSize * 0.4}px`, 
    display: 'flex', 
    alignItems: 'center', 
    justifyContent: 'center', 
    backgroundColor: '#f0f0f0', 
    borderRadius: '4px', 
    color: '#ccc' 
  }
})
</script>

<style scoped>
.logo-item-block { transition: all 0.3s; display: flex; align-items: center; justify-content: center; width: 100%; }
.logo-item--grayscale img { filter: grayscale(100%); opacity: 0.6; }
.logo-item--grayscale.logo-item--hover-color:hover img { filter: grayscale(0); opacity: 1; }
.logo-link { display: block; }
</style>

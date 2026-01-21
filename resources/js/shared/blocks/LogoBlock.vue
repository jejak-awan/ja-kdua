<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperBaseStyles, settings }">
      <div class="logo-block" :style="logoWrapperStyles">
        <component
          :is="settings.link ? 'a' : 'div'"
          :href="settings.link || undefined"
          :target="settings.link ? (settings.openInNewTab ? '_blank' : '_self') : undefined"
          class="logo-link"
        >
          <img v-if="settings.image" :src="settings.image" :alt="settings.altText || 'Logo'" :style="imageStyles" />
          <div v-else class="logo-placeholder" :style="placeholderStyles">
            <ImageIcon class="placeholder-icon" />
          </div>
        </component>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed } from 'vue'
import { Image as ImageIcon } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, toCSS } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})

const logoWrapperStyles = computed(() => {
  return {
    textAlign: getVal(settings.value, 'alignment', props.device) || 'left',
    width: '100%'
  }
})

const imageStyles = computed(() => {
  const align = getVal(settings.value, 'alignment', props.device) || 'left'
  const maxWidth = getVal(settings.value, 'maxWidth', props.device) || 200
  return { 
    maxWidth: toCSS(maxWidth), 
    height: settings.value.height || 'auto', 
    display: 'block',
    margin: align === 'center' ? '0 auto' : align === 'right' ? '0 0 0 auto' : '0'
  }
})

const placeholderStyles = computed(() => {
  const maxWidth = getVal(settings.value, 'maxWidth', props.device) || 200
  return { 
    width: toCSS(maxWidth), 
    height: '60px', 
    display: 'inline-flex', 
    alignItems: 'center', 
    justifyContent: 'center', 
    backgroundColor: 'rgba(0,0,0,0.05)', 
    borderRadius: '4px' 
  }
})
</script>

<style scoped>
.logo-block { width: 100%; }
.logo-link { display: inline-block; text-decoration: none; }
.placeholder-icon { width: 32px; height: 32px; color: #ccc; }
</style>

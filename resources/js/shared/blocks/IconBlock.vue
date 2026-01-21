<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperStyles, settings }">
      <div class="icon-block" :style="iconBlockStyles">
        <component
          :is="settings.linkUrl ? 'a' : 'div'"
          class="icon-wrapper"
          :href="settings.linkUrl || undefined"
          :target="settings.linkUrl ? (settings.linkTarget || '_self') : undefined"
          :style="iconWrapperStyles"
        >
          <LucideIcon 
            :name="iconName" 
            :size="iconSize" 
            :style="iconStyles"
          />
        </component>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal, toCSS } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})

const iconName = computed(() => getVal(settings.value, 'icon', props.device) || 'Star')
const iconSize = computed(() => {
    const size = getVal(settings.value, 'size', props.device) || 48
    return typeof size === 'number' ? size : parseInt(size) || 48
})

const iconBlockStyles = computed(() => {
  return {
    width: '100%',
    textAlign: getVal(settings.value, 'alignment', props.device) || 'center'
  }
})

const iconWrapperStyles = computed(() => {
  return {
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    textDecoration: 'none',
    transition: 'transform 0.2s ease, box-shadow 0.2s ease'
  }
})

const iconStyles = computed(() => {
  return {
    color: getVal(settings.value, 'color', props.device) || 'currentColor',
    display: 'block'
  }
})
</script>

<style scoped>
.icon-block { width: 100%; }
.icon-wrapper:hover { transform: scale(1.05); }
</style>

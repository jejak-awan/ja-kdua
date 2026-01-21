<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperBaseStyles, settings }">
      <div class="spacer-block" :class="{ 'spacer-block--edit': mode === 'edit' }" :style="spacerStyles">
        <div v-if="mode === 'edit'" class="spacer-label">
          <ArrowUpDown class="spacer-icon" />
          <span>{{ height }}px</span>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed } from 'vue'
import { ArrowUpDown } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})

const height = computed(() => {
    const h = getVal(settings.value, 'height', props.device) || 50
    return typeof h === 'number' ? h : parseInt(h) || 50
})

const spacerStyles = computed(() => {
  return {
    height: `${height.value}px`,
    minHeight: `${height.value}px`
  }
})
</script>

<style scoped>
.spacer-block {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: height 0.2s ease;
}

.spacer-block--edit {
  background: repeating-linear-gradient(
    45deg,
    transparent,
    transparent 10px,
    rgba(0, 0, 0, 0.03) 10px,
    rgba(0, 0, 0, 0.03) 20px
  );
  border: 1px dashed rgba(0, 0, 0, 0.1);
}

.spacer-label {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 4px;
  font-size: 11px;
  color: #666;
  pointer-events: none;
}

.spacer-icon {
  width: 12px;
  height: 12px;
  opacity: 0.5;
}
</style>

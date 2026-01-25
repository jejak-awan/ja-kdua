<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <div 
        class="spacer-block" 
        :id="getVal(settings, 'html_id', blockDevice)"
        :class="{ 'spacer-block--edit': mode === 'edit' }" 
        :style="spacerStyles(settings, blockDevice)"
        aria-hidden="true"
      >
        <div v-if="mode === 'edit'" class="spacer-label">
          <ArrowUpDown class="spacer-icon" />
          <span>{{ getHeight(settings, blockDevice) }}px</span>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { ArrowUpDown } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { 
    getVal,
    getLayoutStyles
} from '../utils/styleUtils'

const props = withDefaults(defineProps<{
  module: any;
  mode?: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile' | null;
}>(), {
  mode: 'view',
  device: 'desktop'
})

const getHeight = (settings: any, device: string) => {
    const h = getVal(settings, 'height', device) || 50
    return typeof h === 'number' ? h : parseInt(h) || 50
}

const spacerStyles = (settings: any, device: string) => {
  const h = getHeight(settings, device)
  return {
    height: `${h}px`,
    minHeight: `${h}px`,
    width: '100%',
    ...getLayoutStyles(settings, device)
  }
}
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

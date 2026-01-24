<template>
  <div class="inline-block" ref="wrapperRef">
    <BasePopover
      :is-open="isOpen"
      :trigger-rect="triggerRect"
      :align="align"
      :width="width"
      :no-padding="noPadding"
      :offset="4"
      @close="close"
    >
      <template #trigger>
         <div class="cursor-pointer" @click="toggle">
            <slot name="trigger" :open="isOpen" :toggle="toggle" />
         </div>
      </template>

      <div class="flex flex-col min-w-[140px]" :class="{ 'p-1': !noPadding }">
        <slot :close="close" />
      </div>
    </BasePopover>
  </div>
</template>

<script setup>
import { ref, watch, onUnmounted } from 'vue'
import BasePopover from './BasePopover.vue'

const props = defineProps({
  align: {
    type: String,
    default: 'left'
  },
  width: {
    type: [String, Number],
    default: 200
  },
  noPadding: {
    type: Boolean,
    default: false
  }
})

const isOpen = ref(false)
const wrapperRef = ref(null)
const triggerRect = ref(null)

const toggle = (e) => {
  if (e) e.stopPropagation()
  isOpen.value = !isOpen.value
}

const open = () => {
  window.dispatchEvent(new CustomEvent('builder:dropdown-open', { detail: { id: wrapperRef.value } }))
  isOpen.value = true
}

const close = () => {
  isOpen.value = false
}

const handleOtherOpen = (e) => {
    if (isOpen.value && e.detail.id !== wrapperRef.value) {
        close()
    }
}

watch(isOpen, (val) => {
    if (val) {
        window.addEventListener('builder:dropdown-open', handleOtherOpen)
    } else {
        window.removeEventListener('builder:dropdown-open', handleOtherOpen)
    }
})

defineExpose({ open, close, toggle })
</script>

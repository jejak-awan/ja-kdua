<template>
  <Dialog :open="isOpen" @update:open="onOpenUpdate">
    <DialogContent 
      :class="cn(
        'sm:max-w-md p-0 overflow-hidden',
        placement !== 'center' && 'fixed',
        placementClass,
        themeClasses,
        props.class
      )"
      :style="{ width: typeof width === 'number' ? `${width}px` : width }"
    >
      <DialogHeader v-if="$slots.header || title" class="px-6 py-4 border-b bg-background">
        <slot name="header">
          <DialogTitle>{{ title }}</DialogTitle>
        </slot>
      </DialogHeader>
      
      <div class="px-6 py-6 overflow-y-auto max-h-[80vh] bg-background">
        <slot />
      </div>
      
      <DialogFooter v-if="$slots.footer" class="px-6 py-4 border-t bg-muted/30">
        <slot name="footer" />
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { computed, inject } from 'vue'
import Dialog from './dialog.vue'
import DialogContent from './dialog-content.vue'
import DialogHeader from './dialog-header.vue'
import DialogTitle from './dialog-title.vue'
import DialogFooter from './dialog-footer.vue'
import { cn } from '../../../lib/utils'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  width: {
    type: [String, Number],
    default: 500
  },
  showClose: {
    type: Boolean,
    default: true
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true
  },
  noBackdrop: {
    type: Boolean,
    default: false
  },
  placement: {
    type: String,
    default: 'center', // center, top-left, top-right, bottom-left, bottom-right, right-sidebar
  },
  class: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['close', 'update:isOpen'])

const darkMode = inject('darkMode', computed(() => false))

const placementClass = computed(() => {
    switch (props.placement) {
        case 'top-left': return 'top-4 left-4 translate-x-0 translate-y-0'
        case 'top-right': return 'top-4 right-4 left-auto translate-x-0 translate-y-0'
        case 'bottom-left': return 'bottom-4 left-4 top-auto translate-x-0 translate-y-0'
        case 'bottom-right': return 'bottom-4 right-4 top-auto left-auto translate-x-0 translate-y-0'
        case 'right-sidebar': return 'top-[60px] right-[340px] left-auto translate-x-0 translate-y-0'
        default: return ''
    }
})

const themeClasses = computed(() => {
    return [
        'ja-builder',
        darkMode.value ? 'ja-builder--dark dark' : 'ja-builder--light'
    ].join(' ')
})

const onOpenUpdate = (val) => {
    if (!val) emit('close')
    emit('update:isOpen', val)
}
</script>

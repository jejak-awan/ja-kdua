<template>
  <Popover :open="isOpen" @update:open="onOpenUpdate">
    <PopoverAnchor v-if="triggerRect" :reference-rect="triggerRect" />
    <PopoverTrigger v-else as-child>
      <slot name="trigger" />
    </PopoverTrigger>

    <PopoverContent 
      :side="side" 
      :align="mappedAlign" 
      :side-offset="offset"
      :avoid-collisions="true"
      :collision-padding="20"
      sticky="always"
      :class="cn(
        'p-0 overflow-hidden border-slate-200 dark:border-slate-800 shadow-xl z-[999]',
        props.class
      )"
      :style="{ width: typeof width === 'number' ? `${width}px` : width }"
    >
      <div v-if="$slots.header || title" class="flex items-center justify-between px-4 py-2.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
        <slot name="header">
          <h3 class="text-[13px] font-bold text-slate-700 dark:text-slate-200 uppercase tracking-tight">{{ title }}</h3>
        </slot>
        <PopoverClose v-if="showClose" class="rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none data-[state=open]:bg-accent data-[state=open]:text-muted-foreground">
          <X class="h-4 w-4" />
          <span class="sr-only">Close</span>
        </PopoverClose>
      </div>
      
      <div class="overflow-y-auto" :class="{ 'p-4': !noPadding }">
        <slot />
      </div>
      
      <div v-if="$slots.footer" class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
        <slot name="footer" />
      </div>
    </PopoverContent>
  </Popover>
</template>

<script setup>
import { computed } from 'vue'
import { X } from 'lucide-vue-next'
import { 
    Popover, 
    PopoverTrigger, 
    PopoverContent, 
} from './'
import { PopoverAnchor, PopoverClose } from 'radix-vue'
import { cn } from '../../../lib/utils'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  triggerRect: {
    type: Object,
    default: null
  },
  title: {
    type: String,
    default: ''
  },
  showClose: {
    type: Boolean,
    default: true
  },
  backdrop: {
    type: Boolean,
    default: true
  },
  width: {
    type: [String, Number],
    default: 320
  },
  offset: {
    type: Number,
    default: 8
  },
  noPadding: {
    type: Boolean,
    default: false
  },
  align: {
    type: String,
    default: 'right', // left, right, center
  },
  side: {
    type: String,
    default: 'bottom'
  },
  class: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['close', 'update:isOpen'])

const onOpenUpdate = (val) => {
    if (!val) emit('close')
    emit('update:isOpen', val)
}

const mappedAlign = computed(() => {
    if (props.align === 'left') return 'start'
    if (props.align === 'right') return 'end'
    return 'center'
})
</script>

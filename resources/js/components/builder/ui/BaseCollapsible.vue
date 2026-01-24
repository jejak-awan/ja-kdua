<template>
  <Accordion 
    type="single" 
    collapsible 
    :default-value="defaultOpen ? 'item-1' : ''"
    :model-value="modelValue !== undefined ? (modelValue ? 'item-1' : '') : undefined"
    @update:model-value="handleUpdate"
    class="w-full border-none"
  >
    <AccordionItem value="item-1" class="border-none">
      <div class="flex items-center justify-between group">
        <AccordionTrigger 
            class="flex-1 py-2 px-3 text-[11px] font-bold tracking-wider text-slate-500 hover:text-slate-900 transition-all uppercase hover:no-underline"
            :class="{ 'flex-row-reverse justify-between': iconPosition === 'right' }"
        >
          <slot name="title">
            <span>{{ title }}</span>
          </slot>
        </AccordionTrigger>
        <div class="flex items-center gap-2 pr-3">
            <slot name="actions" />
        </div>
      </div>
      <AccordionContent class="px-3 pb-4 pt-1">
        <slot />
      </AccordionContent>
    </AccordionItem>
  </Accordion>
</template>

<script setup>
import Accordion from './accordion.vue'
import AccordionItem from './accordion-item.vue'
import AccordionTrigger from './accordion-trigger.vue'
import AccordionContent from './accordion-content.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: undefined
  },
  title: {
    type: String,
    default: ''
  },
  defaultOpen: {
    type: Boolean,
    default: false
  },
  iconPosition: {
    type: String,
    default: 'left',
  }
})

const emit = defineEmits(['update:modelValue'])

const handleUpdate = (val) => {
    emit('update:modelValue', val === 'item-1')
}
</script>

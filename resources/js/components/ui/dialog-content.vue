<template>
  <DialogPortal>
    <DialogOverlay
      class="fixed inset-0 z-50 bg-black/20 dark:bg-background/80 backdrop-blur-sm"
    />
    <RadixDialogContent
      :class="cn(
        'fixed left-[50%] top-[50%] z-50 grid w-full max-w-lg translate-x-[-50%] translate-y-[-50%] gap-4 border border-border/40 bg-popover p-6 rounded-xl shadow-2xl',
        props.class
      )"
      v-bind="$attrs"
    >
      <slot />
      
      <TooltipProvider>
        <Tooltip :delay-duration="300">
          <TooltipTrigger as-child>
            <DialogClose
              v-if="!hideClose"
              class="absolute right-4 top-4 rounded-xl p-2 opacity-70 ring-offset-background hover:opacity-100 hover:bg-accent hover:text-accent-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none data-[state=open]:bg-accent data-[state=open]:text-muted-foreground"
            >
              <X class="h-4 w-4" />
              <span class="sr-only">Close</span>
            </DialogClose>
          </TooltipTrigger>
          <TooltipContent side="bottom" :side-offset="8">
            {{ t('common.actions.close') }} (Esc)
          </TooltipContent>
        </Tooltip>
      </TooltipProvider>
    </RadixDialogContent>
  </DialogPortal>
</template>

<script setup>
import { DialogPortal, DialogOverlay, DialogContent as RadixDialogContent, DialogClose } from 'radix-vue';
import { X } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import { useI18n } from 'vue-i18n';
import Tooltip from './tooltip.vue';
import TooltipContent from './tooltip-content.vue';
import TooltipTrigger from './tooltip-trigger.vue';
import TooltipProvider from './tooltip-provider.vue';

const { t } = useI18n();

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
  hideClose: {
    type: Boolean,
    default: false,
  },
});
</script>

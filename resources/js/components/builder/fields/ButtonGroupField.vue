<template>
  <div class="button-group-field">
    <BaseSegmentedControl
      :model-value="value"
      :options="mappedOptions"
      :full-width="true"
      @update:model-value="(val) => $emit('update:value', val)"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import type { BlockInstance } from '../../../types/builder'
import { useI18n } from 'vue-i18n'
import * as LucideIcons from 'lucide-vue-next'
import { BaseSegmentedControl } from '../ui'

interface FieldOption {
  value: string | number | boolean;
  label: string;
  icon?: string;
}

const props = defineProps<{
  field: {
    name: string;
    options: FieldOption[];
  };
  value: string | number | boolean;
}>()

defineEmits<{
  (e: 'update:value', value: string | number | boolean): void;
}>()

const { t, te } = useI18n()
const module = inject<any>('module', {})

const getOptionLabel = (option: FieldOption) => {
  const type = module?.type || 'common'
  const name = props.field.name
  const val = option.value

  // 1. Module specific: builder.settings.{type}.{name}.options.{val}
  if (te(`builder.settings.${type}.${name}.options.${val}`)) {
    return t(`builder.settings.${type}.${name}.options.${val}`)
  }

  // 2. Common field: builder.settings.fields.${name}.options.{val}
  if (te(`builder.settings.fields.${name}.options.${val}`)) {
    return t(`builder.settings.fields.${name}.options.${val}`)
  }

  return option.label
}

const mappedOptions = computed(() => {
  return (props.field.options || []).map((opt: FieldOption) => ({
    ...opt,
    label: getOptionLabel(opt),
    icon: opt.icon ? (LucideIcons as any)[opt.icon] : null,
    iconOnly: !!opt.icon
  }))
})
</script>

<style scoped>
.button-group-field {
  width: 100%;
}
</style>

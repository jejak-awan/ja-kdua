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

<script setup>
import { computed, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import * as LucideIcons from 'lucide-vue-next'
import { BaseSegmentedControl } from '../ui'

const props = defineProps({
  field: {
    type: Object,
    required: true
  },
  value: {
    type: [String, Number, Boolean],
    default: ''
  }
})

defineEmits(['update:value'])

const { t, te } = useI18n()
const module = inject('module', {})

const getOptionLabel = (option) => {
  const type = module.type
  const name = props.field.name
  const val = option.value

  // 1. Module specific: builder.settings.{type}.{name}.options.{val}
  if (te(`builder.settings.${type}.${name}.options.${val}`)) {
    return t(`builder.settings.${type}.${name}.options.${val}`)
  }

  // 2. Common field: builder.settings.fields.{name}.options.{val}
  if (te(`builder.settings.fields.${name}.options.${val}`)) {
    return t(`builder.settings.fields.${name}.options.${val}`)
  }

  return option.label
}

const mappedOptions = computed(() => {
  return (props.field.options || []).map(opt => ({
    ...opt,
    label: getOptionLabel(opt),
    icon: opt.icon ? LucideIcons[opt.icon] : null,
    iconOnly: !!opt.icon
  }))
})
</script>

<style scoped>
.button-group-field {
  width: 100%;
}
</style>

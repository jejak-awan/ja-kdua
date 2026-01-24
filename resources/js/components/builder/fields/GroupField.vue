<template>
  <div class="group-field" :class="{ 'is-nested': isNested }">
    <BaseCollapsible 
      :title="translatedLabel" 
      :default-open="false"
      class="group-collapsible"
    >
      <div class="group-fields">
        <div 
          v-for="subField in field.fields" 
          :key="subField.name" 
          v-show="isFieldVisible(subField)"
          class="sub-field-row"
        >
          <FieldRenderer
            :field="subField"
            :value="getFieldValue(subField.name)"
            :module="module"
            :device="device"
            @update="(val) => updateField(subField.name, val)"
          />
        </div>
      </div>
    </BaseCollapsible>
  </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import { BaseCollapsible } from '../ui'
import FieldRenderer from './FieldRenderer.vue'
import type { BlockInstance, BuilderInstance } from '../../../types/builder'

const props = defineProps<{
  field: any;
  value: any;
  module: BlockInstance;
  device?: string;
  isNested?: boolean;
}>()

const builder = inject<BuilderInstance>('builder')
const { t, te } = useI18n()

const translatedLabel = computed(() => {
  const type = props.module.type
  const name = props.field.name

  if (te(`builder.settings.${type}.${name}.label`)) {
    return t(`builder.settings.${type}.${name}.label`)
  }

  if (te(`builder.settings.fields.${name}`)) {
    return t(`builder.settings.fields.${name}`)
  }

  return props.field.label
})

const getFieldValue = (name: string) => {
  return props.module.settings?.[name]
}

const updateField = (name: string, value: any) => {
  builder?.updateModuleSettings(props.module.id, { [name]: value })
}

const isFieldVisible = (f: any) => {
  if (!f.show_if) return true
  
  const dependencyName = f.show_if.field
  const dependencyValue = getFieldValue(dependencyName)
  const expectedValue = f.show_if.value
  
  if (Array.isArray(expectedValue)) {
    return expectedValue.includes(dependencyValue)
  }
  
  return dependencyValue === expectedValue
}
</script>

<style scoped>
.group-field {
  margin-bottom: 8px;
  border: 1px solid var(--builder-border);
  border-radius: 6px;
  overflow: hidden;
  background: var(--builder-bg-primary);
}

.group-field.is-nested {
  border: none;
  background: transparent;
  margin-bottom: 0;
}

.group-collapsible :deep(.accordion-trigger) {
    padding: 8px 12px;
    background: var(--builder-bg-secondary);
    color: var(--builder-accent);
    text-transform: none;
    font-size: 12px;
}

.group-collapsible :deep(.accordion-trigger:hover) {
    background: var(--builder-bg-hover);
}

.group-fields {
  padding: 16px 12px 8px 12px;
}

.sub-field-row {
  margin-bottom: 12px;
}

.sub-field-row:last-child {
  margin-bottom: 0;
}
</style>

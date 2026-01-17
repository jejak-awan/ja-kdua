<template>
  <FieldWrapper 
    :label="translatedLabel"
    :description="translatedDescription"
    :responsive="field.responsive"
    :show-responsive="field.responsive"
    :show-dynamic-data="supportsDynamicData"
    :show-context-menu="supportsContextMenu"
    :has-custom-value="hasCustomValue"
    @reset="resetFullModule"
    @reset-field="resetSpecificField"
    @responsive="handleResponsiveClick"
    @select-dynamic-data="handleDynamicDataClick"
  >
    <component
      :is="FieldComponent"
      v-bind="field"
      :field="field"
      :value="resolvedValue"
      :placeholder-value="placeholderValue"
      :module="module"
      @update:value="handleValueUpdate"
    />

    <!-- Dynamic Data Popover -->
    <BasePopover
      :is-open="isDynamicPopoverOpen"
      :trigger-rect="dynamicPopoverRect"
      :title="$t('builder.fields.actions.dynamicData')"
      :width="280"
      :no-padding="true"
      @close="isDynamicPopoverOpen = false"
    >
      <DynamicDataPopover 
        :source="loopContextSource"
        @select="handleDynamicSelection" 
      />
    </BasePopover>
  </FieldWrapper>
</template>

<script setup>
import { computed, defineAsyncComponent, inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import FieldWrapper from './FieldWrapper.vue'
import BasePopover from '../ui/BasePopover.vue'
import DynamicDataPopover from '../ui/DynamicDataPopover.vue'

// Inject builder
const builder = inject('builder')

// Dynamic field component imports
const fieldComponents = {
  text: defineAsyncComponent(() => import('./TextField.vue')),
  textarea: defineAsyncComponent(() => import('./TextareaField.vue')),
  richtext: defineAsyncComponent(() => import('./RichtextField.vue')),
  number: defineAsyncComponent(() => import('./NumberField.vue')),
  range: defineAsyncComponent(() => import('./RangeField.vue')),
  select: defineAsyncComponent(() => import('./SelectField.vue')),
  toggle: defineAsyncComponent(() => import('./ToggleField.vue')),
  color: defineAsyncComponent(() => import('./ColorField.vue')),
  buttonGroup: defineAsyncComponent(() => import('./ButtonGroupField.vue')),
  spacing: defineAsyncComponent(() => import('./SpacingField.vue')),
  border: defineAsyncComponent(() => import('./BorderField.vue')),
  shadow: defineAsyncComponent(() => import('./ShadowField.vue')),
  upload: defineAsyncComponent(() => import('./UploadField.vue')),
  children_manager: defineAsyncComponent(() => import('./ChildrenManagerField.vue')),
  background: defineAsyncComponent(() => import('./BackgroundField.vue')),
  meta_query: defineAsyncComponent(() => import('./MetaQueryField.vue')),
  advanced_number: defineAsyncComponent(() => import('./AdvancedNumberField.vue'))
}

const props = defineProps({
  field: {
    type: Object,
    required: true
  },
  value: {
    type: [String, Number, Boolean, Array, Object],
    default: null
  },
  module: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update'])

const { t, te } = useI18n()

// Computed
const translatedLabel = computed(() => {
  const type = props.module.type
  const name = props.field.name

  // 1. Try module-specific: builder.settings.{type}.{name}.label
  if (te(`builder.settings.${type}.${name}.label`)) {
    return t(`builder.settings.${type}.${name}.label`)
  }

  // 2. Try common field: builder.settings.fields.{name}
  if (te(`builder.settings.fields.${name}`)) {
    return t(`builder.settings.fields.${name}`)
  }

  return props.field.label
})

const translatedDescription = computed(() => {
  const type = props.module.type
  const name = props.field.name

  // 1. Try module-specific: builder.settings.{type}.{name}.description
  if (te(`builder.settings.${type}.${name}.description`)) {
    return t(`builder.settings.${type}.${name}.description`)
  }

  // 2. Try loop info: builder.info.loop.{name}
  if (te(`builder.info.loop.${name}`)) {
    return t(`builder.info.loop.${name}`)
  }

  // 3. Try inline info: builder.info.{type}.{name}
  if (te(`builder.info.${type}.${name}`)) {
    return t(`builder.info.${type}.${name}`)
  }

  // 4. Try common info: builder.info.common.{name}
  if (te(`builder.info.common.${name}`)) {
    return t(`builder.info.common.${name}`)
  }

  return props.field.description
})

// Inject builder device
const currentDevice = computed(() => builder?.device || 'desktop')

// Calculate values based on responsive state
const resolvedValue = computed(() => {
  if (!props.field.responsive || currentDevice.value === 'desktop') {
    return props.value
  }
  
  const suffix = currentDevice.value === 'mobile' ? '_phone' : `_${currentDevice.value}`
  const deviceKey = props.field.name + suffix
  return props.module.settings?.[deviceKey]
})

const placeholderValue = computed(() => {
  if (!props.field.responsive || currentDevice.value === 'desktop') {
    return null
  }

  const settings = props.module.settings || {}
  const desktopValue = settings[props.field.name]
  const tabletValue = settings[props.field.name + '_tablet']
  
  if (currentDevice.value === 'tablet') {
    return desktopValue
  }
  
  if (currentDevice.value === 'mobile') {
    // Cascade: Phone inherits from Tablet, then Desktop
    return tabletValue || desktopValue
  }
  
  return desktopValue
})

// Dynamic Data State
const isDynamicPopoverOpen = ref(false)
const dynamicPopoverRect = ref(null)

const loopContextSource = computed(() => {
    // Traverse parents to see if we are in a loop
    const path = builder.modulePath
    const isInLoop = path.some(m => m.settings?.loop_enable === true)
    return isInLoop ? 'loop' : 'page'
})

const FieldComponent = computed(() => {
  return fieldComponents[props.field.type] || fieldComponents.text
})

const supportsDynamicData = computed(() => {
   // Disable dynamic data for specific field types
   const excludedTypes = ['background', 'select', 'toggle', 'buttonGroup', 'range', 'children_manager', 'shadow', 'border', 'spacing']
   return !excludedTypes.includes(props.field.type)
})

const supportsContextMenu = computed(() => {
   // Disable context menu for specific field types
   const excludedTypes = ['background', 'children_manager']
   return !excludedTypes.includes(props.field.type)
})

const hasCustomValue = computed(() => {
    if (props.value === null || props.value === undefined || props.value === '') return false
    
    const moduleDef = builder.getModuleDefinition(props.module.type)
    const defaultValue = moduleDef?.defaults?.[props.field.name] ?? props.field.default
    
    // Simple deep comparison for primitive types or simple objects
    return JSON.stringify(props.value) !== JSON.stringify(defaultValue)
})

const handleValueUpdate = (val) => {
  if (!props.field.responsive || currentDevice.value === 'desktop') {
    emit('update', val)
    return
  }
  
  const suffix = currentDevice.value === 'mobile' ? '_phone' : `_${currentDevice.value}`
  const deviceKey = props.field.name + suffix
  builder?.updateModuleSettings(props.module.id, { [deviceKey]: val })
}

const handleResponsiveClick = () => {
    builder?.openResponsiveModal({
        label: translatedLabel.value,
        baseKey: props.field.name,
        type: props.field.type,
        module: props.module,
        settings: props.module.settings || {}
    })
}

const resetFullModule = () => {
    // This could trigger a full module reset if needed, but for now we'll stick to field reset
    // as per user requirement "trigger reset field yang bekerja khusus hanya reset aksi dirinya sendiri"
    resetSpecificField()
}

const resetSpecificField = () => {
    const moduleDef = builder.getModuleDefinition(props.module.type)
    const defaultValue = moduleDef?.defaults?.[props.field.name] ?? props.field.default
    
    let valueToSet = defaultValue
    if (valueToSet === undefined) {
        // Fallback for types if no default is specified
        const fallbacks = {
            text: '',
            number: 0,
            toggle: false,
            select: ''
        }
        valueToSet = fallbacks[props.field.type] ?? null
    }

    handleValueUpdate(valueToSet)
}

const handleDynamicDataClick = (target) => {
    // target can be an HTMLElement, SVGElement (icon/path), or an event
    const el = target && typeof target.getBoundingClientRect === 'function' 
        ? target 
        : (target.target && typeof target.target.getBoundingClientRect === 'function' ? target.target : null)

    if (el) {
        dynamicPopoverRect.value = el.getBoundingClientRect()
        isDynamicPopoverOpen.value = true
    }
}

const handleDynamicSelection = (item) => {
    // Store as a dynamic tag
    handleValueUpdate(`@dynamic:${item.tag}`)
    isDynamicPopoverOpen.value = false
}
</script>

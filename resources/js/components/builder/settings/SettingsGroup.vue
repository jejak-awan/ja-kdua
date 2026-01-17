<template>
  <div class="settings-group">
    <!-- Header -->
    <button class="group-header" @click="$emit('toggle')">
      <ChevronRight 
        :size="14" 
        class="group-icon"
        :class="{ 'is-open': isOpen }"
      />
      <span class="group-label">{{ translatedLabel }}</span>
      
      <!-- Design Presets Button (Hover) -->
      <div class="group-presets-trigger" @click.stop>
        <BaseDropdown align="right" :width="240">
          <template #trigger="{ open }">
            <IconButton 
              :icon="Library" 
              size="sm"
              :active="open" 
              class="presets-btn"
              :title="$t('builder.presets.title')" 
            />
          </template>
          
          <template #default="{ close }">
            <div class="preset-header">
              <Check :size="14" class="preset-check" />
              <div class="preset-info">
                <div class="preset-name">{{ $t('builder.presets.defaultPreset') }}</div>
                <div class="preset-sub">{{ $t('builder.presets.basedOn', { name: translatedLabel + ' 1' }) }}</div>
              </div>
            </div>
            <BaseDivider :margin="4" />
            <button class="dropdown-item primary-action" @click="close">
              {{ $t('builder.presets.newFromCurrent') }}
            </button>
            <button class="dropdown-item accent-action" @click="close">
              {{ $t('builder.presets.addNew') }}
            </button>
          </template>
        </BaseDropdown>
      </div>
    </button>
    
    <!-- Content -->
    <div v-show="isOpen" class="group-content">
      <div 
        v-for="field in group.fields"
        :key="field.name"
        v-show="isFieldVisible(field)"
        class="field-row"
      >
        <FieldRenderer
          :field="field"
          :value="getFieldValue(field.name)"
          :module="module"
          @update="updateField(field.name, $event)"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { inject, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { ChevronRight, Library, Check } from 'lucide-vue-next'
import FieldRenderer from '../fields/FieldRenderer.vue'
import { IconButton, BaseDropdown, BaseDivider } from '../ui'

const props = defineProps({
  group: {
    type: Object,
    required: true
  },
  module: {
    type: Object,
    required: true
  },
  isOpen: {
    type: Boolean,
    default: false
  }
})

defineEmits(['toggle'])

const { t, te } = useI18n()

// Inject builder
const builder = inject('builder')

// Computed
const translatedLabel = computed(() => {
  if (te(`builder.settings.groups.${props.group.id}`)) {
    return t(`builder.settings.groups.${props.group.id}`)
  }
  return props.group.label
})

// State
// Removed internal isCollapsed state

const getFieldValue = (name) => {
  return props.module.settings?.[name]
}

const updateField = (name, value) => {
  builder?.updateModuleSettings(props.module.id, { [name]: value })

  // Auto-reset loop fields if loop is enabled/disabled? 
  // User specifically requested reset when disabled.
  if (name === 'loop_enable' && value === false && props.group.id === 'loop') {
    const fieldsToReset = {}
    props.group.fields.forEach(field => {
       if (field.name !== 'loop_enable' && field.default !== undefined) {
         fieldsToReset[field.name] = field.default
       }
    })
    if (Object.keys(fieldsToReset).length > 0) {
      builder?.updateModuleSettings(props.module.id, fieldsToReset)
    }
  }
}

const isFieldVisible = (field) => {
  if (!field.show_if) return true
  
  const dependencyName = field.show_if.field
  
  // Find the dependency field in the same group to check its visibility recursively
  const dependencyField = props.group.fields.find(f => f.name === dependencyName)
  if (dependencyField && !isFieldVisible(dependencyField)) {
    return false
  }

  const dependencyValue = getFieldValue(dependencyName)
  const expectedValue = field.show_if.value
  
  if (Array.isArray(expectedValue)) {
    return expectedValue.includes(dependencyValue)
  }
  
  return dependencyValue === expectedValue
}
</script>

<style scoped>
.settings-group {
  border: none;
  border-radius: 0;
  overflow: hidden;
}

.group-header {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  width: 100%;
  padding: 12px 20px;
  background: var(--builder-bg-primary);
  border: none;
  border-bottom: 1px solid var(--builder-border);
  color: var(--builder-text-primary);
  font-size: var(--font-size-sm);
  font-weight: 600;
  cursor: pointer;
  text-align: left;
}

.group-header:hover {
  background: var(--builder-bg-primary);
  opacity: 0.8;
}

.group-icon {
  color: var(--builder-text-muted);
  transition: transform 0.2s;
}

.group-icon.is-open {
  transform: rotate(90deg);
}

.group-presets-trigger {
  margin-left: auto;
  opacity: 0;
  transition: opacity 0.2s;
}

.group-header:hover .group-presets-trigger {
  opacity: 1;
}

.presets-btn {
  color: var(--builder-text-muted);
}

.presets-btn:hover {
  color: var(--builder-accent);
}

/* Preset Dropdown Styles (from RightPanel) */
.preset-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
}

.preset-check {
  color: var(--builder-accent);
}

.preset-info {
  display: flex;
  flex-direction: column;
}

.preset-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--builder-text-primary);
}

.preset-sub {
  font-size: 11px;
  color: var(--builder-text-muted);
}

.dropdown-item {
  display: block;
  width: 100%;
  padding: 10px 12px;
  text-align: left;
  background: none;
  border: none;
  font-size: 13px;
  font-weight: 530;
  color: var(--builder-text-secondary);
  cursor: pointer;
  transition: all 0.2s;
}

.dropdown-item:hover {
  background: var(--builder-bg-secondary);
  color: var(--builder-text-primary);
}

.primary-action {
  color: var(--builder-text-primary);
}

.accent-action {
  color: var(--builder-accent);
}

.group-content {
  padding: var(--spacing-lg) 20px;
}

.field-row {
  margin-bottom: var(--spacing-md);
}

.field-row:last-child {
  margin-bottom: 0;
}
</style>

<template>
  <div class="interaction-field">
    <div v-if="localValue.length > 0" class="interactions-list mb-3">
      <div v-for="(interaction, index) in localValue" :key="index" class="interaction-item">
        <div class="interaction-info">
          <div class="interaction-label">{{ getInteractionLabel(interaction.trigger) }}</div>
          <div class="interaction-summary text-xs text-muted">{{ interaction.action || '(no action)' }}</div>
        </div>
        <div class="interaction-actions">
          <IconButton icon="Settings2" size="sm" @click="editInteraction(index)" />
          <IconButton icon="Trash2" size="sm" variant="danger" @click="removeInteraction(index)" />
        </div>
      </div>
    </div>

    <div class="relative">
      <BaseButton 
        variant="outline" 
        class="w-full justify-start gap-2 h-9"
        @click="isDropdownOpen = !isDropdownOpen"
        ref="addButton"
      >
        <Plus :size="14" />
        {{ $t('builder.advanced.interactions.add', 'Add Interaction') }}
      </BaseButton>

      <div v-if="isDropdownOpen" class="interaction-dropdown custom-scrollbar" v-click-outside="() => isDropdownOpen = false">
        <button v-for="opt in triggers" :key="opt.id" class="dropdown-item" @click="selectTrigger(opt.id)">
          {{ opt.label }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Plus, Settings2, Trash2 } from 'lucide-vue-next'
import { BaseButton, IconButton } from '../ui'

const props = defineProps({
  field: Object,
  value: {
    type: Array,
    default: () => []
  },
  module: Object
})

const emit = defineEmits(['update:value'])

const localValue = ref([...(props.value || [])])
const isDropdownOpen = ref(false)

const triggers = [
  { id: 'click', label: 'Click' },
  { id: 'mouseenter', label: 'Mouse Enter' },
  { id: 'mouseleave', label: 'Mouse Exit' },
  { id: 'viewportenter', label: 'Viewport Enter' },
  { id: 'viewportexit', label: 'Viewport Exit' },
  { id: 'load', label: 'Load' }
]

const getInteractionLabel = (id) => {
  return triggers.find(t => t.id === id)?.label || id
}

const selectTrigger = (trigger) => {
  localValue.value.push({ trigger, action: '' })
  emit('update:value', localValue.value)
  isDropdownOpen.value = false
}

const removeInteraction = (index) => {
  localValue.value.splice(index, 1)
  emit('update:value', localValue.value)
}

const editInteraction = (index) => {
  const interaction = localValue.value[index]
  const action = prompt(`Enter action for ${getInteractionLabel(interaction.trigger)}:`, interaction.action)
  if (action !== null) {
    interaction.action = action
    emit('update:value', localValue.value)
  }
}

watch(() => props.value, (newVal) => {
  localValue.value = [...(newVal || [])]
}, { deep: true })

// Simple click outside directive
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value(event)
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}
</script>

<style scoped>
.interaction-field {
  width: 100%;
}

.interaction-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-sm);
  background: var(--builder-bg-secondary);
  border: 1px solid var(--builder-border);
  border-radius: var(--radius-md);
  margin-bottom: var(--spacing-xs);
}

.interaction-label {
  font-size: var(--font-size-sm);
  font-weight: 600;
  color: var(--builder-text-primary);
}

.interaction-actions {
  display: flex;
  gap: var(--spacing-xs);
}

.interaction-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  width: 100%;
  background: #1e293b;
  border: 1px solid var(--builder-border);
  border-radius: var(--radius-md);
  margin-top: 4px;
  z-index: 100;
  box-shadow: var(--shadow-lg);
  max-height: 250px;
  overflow-y: auto;
}

.dropdown-item {
  width: 100%;
  padding: var(--spacing-sm) var(--spacing-md);
  background: transparent;
  border: none;
  text-align: left;
  font-size: var(--font-size-sm);
  color: #f8fafc;
  cursor: pointer;
  transition: background 0.2s;
}

.dropdown-item:hover {
  background: #334155;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #475569;
  border-radius: 10px;
}
</style>

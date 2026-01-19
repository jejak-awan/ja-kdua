<template>
  <div class="attribute-field">
    <div v-if="localValue.length > 0" class="attributes-list mb-3">
      <div v-for="(attr, index) in localValue" :key="index" class="attribute-item">
        <div class="attribute-info">
          <div class="attribute-name">{{ attr.name }}</div>
          <div class="attribute-value text-xs text-muted">{{ attr.value || '(no value)' }}</div>
        </div>
        <div class="attribute-actions">
          <IconButton icon="Settings2" size="sm" @click="editAttribute(index)" />
          <IconButton icon="Trash2" size="sm" variant="danger" @click="removeAttribute(index)" />
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
        {{ $t('builder.advanced.attributes.add', 'Add Attribute') }}
      </BaseButton>

      <div v-if="isDropdownOpen" class="attribute-dropdown custom-scrollbar" v-click-outside="() => isDropdownOpen = false">
        <button v-for="opt in presetOptions" :key="opt" class="dropdown-item" @click="selectPreset(opt)">
          {{ opt }}
        </button>
        <div class="dropdown-divider"></div>
        <button class="dropdown-item custom-entry" @click="startCustomEntry">
          {{ $t('builder.advanced.attributes.custom', 'Enter Custom Attribute') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
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

const presetOptions = [
  'class', 'id', 'title', 'alt', 'rel', 'target', 'role', 'aria-label', 'data-'
]

const selectPreset = (name) => {
  addAttribute(name)
  isDropdownOpen.value = false
}

const startCustomEntry = () => {
  const name = prompt('Enter attribute name:')
  if (name) {
    addAttribute(name)
  }
  isDropdownOpen.value = false
}

const addAttribute = (name) => {
  localValue.value.push({ name, value: '' })
  emit('update:value', localValue.value)
}

const removeAttribute = (index) => {
  localValue.value.splice(index, 1)
  emit('update:value', localValue.value)
}

const editAttribute = (index) => {
  const attr = localValue.value[index]
  const val = prompt(`Enter value for ${attr.name}:`, attr.value)
  if (val !== null) {
    attr.value = val
    emit('update:value', localValue.value)
  }
}

watch(() => props.value, (newVal) => {
  localValue.value = [...(newVal || [])]
}, { deep: true })

// Simple click outside directive implementation helper
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
.attribute-field {
  width: 100%;
}

.attribute-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-sm);
  background: var(--builder-bg-secondary);
  border: 1px solid var(--builder-border);
  border-radius: var(--radius-md);
  margin-bottom: var(--spacing-xs);
}

.attribute-name {
  font-size: var(--font-size-sm);
  font-weight: 600;
  color: var(--builder-text-primary);
}

.attribute-actions {
  display: flex;
  gap: var(--spacing-xs);
}

.attribute-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  width: 100%;
  background: #1e293b; /* Dark background as per Image 2 */
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

.dropdown-divider {
  height: 1px;
  background: #334155;
  margin: 4px 0;
}

.custom-entry {
  font-weight: 600;
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

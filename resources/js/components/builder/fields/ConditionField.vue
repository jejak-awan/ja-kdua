<template>
  <div class="condition-field">
    <div v-if="localValue.length > 0" class="conditions-list mb-3">
      <div v-for="(condition, index) in localValue" :key="index" class="condition-item">
        <div class="condition-info">
          <div class="condition-label">{{ getConditionLabel(condition) }}</div>
          <div class="condition-summary text-xs text-muted">{{ getConditionSummary(condition) }}</div>
        </div>
        <div class="condition-actions">
          <IconButton icon="Settings2" size="sm" @click="editCondition(index)" />
          <IconButton icon="Trash2" size="sm" variant="danger" @click="removeCondition(index)" />
        </div>
      </div>
    </div>

    <BaseButton 
      variant="outline" 
      class="w-full justify-start gap-2 h-9"
      @click="togglePicker"
      ref="addButton"
    >
      <Plus :size="14" />
      {{ $t('builder.advanced.conditions.add', 'Add Condition') }}
    </BaseButton>

    <BasePopover
      v-if="isPickerOpen"
      :is-open="isPickerOpen"
      :trigger-rect="pickerRect"
      :title="$t('builder.advanced.conditions.pickerTitle', 'Select Condition')"
      :width="280"
      :no-padding="true"
      @close="isPickerOpen = false"
    >
      <div class="condition-picker">
        <div class="popover-search">
          <Search :size="14" />
          <input 
            v-model="searchQuery" 
            type="text" 
            :placeholder="$t('builder.advanced.conditions.search', 'Search conditions...')"
            autofocus
          >
        </div>
        <div class="popover-content custom-scrollbar">
          <div v-for="(group, groupName) in filteredGroups" :key="groupName" class="data-group">
            <h4 class="group-title">{{ group.label }}</h4>
            <div class="group-items">
              <button 
                v-for="item in group.items" 
                :key="item.id"
                class="data-item"
                @click="selectCondition(item)"
              >
                <span class="item-label">{{ item.label }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </BasePopover>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { Plus, Search, Settings2, Trash2 } from 'lucide-vue-next'
import { BaseButton, BasePopover, IconButton } from '../ui'

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

const isPickerOpen = ref(false)
const pickerRect = ref(null)
const addButton = ref(null)
const searchQuery = ref('')

const conditionGroups = {
  post_info: {
    label: 'Post Info',
    items: [
      { id: 'post_type', label: 'Post Type' },
      { id: 'post_category', label: 'Post Category' },
      { id: 'post_tag', label: 'Post Tag' },
      { id: 'author', label: 'Author' },
      { id: 'custom_field', label: 'Custom Field' }
    ]
  },
  location: {
    label: 'Location',
    items: [
      { id: 'tag_page', label: 'Tag Page' },
      { id: 'category_page', label: 'Category Page' },
      { id: 'date_archive', label: 'Date Archive' },
      { id: 'search_results', label: 'Search Results' }
    ]
  },
  user: {
    label: 'User',
    items: [
      { id: 'logged_in', label: 'Logged In Status' },
      { id: 'user_role', label: 'User Role' }
    ]
  },
  interaction: {
    label: 'Interaction',
    items: [
      { id: 'date_time', label: 'Date & Time' },
      { id: 'page_visit', label: 'Page Visit' },
      { id: 'post_visit', label: 'Post Visit' },
      { id: 'view_count', label: 'Number Of Views' },
      { id: 'url_param', label: 'URL Parameter' }
    ]
  },
  device: {
    label: 'Device',
    items: [
      { id: 'browser', label: 'Browser' },
      { id: 'os', label: 'Operating System' },
      { id: 'cookie', label: 'Cookie' }
    ]
  }
}

const filteredGroups = computed(() => {
  if (!searchQuery.value) return conditionGroups
  const query = searchQuery.value.toLowerCase()
  const result = {}
  
  Object.entries(conditionGroups).forEach(([key, group]) => {
    const items = group.items.filter(item => 
      item.label.toLowerCase().includes(query)
    )
    if (items.length > 0) {
      result[key] = { ...group, items }
    }
  })
  
  return result
})

const getConditionLabel = (condition) => {
  for (const group of Object.values(conditionGroups)) {
    const item = group.items.find(i => i.id === condition.type)
    if (item) return item.label
  }
  return condition.type
}

const getConditionSummary = (condition) => {
  return condition.summary || 'Click settings to configure'
}

const togglePicker = () => {
  if (addButton.value?.$el) {
    pickerRect.value = addButton.value.$el.getBoundingClientRect()
    isPickerOpen.value = true
  }
}

const selectCondition = (item) => {
  localValue.value.push({
    type: item.id,
    enabled: true,
    rules: []
  })
  isPickerOpen.value = false
  emit('update:value', localValue.value)
}

const removeCondition = (index) => {
  localValue.value.splice(index, 1)
  emit('update:value', localValue.value)
}

const editCondition = (index) => {
  // Logic for opening condition rule editor
}

watch(() => props.value, (newVal) => {
  localValue.value = [...(newVal || [])]
}, { deep: true })

</script>

<style scoped>
.condition-field {
  width: 100%;
}

.condition-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-sm);
  background: var(--builder-bg-secondary);
  border: 1px solid var(--builder-border);
  border-radius: var(--radius-md);
  margin-bottom: var(--spacing-xs);
}

.condition-label {
  font-size: var(--font-size-sm);
  font-weight: 600;
}

.condition-actions {
  display: flex;
  gap: var(--spacing-xs);
}

/* Picker Styles (Duplicate of DynamicDataPopover for consistency) */
.condition-picker {
  width: 280px;
  max-height: 400px;
  display: flex;
  flex-direction: column;
}

.popover-search {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-sm) var(--spacing-md);
  border-bottom: 1px solid var(--builder-border);
  color: var(--builder-text-muted);
}

.popover-search input {
  flex: 1;
  background: transparent;
  border: none;
  font-size: var(--font-size-sm);
  color: var(--builder-text-primary);
  outline: none;
}

.popover-content {
  flex: 1;
  overflow-y: auto;
  padding: var(--spacing-xs) 0;
}

.data-group {
  padding: var(--spacing-xs) 0;
}

.group-title {
  padding: var(--spacing-xs) var(--spacing-md);
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--builder-text-muted);
  letter-spacing: 0.5px;
}

.group-items {
  display: flex;
  flex-direction: column;
}

.data-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-sm) var(--spacing-md);
  background: transparent;
  border: none;
  text-align: left;
  cursor: pointer;
  transition: background 0.2s;
  color: var(--builder-text-primary);
  font-size: var(--font-size-sm);
}

.data-item:hover {
  background: var(--builder-bg-secondary);
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: var(--builder-border);
  border-radius: 10px;
}
</style>

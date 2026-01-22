<template>
  <div class="settings-panel">
    <template v-if="groups.length > 0">
      <SettingsGroup
        v-for="group in filteredGroups"
        :key="group.id"
        :group="group"
        :module="module"
        :is-open="activeGroupId === group.id"
        :device="device"
        @toggle="toggleGroup(group.id)"
      />
    </template>
    <div v-else class="settings-empty">
      <p>{{ $t('builder.rightPanel.noSettings') }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import ModuleRegistry from '../core/ModuleRegistry'
import SettingsGroup from './SettingsGroup.vue'

const props = defineProps({
  module: {
    type: Object,
    required: true
  },
  activeTab: {
    type: String,
    default: 'content'
  },
  searchQuery: {
    type: String,
    default: ''
  },
  device: {
    type: String, // 'desktop', 'tablet', 'mobile'
    default: 'desktop'
  }
})

// Get module definition
const moduleDefinition = computed(() => 
  ModuleRegistry.get(props.module.type)
)

// Get groups for current tab
const groups = computed(() => {
  const def = moduleDefinition.value
  if (!def?.settings?.[props.activeTab]) return []
  return def.settings[props.activeTab]
})

// Filter groups by search query
const filteredGroups = computed(() => {
  if (!props.searchQuery) return groups.value
  
  const query = props.searchQuery.toLowerCase()
  
  return groups.value.map(group => {
    const filteredFields = group.fields.filter(field => 
      field.label.toLowerCase().includes(query) ||
      field.name.toLowerCase().includes(query)
    )
    
    if (filteredFields.length === 0) return null
    
    return { ...group, fields: filteredFields }
  }).filter(Boolean)
})

// Single group open logic
const activeGroupId = ref(null)

// Initialize first group as open
watch(groups, (newGroups) => {
  if (newGroups.length > 0 && !activeGroupId.value) {
    activeGroupId.value = newGroups[0].id
  }
}, { immediate: true })

const toggleGroup = (id) => {
  if (activeGroupId.value === id) {
    activeGroupId.value = null
  } else {
    activeGroupId.value = id
  }
}
</script>

<style scoped>
.settings-panel {
  padding: var(--spacing-md);
}

.settings-empty {
  padding: var(--spacing-xl);
  text-align: center;
  color: var(--builder-text-muted);
}
</style>

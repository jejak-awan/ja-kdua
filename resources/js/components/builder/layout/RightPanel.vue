<template>
  <aside class="right-panel">
    <!-- Header -->
    <header class="panel-header">
      <!-- Breadcrumb -->
      <div class="panel-breadcrumb">
        <!-- Collapse Button (Before Breadcrumb) -->
        <IconButton 
          :icon="PanelRightClose" 
          class="panel-collapse-btn"
          @click="$emit('close')" 
          :title="$t('builder.rightPanel.collapse')" 
        />
        <template v-for="(item, index) in modulePath" :key="item.id">
          <button 
            class="breadcrumb-item"
            @click="selectModule(item.id)"
          >
            {{ getModuleTitle(item.type) }}
          </button>
          <span v-if="index < modulePath.length - 1" class="breadcrumb-separator">›</span>
        </template>
      </div>
      
      <!-- Title Row -->
      <div class="panel-title-row">
        <IconButton 
          v-if="modulePath.length > 1" 
          :icon="ArrowLeft" 
          size="md" 
          @click="goBack" 
          :title="$t('builder.rightPanel.back')"
        />
        <h3 class="panel-title">{{ moduleTitle }}</h3>
        
        <!-- New: Presets Dropdown (Moved to Header) -->
        <div class="presets-dropdown-wrapper" style="margin-left: auto;">
          <BaseDropdown align="right" :width="240">
            <template #trigger="{ open }">
              <IconButton 
                :icon="Library" 
                :active="open" 
                :title="$t('builder.presets.title')" 
              />
            </template>
            
            <template #default="{ close }">
              <div class="preset-header">
                <Check :size="14" class="preset-check" />
                <div class="preset-info">
                  <div class="preset-name">{{ $t('builder.presets.defaultPreset') }}</div>
                  <div class="preset-sub">{{ $t('builder.presets.basedOn', { name: 'Preset 1' }) }}</div>
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
      </div>
    </header>
    
    <!-- Tabs -->
    <div class="panel-tabs">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        class="panel-tab"
        :class="{ 'panel-tab--active': activeTab === tab.id }"
        @click="activeTab = tab.id"
      >
        {{ $t('builder.tabs.' + tab.id) }}
      </button>
      
      <div class="panel-tabs-actions">

        <!-- Responsive Breakpoint Dropdown -->

        <div class="responsive-dropdown-wrapper">
          <BaseDropdown align="right" :width="160">
            <template #trigger="{ open }">
              <IconButton 
                :icon="currentBreakpointIcon" 
                :active="open" 
                :title="$t('builder.rightPanel.responsiveMode')" 
              />
            </template>
            
            <template #default="{ close }">
              <button 
                v-for="bp in breakpoints" 
                :key="bp.id"
                class="dropdown-item"
                :class="{ 'active': currentBreakpoint === bp.id }"
                @click="selectBreakpoint(bp); close()"
              >
                <component :is="bp.icon" :size="16" />
                {{ $t('builder.breakpoints.' + bp.id) }}
              </button>
              <BaseDivider :margin="4" />
              <button class="dropdown-item" @click="openSettings($event); close()">
                <Settings :size="16" />
                {{ $t('builder.rightPanel.settings') }}
              </button>
            </template>
          </BaseDropdown>
        </div>
        
        </div>
    </div>
    
    <!-- Search -->
    <div class="panel-search">
      <BaseInput 
        v-model="searchQuery"
        :placeholder="$t('builder.rightPanel.searchPlaceholder')"
        @clear="searchQuery = ''"
      >
        <template #prefix>
          <Search :size="14" />
        </template>
        <template #suffix v-if="searchQuery">
          <IconButton :icon="X" size="sm" @click="searchQuery = ''" />
        </template>
      </BaseInput>
    </div>
    
    <!-- Content -->
    <div class="panel-content">
      <SettingsPanel 
        v-if="module"
        :module="module"
        :active-tab="activeTab"
        :search-query="searchQuery"
      />
    </div>
  </aside>
  
  <!-- Responsive Breakpoints Popover -->
  <ResponsiveBreakpointsModal 
    :is-open="showBreakpointsModal"
    :trigger-rect="breakpointsTriggerRect"
    @close="showBreakpointsModal = false"
    @save="handleBreakpointsSave"
  />
</template>

<script setup>
import { ref, computed, inject, onMounted, onUnmounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { X, ArrowLeft, Smartphone, ChevronDown, Copy, Search, Monitor, Tablet, MousePointer, Settings, SlidersHorizontal, Check, Library, PanelRightClose } from 'lucide-vue-next'
import { SETTINGS_TABS } from '../core/constants'
import ModuleRegistry from '../core/ModuleRegistry'
import SettingsPanel from '../settings/SettingsPanel.vue'
import ResponsiveBreakpointsModal from '../modals/ResponsiveBreakpointsModal.vue'
import { BaseButton, IconButton, BaseInput, BaseDropdown, BaseDivider } from '../ui'

const icons = { X, ArrowLeft, Smartphone, ChevronDown, Copy, Search, Monitor, Tablet, MousePointer, Settings, SlidersHorizontal, Check, Library }

// Breakpoints config
const breakpoints = [
  { id: 'desktop', label: 'Desktop', icon: Monitor },
  { id: 'tablet', label: 'Tablet', icon: Tablet },
  { id: 'mobile', label: 'Phone', icon: Smartphone },
  { id: 'hover', label: 'Hover', icon: MousePointer }
]

// Props
const props = defineProps({
  module: {
    type: Object,
    required: true
  }
})

// Emits
defineEmits(['close'])

// Inject
const builder = inject('builder')
const { t, te } = useI18n()

// State
const activeTab = ref('content')
const searchQuery = ref('')
const showResponsive = ref(false)
const triggerRef = ref(null)
const showBreakpointsModal = ref(false)
const breakpointsTriggerRect = ref(null)

// Computed
const modulePath = computed(() => builder?.modulePath || [])

const moduleTitle = computed(() => {
  if (!props.module) return ''
  
  const type = props.module.type
  if (te(`builder.modules.${type}`)) {
    return t(`builder.modules.${type}`)
  }

  const def = ModuleRegistry.get(type)
  return def?.title || type
})

const currentBreakpoint = computed(() => {
  return builder?.device || 'desktop'
})

const currentBreakpointIcon = computed(() => {
  const bp = breakpoints.find(b => b.id === currentBreakpoint.value)
  return bp?.icon || Monitor
})

const tabs = Object.values(SETTINGS_TABS)

// Methods
const getModuleTitle = (type) => {
  if (te(`builder.modules.${type}`)) {
    return t(`builder.modules.${type}`)
  }
  const def = ModuleRegistry.get(type)
  return def?.title || type
}

const selectModule = (id) => {
  builder?.selectModule(id)
}

const goBack = () => {
  if (modulePath.value.length > 1) {
    const parentModule = modulePath.value[modulePath.value.length - 2]
    selectModule(parentModule.id)
  }
}

const selectBreakpoint = (bp) => {
  // Update builder responsive mode (currentBreakpoint is computed from builder.device)
  if (builder?.setDeviceMode) {
    builder.setDeviceMode(bp.id)
  }
}

const openSettings = (event) => {
  // Capture trigger position for popover
  const trigger = event?.target?.closest('.dropdown-item') || event?.target
  if (trigger) {
    breakpointsTriggerRect.value = trigger.getBoundingClientRect()
  }
  showBreakpointsModal.value = true
}

const handleBreakpointsSave = (breakpointsData) => {
  // Save breakpoints to builder or store
  // Could emit to parent or save to state
}

const toggleDeviceView = () => {
  showResponsive.value = !showResponsive.value
}
</script>

<style scoped>
.right-panel {
  display: flex;
  flex-direction: column;
  width: var(--panel-width);
  background: var(--builder-bg-primary);
  border-left: 1px solid var(--builder-border-layout);
  border-bottom: 1px solid var(--builder-border-layout);
}

/* Header */
.panel-header {
  padding: 16px 20px;
  border-bottom: 1px solid var(--builder-border);
  background: var(--builder-bg-primary);
}

.panel-breadcrumb {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 6px;
  margin-bottom: 12px;
}

.breadcrumb-item {
  padding: 0;
  background: none;
  border: none;
  color: var(--builder-text-muted);
  font-size: 11px;
  cursor: pointer;
  font-weight: 500;
  transition: color 0.2s;
}

.breadcrumb-item:hover {
  color: var(--builder-text-primary);
}

.breadcrumb-separator {
  color: var(--builder-text-muted);
  font-size: 10px;
  opacity: 0.6;
}


.panel-collapse-btn {
  background: transparent;
  border: none;
  color: var(--builder-text-muted);
  width: 24px !important;
  height: 24px !important;
  flex-shrink: 0;
  margin-right: 6px;
  margin-left: -4px; /* Flush against left edge */
  padding: 0;
}

.panel-collapse-btn:hover {
  color: var(--builder-accent);
  background: transparent;
}

.panel-title-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.panel-back {
  padding: 6px;
  background: transparent;
  border: 1px solid transparent; /* Prevent jump */
  color: var(--builder-text-secondary);
  cursor: pointer;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.panel-back:hover {
  background: var(--builder-bg-tertiary);
  color: var(--builder-text-primary);
}

.panel-title {
  flex: 1;
  margin: 0;
  font-size: 15px;
  font-weight: 700;
  color: var(--builder-text-primary);
  letter-spacing: -0.01em;
}

.panel-responsive {
  padding: 6px;
  background: none;
  border: 1px solid transparent;
  color: var(--builder-text-muted);
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s;
}

.panel-responsive:hover,
.panel-responsive.active {
  background: var(--builder-bg-tertiary);
  color: var(--builder-accent);
  border-color: var(--builder-border);
}

/* Tabs */
.panel-tabs {
  display: flex;
  align-items: center;
  padding: 0 20px;
  border-bottom: 1px solid var(--builder-border);
  background: var(--builder-bg-primary);
  gap: 20px;
}

.panel-tab {
  padding: 14px 0;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  color: var(--builder-text-secondary);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
  top: 1px; /* Sit on line */
}

.panel-tab:hover {
  color: var(--builder-text-primary);
}

.panel-tab--active {
  color: var(--builder-accent);
  border-bottom-color: var(--builder-accent);
}

.panel-tabs-actions {
  display: flex;
  margin-left: auto;
  gap: 4px;
}

.tab-action {
  padding: 6px;
  background: none;
  border: none;
  color: var(--builder-text-muted);
  cursor: pointer;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.tab-action:hover {
  background: var(--builder-bg-tertiary);
  color: var(--builder-text-primary);
}

/* Search */
.panel-search {
  display: flex;
  align-items: center;
  margin: 16px 20px;
}

.panel-search:focus-within {
  border-color: var(--builder-accent);
  box-shadow: 0 0 0 2px rgba(var(--builder-accent-rgb), 0.1);
}

.search-icon {
  color: var(--builder-text-muted);
  opacity: 0.8;
}

.search-input {
  flex: 1;
  padding: 0;
  background: none;
  border: none;
  color: var(--builder-text-primary);
  font-size: 13px;
  outline: none;
}

.search-input::placeholder {
  color: var(--builder-text-muted);
  opacity: 0.7;
}

.search-clear {
  padding: 2px;
  background: var(--builder-bg-secondary); /* Contrast against tertiary */
  border: none;
  border-radius: 50%;
  color: var(--builder-text-muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.search-clear:hover {
  color: var(--builder-text-primary);
  background: var(--builder-bg-primary); 
}

/* Content */
.panel-content {
  flex: 1;
  overflow-y: auto;
  background: var(--builder-bg-primary);
}

/* Responsive Dropdown */
.responsive-dropdown-wrapper {
  position: relative;
}

.responsive-dropdown-trigger {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px;
  background: var(--builder-bg-secondary);
  border: 1px solid var(--builder-border);
  border-radius: 4px;
  color: var(--builder-text-primary);
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.responsive-dropdown-trigger:hover,
.responsive-dropdown-trigger.active {
  background: var(--builder-bg-tertiary);
  border-color: var(--builder-accent);
}

.dropdown-chevron {
  margin-left: 2px;
  opacity: 0.6;
}

.responsive-dropdown-menu {
  min-width: 140px;
  background: var(--builder-bg-secondary);
  border: 1px solid var(--builder-border);
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  padding: 4px;
  animation: fadeIn 0.15s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-4px); }
  to { opacity: 1; transform: translateY(0); }
}

.responsive-dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 8px 12px;
  background: none;
  border: none;
  color: var(--builder-text-secondary);
  font-size: 13px;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.15s;
}

.responsive-dropdown-item:hover {
  background: var(--builder-bg-tertiary);
  color: var(--builder-text-primary);
}

.responsive-dropdown-item.active {
  background: var(--builder-accent);
  color: white;
}

.responsive-dropdown-item.active:hover {
  background: var(--builder-accent);
}

.dropdown-divider {
  height: 1px;
  background: var(--builder-border);
  margin: 4px 0;
}

.device-icon-btn {
  border: 1px solid var(--builder-border);
}

.device-icon-btn:hover {
  border-color: var(--builder-accent);
  color: var(--builder-accent);
}

@media (max-width: 768px) {
  .right-panel {
    position: fixed;
    top: var(--toolbar-height);
    bottom: 0;
    right: 0;
    z-index: 1000; /* Above everything */
    width: 90% !important; /* Full width or close to it */
    max-width: 360px;
    box-shadow: var(--shadow-xl);
    border-left: 1px solid var(--builder-border);
  }
}
</style>







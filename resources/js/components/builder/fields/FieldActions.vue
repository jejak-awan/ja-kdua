<template>
  <div class="field-actions" :class="{ 'is-active': showMenu }">
    
    <!-- Reset Field Button -->
    <div v-if="showReset" class="action-icon" :title="$t('builder.fields.actions.reset')" @click="$emit('reset')">
       <RotateCcw :size="14" />
    </div>

    <!-- Info Icon (Question Mark) -->
    <div v-if="showInfo" class="action-icon" :class="{ 'is-active': showInfoPanel }" :title="$t('builder.fields.actions.info')" @click="toggleInfo">
        <HelpCircle :size="14" />
    </div>

    <!-- Copy/Duplicate Icon -->
    <div v-if="showDuplicate" class="action-icon" :title="$t('builder.items.duplicate')" @click="$emit('duplicate')">
        <Copy :size="14" />
    </div>

    <!-- Dynamic Data -->
    <div v-if="showDynamicData" class="action-icon" :title="$t('builder.fields.actions.dynamicData')" @click.stop="$emit('select-dynamic-data', $event.target)">
        <Database :size="14" />
    </div>

    <!-- Responsive Editor Button (Stacked Squares) -->
    <div v-if="showResponsive" class="action-icon" :title="$t('builder.fields.actions.responsive')" @click="$emit('responsive')">
       <Layers :size="14" />
    </div>

    <!-- Assign Preset Button (Sparkles) -->
    <div v-if="showPresets" class="action-icon" :title="$t('builder.fields.actions.assignPreset', 'Assign Preset')" @click="$emit('assign-preset')">
        <Sparkles :size="14" />
    </div>

    <!-- Legacy Device Toggle (Smartphone) -->
    <div v-if="responsive && !showResponsive" class="action-icon" :title="$t('builder.fields.actions.responsive')">
        <Smartphone :size="12" />
    </div>

    <!-- More Options -->
    <div v-if="showContextMenu" class="action-icon relative">
         <MoreVertical 
           ref="contextMenuIconRef"
           :size="14" 
           class="cursor-pointer" 
           :class="{ 'text-accent': showMenu }"
           @click="toggleMenu" 
         />
         
         <Teleport to="body">
            <div v-if="showMenu" class="field-menu context-menu" v-click-outside="closeMenu" :style="dropdownStyle">
                <div class="menu-item" @click="copyAttributes">{{ $t('builder.fields.actions.copyAttributes', { label: label }) }}</div>
                <div class="menu-item disabled" @click="extendAttributes">{{ $t('builder.fields.actions.extendAttributes', { label: label }) }}</div>
                <div class="menu-divider"></div>
                <div class="menu-item" @click="resetField">{{ $t('builder.fields.actions.resetAttributes', { label: label }) }}</div>
                <div class="menu-divider"></div>
                <div class="menu-item disabled" @click="findReplace">{{ $t('builder.fields.actions.findReplace') }}</div>
            </div>
         </Teleport>
    </div>
  </div>
  
</template>

<script setup>
import { ref, inject } from 'vue'
import { Smartphone, Database, MoreVertical, Layers, HelpCircle, RotateCcw, Copy, Sparkles } from 'lucide-vue-next'

const builder = inject('builder')

const props = defineProps({
  label: { type: String, required: true },
  responsive: { type: Boolean, default: false },
  showResponsive: { type: Boolean, default: false },
  showReset: { type: Boolean, default: false },
  showDuplicate: { type: Boolean, default: false },
  showDynamicData: { type: Boolean, default: true },
  showContextMenu: { type: Boolean, default: true },
  showInfo: { type: Boolean, default: false },
  showPresets: { type: Boolean, default: false },
  infoContent: { type: String, default: '' }
})

const emit = defineEmits(['reset', 'responsive', 'select-dynamic-data', 'duplicate', 'toggle-info', 'reset-field', 'assign-preset'])

const showMenu = ref(false)
const showInfoPanel = ref(false)
const contextMenuIconRef = ref(null)
const dropdownStyle = ref({})

const toggleInfo = (e) => {
    e.stopPropagation()
    showInfoPanel.value = !showInfoPanel.value
    emit('toggle-info', showInfoPanel.value)
}

const updateDropdownPosition = (anchorEl) => {
    if (!anchorEl) return
    const rect = anchorEl.getBoundingClientRect()
    dropdownStyle.value = {
        position: 'fixed',
        top: `${rect.bottom + 4}px`,
        right: `${window.innerWidth - rect.right}px`,
        zIndex: 99999
    }
}

const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = function(event) {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value(event, el);
      }
    };
    document.body.addEventListener('click', el.clickOutsideEvent);
  },
  unmounted(el) {
    document.body.removeEventListener('click', el.clickOutsideEvent);
  }
}


const closeMenu = () => {
    showMenu.value = false
}

const toggleMenu = (e) => {
    e.stopPropagation()
    showMenu.value = !showMenu.value

    if (showMenu.value) {
        updateDropdownPosition(contextMenuIconRef.value?.$el || contextMenuIconRef.value)
    }
}

const openGlobalVariables = () => {
    if (builder && builder.activePanel) {
        builder.activePanel = 'global_variables'
    }
}

const copyAttributes = () => {
    // TODO: Implement copy attributes
    closeMenu()
}

const extendAttributes = () => {
    // TODO: Implement extend attributes
    closeMenu()
}

const resetField = () => {
    emit('reset-field')
    closeMenu()
}

const findReplace = () => {
    // TODO: Implement find & replace
    closeMenu()
}
</script>

<style scoped>
.field-actions {
  display: flex;
  align-items: center;
  gap: 2px;
  flex-shrink: 0;
}

.field-actions:hover,
.field-actions.is-active {
    opacity: 1;
}

.action-icon {
    color: var(--builder-text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2px;
    transition: color 0.2s;
}

.action-icon:hover,
.action-icon.is-active {
    color: var(--builder-accent);
}

.action-icon.is-active svg {
    color: var(--builder-accent);
}

.action-icon .text-accent {
    color: var(--builder-accent) !important;
}

/* Dropdown Menu */
.field-menu {
    position: absolute;
    top: 100%;
    right: 0;
    width: 240px;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    box-shadow: var(--shadow-lg);
    z-index: 60;
    padding: 4px 0;
}

.dynamic-data-menu {
    width: 260px;
}

.context-menu {
    width: 280px;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    box-shadow: var(--shadow-xl);
}

.dd-header {
    padding: 8px 12px;
    font-size: 11px;
    font-weight: 600;
    color: var(--builder-text-muted);
    text-transform: none;
}

.dd-action {
    padding: 0 12px 8px 12px;
}

.dd-btn-primary {
    display: block;
    width: 100%;
    background-color: var(--builder-accent);
    color: white;
    border: none;
    padding: 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    text-align: center;
    transition: background-color 0.15s;
}

.dd-btn-primary:hover {
    background-color: var(--builder-accent-hover);
}

.menu-item {
    padding: 8px 12px;
    font-size: 12px;
    color: var(--builder-text-primary);
    cursor: pointer;
    transition: background-color 0.15s;
}

.menu-item:hover {
    background-color: var(--builder-bg-secondary);
}

.menu-item.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.menu-divider {
    height: 1px;
    background-color: var(--builder-border);
    margin: 4px 0;
}

.field-info-panel {
    width: 100%;
    order: 10;
    margin-top: 8px;
    font-size: 13px;
    line-height: 1.6;
    color: var(--builder-text-secondary);
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.2s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}
</style>

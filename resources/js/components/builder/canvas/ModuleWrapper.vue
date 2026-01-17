<template>
  <div 
    class="module-wrapper"
    :class="wrapperClasses"
    @click.stop="selectModule"
    @mouseenter="hoverModule"
    @mouseleave="unhoverModule"
    @contextmenu.stop.prevent="handleContextMenu"
  >
    <!-- Inline Toolbar -->
    <div v-if="shouldShowToolbar" class="module-toolbar" :class="`module-toolbar--${moduleType}`" :style="{ zIndex: isSelected ? 150 : 100 }">
      <ModuleActions 
        :label="moduleTitle"
        :show-edit="true"
        :show-duplicate="true"
        :show-delete="true"
        :show-info="true"
        :show-more="true"
        :show-drag="true"
        :info-active="showInfo"
        @edit="openSettings"
        @duplicate="duplicateModule"
        @delete="deleteModule"
        @toggle-info="toggleInfo"
        @more-action="handleMoreAction"
      />
    </div>

    <!-- Inline Info Panel (for Canvas) -->
    <transition name="fade-slide">
      <div v-if="showInfo && !isGhost" class="module-info-panel-canvas">
          <p>{{ moduleInfoText }}</p>
      </div>
    </transition>
    
    <!-- Loop Indicator Badge -->
    <div v-if="isLoopEnabled" class="loop-indicator" title="Loop Enabled">
        <component :is="icons.Repeat" :size="10" />
        <span>LOOP</span>
    </div>

    <!-- Module Content with Children -->
    <div class="module-content" :class="{ 'is-looping': isLoopEnabled }">
      <template v-for="instance in loopInstances" :key="instance.id">
        <ModuleRenderer 
           :module="module"
           :class="{ 'loop-ghost': instance.isGhost }"
           :style="instance.isGhost ? { opacity: 0.6, pointerEvents: 'none' } : {}"
        >
          <!-- Pass children to the block component via slot -->
          <template v-if="hasChildren && !instance.isGhost">
            <draggable
              v-model="module.children"
              item-key="id"
              :group="childType"
              class="children-container"
              ghost-class="ghost-module"
              drag-class="drag-module"
            >
              <template #item="{ element: child, index: idx }">
                <ModuleWrapper
                  :module="child"
                  :index="idx"
                  :is-ghost="isGhost"
                />
              </template>
            </draggable>
          </template>
          
          <!-- Static rendering for ghosts to avoid double-binding draggables -->
          <template v-else-if="hasChildren && instance.isGhost">
            <div class="children-container loop-ghost-children">
              <ModuleWrapper
                v-for="(child, idx) in module.children"
                :key="child.id + '-' + instance.id"
                :module="child"
                :index="idx"
                :is-ghost="true"
              />
            </div>
          </template>
        </ModuleRenderer>
      </template>
    </div>
    
    <!-- Add Sibling Button - On Rows (to add another row below) -->
    <AddModuleButton 
       v-if="isRow && (isSelected || isHovered) && !isGhost"
       type="row"
       :floating="true"
       @click="addSiblingRow"
    />
    
    <!-- Add Sibling Button (Add Section) - Only for Sections when selected/hovered -->
    <AddModuleButton 
       v-if="isSection && (isSelected || isHovered) && !isGhost"
       type="section"
       :floating="true"
       @click="addSiblingSection"
    />
    
    <!-- Module Label (Grid/Wireframe) -->
    <div v-if="gridViewMode || wireframeMode" class="module-label">
      {{ moduleTitle }}
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Plus, Repeat } from 'lucide-vue-next'
import draggable from 'vuedraggable'
import ModuleRegistry from '../core/ModuleRegistry'
import ModuleRenderer from './ModuleRenderer.vue'
import AddModuleButton from './AddModuleButton.vue'
import ModuleActions from '../fields/ModuleActions.vue'
import { ref } from 'vue'

const icons = { Plus, Repeat }

// Props
const props = defineProps({
  module: {
    type: Object,
    required: true
  },
  index: {
    type: Number,
    default: 0
  },
  isGhost: {
    type: Boolean,
    default: false
  }
})

// Inject
const builder = inject('builder')

// Computed
const isSelected = computed(() => 
  !props.isGhost && builder?.selectedModuleId === props.module.id
)

const isHovered = computed(() => 
  !props.isGhost && builder?.hoveredModuleId === props.module.id
)

const shouldShowToolbar = computed(() => {
  if (props.isGhost) return false
  if (isSelected.value) return true
  // If something is selected elsewhere, don't show hovered toolbars
  if (builder?.selectedModuleId && builder.selectedModuleId !== props.module.id) return false
  return isHovered.value
})

const wireframeMode = computed(() => 
  builder?.wireframeMode || false
)

const gridViewMode = computed(() => 
  builder?.gridViewMode || false
)

const moduleDefinition = computed(() => 
  ModuleRegistry.get(props.module.type)
)

const moduleTitle = computed(() => 
  moduleDefinition.value?.title || props.module.type
)

const moduleType = computed(() => props.module.type)

const isSection = computed(() => moduleType.value === 'section')

const isRow = computed(() => moduleType.value === 'row')

const isColumn = computed(() => moduleType.value === 'column')

const hasChildren = computed(() => 
  Array.isArray(props.module.children)
)

const canAddChildren = computed(() => {
  const def = moduleDefinition.value
  if (!def?.children) return false
  return true
})

const childType = computed(() => {
  // Section -> Row, Row -> Column, Column -> any module
  if (moduleType.value === 'section') return 'row'
  if (moduleType.value === 'row') return 'column'
  return 'module'
})

const shouldShowAddChildButton = computed(() => {
    // Rows now have their own floating sibling buttons
    // Columns have internal Add Module button in ColumnBlock.vue
    // No child buttons needed here
    return false
})

const wrapperClasses = computed(() => ({
  'module-wrapper--selected': isSelected.value,
  'module-wrapper--hovered': isHovered.value && !isSelected.value,
  [`module-wrapper--${moduleType.value}`]: true,
  'module-wrapper--grid': gridViewMode.value, // Grid View (Colored structure)
  'module-wrapper--wireframe': wireframeMode.value, // Wireframe Mode (Blocks only)
  'module-wrapper--content': !isSection.value && !isRow.value && !isColumn.value, // Generic content module class
  'module-wrapper--loop': isLoopEnabled.value
}))

const isLoopEnabled = computed(() => props.module.settings?.loop_enable === true)

const loopInstances = computed(() => {
  if (!isLoopEnabled.value) return [{ id: 'single', isGhost: false }]
  
  // Return fixed number of instances for simulation
  const count = parseInt(props.module.settings?.posts_per_page) || 3
  // Cap at 6 for builder performance
  const displayCount = Math.min(Math.max(count, 1), 6)
  
  return Array.from({ length: displayCount }, (_, i) => ({
    id: `loop-${i}`,
    isGhost: i > 0
  }))
})

// Methods
const selectModule = () => {
  builder?.selectModule(props.module.id)
}

const hoverModule = () => {
  builder?.hoverModule(props.module.id)
}

const unhoverModule = () => {
  builder?.hoverModule(null)
}

const handleContextMenu = (e) => {
    // Only trigger if builder has the method (it should)
    if (builder?.openContextMenu) {
        builder.openContextMenu(props.module.id, e, moduleTitle.value, props.module.type)
    }
}

const openSettings = () => {
  selectModule()
}

const addChild = () => {
  // Insert appropriate child type
  const type = childType.value
  if (type === 'row') {
     // Use the modal for rows to select column structure
    builder?.openInsertRowModal(props.module.id)
  } else if (type === 'module') {
    builder?.openInsertModal(props.module.id)
  } else {
    builder?.insertModule(type, props.module.id)
  }
}

const addSiblingSection = () => {
    // Open Insert Section Modal targeting the next index
    builder?.openInsertSectionModal(props.index + 1)
}

const addSiblingRow = () => {
    // Open Insert Row Modal to add another row in the same section
    // Find the parent section ID from the builder's blocks
    const parentSectionId = findParentId(props.module.id)
    if (parentSectionId) {
        builder?.openInsertRowModal(parentSectionId)
    }
}

// Helper to find parent module ID
const findParentId = (moduleId) => {
    const blocks = builder?.blocks || []
    for (const section of blocks) {
        if (section.children) {
            for (const row of section.children) {
                if (row.id === moduleId) return section.id
            }
        }
    }
    return null
}

const duplicateModule = () => {
  builder?.duplicateModule(props.module.id)
}

const deleteModule = () => {
  if (confirm('Delete this module?')) {
    builder?.removeModule(props.module.id)
  }
}

// Reusable Actions Logic
const showInfo = ref(false)
const toggleInfo = () => {
    showInfo.value = !showInfo.value
}

const moduleInfoText = computed(() => {
    const def = moduleDefinition.value
    return `Type: ${props.module.type}. ID: ${props.module.id}. Title: ${moduleTitle.value}.`
})

const handleMoreAction = (action) => {
    if (action === 'reset') {
        const def = ModuleRegistry.get(props.module.type)
        if (def && def.defaults) {
            builder?.updateModuleSettings(props.module.id, { ...def.defaults })
        }
    }
    // Handle other actions...
}
</script>

<style scoped>
.module-wrapper {
  position: relative;
  margin-bottom: var(--spacing-md);
}

/* Grid/Wireframe borders */
.module-wrapper--grid,
.module-wrapper--wireframe {
  border: 1px solid transparent; /* Default transparent */
  border-radius: var(--border-radius-sm);
  padding: var(--spacing-sm);
}

/* Section: Blue */
.module-wrapper--grid.module-wrapper--section,
.module-wrapper--wireframe.module-wrapper--section {
  border: 2px solid var(--builder-section);
  margin-bottom: var(--spacing-lg);
  padding-bottom: 24px; /* Space for add button */
}

/* Row: Green */
.module-wrapper--grid.module-wrapper--row,
.module-wrapper--wireframe.module-wrapper--row {
  border: 2px solid var(--builder-row);
  margin-bottom: var(--spacing-md);
}

/* Column: Gray - Same as toolbar */
.module-wrapper--grid.module-wrapper--column,
.module-wrapper--wireframe.module-wrapper--column {
  border: 1px solid #4a5568;
  min-height: 50px;
}

/* Module: Dashed Gray */
/* But user wants simple block? Modules are gray blocks */
.module-wrapper--wireframe.module-wrapper--text,
.module-wrapper--wireframe.module-wrapper--image,
.module-wrapper--wireframe.module-wrapper--button {
    background: var(--builder-bg-tertiary);
    border: 1px solid var(--builder-border);
}

/* Selection states - Type-specific outline colors */
.module-wrapper--selected.module-wrapper--section {
  outline: 2px solid var(--builder-section);
  outline-offset: -2px;
  z-index: 5;
}
.module-wrapper--selected.module-wrapper--row {
  outline: 2px solid var(--builder-row);
  outline-offset: -2px;
  z-index: 5;
}
.module-wrapper--selected.module-wrapper--column {
  outline: 2px solid #4a5568;
  outline-offset: -2px;
  z-index: 5;
}

.module-wrapper--hovered.module-wrapper--section {
  outline: 1px solid var(--builder-section);
  outline-offset: -1px;
  z-index: 4;
}
.module-wrapper--hovered.module-wrapper--row {
  outline: 1px solid var(--builder-row);
  outline-offset: -1px;
  z-index: 4;
}
.module-wrapper--hovered.module-wrapper--column {
  outline: 1px solid #4a5568;
  outline-offset: -1px;
  z-index: 4;
}
.module-wrapper--hovered.module-wrapper--content {
  outline: 1px solid #000000;
  outline-offset: -1px;
  z-index: 6; /* Higher z-index for inner modules */
}

.module-wrapper--selected.module-wrapper--content {
  outline: 2px solid #000000;
  outline-offset: -2px;
  z-index: 6;
}

/* Inline Toolbar - Base Styles */
.module-toolbar {
  position: absolute;
  display: flex;
  gap: 2px;
  padding: 4px;
  background: var(--builder-toolbar-bg-module);
  z-index: 100;
}

/* Specific toolbar positions per type to avoid overlap (Zig-Zag) */

/* Section: Top-Left Inside */
.module-wrapper--section > .module-toolbar {
    top: 0;
    left: 0;
    background: var(--builder-toolbar-bg-section);
    border-bottom-right-radius: var(--border-radius-sm);
}

/* Row: Top-Right Inside */
.module-wrapper--row > .module-toolbar {
    top: 0;
    left: auto;
    right: 0;
    background: var(--builder-toolbar-bg-row);
    border-bottom-left-radius: var(--border-radius-sm);
}

/* Column: Top-Left Inside */
.module-toolbar--column {
    top: 0;
    left: 0;
    right: auto;
    background: var(--builder-toolbar-bg-module);
    border-bottom-right-radius: var(--border-radius-sm);
}

/* Module/Content: Top-Right Inside */
.module-wrapper--content > .module-toolbar {
    top: 0;
    left: auto;
    right: 0;
    background: var(--builder-toolbar-bg-module);
    border-bottom-left-radius: var(--border-radius-sm);
}

/* Icons inside ModuleActions need to be white on colored toolbars */
:deep(.module-actions .action-icon) {
    color: white !important;
    opacity: 0.9;
}

:deep(.module-actions .action-icon:hover) {
    background: rgba(255, 255, 255, 0.2) !important;
    color: white !important;
    opacity: 1;
}

:deep(.module-actions .delete-btn:hover) {
    background: var(--builder-error) !important;
    color: white !important;
}


/* Module Content */
.module-content {
  min-height: 40px;
}

/* Module Label (Grid/Wireframe) */
.module-label {
  position: absolute;
  top: 0;
  left: 0;
  padding: 2px 6px;
  background: var(--builder-accent);
  border-bottom-right-radius: 4px;
  color: white;
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  z-index: 10;
  pointer-events: none;
}

.module-wrapper--grid.module-wrapper--section > .module-label,
.module-wrapper--wireframe.module-wrapper--section > .module-label {
  background: var(--builder-section);
}

.module-wrapper--grid.module-wrapper--row > .module-label,
.module-wrapper--wireframe.module-wrapper--row > .module-label {
  background: var(--builder-row);
}

.module-wrapper--grid.module-wrapper--column > .module-label,
.module-wrapper--wireframe.module-wrapper--column > .module-label {
  background: var(--builder-column);
  color: var(--builder-text-primary);
}

/* Loop Styles */
.loop-indicator {
    position: absolute;
    top: -18px;
    right: 0;
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 2px 6px;
    background: var(--builder-accent);
    color: white;
    font-size: 9px;
    font-weight: 700;
    border-radius: 4px 4px 0 0;
    z-index: 10;
    pointer-events: none;
    letter-spacing: 0.5px;
}

.is-looping {
    display: flex;
    flex-direction: column;
}

/* If it's a Column being looped, it should probably stay flex-row if the parent is a row? 
   Actually loop_enable is usually on Section/Row/Column as containers. 
   If a Row is looped, it creates multiple Rows. 
   If a Module is looped, it creates multiple Modules within its Column.
*/

.module-info-panel-canvas {
    position: absolute;
    top: 32px;
    left: 0;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    padding: 8px 12px;
    border-radius: 4px;
    box-shadow: var(--shadow-lg);
    z-index: 110;
    max-width: 250px;
    font-size: 11px;
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

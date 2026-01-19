<template>
  <Teleport to="body" :disabled="!builder.isFullscreen">
    <div 
      class="ja-builder" 
      :class="[
        cmsStore.isDarkMode ? 'ja-builder--dark' : 'ja-builder--light',
        { 
          'ja-builder--fullscreen': builder.isFullscreen,
          'ja-builder--wireframe': builder.wireframeMode
        }
      ]"
    >
      <!-- Top Toolbar -->
      <TopToolbar 
        :active-panel="activePanel"
        @toggle-sidebar="handleToggleSidebar"
        @change-device="builder.setDevice"
        @open-pages="showInsertSectionModal = true"
        @close-builder="handleClose"
        @save="handleSave"
      />
      
      <!-- Main Content Area -->
      <div class="ja-builder__main">
        <!-- Left Sidebar (Always Visible) -->
        <LeftSidebar 
          :active-panel="activePanel"
          @change-panel="togglePanel"
        />
        
        <!-- Left Panel Drawer -->
        <LeftPanel
          v-if="sidebarVisible"
          :active-panel="activePanel"
          :visible="!!activePanel"
          @close="activePanel = null"
        />
        
        <!-- Canvas Area -->
        <div 
          class="ja-builder__canvas-area"
          ref="canvasAreaRef"
        >
          <CanvasControls 
            @save="handleSave"
          />
          <CanvasFrame 
            :device="builder.device" 
            :zoom="builder.zoom" 
            :width="builder.customViewportWidth"
          >
            <Canvas />
          </CanvasFrame>
        </div>
        
        <!-- Right Panel (Settings) -->
        <RightPanel 
          v-if="selectedModule"
          :module="selectedModule"
          @close="closeSettings"
        />
      </div>

      <!-- Modals -->
      <InsertModuleModal 
        v-if="showInsertModal"
        @close="showInsertModal = false"
        @insert="handleModuleInsert"
      />
      <InsertRowModal
        v-if="showInsertRowModal"
        :mode="insertRowMode"
        @close="showInsertRowModal = false"
        @insert="insertRow"
        @update="updateRow"
      />
      <InsertSectionModal
        v-if="showInsertSectionModal"
        :target-index="insertTargetIndex"
        @close="showInsertSectionModal = false"
        @inserted="handleSectionInserted"
      />
      <StructureTemplateModal
        v-if="showStructureTemplateModal"
        :target-type="structureTemplateTargetType"
        @close="showStructureTemplateModal = false"
        @insert="handleStructureTemplateInsert"
      />
      <ResponsiveFieldModal
        v-if="builder.responsiveModal && builder.responsiveModal.baseKey"
        v-bind="builder.responsiveModal"
        @close="builder.closeResponsiveModal"
        @update="handleResponsiveUpdate"
      />

      <!-- Canvas Modals -->
      <AddCanvasModal
        v-if="showAddCanvasModal"
        @close="showAddCanvasModal = false"
        @add="handleAddCanvas"
      />
      <ImportExportModal
        v-if="showImportExportModal"
        @close="showImportExportModal = false"
        @export="handleExportCanvas"
        @import="handleImportCanvas"
      />
      <CanvasSettingsModal
        v-if="showCanvasSettingsModal"
        :canvas="activeCanvasData"
        @close="showCanvasSettingsModal = false"
        @save="handleSaveCanvasSettings"
      />

      <SavePresetModal
        v-if="builder.savePresetModal.visible"
        :loading="builder.savePresetModal.loading"
        @close="builder.closeSavePresetModal"
        @save="builder.handleSavePreset"
      />
      
      <ContextMenu 
          :visible="contextMenu.visible"
          :x="contextMenu.x"
          :y="contextMenu.y"
          :module-id="contextMenu.moduleId"
          :title="contextMenu.title"
          :type="contextMenu.type"
          :mode="contextMenu.mode"
          @close="closeContextMenu"
          @action="handleContextMenuAction"
      />
    </div>
  </Teleport>
</template>

<script setup>
import { ref, reactive, computed, provide, watch, onMounted, onUnmounted } from 'vue'

// Layout Components
import TopToolbar from './layout/TopToolbar.vue'
import LeftSidebar from './layout/LeftSidebar.vue'
import LeftPanel from './layout/LeftPanel.vue'
import RightPanel from './layout/RightPanel.vue'
import CanvasFrame from './layout/CanvasFrame.vue'

// Canvas Components
import Canvas from './canvas/Canvas.vue'
import CanvasControls from './canvas/CanvasControls.vue'

// Modal Components
import InsertModuleModal from './modals/InsertModuleModal.vue'
import InsertRowModal from './modals/InsertRowModal.vue'
import InsertSectionModal from './modals/InsertSectionModal.vue'
import StructureTemplateModal from './modals/StructureTemplateModal.vue'
import ResponsiveFieldModal from './modals/ResponsiveFieldModal.vue'
import AddCanvasModal from './modals/AddCanvasModal.vue'
import ImportExportModal from './modals/ImportExportModal.vue'
import CanvasSettingsModal from './modals/CanvasSettingsModal.vue'
import SavePresetModal from './modals/SavePresetModal.vue'
import ContextMenu from './ui/ContextMenu.vue'

// Core
import { useBuilder, ModuleRegistry } from './core'
import { useCmsStore } from '@/stores/cms'

// Register Module Definitions (side-effect import)
import './modules'

// Global Builder Styles
import './styles/builder.css'

// Register all Block Components
import { registerBlockComponents } from './core/registerBlocks'
registerBlockComponents()

// Props
const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({ blocks: [] })
  },
  modelValue: { // Added for v-model support
    type: Array,
    default: undefined
  },
  contentId: { // New prop for CMS integration
    type: [String, Number],
    default: null
  },
  mode: { // site | page
    type: String,
    default: 'site'
  }
})

// Emits
const emit = defineEmits(['update', 'save', 'update:modelValue', 'close', 'update:fullscreen'])

// Initialize builder state
// Prefer modelValue (array of blocks) if provided, otherwise fallback to initialData object
const builderInitialData = computed(() => {
  if (props.modelValue) {
    return { blocks: props.modelValue }
  }
  return props.initialData
})

const builderBase = useBuilder(builderInitialData.value, {
  mode: props.mode
})
const globalAction = ref(null)
const cmsStore = useCmsStore()

// UI State
// Theme initialization is handled by cmsStore
watch(() => cmsStore.isDarkMode, (isDark) => {
  if (isDark) {
    document.body.classList.add('ja-builder--dark')
    document.body.classList.remove('ja-builder--light')
  } else {
    document.body.classList.add('ja-builder--light')
    document.body.classList.remove('ja-builder--dark')
  }
}, { immediate: true })

onUnmounted(() => {
  document.body.classList.remove('ja-builder--dark', 'ja-builder--light')
})

const sidebarVisible = ref(true)
const activePanel = ref('layers')
// device is managed by builder
const showInsertModal = ref(false)
const insertTargetId = ref(null)
const insertTargetIndex = ref(-1)

const showInsertRowModal = ref(false)
const insertRowTargetId = ref(null)

const showInsertSectionModal = ref(false)
const insertSectionIndex = ref(-1)

const showStructureTemplateModal = ref(false)
const structureTemplateTargetId = ref(null)
const structureTemplateTargetType = ref(null)

const builder = reactive({
  ...builderBase,
  darkMode: computed(() => cmsStore.isDarkMode),
  sidebarVisible,
  activePanel,
  globalAction,
})

watch(() => builder.isFullscreen, (val) => {
  emit('update:fullscreen', val)
})

// Watch for changes in builder blocks and emit updates
watch(builder.blocks, (newBlocks) => {
  emit('update', { blocks: newBlocks })
  emit('update:modelValue', newBlocks)
}, { deep: true })



// Watch for external modelValue changes
watch(() => props.modelValue, (newBlocks) => {
  if (newBlocks && JSON.stringify(newBlocks) !== JSON.stringify(builder.blocks)) {
    builder.blocks = newBlocks
  }
}, { deep: true })



// Selected Module
const selectedModule = computed(() => builder.selectedModule)

watch(selectedModule, (newVal) => {
  if (newVal && window.innerWidth <= 768) {
    activePanel.value = null
  }
})

// Methods
const toggleSidebar = () => {
  sidebarVisible.value = !sidebarVisible.value
}

const changeDevice = (newDevice) => {
  builder.setDeviceMode(newDevice)
}

const togglePanel = (panel) => {
  if (activePanel.value === panel) {
    activePanel.value = null
  } else {
    activePanel.value = panel
  }
}

const closeSettings = () => {
  builder.clearSelection()
}

const openInsertModal = (targetId, index = -1) => {
  insertTargetId.value = targetId
  insertTargetIndex.value = index
  showInsertModal.value = true
}

const insertRowMode = ref('insert') // 'insert' | 'edit'

const openInsertRowModal = (targetId) => {
  insertRowTargetId.value = targetId
  insertRowMode.value = 'insert'
  showInsertRowModal.value = true
}

const openUpdateRowModal = (rowId) => {
  insertRowTargetId.value = rowId
  insertRowMode.value = 'edit'
  showInsertRowModal.value = true
}

const openInsertSectionModal = (index = -1) => {
  if (window.innerWidth <= 768) {
      sidebarVisible.value = false
  }
  insertSectionIndex.value = index
  showInsertSectionModal.value = true
}

const openStructureTemplateModal = (targetId, targetType) => {
  structureTemplateTargetId.value = targetId
  structureTemplateTargetType.value = targetType
  showStructureTemplateModal.value = true
}

const insertModule = (type) => { // Keep for backward compat if needed, but primary logic is below
  builder.insertModule(type, insertTargetId.value, insertTargetIndex.value)
  showInsertModal.value = false
}

const handleModuleInsert = (type, payload) => {
  // Handle Row insertion from InsertModuleModal which passes layout object
  if (type === 'row') {
    insertRowTargetId.value = insertTargetId.value
    insertRow(payload)
    return
  }

  // Handle Preset insertion
  if (type === 'preset') {
    builder.insertFromPreset(payload, insertTargetId.value, insertTargetIndex.value)
    showInsertModal.value = false
    return
  }

  // Handle normal module insertion
  insertModule(type)
}

const handleStructureTemplateInsert = (payload) => {
    insertRowTargetId.value = structureTemplateTargetId.value
    insertRow(payload)
    showStructureTemplateModal.value = false
}

const insertRow = (layout) => {
  // layout can be:
  // 1. object {widths}
  // 2. object {rows: [ {widths:[]}, ... ]} (Multi-row preset)
  // 3. object {cols: [ {width, rows:[]}, ... ]} (Specialty preset)
  
  const createSingleRow = (config, parentId = insertRowTargetId.value) => {
    // 1. Create Row
    const row = builder.insertModule('row', parentId)
    
    if (row) {
      if (config.cols) {
        // Specialty/Nested structure
        config.cols.forEach(colConfig => {
          const col = builder.insertModule('column', row.id)
          if (col) {
            builder.updateModuleSettings(col.id, { flexGrow: colConfig.width })
            if (colConfig.rows) {
              colConfig.rows.forEach(nestedRowConfig => {
                createSingleRow(nestedRowConfig, col.id)
              })
            }
          }
        })
      } else {
        // Standard structure
        const structure = config.structure || config
        const widths = config.widths || (typeof structure === 'string' ? structure.split('-').map(() => 1) : [1])
        
        widths.forEach(width => {
          const col = builder.insertModule('column', row.id)
          if (col) {
            builder.updateModuleSettings(col.id, { flexGrow: width })
          }
        })
      }
    }
  }

  if (layout.rows) {
      // Multi-Row Preset
      layout.rows.forEach(rowConfig => createSingleRow(rowConfig))
  } else {
      // Single Row or Specialty
      createSingleRow(layout)
  }
  
  showInsertRowModal.value = false
}

const updateRow = (layout) => {
    const success = builder.updateRowLayout(insertRowTargetId.value, layout)
    if (!success) {
        toast.error('Could not update row layout')
    }
    showInsertRowModal.value = false
}

const handleResponsiveUpdate = (updates) => {
  if (builder.responsiveModal) {
    const moduleId = builder.responsiveModal.module.id
    builder.updateModuleSettings(moduleId, updates)
  }
}

// Close Builder
const handleClose = () => {
    // Emit close event for parent app to handle (e.g. redirect)
    emit('close')
    // Alternatively, could window.history.back() if standalone
}

// Keyboard Shortcuts
const handleKeydown = (e) => {
  // Check if input/textarea is focused to avoid conflict
  if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) || e.target.isContentEditable) {
    return
  }

  // Undo/Redo
  if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'z') {
    e.preventDefault()
    if (e.shiftKey) {
      builder.redo()
    } else {
      builder.undo()
    }
  }
  
  // Save
  if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 's') {
    e.preventDefault()
    emit('save')
  }
}

import { useToast } from '@/composables/useToast'

const toast = useToast()

const handleSave = async (status = null) => {
  if (props.contentId || builder.content.id) {
    try {
      if (status) {
        builder.content.status = status
      }
      await builder.saveContent()
      toast.success.save()
    } catch (err) {
      toast.error.action(err)
    }
  }
}

const canvasAreaRef = ref(null)
let resizeObserver = null

onMounted(async () => {
    window.addEventListener('keydown', handleKeydown)
    builder.loadTheme()
    builder.fetchMetadata()
    
    // Setup ResizeObserver for adaptive device mode
    if (canvasAreaRef.value) {
        resizeObserver = new ResizeObserver((entries) => {
            if (builder.deviceModeType !== 'auto') return

            for (let entry of entries) {
                const width = entry.contentRect.width
                
                // Adaptive thresholds
                let newDevice = 'desktop'
                if (width < 768) {
                    newDevice = 'mobile'
                } else if (width < 1024) {
                    newDevice = 'tablet'
                }
                
                if (builder.device !== newDevice) {
                    builder.device = newDevice
                }
            }
        })
        resizeObserver.observe(canvasAreaRef.value)
    }

    if (props.contentId) {
      try {
        await builder.loadContent(props.contentId)
      } catch (err) {
        toast.error('Failed to load content')
      }
    }
})

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown)
    if (resizeObserver) {
        resizeObserver.disconnect()
    }
})

// Context Menu Logic
const contextMenu = reactive({
    visible: false,
    x: 0,
    y: 0,
    moduleId: null,
    title: '',
    type: '',
    mode: 'module'
})

const openContextMenu = (moduleId, event, title = '', type = '', mode = 'module') => {
    event.preventDefault()
    contextMenu.visible = true
    contextMenu.x = event.clientX
    contextMenu.y = event.clientY
    contextMenu.moduleId = moduleId
    contextMenu.title = title
    contextMenu.type = type
    contextMenu.mode = mode
}

const closeContextMenu = () => {
    contextMenu.visible = false
}

// Canvas Modal State
const showAddCanvasModal = ref(false)
const showImportExportModal = ref(false)
const showCanvasSettingsModal = ref(false)
const activeCanvasForModal = ref(null)

const getCanvasById = (id) => {
    if (!id || !builder || !builder.canvases) return null
    const list = Array.isArray(builder.canvases) ? builder.canvases : (builder.canvases.value || [])
    return list.find(c => c.id === id)
}

const activeCanvasData = computed(() => {
    return getCanvasById(activeCanvasForModal.value)
})

const handleContextMenuAction = (action, id, mode = 'module') => {
    if (mode === 'canvas') {
        handleCanvasAction(action, id)
        return
    }

    if (action === 'duplicate') {
        builder.duplicateModule(id)
    } else if (action === 'delete') {
        if (confirm('Delete this module?')) {
            builder.removeModule(id)
        }
    } else if (action === 'copy') {
        builder.copyModule(id)
    } else if (action === 'paste') {
        builder.pasteModule(id)
    } else if (action === 'copy-style') {
        builder.copyStyles(id)
    } else if (action === 'paste-style') {
        builder.pasteStyles(id)
    } else if (action === 'parent') {
        const parent = builder.findParentById(builder.blocks, id)
        if (parent) {
            builder.selectModule(parent.id)
        }
    }
}

const handleCanvasAction = (action, id) => {
    if (action === 'edit-canvas') {
        builder.switchCanvas(id)
    } else if (action === 'duplicate-canvas') {
        builder.duplicateCanvas(id)
    } else if (action === 'delete-canvas') {
        builder.removeCanvas(id)
    } else if (action === 'canvas-settings') {
        activeCanvasForModal.value = id
        showCanvasSettingsModal.value = true
    } else if (action === 'export-canvas') {
        activeCanvasForModal.value = id
        showImportExportModal.value = true
    } else if (action === 'make-main-canvas') {
        builder.setMainCanvas(id)
    }
}

const handleAddCanvas = (data) => {
    builder.addCanvas(data.title)
    showAddCanvasModal.value = false
}

const handleExportCanvas = (data) => {
    builder.exportCanvas(activeCanvasForModal.value)
    showImportExportModal.value = false
}

const handleImportCanvas = (data) => {
    // Importing to canvas...
    showImportExportModal.value = false
}

const handleSaveCanvasSettings = (data) => {
    builder.renameCanvas(activeCanvasForModal.value, data.title)
    // Update global status if needed (mock for now)
    const canvas = getCanvasById(activeCanvasForModal.value)
    if (canvas) {
        canvas.isGlobal = data.isGlobal
        canvas.append = data.append
    }
    showCanvasSettingsModal.value = false
}

// Global openers
builder.openAddCanvasModal = () => { showAddCanvasModal.value = true }
builder.openImportExportModal = (id) => { 
    activeCanvasForModal.value = id
    showImportExportModal.value = true 
}

// Assign modal openers to builder (to avoid TDZ)
builder.openInsertModal = openInsertModal
builder.openInsertRowModal = openInsertRowModal
builder.openUpdateRowModal = openUpdateRowModal
builder.openInsertSectionModal = openInsertSectionModal
builder.openStructureTemplateModal = openStructureTemplateModal
builder.openContextMenu = openContextMenu

// Provide builder to all children
provide('builder', builder)
provide('darkMode', computed({
  get: () => cmsStore.isDarkMode,
  set: (val) => cmsStore.toggleDarkMode(val)
}))
</script>

<template>
  <Teleport to="body" :disabled="!builder.isFullscreen.value">
    <div 
      class="ja-builder ja-builder-main" 
      :class="[
        cmsStore.isDarkMode ? 'ja-builder--dark' : 'ja-builder--light',
        { 
          'ja-builder--fullscreen': builder.isFullscreen.value,
          'ja-builder--wireframe': builder.wireframeMode.value
        }
      ]"
    >
      <!-- Top Toolbar -->
      <TopToolbar 
        :active-panel="activePanel"
        @toggle-sidebar="toggleSidebar"
        @change-device="changeDevice"
        @open-pages="showInsertSectionModal = true"
        @close-builder="handleClose"
        @save="handleSave"
      />
      
      <!-- Main Content Area -->
      <div class="ja-builder__main">
        <!-- Left Sidebar (Always Visible) -->
        <LeftSidebar 
          :active-panel="activePanel || undefined"
          @change-panel="togglePanel"
        />
        
        <!-- Left Panel Drawer -->
        <LeftPanel
          v-if="sidebarVisible"
          :active-panel="activePanel!"
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
            :device="builder.device.value" 
            :zoom="builder.zoom.value" 
            :width="builder.customViewportWidth.value"
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
        :target-index="insertSectionIndex"
        @close="showInsertSectionModal = false"
        @inserted="handleSectionInserted"
      />
      <StructureTemplateModal
        v-if="showStructureTemplateModal"
        :target-type="(structureTemplateTargetType as string)"
        @close="showStructureTemplateModal = false"
        @insert="handleStructureTemplateInsert"
      />
      <ResponsiveFieldModal
        v-if="builder.responsiveModal.value && builder.responsiveModal.value.baseKey"
        v-bind="builder.responsiveModal.value"
        @close="builder.closeResponsiveModal"
        @update="handleResponsiveUpdate"
      />
      <IconPickerModal
        v-if="showIconPickerModal"
        :value="iconPickerValue"
        :on-select="handleIconSelect"
        @close="showIconPickerModal = false"
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
        v-if="showCanvasSettingsModal && activeCanvasData"
        :canvas="activeCanvasData"
        @close="showCanvasSettingsModal = false"
        @save="handleSaveCanvasSettings"
      />

      <SavePresetModal
        v-if="builder.savePresetModal.value.visible"
        :loading="builder.savePresetModal.value.loading"
        @close="builder.closeSavePresetModal"
        @save="builder.handleSavePreset"
      />

      <ConfirmModal
        v-if="builder.confirmModal.value.visible"
        :is-open="builder.confirmModal.value.visible"
        :title="builder.confirmModal.value.title"
        :message="builder.confirmModal.value.message"
        :confirm-text="builder.confirmModal.value.confirmText"
        :cancel-text="builder.confirmModal.value.cancelText"
        :type="builder.confirmModal.value.type"
        @confirm="builder.closeConfirmModal(true)"
        @cancel="builder.closeConfirmModal(false)"
      />

      <InputModal
        v-if="builder.inputModal.value.visible"
        :is-open="builder.inputModal.value.visible"
        :title="builder.inputModal.value.title"
        :message="builder.inputModal.value.message"
        :placeholder="builder.inputModal.value.placeholder"
        :initial-value="builder.inputModal.value.initialValue"
        :confirm-text="builder.inputModal.value.confirmText"
        :cancel-text="builder.inputModal.value.cancelText"
        @confirm="builder.closeInputModal"
        @cancel="builder.closeInputModal(null)"
      />
      
      <ContextMenu 
          :visible="contextMenu.visible"
          :x="contextMenu.x"
          :y="contextMenu.y"
          :module-id="contextMenu.moduleId || undefined"
          :title="contextMenu.title"
          :type="contextMenu.type"
          :mode="contextMenu.mode"
          @close="closeContextMenu"
          @action="handleContextMenuAction"
      />
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, reactive, computed, provide, watch, onMounted, onUnmounted } from 'vue'
import type { Ref } from 'vue'

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
import IconPickerModal from './modals/IconPickerModal.vue'
import ConfirmModal from './modals/ConfirmModal.vue'
import InputModal from './modals/InputModal.vue'
import ContextMenu from './ui/ContextMenu.vue'

// Core
import { useBuilder } from './core'
import { useCmsStore } from '@/stores/cms'
import type { BlockInstance, BuilderInstance, Canvas as ICanvas } from '../../types/builder'

// Register Module Definitions (side-effect import)
import './modules'

// Global Builder Styles
import './styles/builder.css'

// Register all Block Components
import { registerBlockComponents } from './core/registerBlocks'
registerBlockComponents()

// Props
interface Props {
  initialData?: { blocks: BlockInstance[] };
  modelValue?: BlockInstance[];
  contentId?: string | number | null;
  mode?: 'site' | 'page';
}

const props = withDefaults(defineProps<Props>(), {
  initialData: () => ({ blocks: [] }),
  contentId: null,
  mode: 'site'
})

// Emits
const emit = defineEmits<{
  (e: 'update', payload: { blocks: BlockInstance[] }): void;
  (e: 'save', status: string | null): void;
  (e: 'update:modelValue', blocks: BlockInstance[]): void;
  (e: 'close'): void;
  (e: 'update:fullscreen', val: boolean): void;
  (e: 'update:autoSave', val: boolean): void;
}>()

// Initialize builder state
const builderInitialData = computed(() => {
  if (props.modelValue) {
    return { blocks: props.modelValue }
  }
  return props.initialData
})

const builderBase = useBuilder(builderInitialData.value, {
  mode: props.mode
})

// Expose builder instance
defineExpose({ builder: builderBase })

const globalAction = ref<string | null>(null)
const cmsStore = useCmsStore()

// UI State
watch(() => cmsStore.isDarkMode, (isDark) => {
  if (isDark) {
    document.documentElement.classList.add('dark')
    document.body.classList.add('ja-builder--dark')
    document.body.classList.remove('ja-builder--light')
  } else {
    document.documentElement.classList.remove('dark')
    document.body.classList.add('ja-builder--light')
    document.body.classList.remove('ja-builder--dark')
  }
}, { immediate: true })

onUnmounted(() => {
  document.body.classList.remove('ja-builder--dark', 'ja-builder--light')
})

// Correctly provide dark mode for children
const darkMode = computed(() => cmsStore.isDarkMode)
provide('darkMode', darkMode)

const sidebarVisible = ref(true)
const activePanel = ref<string | null>(props.mode === 'site' ? 'pages' : 'layers')

const showInsertModal = ref(false)
const insertTargetId = ref<string | null>(null)
const insertTargetIndex = ref(-1)

const showInsertRowModal = ref(false)
const insertRowTargetId = ref<string | null>(null)

const showInsertSectionModal = ref(false)
const insertSectionIndex = ref(-1)

const showStructureTemplateModal = ref(false)
const structureTemplateTargetId = ref<string | null>(null)
const structureTemplateTargetType = ref<string | null>(null)

const builder = {
  ...builderBase,
  darkMode,
  sidebarVisible,
  activePanel,
  globalAction,
} as any as BuilderInstance & { darkMode: Ref<boolean>; sidebarVisible: Ref<boolean>; activePanel: Ref<string | null>; globalAction: Ref<string | null> }

// Provide builder for child components
provide('builder', builder)

watch(() => (builder as any).isFullscreen, (val) => {
  emit('update:fullscreen', val)
})

watch(builder.blocks, (newBlocks) => {
  emit('update', { blocks: newBlocks })
  emit('update:modelValue', newBlocks)
}, { deep: true })

watch(() => (builder as any).autoSave, (val) => {
  emit('update:autoSave', val)
}, { immediate: true })

watch(() => props.modelValue, (newBlocks) => {
  if (newBlocks && JSON.stringify(newBlocks) !== JSON.stringify(builder.blocks.value)) {
    builder.blocks.value = newBlocks
  }
}, { deep: true })

const selectedModule = computed(() => builder.selectedModule.value)

watch(selectedModule, (newVal) => {
  if (newVal && window.innerWidth <= 768) {
    activePanel.value = null
  }
})

// Methods
const toggleSidebar = () => {
  sidebarVisible.value = !sidebarVisible.value
}

const changeDevice = (newDevice: string) => {
  builder.setDeviceMode(newDevice as 'desktop' | 'tablet' | 'mobile')
}

const togglePanel = (panel: string) => {
  if (activePanel.value === panel) {
    activePanel.value = null
  } else {
    activePanel.value = panel
  }
}

const closeSettings = () => {
  builder.clearSelection()
}

const openInsertModal = (targetId: string | null, index = -1) => {
  insertTargetId.value = targetId
  insertTargetIndex.value = index
  showInsertModal.value = true
}

const insertRowMode = ref<'insert' | 'edit'>('insert')

const openInsertRowModal = (targetId: string | null) => {
  insertRowTargetId.value = targetId
  insertRowMode.value = 'insert'
  showInsertRowModal.value = true
}

const openUpdateRowModal = (rowId: string | null) => {
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

const openStructureTemplateModal = (targetId: string | null, targetType: string | null) => {
  structureTemplateTargetId.value = targetId
  structureTemplateTargetType.value = targetType
  showStructureTemplateModal.value = true
}

const insertModule = (type: string) => {
  builder.insertModule(type, insertTargetId.value, insertTargetIndex.value)
  showInsertModal.value = false
}

const handleModuleInsert = (type: string, payload: any) => {
  if (type === 'row') {
    insertRowTargetId.value = insertTargetId.value
    insertRow(payload)
    return
  }
  if (type === 'preset') {
    builder.insertFromPreset(payload, insertTargetId.value, insertTargetIndex.value)
    showInsertModal.value = false
    return
  }
  insertModule(type)
}

const handleStructureTemplateInsert = (payload: any) => {
    structureTemplateTargetId.value = structureTemplateTargetId.value
    insertRow(payload)
    showStructureTemplateModal.value = false
}

const insertRow = (layout: any) => {
  const createSingleRow = (config: any, parentId = insertRowTargetId.value) => {
    const row = builder.insertModule('row', parentId)
    if (row) {
      if (config.cols) {
        config.cols.forEach((colConfig: any) => {
          const col = builder.insertModule('column', row.id)
          if (col) {
            builder.updateModuleSettings(col.id, { flexGrow: colConfig.width })
            if (colConfig.rows) {
              colConfig.rows.forEach((nestedRowConfig: any) => {
                createSingleRow(nestedRowConfig, col.id)
              })
            }
          }
        })
      } else {
        const structure = config.structure || config
        const widths = config.widths || (typeof structure === 'string' ? structure.split('-').map(() => 1) : [1])
        widths.forEach((width: number) => {
          const col = builder.insertModule('column', row.id)
          if (col) {
            builder.updateModuleSettings(col.id, { flexGrow: width })
          }
        })
      }
    }
  }

  if (layout.rows) {
      layout.rows.forEach((rowConfig: any) => createSingleRow(rowConfig))
  } else {
      createSingleRow(layout)
  }
  showInsertRowModal.value = false
}

const updateRow = (layout: any) => {
    if (!insertRowTargetId.value) return
    const success = builder.updateRowLayout(insertRowTargetId.value, layout)
    if (!success) {
        toast.error.action('Could not update row layout')
    }
    showInsertRowModal.value = false
}

const handleResponsiveUpdate = (updates: any) => {
  if (builder.responsiveModal.value) {
    const moduleId = builder.responsiveModal.value.module.id
    builder.updateModuleSettings(moduleId, updates)
  }
}

const handleSectionInserted = () => {
  showInsertSectionModal.value = false
}

const handleClose = () => {
    emit('close')
}

const handleKeydown = (e: KeyboardEvent) => {
  if (['INPUT', 'TEXTAREA', 'SELECT'].includes((e.target as HTMLElement).tagName) || (e.target as HTMLElement).isContentEditable) {
    return
  }

  if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'z') {
    e.preventDefault()
    if (e.shiftKey) {
      builder.redo()
    } else {
      builder.undo()
    }
  }
  
  if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 's') {
    e.preventDefault()
    emit('save', null)
  }
}

import { useToast } from '@/composables/useToast'
import { useI18n } from 'vue-i18n'

const toast = useToast()
const { t } = useI18n()

const handleDeleteModule = async (id: string) => {
    const confirmed = await builder.confirm({
        title: t('builder.modals.confirm.deleteModule'),
        message: t('builder.modals.confirm.deleteModuleDesc'),
        confirmText: t('builder.modals.confirm.delete'),
        cancelText: t('builder.modals.confirm.cancel'),
        type: 'delete'
    })
    if (confirmed) {
        builder.removeModule(id)
    }
}

const handleSave = async (status: string | null = null) => {
  emit('save', status)
  builder.markAsSaved()
}

const canvasAreaRef = ref<HTMLElement | null>(null)
let resizeObserver: ResizeObserver | null = null

onMounted(async () => {
    window.addEventListener('keydown', handleKeydown)
    builder.loadTheme()
    builder.fetchMetadata()
    
    if (canvasAreaRef.value) {
        resizeObserver = new ResizeObserver((entries) => {
            if (builder.deviceModeType.value !== 'auto') return

            for (let entry of entries) {
                const width = entry.contentRect.width
                let newDevice: 'desktop' | 'tablet' | 'mobile' = 'desktop'
                if (width < 768) {
                    newDevice = 'mobile'
                } else if (width < 1024) {
                    newDevice = 'tablet'
                }
                if (builder.device.value !== newDevice) {
                    builder.device.value = newDevice
                }
            }
        })
        resizeObserver.observe(canvasAreaRef.value)
    }

    if (props.contentId) {
      try {
        await builder.loadContent(props.contentId)
      } catch (err) {
        toast.error.load('Failed to load content')
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
    moduleId: null as string | null,
    title: '',
    type: '',
    mode: 'module'
})

const openContextMenu = (moduleId: string, event: MouseEvent, title = '', type = '', mode = 'module') => {
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
const showIconPickerModal = ref(false)
const iconPickerValue = ref('')
const onIconSelectCallback = ref<((icon: string) => void) | null>(null)
const activeCanvasForModal = ref<string | null>(null)

const getCanvasById = (id: string | null): ICanvas | null => {
    if (!id || !builder || !builder.canvases.value) return null
    return builder.canvases.value.find((c: ICanvas) => c.id === id) || null
}

const activeCanvasData = computed(() => {
    return getCanvasById(activeCanvasForModal.value)
})

const handleContextMenuAction = async (action: string, id: string, mode = 'module') => {
    if (mode === 'canvas') {
        handleCanvasAction(action, id)
        return
    }

    switch (action) {
        case 'undo': builder.undo(); break
        case 'redo': builder.redo(); break
        case 'duplicate': builder.duplicateModule(id); break
        case 'delete': handleDeleteModule(id); break
        case 'add-element':
            insertTargetId.value = id
            insertTargetIndex.value = -1
            showInsertModal.value = true
            break
        case 'copy': builder.copyModule(id); break
        case 'paste': builder.pasteModule(id); break
        case 'copy-style': builder.copyStyles(id); break
        case 'paste-style': builder.pasteStyles(id); break
        case 'reset-styles': builder.resetModuleStyles?.(id); break
        case 'parent':
            const parent = builder.findParentById(builder.blocks.value, id)
            if (parent) {
                builder.selectModule(parent.id)
            }
            break
        case 'go-to-layer':
            activePanel.value = 'layers'
            setTimeout(() => {
                const layerEl = document.querySelector(`[data-layer-id="${id}"]`)
                if (layerEl) {
                    layerEl.scrollIntoView({ behavior: 'smooth', block: 'center' })
                    layerEl.classList.add('layer-highlight')
                    setTimeout(() => layerEl.classList.remove('layer-highlight'), 1500)
                }
            }, 100)
            break
        case 'rename':
            const currentModule = builder.findModule(id)
            const newLabel = await builder.prompt({
                title: t('builder.contextMenu.renameLabel'),
                initialValue: currentModule?.settings?._label || '',
                confirmText: 'OK',
                cancelText: 'Cancel'
            })
            if (newLabel !== null && newLabel !== '') {
                builder.updateModuleSettings(id, { _label: newLabel })
            }
            break
        case 'toggle-visibility':
            const module = builder.findModule(id)
            if (module) {
                const isDisabled = module.settings?.disabled === true
                builder.updateModuleSettings(id, { disabled: !isDisabled })
            }
            break
        case 'save-to-library':
            builder.openSavePresetModal?.(id)
            break
    }
}

const handleCanvasAction = (action: string, id: string) => {
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

const handleAddCanvas = (data: { title: string }) => {
    builder.addCanvas(data.title)
    showAddCanvasModal.value = false
}

const handleExportCanvas = (_data: any) => {
    if (activeCanvasForModal.value) {
        builder.exportCanvas(activeCanvasForModal.value)
    }
    showImportExportModal.value = false
}

const handleImportCanvas = (_data: any) => {
    showImportExportModal.value = false
}

const handleSaveCanvasSettings = (data: { title: string, isGlobal: boolean, append: boolean }) => {
    if (activeCanvasForModal.value) {
        builder.renameCanvas(activeCanvasForModal.value, data.title)
        const canvas = getCanvasById(activeCanvasForModal.value)
        if (canvas) {
            (canvas as any).isGlobal = data.isGlobal;
            (canvas as any).append = data.append;
        }
    }
    showCanvasSettingsModal.value = false
}

const handleIconSelect = (iconName: string) => {
    if (onIconSelectCallback.value) {
        onIconSelectCallback.value(iconName)
    }
}

builder.openIconPickerModal = (value: string, onSelect: (icon: string) => void) => {
    iconPickerValue.value = value
    onIconSelectCallback.value = onSelect
    showIconPickerModal.value = true
}

builder.openAddCanvasModal = () => { showAddCanvasModal.value = true }
builder.openImportExportModal = (id: string) => { 
    activeCanvasForModal.value = id
    showImportExportModal.value = true 
}

builder.openInsertModal = openInsertModal
builder.openInsertRowModal = openInsertRowModal
builder.openUpdateRowModal = openUpdateRowModal
builder.openInsertSectionModal = openInsertSectionModal
builder.openStructureTemplateModal = openStructureTemplateModal
builder.openContextMenu = openContextMenu
</script>

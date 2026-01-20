<template>
  <div 
    v-if="visible" 
    class="context-menu"
    :style="menuStyle"
    @click.stop
  >
    <div class="menu-header" v-if="title">
        <span>{{ title }}</span>
        <span class="menu-type">{{ type }}</span>
    </div>
    <div class="menu-divider" v-if="title"></div>
    
    <!-- Module Mode Items -->
    <template v-if="mode === 'module'">
      <!-- Undo/Redo -->
      <div class="menu-item" :class="{ 'menu-item--disabled': !canUndo }" @click="handleAction('undo')">
         <Undo2 :size="14" />
         <span>{{ t('builder.contextMenu.undo') }}</span>
         <span class="shortcut">⌘Z</span>
      </div>
      <div class="menu-item" :class="{ 'menu-item--disabled': !canRedo }" @click="handleAction('redo')">
         <Redo2 :size="14" />
         <span>{{ t('builder.contextMenu.redo') }}</span>
         <span class="shortcut">⇧⌘Z</span>
      </div>

      <div class="menu-divider"></div>
      
      <!-- Module Actions -->
      <div class="menu-item" @click="handleAction('duplicate')">
         <CopyPlus :size="14" />
         <span>{{ t('builder.contextMenu.duplicate', { type: typeLabel }) }}</span>
         <span class="shortcut">⌘D</span>
      </div>

      <div class="menu-item menu-item--danger" @click="handleAction('delete')">
         <Trash2 :size="14" />
         <span>{{ t('builder.contextMenu.delete', { type: typeLabel }) }}</span>
         <span class="shortcut">Del</span>
      </div>

      <!-- Add Element (for containers only) -->
      <div v-if="isContainer" class="menu-item" @click="handleAction('add-element')">
         <Plus :size="14" />
         <span>{{ t('builder.contextMenu.addElement') }}</span>
         <ChevronRight :size="12" class="submenu-arrow" />
      </div>

      <div class="menu-divider"></div>

      <!-- Copy/Paste -->
      <div class="menu-item" @click="handleAction('copy')">
         <Copy :size="14" />
         <span>{{ t('builder.contextMenu.copy', { type: typeLabel }) }}</span>
         <span class="shortcut">⌘C</span>
      </div>

      <div class="menu-item" :class="{ 'menu-item--disabled': !hasClipboard }" @click="handleAction('paste')">
         <ClipboardPaste :size="14" />
         <span>{{ t('builder.contextMenu.paste') }}</span>
         <span class="shortcut">⌘V</span>
      </div>

      <div class="menu-divider"></div>

      <!-- Styles -->
      <div class="menu-item" @click="handleAction('copy-style')">
         <Palette :size="14" />
         <span>{{ t('builder.contextMenu.copyStyles') }}</span>
      </div>
      
      <div class="menu-item" :class="{ 'menu-item--disabled': !hasStyleClipboard }" @click="handleAction('paste-style')">
         <PaintBucket :size="14" />
         <span>{{ t('builder.contextMenu.pasteStyles') }}</span>
      </div>

      <div class="menu-item" @click="handleAction('reset-styles')">
         <RotateCcw :size="14" />
         <span>{{ t('builder.contextMenu.resetStyles') }}</span>
      </div>

      <div class="menu-divider"></div>
      
      <!-- Navigation -->
      <div v-if="hasParent" class="menu-item" @click="handleAction('parent')">
         <ArrowUpLeft :size="14" />
         <span>{{ t('builder.contextMenu.goToParent') }}</span>
      </div>

      <div class="menu-item" @click="handleAction('go-to-layer')">
         <Layers :size="14" />
         <span>{{ t('builder.contextMenu.goToLayer') }}</span>
      </div>

      <div class="menu-divider"></div>

      <!-- Settings -->
      <div class="menu-item" @click="handleAction('rename')">
         <Type :size="14" />
         <span>{{ t('builder.contextMenu.renameLabel') }}</span>
      </div>

      <div class="menu-item" @click="handleAction('toggle-visibility')">
         <component :is="isDisabled ? Eye : EyeOff" :size="14" />
         <span>{{ isDisabled ? t('builder.contextMenu.enable') : t('builder.contextMenu.disable') }}</span>
      </div>

      <div v-if="canSaveToLibrary" class="menu-item" @click="handleAction('save-to-library')">
         <BookmarkPlus :size="14" />
         <span>{{ t('builder.contextMenu.saveToLibrary') }}</span>
      </div>
    </template>

    <!-- Canvas Mode Items -->
    <template v-else-if="mode === 'canvas'">
      <div class="menu-item" @click="handleAction('canvas-settings')">
        <Settings :size="14" />
        <span>{{ t('builder.contextMenu.canvasSettings') }}</span>
      </div>
      <div class="menu-item" @click="handleAction('export-canvas')">
        <Download :size="14" />
        <span>{{ t('builder.contextMenu.exportCanvas') }}</span>
      </div>
      
      <div class="menu-divider"></div>
      
      <div class="menu-item" @click="handleAction('edit-canvas')">
        <Edit :size="14" />
        <span>{{ t('builder.contextMenu.editCanvas') }}</span>
      </div>
      <div v-if="!isMainCanvas" class="menu-item" @click="handleAction('make-main-canvas')">
        <CheckCircle :size="14" />
        <span>{{ t('builder.contextMenu.makeMainCanvas') }}</span>
      </div>
      <div class="menu-item" @click="handleAction('duplicate-canvas')">
        <Copy :size="14" />
        <span>{{ t('builder.contextMenu.duplicateCanvas') }}</span>
      </div>
      <div v-if="!isMainCanvas" class="menu-item menu-item--danger" @click="handleAction('delete-canvas')">
        <Trash2 :size="14" />
        <span>{{ t('builder.contextMenu.deleteCanvas') }}</span>
      </div>
    </template>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, computed, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import { 
  Copy, CopyPlus, Trash2, Type, ClipboardPaste, Undo2, Redo2,
  ArrowUpLeft, Settings, Download, Edit, CheckCircle, Plus, ChevronRight,
  Palette, PaintBucket, RotateCcw, Layers, Eye, EyeOff, BookmarkPlus
} from 'lucide-vue-next'

const { t } = useI18n()
const builder = inject('builder')

const props = defineProps({
    visible: Boolean,
    x: Number,
    y: Number,
    moduleId: String,
    title: String,
    type: String,
    mode: {
      type: String,
      default: 'module'
    }
})

const emit = defineEmits(['close', 'action'])

// Computed properties for menu state
const canUndo = computed(() => builder?.canUndo ?? false)
const canRedo = computed(() => builder?.canRedo ?? false)
const hasClipboard = computed(() => !!builder?.clipboard)
const hasStyleClipboard = computed(() => !!builder?.styleClipboard)
const isMainCanvas = computed(() => props.type === 'Main')
const typeLabel = computed(() => props.type || 'Module')

const isContainer = computed(() => {
    const containerTypes = ['section', 'row', 'column']
    return containerTypes.includes(props.type?.toLowerCase())
})

const hasParent = computed(() => {
    if (!props.moduleId || !builder) return false
    const parent = builder.findParentById?.(builder.blocks, props.moduleId)
    return !!parent
})

const canSaveToLibrary = computed(() => {
    const libraryTypes = ['section', 'row']
    return libraryTypes.includes(props.type?.toLowerCase())
})

const isDisabled = computed(() => {
    if (!props.moduleId || !builder) return false
    const module = builder.findModuleById?.(builder.blocks, props.moduleId)
    return module?.settings?.disabled === true
})

// Dynamic positioning to prevent overflow
const menuStyle = computed(() => {
    const style = {
        top: `${props.y}px`,
        left: `${props.x}px`
    }
    
    // Adjust if menu would go off-screen (basic check)
    if (typeof window !== 'undefined') {
        const menuWidth = 220
        const menuHeight = 400
        
        if (props.x + menuWidth > window.innerWidth) {
            style.left = `${props.x - menuWidth}px`
        }
        if (props.y + menuHeight > window.innerHeight) {
            style.top = `${Math.max(10, window.innerHeight - menuHeight - 10)}px`
        }
    }
    
    return style
})

// Close on click outside
const handleClickOutside = (e) => {
    if (props.visible) {
        emit('close')
    }
}

const handleAction = (action) => {
    // Prevent disabled actions
    if (action === 'undo' && !canUndo.value) return
    if (action === 'redo' && !canRedo.value) return
    if (action === 'paste' && !hasClipboard.value) return
    if (action === 'paste-style' && !hasStyleClipboard.value) return
    
    emit('action', action, props.moduleId, props.mode)
    emit('close')
}

onMounted(() => {
    window.addEventListener('click', handleClickOutside, { capture: true })
    window.addEventListener('contextmenu', handleClickOutside) 
})

onUnmounted(() => {
    window.removeEventListener('click', handleClickOutside, { capture: true })
    window.removeEventListener('contextmenu', handleClickOutside)
})
</script>

<style scoped>
.context-menu {
    position: fixed;
    z-index: 9999;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    box-shadow: var(--shadow-xl);
    border-radius: var(--border-radius-md);
    padding: 4px;
    min-width: 220px;
    max-width: 280px;
    animation: fadeIn 0.1s ease-out;
}

.menu-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 12px;
    font-size: 11px;
    font-weight: 600;
    color: var(--builder-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: var(--builder-bg-secondary);
    border-radius: 4px;
    margin-bottom: 2px;
}

.menu-type {
    font-size: 9px;
    opacity: 0.7;
    background: var(--builder-border);
    padding: 1px 4px;
    border-radius: 2px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    font-size: 13px;
    color: var(--builder-text-primary);
    cursor: pointer;
    border-radius: 4px;
    transition: background 0.1s;
}

.menu-item:hover:not(.menu-item--disabled) {
    background: var(--builder-bg-tertiary);
    color: var(--builder-accent);
}

.menu-item--disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.menu-item--danger:hover:not(.menu-item--disabled) {
    background: rgba(239, 68, 68, 0.1);
    color: var(--builder-error);
}

.menu-divider {
    height: 1px;
    background: var(--builder-border);
    margin: 4px 0;
}

.shortcut {
    margin-left: auto;
    font-size: 10px;
    color: var(--builder-text-muted);
    opacity: 0.7;
}

.submenu-arrow {
    margin-left: auto;
    opacity: 0.5;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>

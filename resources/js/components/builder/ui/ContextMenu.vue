<template>
  <div 
    v-if="visible" 
    class="context-menu"
    :style="{ top: `${y}px`, left: `${x}px` }"
    @click.stop
  >
    <div class="menu-header" v-if="title">
        <span>{{ title }}</span>
        <span class="menu-type">{{ type }}</span>
    </div>
    <div class="menu-divider" v-if="title"></div>
    
    <!-- Module Mode Items -->
    <template v-if="mode === 'module'">
      <div class="menu-item" @click="handleAction('rename')">
         <Type :size="14" />
         <span>Rename Label</span>
      </div>
      
      <div class="menu-divider"></div>

      <div class="menu-item" @click="handleAction('copy')">
         <Copy :size="14" />
         <span>Copy Module</span>
         <span class="shortcut">⌘C</span>
      </div>

      <div class="menu-item" @click="handleAction('paste')">
         <ClipboardPaste :size="14" />
         <span>Paste Module</span>
         <span class="shortcut">⌘V</span>
      </div>

      <div class="menu-divider"></div>

      <div class="menu-item" @click="handleAction('duplicate')">
         <CopyPlus :size="14" />
         <span>Duplicate</span>
         <span class="shortcut">⌘D</span>
      </div>

      <div class="menu-item" @click="handleAction('delete')">
         <Trash2 :size="14" />
         <span>Delete</span>
         <span class="shortcut">Del</span>
      </div>

      <div class="menu-divider"></div>
      
      <div class="menu-item" @click="handleAction('copy-style')">
         <ClipboardCopy :size="14" />
         <span>Copy Styles</span>
      </div>
      
      <div class="menu-item" @click="handleAction('paste-style')">
         <Brush :size="14" />
         <span>Paste Styles</span>
      </div>

      <div class="menu-divider"></div>
      
      <div class="menu-item" @click="handleAction('parent')">
         <ArrowUpLeft :size="14" />
         <span>Go to Parent</span>
      </div>
    </template>

    <!-- Canvas Mode Items -->
    <template v-else-if="mode === 'canvas'">
      <div class="menu-item" @click="handleAction('canvas-settings')">
        <Settings :size="14" />
        <span>Canvas Settings</span>
      </div>
      <div class="menu-item" @click="handleAction('export-canvas')">
        <Download :size="14" />
        <span>Export Canvas</span>
      </div>
      
      <div class="menu-divider"></div>
      
      <div class="menu-item" @click="handleAction('edit-canvas')">
        <Edit :size="14" />
        <span>Edit Canvas</span>
      </div>
      <div v-if="!isMainCanvas" class="menu-item" @click="handleAction('make-main-canvas')">
        <CheckCircle :size="14" />
        <span>Make Main Canvas</span>
      </div>
      <div class="menu-item" @click="handleAction('duplicate-canvas')">
        <Copy :size="14" />
        <span>Duplicate Canvas</span>
      </div>
      <div v-if="!isMainCanvas" class="menu-item menu-item--danger" @click="handleAction('delete-canvas')">
        <Trash2 :size="14" />
        <span>Delete Canvas</span>
      </div>
    </template>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, computed } from 'vue'
import { 
  Copy, CopyPlus, Trash2, Type, ClipboardCopy, ClipboardPaste, 
  ArrowUpLeft, Brush, Settings, Download, Edit, CheckCircle 
} from 'lucide-vue-next'

const props = defineProps({
    visible: Boolean,
    x: Number,
    y: Number,
    moduleId: String, // Or canvasId depending on mode
    title: String,
    type: String,
    mode: {
      type: String,
      default: 'module' // module | canvas
    }
})

const emit = defineEmits(['close', 'action'])

const isMainCanvas = computed(() => props.type === 'Main')

// Close on click outside
const handleClickOutside = (e) => {
    if (props.visible) {
        emit('close')
    }
}

const handleAction = (action) => {
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
    min-width: 180px;
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

.menu-item:hover {
    background: var(--builder-bg-tertiary);
    color: var(--builder-accent);
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

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>

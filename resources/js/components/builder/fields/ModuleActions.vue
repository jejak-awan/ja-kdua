<template>
  <div class="module-actions" :class="{ 'is-active': showMenu }">
    
    <!-- Drag Handle -->
    <div v-if="showDrag" class="action-icon drag-handle" :title="$t('builder.items.drag')">
        <GripVertical :size="size" />
    </div>

    <!-- Delete -->
    <div v-if="showDelete" class="action-icon delete-btn" :title="$t('builder.items.delete')" @click.stop="$emit('delete')">
        <Trash :size="size" />
    </div>

    <!-- Duplicate -->
    <div v-if="showDuplicate" class="action-icon" :title="$t('builder.items.duplicate')" @click.stop="$emit('duplicate')">
        <Copy :size="size" />
    </div>

    <!-- Layout -->
    <div v-if="showLayout" class="action-icon" :title="$t('builder.items.layout')" @click.stop="$emit('layout')">
        <LayoutTemplate :size="size" />
    </div>

    <!-- Info/Help -->
    <div v-if="showInfo" class="action-icon" :class="{ 'is-active': infoActive }" :title="$t('builder.items.info')" @click.stop="$emit('toggle-info')">
        <Info :size="size" />
    </div>

    <!-- More Options -->
    <div v-if="showMore" class="action-icon relative">
         <MoreVertical 
           ref="moreIconRef"
           :size="size" 
           class="cursor-pointer" 
           :class="{ 'text-accent': showMenu }"
           @click.stop="toggleMenu" 
         />
         
         <Teleport to="body">
            <div v-if="showMenu" class="field-menu context-menu" v-click-outside="closeMenu" :style="dropdownStyle">
                <div class="menu-item disabled" @click="handleAction('copy')">{{ $t('builder.fields.actions.copy') }}</div>
                <div class="menu-divider"></div>
                <div class="menu-item" @click="handleAction('savePreset')">{{ $t('builder.fields.actions.savePreset') }}</div>
                <div class="menu-divider"></div>
                <div class="menu-item" @click="handleAction('reset')">{{ $t('builder.fields.actions.reset') }}</div>
            </div>
         </Teleport>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Pencil, Copy, Trash, MoreVertical, Info, GripVertical, LayoutTemplate } from 'lucide-vue-next'

const props = defineProps({
  label: { type: String, default: 'Item' },
  size: { type: Number, default: 14 },
  showEdit: { type: Boolean, default: true },
  showLayout: { type: Boolean, default: false },
  showDuplicate: { type: Boolean, default: true },
  showDelete: { type: Boolean, default: true },
  showInfo: { type: Boolean, default: false },
  showMore: { type: Boolean, default: true },
  showDrag: { type: Boolean, default: false },
  infoActive: { type: Boolean, default: false }
})

const emit = defineEmits(['edit', 'layout', 'duplicate', 'delete', 'toggle-info', 'more-action'])

const showMenu = ref(false)
const moreIconRef = ref(null)
const dropdownStyle = ref({})

const toggleMenu = (e) => {
    showMenu.value = !showMenu.value
    if (showMenu.value) {
        updateDropdownPosition(moreIconRef.value?.$el || moreIconRef.value)
    }
}

const closeMenu = () => {
    showMenu.value = false
}

const updateDropdownPosition = (anchorEl) => {
    if (!anchorEl) return
    const rect = anchorEl.getBoundingClientRect()
    dropdownStyle.value = {
        position: 'fixed',
        top: `${rect.bottom + 4}px`,
        right: `${window.innerWidth - rect.right}px`,
        zIndex: 99999,
        background: 'var(--builder-bg-primary)',
        border: '1px solid var(--builder-border)',
        borderRadius: '4px',
        boxShadow: 'var(--shadow-lg)',
        padding: '4px 0',
        width: '240px'
    }
}

const handleAction = (action) => {
    emit('more-action', action)
    closeMenu()
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
</script>

<style scoped>
.module-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.action-icon {
    color: var(--builder-text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2px;
    transition: all 0.15s;
    border-radius: 4px;
}

.action-icon:hover,
.action-icon.is-active {
    color: var(--builder-accent);
    background: rgba(var(--builder-accent-rgb), 0.1);
}

.delete-btn:hover {
    color: #ef4444 !important;
    background: rgba(239, 68, 68, 0.1) !important;
}

/* Dropdown Menu - Styled similar to FieldActions */
.context-menu {
    display: flex;
    flex-direction: column;
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
}

.menu-divider {
    height: 1px;
    background-color: var(--builder-border);
    margin: 4px 0;
}
</style>

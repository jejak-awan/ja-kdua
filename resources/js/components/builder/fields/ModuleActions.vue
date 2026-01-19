<template>
  <div class="module-actions">
    
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
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Pencil, Copy, Trash, GripVertical, LayoutTemplate } from 'lucide-vue-next'

defineProps({
  label: { type: String, default: 'Item' },
  size: { type: Number, default: 14 },
  showEdit: { type: Boolean, default: true },
  showLayout: { type: Boolean, default: false },
  showDuplicate: { type: Boolean, default: true },
  showDelete: { type: Boolean, default: true },
  showDrag: { type: Boolean, default: false }
})

defineEmits(['edit', 'layout', 'duplicate', 'delete'])
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

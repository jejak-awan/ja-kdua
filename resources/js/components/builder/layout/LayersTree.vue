<template>
  <draggable 
    :list="filteredBlocks" 
    item-key="id"
    group="layers"
    tag="ul"
    class="layers-list"
    ghost-class="ja-builder-ghost"
    drag-class="layer-drag"
    handle=".layer-row"
    :animation="200"
  >
    <template #item="{ element: block }">
      <li class="layer-item">
        <!-- Item Content -->
        <div 
          class="layer-row" 
          :data-layer-id="block.id"
          :class="{ 
            'layer-row--selected': selectedId === block.id,
            [`layer-row--${block.type}`]: true
          }"
          @click.stop="$emit('select', block.id)"
          @contextmenu.stop.prevent="handleContextMenu($event, block)"
        >
          <!-- Toggle Collapse -->
          <button 
            v-if="block.children && block.children.length > 0"
            class="layer-toggle"
            @click.stop="toggleExpand(block.id)"
          >
            <component 
              :is="isExpanded(block.id) ? icons.ChevronDown : icons.ChevronRight" 
              :size="12" 
            />
          </button>
          <span v-else class="layer-spacer"></span>
          
          <!-- Icon -->
          <span class="layer-icon">
               <component :is="getIcon(block.type)" :size="12" />
          </span>
          
          <!-- Name -->
          <span class="layer-name">{{ getTitle(block) }}</span>

          <!-- Meatballs Menu (Context Actions) -->
          <button class="layer-actions-trigger" @click.stop="handleContextMenu($event, block)" @contextmenu.stop.prevent="handleContextMenu($event, block)" @mousedown.stop>
            <component :is="icons.MoreVertical" :size="14" />
          </button>
        </div>
        
        <!-- Children (Recursive) -->
        <div v-if="block.children && block.children.length > 0 && isExpanded(block.id)" class="layer-children">
          <LayersTree 
            :blocks="block.children" 
            :selected-id="selectedId" 
            :search-term="searchTerm"
            :collapse-signal="collapseSignal"
            @select="$emit('select', $event)"
          />
        </div>
      </li>
    </template>
  </draggable>
</template>

<script setup>
import { ref, inject, computed, watch } from 'vue'
import draggable from 'vuedraggable'
import { useI18n } from 'vue-i18n'
import { 
    ChevronRight, ChevronDown, Layout, Columns, Box, 
    Type, Image, Square, MoreVertical 
} from 'lucide-vue-next'
import ModuleRegistry from '../core/ModuleRegistry'

// CRITICAL: Define name for recursion
defineOptions({
  name: 'LayersTree'
})

const icons = { ChevronRight, ChevronDown, MoreVertical }
const builder = inject('builder')

const props = defineProps({
  blocks: {
    type: Array,
    required: true
  },
  selectedId: {
    type: String,
    default: null
  },
  searchTerm: {
    type: String,
    default: ''
  },
  collapseSignal: {
    type: Number,
    default: 0
  }
})

defineEmits(['select'])

const { t, te } = useI18n()

// Expanded state
const expanded = ref({})

const toggleExpand = (id) => {
  expanded.value[id] = !expanded.value[id]
}

const isExpanded = (id) => {
  return expanded.value[id] !== false
}

// Watch for collapse signal from parent
watch(() => props.collapseSignal, () => {
    expanded.value = {} // Reset all to non-existent (default False in my logic? No, wait)
    // Actually, isExpanded returns true if not false. 
    // To collapse all, we need to set all to false.
    props.blocks.forEach(block => {
        expanded.value[block.id] = false
    })
})

const getTitle = (block) => {
  if (block.settings?.admin_label) return block.settings.admin_label
  if (te(`builder.modules.${block.type}`)) return t(`builder.modules.${block.type}`)
  const def = ModuleRegistry.get(block.type)
  return def?.title || block.type
}

const getIcon = (type) => {
    if (type === 'section') return Layout
    if (type === 'row') return Columns
    if (type === 'column') return Square
    if (type.includes('text') || type.includes('heading')) return Type
    if (type.includes('image')) return Image
    return Box
}

const handleContextMenu = (e, block) => {
    if (builder?.openContextMenu) {
        builder.openContextMenu(block.id, e, getTitle(block), block.type)
    }
}

// Filtering
const filteredBlocks = computed(() => {
    if (!props.searchTerm) return props.blocks
    
    const term = props.searchTerm.toLowerCase()
    return props.blocks.filter(block => {
        const titleMatch = getTitle(block).toLowerCase().includes(term)
        const typeMatch = block.type.toLowerCase().includes(term)
        
        // Also check children recursively (if any child matches, parent should show)
        const hasMatchingChild = block.children?.some(child => {
            // Simplified check for depth 1, the component itself handles deeper recursion
            return getTitle(child).toLowerCase().includes(term) || child.type.toLowerCase().includes(term)
        })

        return titleMatch || typeMatch || hasMatchingChild
    })
})
</script>

<style scoped>
.layers-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.layer-item {
  margin-bottom: 1px;
}

.layer-row {
  display: flex;
  align-items: center;
  padding: 4px 8px;
  cursor: pointer;
  border-radius: 4px;
  font-size: 13px;
  font-weight: 500;
  color: var(--builder-text-primary);
  transition: background 0.1s;
  position: relative;
}

.layer-row:not(.layer-row--selected):hover {
  background: var(--builder-bg-tertiary);
  color: var(--builder-accent) !important;
}

.layer-name {
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-right: 20px;
}

/* Color Coding based on Vibrant Hierarchy */
.layer-row--section { color: var(--builder-section); }
.layer-row--row { color: var(--builder-row); }
.layer-row--column { color: var(--builder-column); }
.layer-row--module,
.layer-row--text,
.layer-row--heading,
.layer-row--image,
.layer-row--button,
.layer-row--divider,
.layer-row--code,
.layer-row--gallery { 
    color: var(--builder-module); 
}

/* Unselected rows base state */
.layer-row:not(.layer-row--selected) {
    background: transparent;
}

/* Icons inherit color */
.layer-icon {
  margin-right: 8px;
  display: flex;
  align-items: center;
}

/* Selected State - Solid colored background with white text */
.layer-row--selected {
    background: var(--builder-module); /* Default background for all modules */
    color: #ffffff !important;
}

.layer-row--selected.layer-row--section {
    background: var(--builder-section);
}
.layer-row--selected.layer-row--row {
    background: var(--builder-row);
}
.layer-row--selected.layer-row--column {
    background: var(--builder-column);
}

.layer-row--selected .layer-name,
.layer-row--selected .layer-toggle,
.layer-row--selected .layer-icon,
.layer-row--selected .layer-actions-trigger {
    color: #ffffff !important;
}

.layer-toggle {
  width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 2px;
  background: none;
  border: none;
  color: inherit;
  cursor: pointer;
  opacity: 0.6;
}
.layer-toggle:hover {
    opacity: 1;
}

.layer-spacer {
    width: 18px;
}

.layer-actions-trigger {
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    padding: 6px;
    margin: -4px;
    opacity: 0;
    transition: opacity 0.1s;
    position: absolute;
    right: 8px;
    display: flex;
    align-items: center;
    z-index: 10;
}

.layer-row:hover .layer-actions-trigger,
.layer-row--selected .layer-actions-trigger {
    opacity: 0.8;
}

.layer-actions-trigger:hover {
    opacity: 1 !important;
}

.layer-children {
  padding-left: 14px;
  margin-left: 7px;
  border-left: 1px solid rgba(0,0,0,0.05);
}

.ja-builder--dark .layer-children {
    border-left-color: rgba(255,255,255,0.05);
}

.layer-ghost {
    opacity: 0.4;
    background: var(--builder-bg-tertiary) !important;
}

.layer-drag {
    background: var(--builder-accent) !important; 
    color: white !important;
    box-shadow: var(--shadow-lg);
}
</style>

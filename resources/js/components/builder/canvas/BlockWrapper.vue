<template>
    <div 
        class="group/block relative border-2 border-transparent transition-all"
        :class="[
            !builder.isPreview.value ? 'hover:border-primary cursor-pointer' : '',
            isSelected && !builder.isPreview.value ? 'border-primary ring-4 ring-primary/20 z-10' : ''
        ]"
        @click.stop="!builder.isPreview.value && onEdit()"
        @contextmenu.prevent="!builder.isPreview.value && onContextMenu()"
    >
        <!-- Block Toolbar (Elementor/Divi Style) -->
        <div v-if="!builder.isPreview.value" class="absolute top-1 left-1 opacity-0 group-hover/block:opacity-100 transition-all z-[30] flex items-center gap-0.5 bg-white text-zinc-950 border border-zinc-200 rounded-md px-1 py-1 shadow-lg scale-95 origin-top-left"
             :class="{ 'opacity-100': isSelected }">
            <GripVertical class="w-3 h-3 cursor-move drag-handle mx-0.5" />
            <div class="w-px h-3 bg-zinc-200 mx-1"></div>
            <button class="h-6 w-6 flex items-center justify-center hover:bg-zinc-100 rounded-full transition-colors" @click.stop="onEdit" title="Settings">
                <Settings2 class="w-3 h-3" />
            </button>
            <button class="h-6 w-6 flex items-center justify-center hover:bg-zinc-100 rounded-full transition-colors" @click.stop="onDuplicate" :title="t('features.builder.properties.tooltips.duplicate')">
                <Copy class="w-3 h-3" />
            </button>
            <div class="w-px h-3 bg-zinc-200 mx-1"></div>
            <button class="h-6 w-6 flex items-center justify-center hover:bg-zinc-100 rounded-full transition-colors" @click.stop="emit('wrap')" title="Wrap in Container">
                <Box class="w-3 h-3" />
            </button>
            <button class="h-6 w-6 flex items-center justify-center hover:bg-zinc-100 rounded-full transition-colors" @click.stop="emit('split')" title="Split (Add Column)">
                <Columns class="w-3 h-3" />
            </button>
            <div class="w-px h-3 bg-zinc-200 mx-1"></div>
            <button class="h-6 w-6 flex items-center justify-center hover:bg-zinc-100 rounded-full transition-colors" @click.stop="onDelete" :title="t('features.builder.properties.tooltips.delete')">
                <Trash2 class="w-3 h-3" />
            </button>
        </div>

        <!-- Live Rendering of Block -->
        <div class="relative">
            <BlockRenderer :blocks="[block]" :context="context" class="builder-render" />
            <!-- Overlay to capture clicks (prevents clicking links inside blocks) -->
            <!-- Uses pointer-events-none so drag-drop passes through to nested components -->
            <div v-if="!builder.isPreview.value" class="absolute inset-0 z-[5] pointer-events-none"></div>
        </div>
    </div>
</template>

<script setup>
import { computed, inject, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { GripVertical, Copy, Trash2, Settings2, Box, Columns } from 'lucide-vue-next';
import BlockRenderer from '../blocks/BlockRenderer.vue';

const props = defineProps({
    block: { type: Object, required: true },
    index: { type: Number, required: true },
    context: { type: Object, default: () => ({}) },
    isNested: { type: Boolean, default: false } // To handle logic diffs if needed
});

const emit = defineEmits(['edit', 'delete', 'duplicate', 'wrap', 'split']);

const builder = inject('builder');
const { t } = useI18n();

// Determine if this block is currently selected
const isSelected = computed(() => {
    if (!builder) return false;
    
    // If nested, we might need a unique ID check instead of index
    if (props.isNested) {
         return builder.activeBlockId.value === props.block.id;
    }
    return builder.editingIndex.value === props.index;
});

const onEdit = () => {
    if (props.isNested) {
        // If nested, use ID based selection? 
        // We need to update builder to support ID-based selection or path-based.
        // For now, let's just set the ID and open properties
        builder.activeBlockId.value = props.block.id;
        builder.activeTab.value = 'content';
        // We might lose 'index' context for "Layers" tab properties in nested mode.
        // But Properties Panel largely works on 'selectedBlock' computed.
        
        // Hack: If we rely on editingIndex, it breaks for nested. 
        // We need to update PropertiesPanel to find block by ID if editingIndex is null?
        // Or we pass a special "path" index?
    } else {
        builder.editingIndex.value = props.index;
        builder.activeTab.value = 'content';
        builder.activeBlockId.value = props.block.id;
    }
    
    emit('edit');
};

const onDelete = () => {
    emit('delete');
};

const onDuplicate = () => {
    emit('duplicate');
};

const onContextMenu = (e) => {
    if (!builder) return;
    builder.contextMenu.value = {
        visible: true,
        x: e.clientX,
        y: e.clientY,
        type: props.block.type,
        index: props.index,
        blockId: props.block.id
    };
};
</script>

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
        <!-- Block Toolbar (Elementor/Divi Style) - Show on hover OR when selected -->
        <div v-if="!builder.isPreview.value" class="absolute top-1 left-1 opacity-0 transition-all z-[30] flex items-center gap-0.5 bg-white text-zinc-950 border border-zinc-200 rounded-md px-1 py-1 shadow-lg scale-95 origin-top-left group-hover/block:opacity-100 group-hover/block:scale-100"
             :class="{ 'opacity-100 scale-100': isSelected }">
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
        <ResizableWrapper
            :is-active="isSelected"
            :is-preview="builder.isPreview.value"
            :width="block.settings?.width"
            :height="isStructuralBlock ? 'auto' : block.settings?.height"
            @update:width="updateBlockSize('width', $event)"
            @update:height="updateBlockSize('height', $event)"
            @resize-end="onResizeEnd"
        >
            <div class="relative h-full">
                <BlockRenderer :blocks="[block]" :context="context" class="builder-render h-full" />
                <!-- Overlay to capture clicks (prevents clicking links inside blocks) -->
                <!-- Uses pointer-events-none so drag-drop passes through to nested components -->
                <div v-if="!builder.isPreview.value" class="absolute inset-0 z-[5] pointer-events-none"></div>
            </div>
        </ResizableWrapper>
    </div>
</template>

<script setup>
import { computed, inject, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { GripVertical, Copy, Trash2, Settings2, Box, Columns } from 'lucide-vue-next';
import BlockRenderer from '../blocks/BlockRenderer.vue';
import ResizableWrapper from './ResizableWrapper.vue';

const props = defineProps({
    block: { type: Object, required: true },
    index: { type: Number, required: true },
    context: { type: Object, default: () => ({}) },
    isNested: { type: Boolean, default: false } // To handle logic diffs if needed
});

const emit = defineEmits(['edit', 'delete', 'duplicate', 'wrap', 'split']);

const builder = inject('builder');
const { t } = useI18n();

// Determine if this block is currently selected - always use ID-based selection
const isSelected = computed(() => {
    if (!builder) return false;
    return builder.activeBlockId.value === props.block.id;
});

const onEdit = () => {
    // Always use ID-based selection for consistency
    builder.activeBlockId.value = props.block.id;
    builder.activeTab.value = 'content';
    builder.activeRightSidebarTab.value = 'properties';
    builder.isRightSidebarOpen.value = true;
    
    // Also set editingIndex for root-level blocks (backwards compatibility)
    if (!props.isNested) {
        builder.editingIndex.value = props.index;
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

const isStructuralBlock = computed(() => ['section', 'hero', 'container'].includes(props.block.type));

const updateBlockSize = (dimension, value) => {
    if (!props.block.settings) props.block.settings = {};
    
    // For structural blocks, resize maps to minHeight for vertical resizing
    if (dimension === 'height' && isStructuralBlock.value) {
        props.block.settings.minHeight = value;
        // Ensure fixed height doesn't conflict
        if (props.block.settings.height) delete props.block.settings.height;
    } else {
        props.block.settings[dimension] = value;
    }
};

const onResizeEnd = () => {
    if (builder) builder.takeSnapshot();
};

</script>

<template>
    <div 
        class="group/block relative border-2 border-transparent transition-all"
        :class="[
            !builder.isPreview.value ? 'hover:border-primary cursor-pointer' : '',
            isSelected && !builder.isPreview.value ? 'border-primary ring-4 ring-primary/20 z-10' : ''
        ]"
        @click.stop="!builder.isPreview.value && onEdit()"
        @contextmenu.prevent="!builder.isPreview.value && onContextMenu($event)"
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
            <div 
                ref="contentRef"
                class="relative"
                :class="[
                    !builder.isPreview.value && !isStructuralBlock ? 'builder-block-content' : 'h-full',
                    hasOverflow && !builder.isPreview.value ? 'has-overflow' : ''
                ]"
            >
                <BlockRenderer :blocks="[block]" :context="context" class="builder-render h-full" />
                <!-- Overlay to capture clicks (prevents clicking links inside blocks) -->
                <!-- Uses pointer-events-none so drag-drop passes through to nested components -->
                <div v-if="!builder.isPreview.value" class="absolute inset-0 z-[5] pointer-events-none"></div>
                
                <!-- Overflow Indicator -->
                <div 
                    v-if="hasOverflow && !builder.isPreview.value" 
                    class="absolute bottom-0 left-0 right-0 h-8 bg-gradient-to-t from-white dark:from-zinc-900 to-transparent pointer-events-none z-[6] flex items-end justify-center pb-1"
                >
                    <span class="text-[9px] font-bold text-muted-foreground bg-white/80 dark:bg-zinc-900/80 px-2 py-0.5 rounded-full border border-border shadow-sm">
                        ↓ Scroll for more
                    </span>
                </div>
            </div>
        </ResizableWrapper>
    </div>
</template>

<script setup>
import { computed, inject, ref, onMounted, onUnmounted, nextTick, watch } from 'vue';
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

// Overflow detection
const contentRef = ref(null);
const hasOverflow = ref(false);

const checkOverflow = () => {
    if (!contentRef.value) return;
    hasOverflow.value = contentRef.value.scrollHeight > contentRef.value.clientHeight;
};

onMounted(() => {
    nextTick(checkOverflow);
    // Recheck on window resize
    window.addEventListener('resize', checkOverflow);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkOverflow);
});

// Watch for content changes
watch(() => props.block.settings, () => {
    nextTick(checkOverflow);
}, { deep: true });

</script>

<style scoped>
.builder-block-content {
    /* Ensure blocks adapt to container width */
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
    /* Remove vertical scroll - allow natural height */
    overflow-y: visible;
}

/* For blocks that explicitly need height constraint */
.builder-block-content.has-overflow {
    max-height: 500px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: hsl(var(--muted-foreground) / 0.3) transparent;
}

.builder-block-content.has-overflow::-webkit-scrollbar {
    width: 4px;
}

.builder-block-content.has-overflow::-webkit-scrollbar-track {
    background: transparent;
}

.builder-block-content.has-overflow::-webkit-scrollbar-thumb {
    background: hsl(var(--muted-foreground) / 0.3);
    border-radius: 4px;
}

.builder-block-content.has-overflow::-webkit-scrollbar-thumb:hover {
    background: hsl(var(--muted-foreground) / 0.5);
}
</style>

<template>
    <section 
        :class="['transition-all duration-500 h-full', padding, radius, animation, isResizing ? 'cursor-col-resize select-none' : '']"
        :style="{ backgroundColor: bgColor || 'transparent' }"
        v-bind="$attrs"
    >
        <div :class="['mx-auto px-6 h-full', width]">
            <div 
                :class="['grid gap-8 h-full', layout !== 'custom' ? gridLayout : '']"
                :style="layout === 'custom' ? { gridTemplateColumns: customGridTemplate } : {}"
                ref="gridRef"
            >
                <template v-for="(column, index) in columns" :key="index">
                    <div class="column-container relative flex items-stretch">
                        <div class="flex-1 min-h-[50px] space-y-4">
                            <!-- Builder Mode -->
                            <draggable 
                                v-if="isBuilder && !isPreview"
                                v-model="column.blocks" 
                                item-key="id"
                                :group="{ name: 'blocks', pull: true, put: true }"
                                handle=".drag-handle"
                                class="min-h-[100px] h-full border-2 border-dashed border-transparent hover:border-sidebar-border/50 rounded-xl transition-colors p-2"
                                ghost-class="block-ghost"
                                :class="column.blocks.length === 0 ? 'bg-muted/30 border-muted-foreground/10' : ''"
                            >
                                <template #item="{ element: block, index: blockIndex }">
                                    <BlockWrapper 
                                        :block="block" 
                                        :index="blockIndex"
                                        :isNested="true"
                                        class="mb-2 last:mb-0"
                                        @edit="onEditBlock(block.id)"
                                        @duplicate="onDuplicate(column, blockIndex)"
                                        @delete="onDelete(column, blockIndex)"
                                        @wrap="onWrapInColumn(column, blockIndex)"
                                        @split="onSplitInColumn(column, blockIndex)"
                                    />
                                </template>
                                
                                <template #footer>
                                     <div v-if="column.blocks.length === 0" class="h-full flex flex-col items-center justify-center p-4 text-center relative z-[20]">
                                        <div 
                                            @click.stop.prevent="openBlockPicker(index)"
                                            class="flex flex-col items-center gap-2 p-3 rounded-lg border-2 border-dashed border-muted-foreground/10 hover:border-primary/50 hover:bg-primary/5 transition-all w-full h-full justify-center group/btn cursor-pointer"
                                        >
                                            <div class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center group-hover/btn:bg-primary/10 transition-colors">
                                                <Plus class="w-4 h-4 text-muted-foreground group-hover/btn:text-primary" />
                                            </div>
                                            <div>
                                                <span class="text-[10px] font-bold text-muted-foreground group-hover/btn:text-primary block">Add Block</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </draggable>

                            <!-- Live Mode -->
                            <div v-else class="h-full">
                                <BlockRenderer 
                                    :blocks="column.blocks" 
                                    :context="context" 
                                    :is-preview="true"
                                />
                            </div>
                        </div>

                        <!-- Resizer Gutter -->
                        <div 
                            v-if="isBuilder && !isPreview && index < columns.length - 1"
                            class="absolute -right-4 top-0 bottom-0 w-8 z-10 cursor-col-resize group flex items-center justify-center"
                            @mousedown.stop.prevent="startResize(index, $event)"
                        >
                            <div class="w-1 h-12 bg-border group-hover:bg-primary transition-colors rounded-full"></div>
                        </div>

                    </div>
                </template>
            </div>
        </div>
    </section>

    <!-- Block Picker Modal -->
    <BlockPicker 
        :visible="showBlockPicker" 
        @close="showBlockPicker = false"
        @add="handleAddBlock"
    />
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

import { ref, computed, inject, watch, onMounted, onUnmounted } from 'vue';
import draggable from 'vuedraggable';
import { Plus } from 'lucide-vue-next';
import BlockWrapper from '../canvas/BlockWrapper.vue';
import BlockRenderer from './BlockRenderer.vue';
import BlockPicker from '../canvas/BlockPicker.vue';
import { generateUUID } from '../utils';

const props = defineProps({
    id: String,
    columns: { type: Array, default: () => [{ blocks: [] }, { blocks: [] }] },
    layout: { type: String, default: '1-1' },
    customWidths: { type: Array, default: () => [50, 50] },
    padding: { type: String, default: 'py-16' },
    width: { type: String, default: 'max-w-7xl' },
    bgColor: String,
    radius: { type: String, default: 'rounded-none' },
    animation: { type: String, default: '' },
    context: { type: Object, default: () => ({}) },
    isPreview: { type: Boolean, default: false }
});

const builder = inject('builder', null);
const isBuilder = computed(() => !!builder);
const gridRef = ref(null);
const isResizing = ref(false);
const activeResizer = ref(null);
const showBlockPicker = ref(false);
const activeColumnIndex = ref(null);
const rafId = ref(null); // For throttling resize updates

const openBlockPicker = (colIndex) => {
    activeColumnIndex.value = colIndex;
    showBlockPicker.value = true;
};

const handleAddBlock = (newBlock) => {
    if (activeColumnIndex.value !== null && props.columns[activeColumnIndex.value]) {
        props.columns[activeColumnIndex.value].blocks.push(newBlock);
        builder.takeSnapshot();
    }
    showBlockPicker.value = false;
    activeColumnIndex.value = null;
};

const getColumnCount = (layout) => {
    switch (layout) {
        case '1-1': case '1-2': case '2-1': case 'custom': return 2;
        case '1-1-1': return 3;
        case '1-1-1-1': return 4;
        default: return 1;
    }
};

watch(() => props.layout, (newLayout) => {
    const target = getColumnCount(newLayout);
    if (props.columns.length < target) {
        const diff = target - props.columns.length;
        for (let i = 0; i < diff; i++) {
            props.columns.push({ blocks: [] });
        }
    }
    
    // Update customWidths if layout matches presets
    if (newLayout === '1-1') updateCustomWidths([50, 50]);
    if (newLayout === '1-2') updateCustomWidths([33.33, 66.66]);
    if (newLayout === '2-1') updateCustomWidths([66.66, 33.33]);
    if (newLayout === '1-1-1') updateCustomWidths([33.33, 33.33, 33.33]);
});

const updateCustomWidths = (widths) => {
    // We need to update the block settings in the builder state
    const blockIndex = builder.blocks.value.findIndex(b => b.id === props.id);
    if (blockIndex !== -1) {
        builder.blocks.value[blockIndex].settings.customWidths = widths;
    }
};

const updateLayoutToCustom = () => {
    const blockIndex = builder.blocks.value.findIndex(b => b.id === props.id);
    if (blockIndex !== -1 && builder.blocks.value[blockIndex].settings.layout !== 'custom') {
        builder.blocks.value[blockIndex].settings.layout = 'custom';
    }
};

const customGridTemplate = computed(() => {
    if (!props.customWidths || props.customWidths.length === 0) return '1fr 1fr';
    return props.customWidths.map(w => `${w}%`).join(' ');
});

const gridLayout = computed(() => {
    switch (props.layout) {
        case '1-1': return 'md:grid-cols-2';
        case '1-2': return 'md:grid-cols-[1fr_2fr]'; 
        case '2-1': return 'md:grid-cols-[2fr_1fr]';
        case '1-1-1': return 'md:grid-cols-3';
        case '1-1-1-1': return 'md:grid-cols-2 lg:grid-cols-4';
        default: return 'grid-cols-1';
    }
});

const startResize = (index, event) => {
    isResizing.value = true;
    activeResizer.value = index;
    updateLayoutToCustom();

    window.addEventListener('mousemove', doResize);
    window.addEventListener('mouseup', stopResize);
};

const doResize = (event) => {
    if (!isResizing.value || activeResizer.value === null || !gridRef.value) return;

    // Cancel any pending animation frame to throttle updates
    if (rafId.value) {
        cancelAnimationFrame(rafId.value);
    }

    // Use requestAnimationFrame for smoother updates
    rafId.value = requestAnimationFrame(() => {
        if (!gridRef.value) return;
        
        const gridRect = gridRef.value.getBoundingClientRect();
        const mouseX = event.clientX - gridRect.left;
        const gridWidth = gridRect.width;
        
        // Percentage from left
        const percentage = (mouseX / gridWidth) * 100;

        // We only support 2 columns for now for simplicity in resizing logic
        if (props.columns.length === 2 && activeResizer.value === 0) {
            const leftWidth = Math.max(15, Math.min(85, percentage));
            const rightWidth = 100 - leftWidth;
            updateCustomWidths([leftWidth, rightWidth]);
        }
    });
};

const stopResize = () => {
    // Cancel any pending animation frame
    if (rafId.value) {
        cancelAnimationFrame(rafId.value);
        rafId.value = null;
    }
    isResizing.value = false;
    activeResizer.value = null;
    window.removeEventListener('mousemove', doResize);
    window.removeEventListener('mouseup', stopResize);
    builder.takeSnapshot();
};

const onEditBlock = (blockId) => {
    builder.activeBlockId.value = blockId;
    builder.activeTab.value = 'content';
};

const onDuplicate = (column, index) => {
    const original = column.blocks[index];
    const clone = {
        ...JSON.parse(JSON.stringify(original)),
        id: generateUUID()
    };
    column.blocks.splice(index + 1, 0, clone);
    builder.takeSnapshot();
};

const onDelete = (column, index) => {
    column.blocks.splice(index, 1);
    builder.takeSnapshot();
};

const onWrapInColumn = (column, index) => {
    const original = column.blocks[index];
    const container = {
        id: generateUUID(),
        type: 'container',
        settings: {
            direction: 'flex-col',
            justify: 'justify-start',
            align: 'items-start',
            gap: 'gap-4',
            padding: 'p-4',
            blocks: [original]
        }
    };
    column.blocks.splice(index, 1, container);
    builder.takeSnapshot();
};

const onSplitInColumn = (column, index) => {
    const original = column.blocks[index];
    // Create a Columns block with proper grid distribution
    const columns = {
        id: generateUUID(),
        type: 'columns',
        settings: {
            layout: '1-1',
            columns: [
                { blocks: [original] },
                { blocks: [] }
            ],
            customWidths: [50, 50],
            padding: 'py-0',
            width: 'max-w-full',
            bgColor: 'transparent',
            radius: 'rounded-none'
        }
    };
    column.blocks.splice(index, 1, columns);
    builder.takeSnapshot();
};

onUnmounted(() => {
    window.removeEventListener('mousemove', doResize);
    window.removeEventListener('mouseup', stopResize);
});
</script>

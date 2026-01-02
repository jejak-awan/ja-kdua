<template>
    <section 
        :class="['transition-all duration-500', padding, radius, animation, isResizing ? 'cursor-col-resize select-none' : '']"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto px-6', width]">
            <div 
                :class="['grid gap-8', layout !== 'custom' ? gridLayout : '']"
                :style="layout === 'custom' ? { gridTemplateColumns: customGridTemplate } : {}"
                ref="gridRef"
            >
                <template v-for="(column, index) in columns" :key="index">
                    <div class="column-container relative flex items-stretch">
                        <div class="flex-1 min-h-[50px] space-y-4">
                            <draggable 
                                v-model="column.blocks" 
                                item-key="id"
                                group="blocks"
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
                                    />
                                </template>
                                
                                <template #footer>
                                     <div v-if="column.blocks.length === 0" class="h-full flex flex-col items-center justify-center p-4 text-center pointer-events-none opacity-50">
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">Column {{ index + 1 }}</span>
                                        <span class="text-[9px] text-muted-foreground mt-1">Drop blocks here</span>
                                    </div>
                                </template>
                            </draggable>
                        </div>

                        <!-- Resizer Gutter -->
                        <div 
                            v-if="index < columns.length - 1"
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
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

import { ref, computed, inject, watch, onMounted, onUnmounted } from 'vue';
import draggable from 'vuedraggable';
import BlockWrapper from '../canvas/BlockWrapper.vue';

const props = defineProps({
    id: String,
    columns: { type: Array, default: () => [{ blocks: [] }, { blocks: [] }] },
    layout: { type: String, default: '1-1' },
    customWidths: { type: Array, default: () => [50, 50] },
    padding: { type: String, default: 'py-16' },
    width: { type: String, default: 'max-w-7xl' },
    bgColor: String,
    radius: { type: String, default: 'rounded-none' },
    animation: { type: String, default: '' }
});

const builder = inject('builder');
const gridRef = ref(null);
const isResizing = ref(false);
const activeResizer = ref(null);

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

    const gridRect = gridRef.value.getBoundingClientRect();
    const mouseX = event.clientX - gridRect.left;
    const gridWidth = gridRect.width;
    
    // Percentage from left
    const percentage = (mouseX / gridWidth) * 100;

    // We only support 2 columns for now for simplicity in resizing logic, 
    // but can expand to multi-column drag later.
    if (props.columns.length === 2 && activeResizer.value === 0) {
        const leftWidth = Math.max(10, Math.min(90, percentage));
        const rightWidth = 100 - leftWidth;
        updateCustomWidths([leftWidth, rightWidth]);
    } else if (props.columns.length === 3) {
        // More complex multi-column resizing logic if needed
        const currentWidths = [...props.customWidths];
        const resizerIdx = activeResizer.value;
        
        // Calculate combined width of columns up to the resizer
        let prevSum = 0;
        for(let i=0; i < resizerIdx; i++) prevSum += currentWidths[i];
        
        const newColWidth = percentage - prevSum;
        const remainingWidth = 100 - percentage;
        
        // Need to be careful with total % 
        // For now, let's just stick to 2-column resizing as a starting point 
        // until we have a more robust multi-point resizer.
    }
};

const stopResize = () => {
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
        id: crypto.randomUUID()
    };
    column.blocks.splice(index + 1, 0, clone);
    builder.takeSnapshot();
};

const onDelete = (column, index) => {
    column.blocks.splice(index, 1);
    builder.takeSnapshot();
};

onUnmounted(() => {
    window.removeEventListener('mousemove', doResize);
    window.removeEventListener('mouseup', stopResize);
});
</script>

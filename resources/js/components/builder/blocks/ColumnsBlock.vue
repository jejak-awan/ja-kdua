<template>
    <section 
        :class="['transition-all duration-500 h-full', padding, radius, animation, isResizing ? 'cursor-col-resize select-none' : '']"
        :style="{ backgroundColor: bgColor || 'transparent' }"
        v-bind="$attrs"
    >
        <div :class="['mx-auto px-6 h-full', width]">
            <div 
                :class="[
                    'flex flex-wrap gap-8 h-full',
                    reverseClasses
                ]"
                :style="flexStyles"
                ref="gridRef"
            >
                <template v-for="(column, index) in computedColumns" :key="column.id || index">
                    <!-- Modern Column (via BlockWrapper) - Only in Builder -->
                    <BlockWrapper
                        v-if="isModern && isBuilder && !isPreview"
                        :block="column"
                        :index="index"
                        :isNested="true"
                        class="column-wrapper relative flex items-stretch flex-shrink-0 flex-grow-0"
                        :class="getColumnClasses()"
                        :style="{ '--desktop-width': colWidths[index] }"
                        @delete="removeColumn(index)"
                        @edit="onEditBlock(column.id)"
                        @duplicate="duplicateColumn(index)"
                    />

                    <!-- Modern Column (Frontend/Preview) -->
                    <div 
                        v-else-if="isModern"
                        class="column-wrapper relative flex items-stretch flex-shrink-0 flex-grow-0"
                        :class="getColumnClasses()"
                        :style="{ '--desktop-width': colWidths[index] }"
                    >
                         <BlockRenderer 
                            :blocks="[column]" 
                            :context="context" 
                            :is-preview="isPreview"
                            class="h-full w-full"
                        />
                    </div>

                    <!-- Legacy Column (inline render) -->
                    <div 
                        v-else
                        class="column-container relative flex items-stretch flex-shrink-0 flex-grow-0"
                        :class="getColumnClasses()"
                        :style="{ '--desktop-width': colWidths[index] }"
                    >
                        <div class="flex-1 min-h-[50px] space-y-4">
                            <!-- Builder Mode -->
                            <draggable 
                                v-if="isBuilder && !isPreview"
                                v-model="column.blocks" 
                                item-key="id"
                                :group="{ name: 'blocks', pull: true, put: true }"
                                handle=".drag-handle"
                                class="min-h-[100px] h-full flex flex-col border-2 border-dashed border-transparent hover:border-sidebar-border/50 rounded-xl transition-colors p-2"
                                ghost-class="block-ghost"
                                :class="(!column.blocks || column.blocks.length === 0) ? 'bg-muted/30 border-muted-foreground/10' : ''"
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
                                     <div v-if="!column.blocks || column.blocks.length === 0" class="h-full flex flex-col items-center justify-center p-4 text-center relative z-[20]">
                                        <!-- Column Actions Bar (for empty columns) -->
                                        <div v-if="computedColumns.length > 1" class="absolute top-1 right-1 flex items-center gap-1 z-30">
                                            <button 
                                                @click.stop.prevent="removeColumn(index)"
                                                class="h-6 w-6 flex items-center justify-center bg-background text-foreground hover:bg-destructive hover:text-white border border-border rounded-md transition-colors shadow-sm"
                                                title="Remove Column"
                                            >
                                                <Trash2 class="w-3 h-3" />
                                            </button>
                                        </div>
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
                                    :blocks="column.blocks || []" 
                                    :context="context" 
                                    :is-preview="true"
                                />
                            </div>
                        </div>

                        <!-- Resizer Gutter -->
                        <div 
                            v-if="isBuilder && !isPreview && index < computedColumns.length - 1"
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
import { Plus, Trash2 } from 'lucide-vue-next';
import BlockWrapper from '../canvas/BlockWrapper.vue';
import BlockRenderer from './BlockRenderer.vue';
import BlockPicker from '../canvas/BlockPicker.vue';
import { generateUUID } from '../utils';

const props = defineProps({
    id: String,
    columns: { type: Array, default: () => [] }, // Legacy
    blocks: { type: Array, default: () => [] },   // Modern: Column block objects
    layout: { type: String, default: '1-1' },
    customWidths: { type: Array, default: () => [50, 50] },
    stackOn: { type: String, default: 'never' },
    direction: { type: String, default: 'row' },
    mobileDirection: { type: String, default: 'column' },
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

// Determine if using modern blocks array or legacy columns
const isModern = computed(() => props.blocks && props.blocks.length > 0);

// Computed columns - use modern blocks if available, else legacy columns
const computedColumns = computed(() => {
    if (isModern.value) return props.blocks;
    return props.columns;
});


const openBlockPicker = (colIndex) => {
    activeColumnIndex.value = colIndex;
    showBlockPicker.value = true;
};

const handleAddBlock = (newBlock) => {
    if (activeColumnIndex.value === null) {
        showBlockPicker.value = false;
        return;
    }
    
    const block = builder.findBlockById(props.id);
    if (!block || !block.settings) return;
    
    if (isModern.value) {
        // Modern: Add to column.settings.blocks
        const column = block.settings.blocks[activeColumnIndex.value];
        if (column && column.settings) {
            if (!column.settings.blocks) column.settings.blocks = [];
            column.settings.blocks.push(newBlock);
        }
    } else {
        // Legacy: Add to column.blocks
        const column = block.settings.columns[activeColumnIndex.value];
        if (column) {
            if (!column.blocks) column.blocks = [];
            column.blocks.push(newBlock);
        }
    }
    
    builder.takeSnapshot();
    showBlockPicker.value = false;
    activeColumnIndex.value = null;
};

/**
 * Remove a column from the layout
 * Only allowed when column is empty and there are more than 1 columns
 */
const removeColumn = (columnIndex) => {
    if (computedColumns.value.length <= 1) return;
    
    const column = computedColumns.value[columnIndex];
    // Check if empty - modern uses column.settings.blocks, legacy uses column.blocks
    const blocks = isModern.value ? column.settings?.blocks : column.blocks;
    if (blocks && blocks.length > 0) return; // Don't remove columns with content
    
    const block = builder.findBlockById(props.id);
    if (!block || !block.settings) return;
    
    if (isModern.value) {
        block.settings.blocks.splice(columnIndex, 1);
        const numColumns = block.settings.blocks.length;
        if (numColumns > 0) {
            block.settings.customWidths = Array(numColumns).fill(100 / numColumns);
        }
    } else {
        block.settings.columns.splice(columnIndex, 1);
        const numColumns = block.settings.columns.length;
        if (numColumns > 0) {
            block.settings.customWidths = Array(numColumns).fill(100 / numColumns);
        }
    }
    
    block.settings.layout = 'custom';
    builder.blocks.value = [...builder.blocks.value];
    builder.takeSnapshot();
};

/**
 * Duplicate a column (modern only)
 */
const duplicateColumn = (index) => {
    if (!isModern.value) return;
    
    const block = builder.findBlockById(props.id);
    if (!block || !block.settings || !block.settings.blocks) return;
    
    const original = block.settings.blocks[index];
    const clone = JSON.parse(JSON.stringify(original));
    clone.id = generateUUID();
    
    // Regenerate nested IDs
    if (builder.regenerateIds) {
        builder.regenerateIds(clone);
    }
    
    block.settings.blocks.splice(index + 1, 0, clone);
    
    // Update widths
    const numColumns = block.settings.blocks.length;
    block.settings.customWidths = Array(numColumns).fill(100 / numColumns);
    block.settings.layout = 'custom';
    
    builder.takeSnapshot();
};

const getColumnCount = (layout) => {
    switch (layout) {
        case '1': return 1;
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
    
    // Ensure all columns have blocks array
    props.columns.forEach(col => {
        if (!col.blocks) col.blocks = [];
    });
    
    // Update customWidths if layout matches presets
    if (newLayout === '1') updateCustomWidths([100]);
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

const flexStyles = computed(() => {
    return {
        // Any container level styles if needed
    };
});

// Calculate column widths accounting for gap
const colWidths = computed(() => {
    const numCols = computedColumns.value.length;
    if (numCols === 0) return [];

    let widths = [];
    
    // Determine base percentages
    if (props.layout === 'custom' && props.customWidths.length === numCols) {
        widths = props.customWidths;
    } else {
        // Standard layouts
        switch (props.layout) {
            case '1': widths = [100]; break;
            case '1-1': widths = [50, 50]; break;
            case '1-2': widths = [33.333, 66.667]; break;
            case '2-1': widths = [66.667, 33.333]; break;
            case '1-1-1': widths = [33.333, 33.333, 33.333]; break;
            case '1-1-1-1': widths = [25, 25, 25, 25]; break;
            default: widths = Array(numCols).fill(100 / numCols);
        }
    }

    // Safety: If widths count doesn't match columns count (e.g. added a column), fallback to even distribution
    if (widths.length !== numCols) {
        widths = Array(numCols).fill(100 / numCols);
    }

    // Convert percentages to calc strings subtracting gap
    // Formula: calc(P% - (totalGap * P/100))
    // Total gap = (n-1) * 2rem (gap-8)
    const gap = 2; // rem
    const totalGap = (numCols - 1) * gap;
    
    return widths.map(w => {
        const gapSubtraction = (totalGap * (w / 100)).toFixed(3);
        return `calc(${w}% - ${gapSubtraction}rem)`;
    });
});

const breakpointPrefix = computed(() => {
    const stack = props.stackOn || 'sm';
    if (stack === 'never') return ''; // No prefix, applies always
    if (stack === 'lg') return 'xl'; // Stack until XL
    if (stack === 'md') return 'lg'; // Stack until LG
    return 'md'; // Default 'sm' -> stack until MD
});

const getColumnClasses = () => {
    const bp = breakpointPrefix.value;
    
    // If always stacked (e.g. 'lg' implies stack most of the time? Or maybe we need explicit 'always')
    // For now assuming 'lg' stacks until XL.
    
    if (bp === '') return 'w-[var(--desktop-width)]'; // Never stack
    
    return `w-full ${bp}:w-[var(--desktop-width)]`;
};

// Direction classes based on mobile and desktop direction settings
const reverseClasses = computed(() => {
    const stackBreakpoint = props.stackOn || 'sm';
    const mobileDir = props.mobileDirection || 'column';
    const desktopDir = props.direction || 'row';
    
    // Map direction values to Tailwind flex classes
    const directionMap = {
        'row': 'flex-row',
        'row-reverse': 'flex-row-reverse',
        'column': 'flex-col',
        'column-reverse': 'flex-col-reverse'
    };
    
    // If never stack, just use desktop direction
    if (stackBreakpoint === 'never') {
        return directionMap[desktopDir] || 'flex-row';
    }
    
    // Responsive breakpoint prefix
    const bp = stackBreakpoint === 'sm' ? 'md' : stackBreakpoint === 'md' ? 'lg' : 'xl';
    
    // Mobile direction + Desktop direction with breakpoint
    const mobileClass = directionMap[mobileDir] || 'flex-col';
    const desktopClass = directionMap[desktopDir] || 'flex-row';
    
    return `${mobileClass} ${bp}:${desktopClass}`;
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
        const numColumns = props.columns.length;
        const resizerIndex = activeResizer.value;
        
        // Get current widths or initialize evenly
        let currentWidths;
        if (props.customWidths && props.customWidths.length === numColumns) {
            currentWidths = [...props.customWidths];
        } else {
            currentWidths = Array(numColumns).fill(100 / numColumns);
        }
        
        // Calculate cumulative width up to the resizer
        let cumulativeWidth = 0;
        for (let i = 0; i <= resizerIndex; i++) {
            cumulativeWidth += currentWidths[i];
        }
        
        // Calculate the delta (how much the resizer moved)
        const delta = percentage - cumulativeWidth;
        
        // Calculate new widths with constraints (min 10%, max 90% for each column)
        const minWidth = 10;
        const maxWidth = 90;
        
        // Adjust the column before the resizer and the one after
        const leftColIndex = resizerIndex;
        const rightColIndex = resizerIndex + 1;
        
        let newLeftWidth = currentWidths[leftColIndex] + delta;
        let newRightWidth = currentWidths[rightColIndex] - delta;
        
        // Apply constraints
        if (newLeftWidth < minWidth) {
            newRightWidth += newLeftWidth - minWidth;
            newLeftWidth = minWidth;
        } else if (newLeftWidth > maxWidth) {
            newRightWidth -= newLeftWidth - maxWidth;
            newLeftWidth = maxWidth;
        }
        
        if (newRightWidth < minWidth) {
            newLeftWidth += newRightWidth - minWidth;
            newRightWidth = minWidth;
        } else if (newRightWidth > maxWidth) {
            newLeftWidth -= newRightWidth - maxWidth;
            newRightWidth = maxWidth;
        }
        
        // Final constraints
        newLeftWidth = Math.max(minWidth, Math.min(maxWidth, newLeftWidth));
        newRightWidth = Math.max(minWidth, Math.min(maxWidth, newRightWidth));
        
        // Update the widths array
        const newWidths = [...currentWidths];
        newWidths[leftColIndex] = newLeftWidth;
        newWidths[rightColIndex] = newRightWidth;
        
        updateCustomWidths(newWidths);
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

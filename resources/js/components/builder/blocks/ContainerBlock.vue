<template>
    <div 
        class="relative min-h-[50px] transition-all duration-300 group/container"
        :class="[
            direction, justify, align, gap, padding, radius,
            // Border interaction in builder mode
            isBuilder && !isPreview ? 'hover:outline hover:outline-1 hover:outline-primary/30 hover:bg-primary/5' : ''
        ]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
        @click.stop="onSelect"
    >
        <!-- Builder Overlay for Selection -->
        <div v-if="isSelected && isBuilder && !isPreview" class="absolute inset-0 border-2 border-primary pointer-events-none rounded-[inherit] z-[2]"></div>

        <!-- Label tag (only builder) -->
        <div v-if="isSelected && isBuilder && !isPreview" class="absolute -top-6 left-0 bg-primary text-primary-foreground text-[10px] px-2 py-0.5 rounded-t-md font-bold uppercase tracking-wider z-[20]">
            Container
        </div>

        <!-- Builder Mode: Draggable Area -->
        <draggable 
            v-if="isBuilder && !isPreview"
            v-model="nestedBlocks" 
            item-key="id"
            :group="{ name: 'blocks', pull: true, put: true }"
            handle=".drag-handle"
            class="w-full h-full min-h-[50px] flex flex-1 flex-wrap"
            :class="[direction, justify, align, gap]"
            ghost-class="block-ghost opacity-50 bg-primary/10"
        >
            <template #item="{ element: block, index }">
                <!-- Child blocks get flex-1 in row mode for equal distribution -->
                <BlockWrapper 
                    :block="block" 
                    :index="index"
                    :context="context"
                    :isNested="true"
                    :class="['relative', direction === 'flex-row' ? 'flex-1 min-w-0' : '']"
                    @edit="onEditBlock(block.id)"
                    @duplicate="onDuplicateNested(index)"
                    @delete="onDeleteNested(index)"
                    @wrap="onWrapNested(index)"
                    @split="onSplitNested(index)"
                />
            </template>
            
            <template #footer>
                 <!-- Empty State with Add Button if empty (matches ColumnsBlock exactly) -->
                 <div v-if="nestedBlocks.length === 0" class="h-full flex flex-col items-center justify-center p-4 text-center relative z-[20] min-h-[100px] flex-1">
                    <div 
                        @click.stop.prevent="showBlockPicker = true"
                        class="flex flex-col items-center gap-2 p-3 rounded-lg border-2 border-dashed border-muted-foreground/10 hover:border-primary/50 hover:bg-primary/5 transition-all w-full h-full justify-center group/btn cursor-pointer min-h-[80px]"
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

        <!-- Live Mode: Static Render -->
        <div 
            v-else 
            class="w-full flex h-full"
            :class="[direction, justify, align, gap]"
        >
             <BlockRenderer 
                :blocks="nestedBlocks" 
                :context="context"
                :is-preview="isPreview"
            />
        </div>

        <!-- Helper: Add Button at bottom for convenience (only if row or col) ?? -->
        <!-- Actually, Standard "Add" flow via draggable footer or toolbar is better -->

        <!-- Block Picker -->
        <BlockPicker 
            :visible="showBlockPicker" 
            @close="showBlockPicker = false"
            @add="handleAddBlock"
        />
    </div>
</template>

<script setup>
import { computed, inject, ref } from 'vue';
import draggable from 'vuedraggable';
import { Plus } from 'lucide-vue-next';
import BlockWrapper from '../canvas/BlockWrapper.vue';
import BlockRenderer from './BlockRenderer.vue';
import BlockPicker from '../canvas/BlockPicker.vue';
import { generateUUID } from '../utils';

defineOptions({
  inheritAttrs: false
});

const props = defineProps({
    id: String,
    // Settings props
    direction: { type: String, default: 'flex-col' },
    justify: { type: String, default: 'justify-start' },
    align: { type: String, default: 'items-start' },
    gap: { type: String, default: 'gap-4' },
    padding: { type: String, default: 'p-4' },
    bgColor: String,
    radius: { type: String, default: 'rounded-none' },
    // Data
    blocks: { type: Array, default: () => [] },
    context: Object,
    isPreview: { type: Boolean, default: false }
});

const builder = inject('builder', null);
const isBuilder = computed(() => !!builder);
const showBlockPicker = ref(false);

const blockObject = computed(() => {
    if (!builder || !props.id) return null;
    return builder.findBlockById(props.id);
});

const isSelected = computed(() => {
    return builder?.activeBlockId?.value === props.id;
});

const nestedBlocks = computed({
    get: () => {
        if (blockObject.value) {
            return blockObject.value.settings?.blocks || [];
        }
        return props.blocks || [];
    },
    set: (val) => {
        if (blockObject.value) {
            if (!blockObject.value.settings) blockObject.value.settings = {};
            blockObject.value.settings.blocks = val;
            builder?.takeSnapshot();
        }
    }
});

const onSelect = () => {
    if (builder && props.id) {
        builder.activeBlockId.value = props.id;
    }
};

const handleAddBlock = (newBlock) => {
    if (!blockObject.value) return;
    if (!blockObject.value.settings) blockObject.value.settings = {};
    if (!blockObject.value.settings.blocks) blockObject.value.settings.blocks = [];
    blockObject.value.settings.blocks.push(newBlock);
    builder?.takeSnapshot();
    showBlockPicker.value = false;
};

const onEditBlock = (id) => {
    if (builder) builder.activeBlockId.value = id;
};

const onDuplicateNested = (index) => {
    if (!blockObject.value?.settings?.blocks) return;
    const original = blockObject.value.settings.blocks[index];
    const clone = {
        ...JSON.parse(JSON.stringify(original)),
        id: generateUUID()
    };
    blockObject.value.settings.blocks.splice(index + 1, 0, clone);
    builder?.takeSnapshot();
};

const onDeleteNested = (index) => {
    if (!blockObject.value?.settings?.blocks) return;
    blockObject.value.settings.blocks.splice(index, 1);
    builder?.takeSnapshot();
};

const onWrapNested = (index) => {
    if (!blockObject.value?.settings?.blocks) return;
    const original = blockObject.value.settings.blocks[index];
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
    blockObject.value.settings.blocks.splice(index, 1, container);
    builder?.takeSnapshot();
};

const onSplitNested = (index) => {
    if (!blockObject.value?.settings?.blocks) return;
    const original = blockObject.value.settings.blocks[index];
    
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
    blockObject.value.settings.blocks.splice(index, 1, columns);
    builder?.takeSnapshot();
};
</script>

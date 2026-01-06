<template>
    <div 
        class="relative min-h-[50px] transition-all duration-300 group/container h-full"
        :class="[
            displayClass,
            direction, justify, align, wrap, 
            radius,
            shadow !== 'none' ? shadow : '',
            // Builder interaction
            isBuilder && !isPreview ? 'hover:outline hover:outline-1 hover:outline-primary/30' : ''
        ]"
        :style="containerStyle"
        v-bind="$attrs"
        @click.stop="onSelect"
    >
        <!-- Builder Overlay -->
        <div v-if="isSelected && isBuilder && !isPreview" class="absolute inset-0 border-2 border-primary pointer-events-none rounded-[inherit] z-[2]"></div>
        
        <!-- Builder Label -->
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
            class="flex flex-1"
            :class="[direction, justify, align, wrap]"
            :style="{ gap: gap }"
            ghost-class="block-ghost opacity-50 bg-primary/10"
        >
            <template #item="{ element: block, index }">
                <BlockWrapper 
                    :block="block" 
                    :index="index"
                    :context="context"
                    :isNested="true"
                    :class="['relative', direction === 'flex-row' && block.settings.width === 'auto' ? 'flex-1' : '']"
                    @edit="onEditBlock(block.id)"
                    @duplicate="onDuplicateNested(index)"
                    @delete="onDeleteNested(index)"
                    @wrap="onWrapNested(index)"
                    @split="onSplitNested(index)"
                />
            </template>
            
            <template #footer>
                 <div v-if="nestedBlocks.length === 0" class="flex-1 flex flex-col items-center justify-center p-4 text-center relative z-[20] min-h-[50px]">
                    <div 
                        @click.stop.prevent="showBlockPicker = true"
                        class="flex flex-col items-center gap-2 p-3 rounded-lg border-2 border-dashed border-muted-foreground/10 hover:border-primary/50 hover:bg-primary/5 transition-all w-full h-full justify-center group/btn cursor-pointer min-h-[80px]"
                    >
                        <div class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center group-hover/btn:bg-primary/10 transition-colors">
                            <Plus class="w-4 h-4 text-muted-foreground group-hover/btn:text-primary" />
                        </div>
                        <p class="text-[10px] font-bold text-muted-foreground group-hover/btn:text-primary">Add Block</p>
                    </div>
                </div>
            </template>
        </draggable>

        <!-- Live Mode -->
        <div 
            v-else 
            class="flex w-full h-full"
            :class="[direction, justify, align, wrap]"
            :style="{ gap: gap }"
        >
             <BlockRenderer 
                :blocks="nestedBlocks" 
                :context="context"
                :is-preview="isPreview"
            />
        </div>

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
    // Settings
    direction: { type: String, default: 'flex-col' },
    justify: { type: String, default: 'justify-start' },
    align: { type: String, default: 'items-start' },
    wrap: { type: String, default: 'flex-nowrap' },
    gap: { type: String, default: '16px' },
    
    width: { type: String, default: '100%' },
    maxWidth: { type: String, default: 'none' },
    minHeight: { type: String, default: '0' },
    
    padding: { type: Object, default: () => ({ top: '16px', right: '16px', bottom: '16px', left: '16px' }) },
    margin: { type: Object, default: () => ({ top: '0', right: '0', bottom: '0', left: '0' }) },
    
    bgColor: String,
    borderWidth: { type: Number, default: 0 },
    borderColor: String,
    radius: { type: String, default: 'rounded-none' },
    shadow: { type: String, default: 'none' },
    overflow: { type: String, default: 'visible' },
    
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

const isSelected = computed(() => builder?.activeBlockId?.value === props.id);

const nestedBlocks = computed({
    get: () => blockObject.value?.settings?.blocks || props.blocks || [],
    set: (val) => {
        if (blockObject.value) {
            if (!blockObject.value.settings) blockObject.value.settings = {};
            blockObject.value.settings.blocks = val;
            builder?.takeSnapshot();
        }
    }
});

const displayClass = computed(() => 'flex'); // Always flex for now

const containerStyle = computed(() => {
    const p = props.padding || {};
    const m = props.margin || {};
    
    const style = {
        paddingTop: p.top,
        paddingRight: p.right,
        paddingBottom: p.bottom,
        paddingLeft: p.left,
        marginTop: m.top,
        marginRight: m.right,
        marginBottom: m.bottom,
        marginLeft: m.left,
        width: props.width,
        maxWidth: props.maxWidth,
        minHeight: props.minHeight,
        backgroundColor: props.bgColor,
        overflow: props.overflow
    };
    
    if (props.borderWidth > 0) {
        style.borderWidth = `${props.borderWidth}px`;
        style.borderColor = props.borderColor;
        style.borderStyle = 'solid';
    }
    
    return style;
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
    blockObject.value.settings.blocks.splice(index, 1);
    builder?.takeSnapshot();
};

const onWrapNested = (index) => {
    // Already inside a container, but can wrap again
    const original = blockObject.value.settings.blocks[index];
    const container = {
        id: generateUUID(),
        type: 'container',
        settings: {
            direction: 'flex-col',
            padding: { top: '16px', right: '16px', bottom: '16px', left: '16px' },
            blocks: [original]
        }
    };
    blockObject.value.settings.blocks.splice(index, 1, container);
    builder?.takeSnapshot();
};

const onSplitNested = (index) => {
    const original = blockObject.value.settings.blocks[index];
    const columns = {
        id: generateUUID(),
        type: 'columns',
        settings: {
            layout: '1-1',
            columns: [{ blocks: [original] }, { blocks: [] }]
        }
    };
    blockObject.value.settings.blocks.splice(index, 1, columns);
    builder?.takeSnapshot();
};
</script>

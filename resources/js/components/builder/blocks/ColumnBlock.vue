<template>
    <div 
        class="relative transition-all duration-300 group/column h-full flex flex-col"
        :class="[
            displayClass,
            direction, justify, align,
            radius,
            // Builder interaction - distinct border for column selection if needed
            isBuilder && !isPreview && isSelected ? 'ring-2 ring-primary/50' : ''
        ]"
        :style="columnStyle"
        v-bind="$attrs"
        @click.stop="onSelect"
    >
        <!-- Builder Overlay / UI for Column -->
        <div v-if="isSelected && isBuilder && !isPreview" class="absolute -top-5 left-0 bg-primary/80 text-primary-foreground text-[9px] px-2 py-0.5 rounded-t-sm font-bold uppercase tracking-wider z-[20] flex items-center gap-1">
            <Columns class="w-3 h-3" /> Column
        </div>

        <!-- content -->
        <draggable 
            v-if="isBuilder && !isPreview"
            v-model="nestedBlocks" 
            item-key="id"
            :group="{ name: 'blocks', pull: true, put: true }"
            handle=".drag-handle"
            class="flex-1 flex flex-col min-h-[50px]"
            :class="[direction, justify, align]"
            ghost-class="block-ghost opacity-50 bg-primary/10"
        >
            <template #item="{ element: block, index }">
                <BlockWrapper 
                    :block="block" 
                    :index="index"
                    :context="context"
                    :isNested="true"
                    class="relative"
                    @edit="onEditBlock(block.id)"
                    @duplicate="onDuplicateNested(index)"
                    @delete="onDeleteNested(index)"
                    @wrap="onWrapNested(index)"
                    @split="onSplitNested(index)"
                />
            </template>
            
            <template #footer>
                 <!-- Empty State -->
                 <div v-if="nestedBlocks.length === 0" class="flex-1 flex flex-col items-center justify-center p-2 text-center relative z-[10] min-h-[50px] border-2 border-dashed border-muted/30 hover:border-primary/30 rounded m-2 transition-colors">
                    <Button variant="ghost" size="sm" class="h-6 text-[10px] text-muted-foreground" @click.stop.prevent="showBlockPicker = true">
                        <Plus class="w-3 h-3 mr-1" /> Add Block
                    </Button>
                </div>
            </template>
        </draggable>

        <!-- Live Mode -->
        <div 
            v-else 
            class="flex-1 flex flex-col h-full"
            :class="[direction, justify, align]"
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
            :target-block-id="id"
        />
    </div>
</template>

<script setup>
import { computed, inject, ref } from 'vue';
import draggable from 'vuedraggable';
import { Plus, Columns } from 'lucide-vue-next';
import BlockWrapper from '../canvas/BlockWrapper.vue';
import BlockRenderer from './BlockRenderer.vue';
import BlockPicker from '../canvas/BlockPicker.vue';
import { generateUUID } from '../utils';
import Button from '@/components/ui/button.vue';

defineOptions({
  inheritAttrs: false
});

const props = defineProps({
    id: String,
    // Settings passed via v-bind from BlockRenderer
    direction: { type: String, default: 'flex-col' },
    justify: { type: String, default: 'justify-start' },
    align: { type: String, default: 'items-stretch' },
    
    padding: { type: Object, default: () => ({ top: '0', right: '0', bottom: '0', left: '0' }) },
    bgColor: String,
    borderWidth: { type: Number, default: 0 },
    borderColor: String,
    radius: { type: String, default: 'rounded-none' },
    
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

const displayClass = computed(() => 'flex'); // Always flex

const columnStyle = computed(() => {
    const p = props.padding || {};
    
    const style = {
        paddingTop: p.top,
        paddingRight: p.right,
        paddingBottom: p.bottom,
        paddingLeft: p.left,
        backgroundColor: props.bgColor,
        // Column width is handled by parent Row/Columns via flex-basis or width classes
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
        builder.activeTab.value = 'content'; // or style?
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
    // Legacy support? No, use new Row logic ideally? 
    // For now use 'columns' which is legacy.
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

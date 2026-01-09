<template>
    <div 
        class="column-block relative h-full flex transition-all duration-300"
        :class="[
            direction,
            justify,
            align,
            `gap-${gap}`,
            radius,
            // Builder interaction
            isBuilder && !isPreview && isSelected ? 'ring-2 ring-primary/50' : ''
        ]"
        :style="columnStyle"
        v-bind="$attrs"
    >
        <!-- Builder Mode: Draggable content -->
        <draggable 
            v-if="isBuilder && !isPreview"
            v-model="nestedBlocks" 
            item-key="id"
            :group="{ name: 'blocks', pull: true, put: true }"
            handle=".drag-handle"
            class="flex-1 flex min-h-[50px]"
            :class="[
                direction, justify, align, `gap-${gap}`,
                (!nestedBlocks || nestedBlocks.length === 0) ? 'border-2 border-dashed border-muted-foreground/20 rounded-xl bg-muted/20 items-center justify-center' : ''
            ]"
            ghost-class="column-ghost"
        >
            <template #item="{ element: block, index }">
                <BlockWrapper 
                    :block="block" 
                    :index="index"
                    :isNested="true"
                    class="w-full"
                    @edit="onEditBlock(block.id)"
                    @duplicate="onDuplicate(index)"
                    @delete="onDelete(index)"
                    @wrap="onWrapInContainer(index)"
                    @split="onSplitNested(index)"
                />
            </template>

            <!-- Empty State -->
            <template #header v-if="!nestedBlocks || nestedBlocks.length === 0">
                <div 
                    class="flex flex-col items-center gap-2 p-3 rounded-lg border-2 border-dashed border-muted-foreground/10 hover:border-primary/50 hover:bg-primary/5 transition-all w-full h-full justify-center group/btn cursor-pointer"
                    @click.stop="showBlockPicker = true"
                >
                    <Plus class="w-4 h-4 text-muted-foreground group-hover/btn:text-primary" />
                    <span class="text-[10px] font-bold text-muted-foreground group-hover/btn:text-primary block">Add Block</span>
                </div>
            </template>
        </draggable>

        <!-- Preview Mode: Render content -->
        <div 
            v-else
            class="flex-1 flex"
            :class="[direction, justify, align, `gap-${gap}`]"
        >
            <BlockRenderer 
                v-if="blocks && blocks.length > 0"
                :blocks="blocks" 
                :context="context"
            />
        </div>

        <!-- Block Picker Modal -->
        <BlockPicker 
            v-if="showBlockPicker"
            :visible="true"
            @close="showBlockPicker = false"
            @add="handleAddBlock"
            :target-block-id="id"
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
    // Layout
    direction: { type: String, default: 'flex-col' },
    justify: { type: String, default: 'justify-start' },
    align: { type: String, default: 'items-stretch' },
    gap: { type: String, default: '4' },
    // Spacing
    padding: { type: Object, default: () => ({ top: '0', right: '0', bottom: '0', left: '0' }) },
    // Style
    bgColor: { type: String, default: 'transparent' },
    borderWidth: { type: Number, default: 0 },
    borderColor: { type: String, default: '#e5e7eb' },
    radius: { type: String, default: 'rounded-none' },
    // Content
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
    get: () => {
        // Return direct reference to blocks array for vuedraggable compatibility
        if (blockObject.value?.settings?.blocks) {
            return blockObject.value.settings.blocks;
        }
        // Fallback: ensure we create the array if it doesn't exist
        if (blockObject.value) {
            if (!blockObject.value.settings) blockObject.value.settings = {};
            if (!blockObject.value.settings.blocks) blockObject.value.settings.blocks = [];
            return blockObject.value.settings.blocks;
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

const columnStyle = computed(() => {
    const style = {
        backgroundColor: props.bgColor || 'transparent'
    };

    // Padding
    if (props.padding) {
        style.paddingTop = props.padding.top || '0';
        style.paddingRight = props.padding.right || '0';
        style.paddingBottom = props.padding.bottom || '0';
        style.paddingLeft = props.padding.left || '0';
    }

    // Border
    if (props.borderWidth > 0) {
        style.borderWidth = `${props.borderWidth}px`;
        style.borderStyle = 'solid';
        style.borderColor = props.borderColor || '#e5e7eb';
    }

    return style;
});

const onEditBlock = (blockId) => {
    if (builder) {
        builder.activeBlockId.value = blockId;
        builder.activeRightSidebarTab.value = 'properties';
        builder.isRightSidebarOpen.value = true;
    }
};

const onDuplicate = (index) => {
    const original = nestedBlocks.value[index];
    const clone = JSON.parse(JSON.stringify(original));
    clone.id = generateUUID();
    // Regenerate nested IDs
    if (builder?.regenerateIds) {
        builder.regenerateIds(clone);
    }
    nestedBlocks.value.splice(index + 1, 0, clone);
    builder?.takeSnapshot();
};

const onDelete = (index) => {
    nestedBlocks.value.splice(index, 1);
    builder?.takeSnapshot();
};

const onWrapInContainer = (index) => {
    const original = nestedBlocks.value[index];
    const container = {
        id: generateUUID(),
        type: 'container',
        settings: {
            blocks: [original],
            direction: 'flex-col',
            padding: { top: '16px', right: '16px', bottom: '16px', left: '16px' }
        }
    };
    nestedBlocks.value.splice(index, 1, container);
    builder?.takeSnapshot();
};

const onSplitNested = (index) => {
    const original = nestedBlocks.value[index];
    const columns = {
        id: generateUUID(),
        type: 'columns',
        settings: {
            layout: '1-1',
            stackOn: 'never',
            gap: '8',
            width: 'max-w-full',
            padding: 'py-0',
            blocks: [
                {
                    id: generateUUID(),
                    type: 'column',
                    settings: { blocks: [original] }
                },
                {
                    id: generateUUID(),
                    type: 'column',
                    settings: { blocks: [] }
                }
            ]
        }
    };
    nestedBlocks.value.splice(index, 1, columns);
    builder?.takeSnapshot();
};

const handleAddBlock = (blockData) => {
    nestedBlocks.value.push(blockData);
    showBlockPicker.value = false;
    builder?.takeSnapshot();
};
</script>

<style scoped>
.column-ghost {
    opacity: 0.5;
    background: hsl(var(--primary) / 0.1);
    border: 2px dashed hsl(var(--primary));
    border-radius: 0.5rem;
}
</style>

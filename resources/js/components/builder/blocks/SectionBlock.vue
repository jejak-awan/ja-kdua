<template>
    <div class="relative group/section w-full transition-all duration-200" 
         :class="[
             isSelected ? 'ring-2 ring-primary ring-offset-2 z-10' : 'hover:ring-1 hover:ring-primary/50'
         ]"
         :style="sectionStyle"
         @click.stop="onSelect"
    >
        <!-- Overlay for selection (Builder Mode) -->
        <div class="absolute inset-0 pointer-events-none z-[1]" 
             :class="isSelected ? 'bg-primary/5' : ''">
        </div>

        <!-- Toolbar (Top Right) -->
        <div v-if="isSelected && isBuilder && !isPreview" 
             class="absolute right-4 top-4 z-[20] flex items-center gap-1 bg-background shadow-sm border border-border rounded-md p-1 opacity-100 transition-opacity">
            <span class="text-[10px] font-bold px-2 text-muted-foreground uppercase tracking-wider">Section</span>
            <div class="w-px h-3 bg-border mx-1"></div>
            
            <button class="p-1 hover:bg-accent rounded" title="Settings" @click.stop="onEdit">
                 <Settings2 class="w-3.5 h-3.5" />
            </button>
            <button class="p-1 hover:bg-accent rounded" title="Duplicate" @click.stop="">
                <Copy class="w-3.5 h-3.5" />
            </button>
            <button class="p-1 hover:bg-destructive/10 hover:text-destructive rounded" title="Delete" @click.stop="">
                <Trash2 class="w-3.5 h-3.5" />
            </button>
            <div class="w-px h-3 bg-border mx-1 drag-handle cursor-move"></div>
            <GripVertical class="w-3.5 h-3.5 text-muted-foreground drag-handle cursor-move" />
        </div>

        <!-- Content Container -->
        <div :class="containerClass" class="relative z-[2] min-h-[100px] h-full flex flex-col">
            
            <!-- Builder Mode: Draggable nested blocks -->
            <draggable 
                v-if="isBuilder && !isPreview"
                v-model="nestedBlocks" 
                item-key="id"
                :group="{ name: 'blocks', pull: true, put: true }"
                handle=".drag-handle"
                class="space-y-4 min-h-[60px] border-2 border-dashed border-transparent hover:border-primary/30 rounded-xl transition-colors flex flex-col flex-1"
                :class="nestedBlocks.length === 0 ? 'bg-primary/5 border-primary/20' : ''"
                ghost-class="block-ghost"
            >
                <template #item="{ element: block, index }">
                    <BlockWrapper 
                        :block="block" 
                        :index="index"
                        :context="context"
                        :isNested="true"
                        @edit="onEditBlock(block.id)"
                        @duplicate="onDuplicateNested(index)"
                        @delete="onDeleteNested(index)"
                    />
                </template>

                <template #footer>
                     <div v-if="nestedBlocks.length === 0" class="flex-1 flex flex-col items-center justify-center p-6 text-center relative z-[20]">
                        <button 
                            @click.stop.prevent="showBlockPicker = true"
                            type="button"
                            class="flex flex-col items-center gap-3 p-4 rounded-xl border-2 border-dashed border-muted-foreground/20 hover:border-primary/50 hover:bg-primary/5 transition-all group cursor-pointer w-full h-full justify-center min-h-[100px]"
                        >
                            <div class="w-12 h-12 rounded-xl bg-muted flex items-center justify-center group-hover:bg-primary/10 transition-colors">
                                <Plus class="w-6 h-6 text-muted-foreground group-hover:text-primary" />
                            </div>
                            <div class="space-y-1">
                                <span class="text-xs font-bold text-muted-foreground group-hover:text-primary block">Add Row or Element</span>
                                <span class="text-[10px] text-muted-foreground/70">Click to browse blocks</span>
                            </div>
                        </button>
                    </div>
                </template>
            </draggable>

            <!-- Live Mode: Render nested blocks -->
            <div v-else class="space-y-4">
                 <BlockRenderer 
                    :blocks="nestedBlocks" 
                    :context="context"
                    :is-preview="isPreview"
                />
            </div>
        </div>

        <!-- Block Picker Modal -->
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
import { Settings2, Copy, Trash2, GripVertical, Plus } from 'lucide-vue-next';
import BlockWrapper from '../canvas/BlockWrapper.vue';
import BlockRenderer from '../blocks/BlockRenderer.vue';
import BlockPicker from '../canvas/BlockPicker.vue';

defineOptions({
  inheritAttrs: false
});

const props = defineProps({
    // Props from BlockRenderer (flat)
    id: String,
    settings: Object,
    full_width: Boolean,
    padding_top: String,
    padding_bottom: String,
    background_color: String,
    background_image: String,
    blocks: { type: Array, default: () => [] },
    context: Object,
    isPreview: { type: Boolean, default: false }
});

const builder = inject('builder', null);
const isBuilder = computed(() => !!builder);

// Find the actual block object from builder state using ID
const blockObject = computed(() => {
    if (!builder || !props.id) return null;
    
    const findBlock = (blocks, id) => {
        for (const block of blocks) {
            if (block.id === id) return block;
            // Search in nested
            if (block.settings?.columns) {
                for (const col of block.settings.columns) {
                    const found = findBlock(col.blocks || [], id);
                    if (found) return found;
                }
            }
            if (block.settings?.blocks) {
                const found = findBlock(block.settings.blocks, id);
                if (found) return found;
            }
        }
        return null;
    };
    
    return findBlock(builder.blocks.value, props.id);
});

// Nested blocks - use blockObject for reactive updates
const nestedBlocks = computed({
    get: () => {
        // In builder mode, get from blockObject for reactivity
        if (blockObject.value) {
            return blockObject.value.settings?.blocks || [];
        }
        // Fallback to props for preview mode
        return props.blocks || props.settings?.blocks || [];
    },
    set: (val) => {
        if (blockObject.value) {
            if (!blockObject.value.settings) blockObject.value.settings = {};
            blockObject.value.settings.blocks = val;
            builder?.takeSnapshot();
        }
    }
});

const showBlockPicker = ref(false);

const handleAddBlock = (newBlock) => {
    if (!blockObject.value) return;
    if (!blockObject.value.settings) blockObject.value.settings = {};
    if (!blockObject.value.settings.blocks) blockObject.value.settings.blocks = [];
    blockObject.value.settings.blocks.push(newBlock);
    builder?.takeSnapshot();
    showBlockPicker.value = false;
};

const isSelected = computed(() => {
    if (!builder) return false;
    return builder.activeBlockId?.value === props.id;
});

// Styles
const sectionStyle = computed(() => {
    const style = {
        paddingTop: props.padding_top || props.settings?.padding_top || '4rem',
        paddingBottom: props.padding_bottom || props.settings?.padding_bottom || '4rem',
        backgroundColor: props.background_color || props.settings?.background_color || 'transparent',
    };

    const bgImage = props.background_image || props.settings?.background_image;
    if (bgImage) {
        style.backgroundImage = `url(${bgImage})`;
        style.backgroundSize = 'cover';
        style.backgroundPosition = 'center';
    }
    
    return style;
});

const containerClass = computed(() => {
    const fullWidth = props.full_width ?? props.settings?.full_width ?? false;
    return [
        fullWidth ? 'w-full' : (builder?.getGlobalSetting?.('container_max_width') || 'max-w-7xl mx-auto px-4'),
    ];
});

const onSelect = () => {
    if (builder && props.id) {
        builder.activeBlockId.value = props.id;
    }
};

const onEdit = () => {
    if (builder && props.id) {
        builder.activeBlockId.value = props.id;
        builder.activeRightSidebarTab.value = 'properties';
        builder.isRightSidebarOpen.value = true;
    }
};

const onEditBlock = (id) => {
    if (builder) builder.activeBlockId.value = id;
};

const onDuplicateNested = (index) => {
    if (!blockObject.value?.settings?.blocks) return;
    const original = blockObject.value.settings.blocks[index];
    const clone = {
        ...JSON.parse(JSON.stringify(original)),
        id: crypto.randomUUID()
    };
    blockObject.value.settings.blocks.splice(index + 1, 0, clone);
    builder?.takeSnapshot();
};

const onDeleteNested = (index) => {
    if (!blockObject.value?.settings?.blocks) return;
    blockObject.value.settings.blocks.splice(index, 1);
    builder?.takeSnapshot();
};
</script>

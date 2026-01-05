<template>
    <div class="relative group/section w-full transition-all duration-200" 
         :class="[
             isSelected ? 'ring-2 ring-primary ring-offset-2 z-10' : 'hover:ring-1 hover:ring-primary/50',
             getGlobalMarginClass(block)
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
            
            <button class="p-1 hover:bg-accent rounded" title="Settings" @click.stop="">
                 <Settings2 class="w-3.5 h-3.5" />
            </button>
            <button class="p-1 hover:bg-accent rounded" title="Duplicate" @click.stop="onDuplicate">
                <Copy class="w-3.5 h-3.5" />
            </button>
            <button class="p-1 hover:bg-destructive/10 hover:text-destructive rounded" title="Delete" @click.stop="onDelete">
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
                v-model="blocks" 
                item-key="id"
                :group="{ name: 'blocks', pull: true, put: true }"
                handle=".drag-handle"
                class="space-y-4 min-h-[60px] border-2 border-dashed border-transparent hover:border-primary/30 rounded-xl transition-colors flex flex-col flex-1"
                :class="blocks.length === 0 ? 'bg-primary/5 border-primary/20' : ''"
                ghost-class="block-ghost"
            >
                <template #item="{ element: block, index }">
                    <BlockWrapper 
                        :block="block" 
                        :index="index"
                        :context="context"
                        :isNested="true"
                        @edit="onEditBlock(block.id)"
                        @duplicate="onDuplicate(index)"
                        @delete="onDelete(index)"
                    />
                </template>

                <template #footer>
                     <div v-if="blocks.length === 0" class="flex-1 flex flex-col items-center justify-center p-6 text-center relative z-[20]">
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
                    v-for="(block, index) in blocks" 
                    :key="block.id" 
                    :block="block" 
                    :context="context"
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
import BlockRenderer from '../blocks/BlockRenderer.vue'; // Recursive renderer
import BlockPicker from '../canvas/BlockPicker.vue';

const props = defineProps({
    block: Object,
    context: Object,
    isSelected: Boolean,
});

const builder = inject('builder');
const isBuilder = computed(() => !!builder);
const isPreview = computed(() => builder?.isPreview.value);

// Nested blocks state (synced with block.settings.blocks)
const blocks = computed({
    get: () => props.block?.settings?.blocks || [],
    set: (val) => {
        if (!props.block) return;
        // Update model logic
        if (!props.block.settings) props.block.settings = {};
        props.block.settings.blocks = val;
        builder?.takeSnapshot();
    }
});

const showBlockPicker = ref(false);

const handleAddBlock = (newBlock) => {
    if (!props.block) return;
    // Add to nested blocks
    if (!props.block.settings) props.block.settings = {};
    if (!props.block.settings.blocks) props.block.settings.blocks = [];
    props.block.settings.blocks.push(newBlock);
    builder?.takeSnapshot();
    showBlockPicker.value = false;
};

// ... Styles
const sectionStyle = computed(() => {
    const s = props.block?.settings || {};
    const style = {
        paddingTop: s.padding_top || '4rem',
        paddingBottom: s.padding_bottom || '4rem',
        backgroundColor: s.background_color || 'transparent',
    };

    if (s.background_image) {
        style.backgroundImage = `url(${s.background_image})`;
        style.backgroundSize = 'cover';
        style.backgroundPosition = 'center';
    }
    
    return style;
});

const containerClass = computed(() => {
    const s = props.block?.settings || {};
    return [
        s.full_width ? 'w-full' : (builder?.getGlobalSetting?.('container_max_width') || 'max-w-7xl mx-auto px-4'),
    ];
});

// Helper for margin
const getGlobalMarginClass = (block) => {
    // Basic wrapper, implementation detail
    return 'mb-0'; 
}

const onSelect = () => {
    if (builder) builder.editingIndex.value = props.block.id; // Or however selection works
    // Actually builder.editingIndex is index based in root? 
    // For nested, we might need ID based selection.
    // Assuming BlockWrapper handles selection emit, but here we capture click.
    // If builder.activeBlockId is used.
    if(builder) builder.activeBlockId.value = props.block.id;
};

const onDelete = () => {
    // Emit delete event to parent
    // Needs BlockWrapper to handle this usually or emit up
};

const onDuplicate = () => {
    // ...
};

// Actions pass-through
const onEditBlock = (id) => builder.activeBlockId.value = id;

</script>

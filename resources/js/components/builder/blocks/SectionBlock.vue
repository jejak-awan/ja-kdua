<template>
    <section 
        class="relative group/section transition-all duration-300 h-full" 
        :class="[
            isSelected ? 'z-10' : '',
            radius,
            shadow !== 'none' ? shadow : ''
        ]"
        :style="wrapperStyle"
        v-bind="$attrs"
        @click.stop="onSelect"
    >
        <!-- Background Layer -->
        <div class="absolute inset-0 z-0 overflow-hidden" :class="radius" :style="backgroundStyle">
             <div 
                v-if="bgType === 'image' && bgImage" 
                class="absolute inset-0 bg-no-repeat w-full h-full transition-transform duration-700 hover:scale-105"
                :style="{ 
                    backgroundImage: `url(${bgImage})`,
                    backgroundSize: bgSize || 'cover',
                    backgroundPosition: bgPosition || 'center'
                }"
            ></div>
        </div>

        <!-- Overlay -->
        <div 
            v-if="overlayColor && overlayColor !== 'transparent'" 
            class="absolute inset-0 z-[1] pointer-events-none"
            :class="radius"
            :style="{ backgroundColor: overlayColor }"
        ></div>

        <!-- Selection Ring (Builder) -->
        <div v-if="isSelected && isBuilder && !isPreview" class="absolute inset-0 z-[20] border-2 border-primary pointer-events-none" :class="radius"></div>

        <!-- Content Container -->
        <!-- Resizable Wrapper integration: Usually we want to resize the SECTION height, not width -->
        <!-- But ResizableWrapper controls its own root div. Here we are inside a section. -->
        <!-- Let's wrap the internal flex container with ResizableWrapper logic? -->
        <!-- Or just apply ResizableWrapper at the section root? -->
        <!-- Since we are replacing the root element approach, let's keep it simple: -->
        <!-- Section height is controlled by min-height setting usually, OR content. -->
        <!-- Drag resizing specific min-height is useful. -->
        
        <div class="relative z-10 w-full h-full">
            <div :class="[containerClass, 'h-full flex flex-col']" :style="contentStyle">
                
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
                            @wrap="onWrapNested(index)"
                            @split="onSplitNested(index)"
                        />
                    </template>

                    <template #footer>
                         <div v-if="nestedBlocks.length === 0" class="flex-1 flex flex-col items-center justify-center p-6 text-center relative z-[20]">
                            <div 
                                @click.stop.prevent="showBlockPicker = true"
                                class="flex flex-col items-center gap-3 p-4 rounded-xl border-2 border-dashed border-muted-foreground/20 hover:border-primary/50 hover:bg-primary/5 transition-all group cursor-pointer w-full h-full justify-center min-h-[100px]"
                            >
                                <div class="w-12 h-12 rounded-xl bg-muted flex items-center justify-center group-hover:bg-primary/10 transition-colors">
                                    <Plus class="w-6 h-6 text-muted-foreground group-hover:text-primary" />
                                </div>
                                <div class="space-y-1">
                                    <span class="text-xs font-bold text-muted-foreground group-hover:text-primary block">Add Block</span>
                                    <span class="text-[10px] text-muted-foreground/70">Click to browse blocks</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </draggable>

                <!-- Live Mode: Render nested blocks -->
                <div v-else class="space-y-4 w-full h-full">
                     <BlockRenderer 
                        :blocks="nestedBlocks" 
                        :context="context"
                        :is-preview="isPreview"
                    />
                </div>
            </div>
        </div>

        <!-- Block Picker Modal -->
        <BlockPicker 
            :visible="showBlockPicker" 
            @close="showBlockPicker = false"
            @add="handleAddBlock"
        />
    </section>
</template>

<script setup>
import { computed, inject, ref } from 'vue';
import draggable from 'vuedraggable';
import { Plus } from 'lucide-vue-next';
import BlockWrapper from '../canvas/BlockWrapper.vue';
import BlockRenderer from '../blocks/BlockRenderer.vue';
import BlockPicker from '../canvas/BlockPicker.vue';
import { generateUUID } from '../utils';

defineOptions({
  inheritAttrs: false
});

const props = defineProps({
    id: String,
    // Settings
    full_width: Boolean,
    minHeight: { type: Number, default: 100 },
    verticalAlign: { type: String, default: 'start' },
    padding: { type: Object, default: () => ({ top: '64px', right: '0', bottom: '64px', left: '0' }) },
    margin: { type: Object, default: () => ({ top: '0', right: '0', bottom: '0', left: '0' }) },
    
    bgType: { type: String, default: 'color' },
    bgColor: String,
    bgImage: String,
    bgSize: String,
    bgPosition: String,
    gradientStart: String,
    gradientEnd: String,
    gradientDirection: String,
    
    borderWidth: { type: Number, default: 0 },
    borderColor: String,
    radius: { type: String, default: 'rounded-none' },
    shadow: { type: String, default: 'none' },
    overlayColor: String,
    
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

const wrapperStyle = computed(() => {
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
        minHeight: `${props.minHeight}px`
    };
    
    if (props.borderWidth > 0) {
        style.borderWidth = `${props.borderWidth}px`;
        style.borderColor = props.borderColor;
        style.borderStyle = 'solid';
    }
    
    return style;
});

const backgroundStyle = computed(() => {
    const style = {};
    // Always apply background color if present (allows fallback for images/gradients)
    if (props.bgColor) style.backgroundColor = props.bgColor;
    
    if (props.bgType === 'gradient') {
         style.backgroundImage = `linear-gradient(${props.gradientDirection || 'to right'}, ${props.gradientStart}, ${props.gradientEnd})`;
    }
    return style;
});

const containerClass = computed(() => {
    return props.full_width ? 'w-full' : (builder?.getGlobalSetting?.('container_max_width') || 'max-w-7xl mx-auto px-4');
});

const contentStyle = computed(() => {
    return {
        justifyContent: props.verticalAlign === 'center' ? 'center' : 
                       props.verticalAlign === 'end' ? 'flex-end' : 'flex-start'
    };
});

const onSelect = () => {
    if (builder && props.id) {
        builder.activeBlockId.value = props.id;
        builder.activeRightSidebarTab.value = 'properties';
        builder.isRightSidebarOpen.value = true;
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
            padding: { top: '24px', right: '24px', bottom: '24px', left: '24px' },
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

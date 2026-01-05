<template>
    <section 
        :class="['relative overflow-hidden transition-all duration-500', padding, radius, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <!-- Background Image Overlay -->
        <div 
            v-if="bgImage" 
            class="absolute inset-0 bg-cover bg-center z-0" 
            :style="{ backgroundImage: `url(${bgImage})` }"
        >
            <div class="absolute inset-0 bg-black/50"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-6 text-center">
            <h1 v-if="title" class="text-5xl md:text-7xl font-extrabold mb-6 tracking-tight drop-shadow-sm">
                {{ title }}
            </h1>
            <p v-if="subtitle" class="text-xl md:text-2xl max-w-3xl mx-auto leading-relaxed drop-shadow-sm opacity-80 mb-8">
                {{ subtitle }}
            </p>

            <!-- Nested Blocks Area -->
            <div class="relative min-h-[50px]">
                <!-- Builder Mode -->
                <draggable 
                    v-if="isBuilder && !isPreview"
                    v-model="nestedBlocks" 
                    item-key="id"
                    :group="{ name: 'blocks', pull: true, put: true }"
                    handle=".drag-handle"
                    class="space-y-4 min-h-[50px] transition-colors rounded-xl p-2"
                    :class="nestedBlocks.length === 0 ? 'border-2 border-dashed border-white/20 hover:border-white/50 bg-white/5' : ''"
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
                         <div v-if="nestedBlocks.length === 0" class="h-full flex flex-col items-center justify-center p-4 text-center">
                            <button 
                                @click.stop.prevent="showBlockPicker = true"
                                type="button"
                                class="text-sm font-medium text-white/50 hover:text-white transition-colors flex items-center gap-2"
                            >
                                <Plus class="w-4 h-4" />
                                <span>Add Button or Element</span>
                            </button>
                        </div>
                    </template>
                </draggable>

                <!-- Live Mode -->
                <div v-else class="space-y-4">
                     <BlockRenderer 
                        :blocks="nestedBlocks" 
                        :context="context"
                        :is-preview="isPreview"
                    />
                </div>
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
    title: String,
    subtitle: String,
    bgImage: String,
    bgColor: { type: String, default: 'transparent' },
    padding: { type: String, default: 'py-32' },
    radius: { type: String, default: 'rounded-none' },
    animation: { type: String, default: '' },
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

// Nested blocks logic
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
</script>


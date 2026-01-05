<template>
    <section 
        :class="['transition-all duration-500', padding, radius, animation]"
        :style="{ backgroundColor: bgColor || 'transparent', color: textColor || 'inherit' }"
    >
        <div :class="['container mx-auto px-6 text-center space-y-8', width]">
            <h2 v-if="title" class="text-4xl md:text-6xl font-extrabold tracking-tight">{{ title }}</h2>
            <p v-if="subtitle" class="text-xl opacity-90 leading-relaxed max-w-2xl mx-auto">{{ subtitle }}</p>
            <div class="pt-4 mb-4">
                <a 
                    :href="buttonUrl || '#'" 
                    class="inline-flex items-center px-10 py-5 bg-primary text-primary-foreground rounded-full font-bold text-lg hover:shadow-2xl transition-all shadow-xl transform hover:-translate-y-1 active:scale-95"
                >
                    {{ buttonText || 'Get Started' }}
                    <ArrowRight class="ml-2 w-5 h-5" />
                </a>
            </div>

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
                                class="text-sm font-medium opacity-50 hover:opacity-100 transition-colors flex items-center gap-2"
                            >
                                <Plus class="w-4 h-4" />
                                <span>Add Element</span>
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
defineOptions({
  inheritAttrs: false
});

import { computed, inject, ref } from 'vue';
import draggable from 'vuedraggable';
import { ArrowRight, Plus } from 'lucide-vue-next';
import BlockWrapper from '../canvas/BlockWrapper.vue';
import BlockRenderer from './BlockRenderer.vue';
import BlockPicker from '../canvas/BlockPicker.vue';
import { generateUUID } from '../utils';

const props = defineProps({
    id: String,
    title: String,
    subtitle: String,
    buttonText: String,
    buttonUrl: String,
    padding: { type: String, default: 'py-24' },
    width: { type: String, default: 'max-w-4xl' },
    bgColor: String,
    textColor: String,
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

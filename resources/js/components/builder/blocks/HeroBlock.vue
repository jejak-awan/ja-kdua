<template>
    <section 
        :class="['relative overflow-hidden transition-all duration-500 h-full', radius, entranceAnimation]"
        :style="sectionStyle"
        v-bind="$attrs"
    >
        <!-- Background Layer -->
        <div class="absolute inset-0 z-0" :style="backgroundStyle">
            <!-- Parallax wrapper -->
            <div 
                v-if="bgImage && bgType === 'image'" 
                class="absolute inset-0 bg-cover transition-transform duration-100"
                :style="{ 
                    backgroundImage: `url(${bgImage})`,
                    backgroundSize: bgSize || 'cover',
                    backgroundPosition: bgPosition || 'center',
                    transform: parallaxEnabled ? `translateY(${parallaxOffset}px)` : 'none'
                }"
            ></div>
        </div>

        <!-- Overlay -->
        <div 
            v-if="overlayEnabled" 
            class="absolute inset-0 z-[1]"
            :style="{ backgroundColor: overlayColor, opacity: overlayOpacity / 100 }"
        ></div>

        <!-- Content -->
        <div 
            class="relative z-10 w-full h-full flex flex-col"
            :class="[
                verticalAlign === 'start' ? 'justify-start' : '',
                verticalAlign === 'center' ? 'justify-center' : '',
                verticalAlign === 'end' ? 'justify-end' : ''
            ]"
            :style="contentContainerStyle"
        >
            <div :style="{ maxWidth: `${contentMaxWidth}px`, margin: '0 auto', width: '100%' }">
                <!-- Title -->
                <component 
                    :is="titleTag || 'h1'"
                    v-if="title" 
                    class="font-extrabold mb-6 tracking-tight transition-all duration-300"
                    :style="titleStyle"
                >
                    {{ title }}
                </component>
                
                <!-- Subtitle -->
                <p 
                    v-if="subtitle" 
                    class="leading-relaxed mb-8 transition-all duration-300"
                    :style="subtitleStyle"
                >
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
import { computed, inject, ref, onMounted, onUnmounted } from 'vue';
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
    // Content
    title: String,
    titleTag: { type: String, default: 'h1' },
    subtitle: String,
    
    // Title Style
    titleFontFamily: { type: String, default: 'inherit' },
    titleSize: { type: Number, default: 56 },
    titleWeight: { type: String, default: '700' },
    titleAlign: { type: String, default: 'center' },
    titleColor: { type: String, default: '#ffffff' },
    titleShadow: { type: Boolean, default: true },
    
    // Subtitle Style
    subtitleFontFamily: { type: String, default: 'inherit' },
    subtitleSize: { type: Number, default: 20 },
    subtitleWeight: { type: String, default: '400' },
    subtitleColor: { type: String, default: 'rgba(255, 255, 255, 0.8)' },
    subtitleMaxWidth: { type: Number, default: 700 },
    
    // Background
    bgType: { type: String, default: 'color' },
    bgColor: { type: String, default: '#18181b' },
    bgImage: String,
    bgSize: { type: String, default: 'cover' },
    bgPosition: { type: String, default: 'center' },
    gradientStart: { type: String, default: '#3b82f6' },
    gradientEnd: { type: String, default: '#8b5cf6' },
    gradientDirection: { type: String, default: 'to bottom right' },
    
    // Overlay
    overlayEnabled: { type: Boolean, default: true },
    overlayColor: { type: String, default: 'rgba(0, 0, 0, 0.5)' },
    overlayOpacity: { type: Number, default: 50 },
    
    // Layout
    minHeight: { type: Number, default: 500 },
    contentMaxWidth: { type: Number, default: 1200 },
    verticalAlign: { type: String, default: 'center' },
    padding: { type: Object, default: () => ({ top: '80px', right: '24px', bottom: '80px', left: '24px' }) },
    radius: { type: String, default: 'rounded-none' },
    
    // Animation
    entranceAnimation: { type: String, default: 'animate-fade' },
    animationDuration: { type: Number, default: 700 },
    animationDelay: { type: Number, default: 0 },
    parallaxEnabled: { type: Boolean, default: false },
    parallaxSpeed: { type: Number, default: 0.5 },
    
    // Nested
    blocks: { type: Array, default: () => [] },
    context: Object,
    isPreview: { type: Boolean, default: false }
});

const builder = inject('builder', null);
const isBuilder = computed(() => !!builder);
const showBlockPicker = ref(false);

// Parallax effect
const parallaxOffset = ref(0);
const handleScroll = () => {
    if (props.parallaxEnabled) {
        parallaxOffset.value = window.scrollY * props.parallaxSpeed;
    }
};

onMounted(() => {
    if (props.parallaxEnabled) {
        window.addEventListener('scroll', handleScroll);
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

// Computed styles
const sectionStyle = computed(() => ({
    minHeight: `${props.minHeight}px`,
    animationDuration: `${props.animationDuration}ms`,
    animationDelay: `${props.animationDelay}ms`
}));

const backgroundStyle = computed(() => {
    const style = {};
    
    switch (props.bgType) {
        case 'color':
            style.backgroundColor = props.bgColor;
            break;
        case 'gradient':
            const direction = props.gradientDirection || 'to bottom right';
            if (direction === 'radial') {
                style.backgroundImage = `radial-gradient(circle, ${props.gradientStart}, ${props.gradientEnd})`;
            } else {
                style.backgroundImage = `linear-gradient(${direction}, ${props.gradientStart}, ${props.gradientEnd})`;
            }
            break;
        case 'image':
            // Image is handled separately in the template
            if (!props.bgImage) {
                style.backgroundColor = props.bgColor;
            }
            break;
    }
    
    return style;
});

const contentContainerStyle = computed(() => {
    const p = props.padding || { top: '80px', right: '24px', bottom: '80px', left: '24px' };
    return {
        paddingTop: p.top,
        paddingRight: p.right,
        paddingBottom: p.bottom,
        paddingLeft: p.left
    };
});

const titleStyle = computed(() => ({
    fontFamily: props.titleFontFamily,
    fontSize: `${props.titleSize}px`,
    fontWeight: props.titleWeight,
    textAlign: props.titleAlign,
    color: props.titleColor,
    textShadow: props.titleShadow ? '0 2px 4px rgba(0,0,0,0.3)' : 'none'
}));

const subtitleStyle = computed(() => ({
    fontFamily: props.subtitleFontFamily,
    fontSize: `${props.subtitleSize}px`,
    fontWeight: props.subtitleWeight,
    textAlign: props.titleAlign, // Use same alignment as title
    color: props.subtitleColor,
    maxWidth: `${props.subtitleMaxWidth}px`,
    margin: props.titleAlign === 'center' ? '0 auto' : '0'
}));

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

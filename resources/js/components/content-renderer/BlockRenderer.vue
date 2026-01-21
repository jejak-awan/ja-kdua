<template>
    <div class="block-renderer w-full h-full">
        <template v-for="blockInstance in internalBlocks" :key="blockInstance.id">
            <!-- Visibility Condition Check -->
            <template v-if="ConditionEvaluator.evaluate(blockInstance, context)">
                <component 
                    :is="getBlockComponent(blockInstance.type)" 
                    v-bind="resolveBlockSettings(blockInstance)"
                    :context="context"
                    :is-preview="isPreview"
                    :mode="mode"
                    :id="blockInstance.id"
                />
            </template>
            <!-- Hidden placeholder for builder mode is handled by BaseBlock styles or can be re-added if needed for strictly hidden elements, 
                 but Visibility logic is now in BaseBlock. -->
        </template>
    </div>
</template>

<script setup>
import { computed, provide, getCurrentInstance } from 'vue';
import { blockRegistry } from './BlockRegistry';
import { dynamicContent } from '@/services/DynamicContentService';
import { ConditionEvaluator } from '@/services/ConditionEvaluator.js';

const props = defineProps({
    blocks: {
        type: Array,
        default: () => []
    },
    // Support single block for recursion
    block: {
        type: Object,
        default: null
    },
    context: {
        type: Object,
        default: () => ({})
    },
    isPreview: {
        type: Boolean,
        default: false
    },
    // Pass mode for shared blocks
    mode: {
        type: String,
        default: 'view'
    }
});

const internalBlocks = computed(() => {
    if (props.block) return [props.block];
    return props.blocks || [];
});

const instance = getCurrentInstance();
if (instance) {
    // Provide this component for recursion by name or type
    provide('BlockRenderer', instance.type);
}

const resolveBlockSettings = (block) => {
    // Simplified: Pass module, settings, and children.
    // Legacy prop flattening is removed as we expect blocks to use BaseBlock or access module/settings directly.
    
    const settings = { ...block.settings };
    
    // Process dynamic settings
    if (block.dynamicSettings) {
        Object.entries(block.dynamicSettings).forEach(([key, sourceId]) => {
            if (sourceId) {
                const resolvedValue = dynamicContent.resolve(sourceId, props.context);
                if (resolvedValue !== null && resolvedValue !== undefined) {
                    settings[key] = resolvedValue;
                }
            }
        });
    }
    
    return {
        module: block,
        settings: settings,
        nestedBlocks: block.children || []
    };
};

const getBlockComponent = (type) => {
    return blockRegistry.getComponent(type);
};
</script>

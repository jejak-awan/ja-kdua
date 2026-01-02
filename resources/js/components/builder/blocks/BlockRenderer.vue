<template>
    <div class="block-renderer w-full">
        <template v-for="block in blocks" :key="block.id">
            <div v-if="block && block.settings" :class="getVisibilityClasses(block.settings.visibility)">
                <component 
                    :is="getBlockComponent(block.type)" 
                    v-bind="resolveBlockSettings(block)"
                    :id="block.id"
                    class="block-item"
                />
            </div>
            <div v-else-if="block" class="p-4 border border-dashed rounded-lg bg-muted/20 text-xs text-muted-foreground text-center">
                Invalid Block: {{ block.type || 'Unknown' }}
            </div>
        </template>
    </div>
</template>

<script setup>
import { blockRegistry } from '../BlockRegistry';
import { dynamicContent } from '@/services/DynamicContentService';

const props = defineProps({
    blocks: {
        type: Array,
        default: () => []
    },
    context: {
        type: Object,
        default: () => ({})
    }
});

const resolveBlockSettings = (block) => {
    // Clone settings to avoid mutating original
    const settings = { ...block.settings };
    
    // Check for dynamic overrides
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
    
    return settings;
};

const getVisibilityClasses = (visibility) => {
    if (!visibility) return '';
    const v = visibility;
    let classes = [];
    
    if (v.mobile === false) classes.push('hidden');
    
    if (v.tablet !== v.mobile) {
        classes.push(v.tablet ? 'md:block' : 'md:hidden');
    }
    
    if (v.desktop !== v.tablet) {
        classes.push(v.desktop ? 'lg:block' : 'lg:hidden');
    }
    
    return classes.join(' ');
};

const getBlockComponent = (type) => {
    return blockRegistry.getComponent(type);
};
</script>

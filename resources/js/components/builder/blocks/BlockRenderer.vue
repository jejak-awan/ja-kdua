<template>
    <div class="block-renderer w-full">
        <template v-for="block in blocks" :key="block.id">
            <div 
                v-if="block && block.settings" 
                :class="[getVisibilityClasses(block.settings.visibility), block.settings._css_class]"
                :id="block.settings._css_id"
                :style="block.settings._custom_css"
            >
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

const applyPrefix = (str, prefix) => {
    if (!str && str !== 0) return '';
    return String(str).split(' ').filter(c => c.trim()).map(c => `${prefix}:${c.trim()}`).join(' ');
};

const resolveBlockSettings = (block) => {
    // Clone settings to avoid mutating original
    const settings = { ...block.settings };

    // 1. Resolve Responsive Objects to Tailwind Strings
    for (const key in settings) {
        const val = settings[key];
        // Check if it's a responsive object (not an array, not null)
        if (val && typeof val === 'object' && !Array.isArray(val) && ('desktop' in val || 'mobile' in val)) {
             // Mobile First strategy
             const mobile = val.mobile !== undefined ? val.mobile : '';
             const tablet = val.tablet;
             const desktop = val.desktop;
             
             let classes = [];
             
             // Base classes (mobile)
             if (mobile !== '') classes.push(mobile);
             
             // Tablet overrides (md:)
             if (tablet !== undefined && tablet !== mobile) {
                 classes.push(applyPrefix(tablet, 'md'));
             }
             
             // Desktop overrides (lg:)
             if (desktop !== undefined && desktop !== tablet) {
                 classes.push(applyPrefix(desktop, 'lg'));
             }
             
             settings[key] = classes.join(' ');
        }
    }
    
    // 2. Check for dynamic overrides
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

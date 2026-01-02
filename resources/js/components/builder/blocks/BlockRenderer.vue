<template>
    <div class="block-renderer w-full">
        <template v-for="block in blocks" :key="block.id">
            <!-- Visibility Condition Check -->
            <template v-if="ConditionEvaluator.evaluate(block, context)">
                <div 
                    v-if="block && block.settings" 
                    :class="[
                        getVisibilityClasses(block.settings.visibility), 
                        block.settings._css_class,
                        resolveAdvancedStyles(block.settings),
                        block.settings.animation_effect,
                        block.settings._position
                    ].filter(Boolean)"
                    :id="block.settings._css_id?.trim()"
                    :style="[
                        block.settings._custom_css,
                        getAnimationStyles(block.settings),
                        getPositioningStyles(block.settings)
                    ]"
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
            <!-- Hidden placeholder for builder mode -->
            <div v-else-if="!isPreview" class="p-4 border border-dashed border-primary/20 bg-primary/5 rounded-lg text-[10px] text-primary/60 text-center mb-4">
                <div class="flex items-center justify-center gap-2">
                    <Database class="w-3 h-3" />
                    <span>Hidden Block: {{ block.type }} (Rules not met)</span>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { blockRegistry } from '../BlockRegistry';
import { dynamicContent } from '@/services/DynamicContentService';
import { ConditionEvaluator } from '@/services/ConditionEvaluator.js';
import { Database } from 'lucide-vue-next';

const props = defineProps({
    blocks: {
        type: Array,
        default: () => []
    },
    context: {
        type: Object,
        default: () => ({})
    },
    isPreview: {
        type: Boolean,
        default: false
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

const resolveAdvancedStyles = (settings) => {
    let classes = [];
    
    // Mapping of setting key to Tailwind prefix
    const mapping = {
        margin_top: 'mt',
        margin_bottom: 'mb',
        margin_left: 'ml',
        margin_right: 'mr',
        blur: 'blur',
        brightness: 'brightness',
        contrast: 'contrast',
        saturate: 'saturate',
        hue_rotate: 'hue-rotate',
        scale: 'scale',
        rotate: 'rotate',
        skew_x: 'skew-x',
        skew_y: 'skew-y',
        translate_x: 'translate-x',
        translate_y: 'translate-y'
    };

    let hasFilter = false;
    let hasTransform = false;

    Object.entries(mapping).forEach(([key, prefix]) => {
        const val = settings[key];
        if (val === undefined || val === null || val === '' || val === 0) return;

        // Special check for default values to avoid unnecessary classes
        if ((key === 'scale' || key === 'brightness' || key === 'contrast' || key === 'saturate') && val === 100) return;
        if ((key === 'rotate' || key === 'skew_x' || key === 'skew_y' || key === 'translate_x' || key === 'translate_y') && val === 0) return;

        if (['blur', 'brightness', 'contrast', 'saturate', 'hue_rotate'].includes(key)) hasFilter = true;
        if (['scale', 'rotate', 'skew_x', 'skew_y', 'translate_x', 'translate_y'].includes(key)) hasTransform = true;

        if (typeof val === 'object' && !Array.isArray(val)) {
            if (val.mobile !== undefined && val.mobile !== '') classes.push(`${prefix}-${val.mobile}`);
            if (val.tablet !== undefined && val.tablet !== '' && val.tablet !== val.mobile) classes.push(`md:${prefix}-${val.tablet}`);
            if (val.desktop !== undefined && val.desktop !== '' && val.desktop !== val.tablet) classes.push(`lg:${prefix}-${val.desktop}`);
        } else {
            classes.push(`${prefix}-${val}`);
        }
    });

    // Handle Box Shadow separately as it's often a full class name already
    if (settings.shadow && settings.shadow !== 'none') {
        const shadow = settings.shadow;
        if (typeof shadow === 'object' && !Array.isArray(shadow)) {
            if (shadow.mobile) classes.push(shadow.mobile);
            if (shadow.tablet && shadow.tablet !== shadow.mobile) classes.push(`md:${shadow.tablet}`);
            if (shadow.desktop && shadow.desktop !== shadow.tablet) classes.push(`lg:${shadow.desktop}`);
        } else {
            classes.push(shadow);
        }
    }

    if (hasFilter) classes.push('filter');
    if (hasTransform) classes.push('transform');

    return classes.join(' ');
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

const getAnimationStyles = (settings) => {
    if (!settings.animation_effect) return {};
    
    return {
        animationDuration: `${settings.animation_duration || 600}ms`,
        animationDelay: `${settings.animation_delay || 0}ms`,
        animationIterationCount: settings.animation_repeat === 'infinite' ? 'infinite' : '1'
    };
};

const getPositioningStyles = (settings) => {
    let styles = {};
    if (settings._z_index !== undefined && settings._z_index !== 0) {
        styles.zIndex = settings._z_index;
    }
    if (settings._position === 'sticky' || settings._position === 'fixed') {
        styles.top = `${settings._sticky_top || 0}px`;
    }
    return styles;
};

const getBlockComponent = (type) => {
    return blockRegistry.getComponent(type);
};
</script>

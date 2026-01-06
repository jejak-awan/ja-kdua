<template>
    <div class="block-renderer w-full h-full">
        <template v-for="block in blocks" :key="block.id">
            <!-- Visibility Condition Check -->
            <template v-if="ConditionEvaluator.evaluate(block, context)">
                <div 
                    v-if="block && block.settings" 
                    :class="[
                        getVisibilityClasses(block.settings.visibility), 
                        resolveResponsiveValue(block.settings._css_class),
                        resolveAdvancedStyles(block.settings),
                        resolveAdvancedStyles(block.hoverSettings, 'hover'),
                        resolveResponsiveValue(block.settings.animation_effect),
                        resolveResponsiveValue(block.settings._position),
                        getColorClasses(block),
                        'h-full'
                    ].filter(Boolean)"
                    :id="block.settings._css_id ? String(resolveResponsiveValue(block.settings._css_id)).trim().replace(/\s/g, '-') : undefined"
                    :style="[
                        block.settings._custom_css,
                        getAnimationStyles(block.settings),
                        getPositioningStyles(block.settings),
                        getColorStyles(block)
                    ]"
                >
                    <component 
                        :is="getBlockComponent(block.type)" 
                        v-bind="resolveBlockSettings(block)"
                        :context="context"
                        :is-preview="isPreview"
                        :id="block.id"
                        class="block-item h-full"
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

const resolveResponsiveValue = (val) => {
    if (!val) return '';
    if (typeof val !== 'object' || Array.isArray(val)) return String(val);
    
    // Check if it's a responsive object
    if ('desktop' in val || 'mobile' in val || 'tablet' in val) {
         const mobile = val.mobile !== undefined ? val.mobile : '';
         const tablet = val.tablet;
         const desktop = val.desktop;
         
         let classes = [];
         if (mobile !== '') classes.push(mobile);
         if (tablet !== undefined && tablet !== mobile) {
             classes.push(applyPrefix(tablet, 'md'));
         }
         if (desktop !== undefined && desktop !== tablet) {
             classes.push(applyPrefix(desktop, 'lg'));
         }
         
         return classes.join(' ');
    }
    
    return ''; // Generic object that we don't know how to handle
};

const resolveBlockSettings = (block) => {
    // Clone settings to avoid mutating original
    const settings = { ...block.settings };
    const propsToPass = {};

    // 1. Resolve Responsive Objects and filterProps
    for (const key in settings) {
        // Strict key validation: only allow alphanumeric, underscore, hyphen
        // MUST start with a letter to avoid DOMException with setAttribute ('1foo', etc)
        if (!/^[a-zA-Z][a-zA-Z0-9_-]*$/.test(key)) continue;

        // Skip internal settings (prefixed with _)
        if (key.startsWith('_')) continue;
        
        // Skip system-wide settings handled by BlockRenderer
        if (['visibility', 'animation_effect', 'animation_duration', 'animation_delay', 'animation_repeat'].includes(key)) continue;

        let val = settings[key];
        
        // Resolve responsive objects
        if (val && typeof val === 'object' && !Array.isArray(val) && ('desktop' in val || 'mobile' in val)) {
             val = resolveResponsiveValue(val);
        }
        
        propsToPass[key] = val;
    }
    
    // 2. Add settings object itself for backward compatibility with blocks not yet refactored to flat props
    propsToPass.settings = settings;

    // 3. Check for dynamic overrides
    if (block.dynamicSettings) {
        Object.entries(block.dynamicSettings).forEach(([key, sourceId]) => {
            if (sourceId && propsToPass.hasOwnProperty(key)) {
                const resolvedValue = dynamicContent.resolve(sourceId, props.context);
                if (resolvedValue !== null && resolvedValue !== undefined) {
                    propsToPass[key] = resolvedValue;
                }
            }
        });
    }
    
    return propsToPass;
};

const resolveAdvancedStyles = (settings, prefix = '') => {
    if (!settings) return '';
    let classes = [];
    const p = prefix ? `${prefix}:` : '';
    
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
        translate_y: 'translate-y',
        bgColor: 'bg' // Add bgColor mapping for hover
    };

    let hasFilter = false;
    let hasTransform = false;

    Object.entries(mapping).forEach(([key, tailwindPrefix]) => {
        const val = settings[key];
        if (val === undefined || val === null || val === '' || val === 0) return;

        // Special check for default values to avoid unnecessary classes
        if (!prefix) {
            if ((key === 'scale' || key === 'brightness' || key === 'contrast' || key === 'saturate') && val === 100) return;
            if ((key === 'rotate' || key === 'skew_x' || key === 'skew_y' || key === 'translate_x' || key === 'translate_y') && val === 0) return;
        }

        if (['blur', 'brightness', 'contrast', 'saturate', 'hue_rotate'].includes(key)) hasFilter = true;
        if (['scale', 'rotate', 'skew_x', 'skew_y', 'translate_x', 'translate_y'].includes(key)) hasTransform = true;

        if (typeof val === 'object' && !Array.isArray(val)) {
            if (val.mobile !== undefined && val.mobile !== '') classes.push(`${p}${tailwindPrefix}-${val.mobile}`);
            if (val.tablet !== undefined && val.tablet !== '' && val.tablet !== val.mobile) classes.push(`${p}md:${tailwindPrefix}-${val.tablet}`);
            if (val.desktop !== undefined && val.desktop !== '' && val.desktop !== val.tablet) classes.push(`${p}lg:${tailwindPrefix}-${val.desktop}`);
        } else {
            classes.push(`${p}${tailwindPrefix}-${val}`);
        }
    });

    // Handle Box Shadow separately
    if (settings.shadow && settings.shadow !== 'none') {
        const shadow = settings.shadow;
        if (typeof shadow === 'object' && !Array.isArray(shadow)) {
            if (shadow.mobile) classes.push(`${p}${shadow.mobile}`);
            if (shadow.tablet && shadow.tablet !== shadow.mobile) classes.push(`${p}md:${shadow.tablet}`);
            if (shadow.desktop && shadow.desktop !== shadow.tablet) classes.push(`${p}lg:${shadow.desktop}`);
        } else {
            classes.push(`${p}${shadow}`);
        }
    }

    if (hasFilter) classes.push(`${p}filter`);
    if (hasTransform) classes.push(`${p}transform`);

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
const getColorStyles = (block) => {
    const settings = block.settings || {};
    const hover = block.hoverSettings || {};
    
    // Check for textColor or color (legacy/alt names)
    const textColor = settings.textColor || settings.color;
    const hoverTextColor = hover.textColor || hover.color;
    
    const bgColor = settings.bgColor || settings.background_color;
    const hoverBgColor = hover.bgColor || hover.background_color;
    
    const styles = {};
    
    if (textColor) styles['--text-color'] = textColor;
    if (hoverTextColor) styles['--hover-text-color'] = hoverTextColor;
    
    if (bgColor) styles['--bg-color'] = bgColor;
    if (hoverBgColor) styles['--hover-bg-color'] = hoverBgColor;
    
    return styles;
};

const getColorClasses = (block) => {
    const hover = block.hoverSettings || {};
    const classes = [];
    
    if (hover.textColor || hover.color) {
        classes.push('hover:[--text-color:var(--hover-text-color)]');
    }
    
    if (hover.bgColor || hover.background_color) {
        classes.push('hover:[--bg-color:var(--hover-bg-color)]');
    }
    
    return classes.join(' ');
};
</script>

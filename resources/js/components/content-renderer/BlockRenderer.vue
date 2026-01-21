<template>
    <div class="block-renderer w-full h-auto">
        <template v-for="block in (blocks || [])" :key="block.id">
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
                        getColorClasses(block)
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
import { blockRegistry } from './BlockRegistry';
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
    if (!str && str !== 0) return [];
    return String(str).split(' ').filter(c => c.trim()).map(c => `${prefix}:${c.trim()}`);
};

const resolveResponsiveValue = (val, asClasses = true) => {
    if (!val) return asClasses ? [] : '';
    if (typeof val !== 'object' || Array.isArray(val)) {
        return asClasses ? String(val).split(' ').filter(Boolean) : String(val);
    }
    
    // Check if it's a responsive object
    if ('desktop' in val || 'mobile' in val || 'tablet' in val) {
         // Inheritance Logic: Mobile -> Tablet -> Desktop
         const desktop = val.desktop;
         const tablet = (val.tablet !== undefined && val.tablet !== null && val.tablet !== '') ? val.tablet : desktop;
         const mobile = (val.mobile !== undefined && val.mobile !== null && val.mobile !== '') ? val.mobile : tablet;
         
         if (asClasses) {
             let classes = [];
             if (mobile !== '') {
                 // Split mobile value in case it contains multiple classes
                 classes.push(...String(mobile).split(' ').filter(Boolean));
             }
             if (tablet !== undefined && tablet !== mobile) {
                 classes.push(...applyPrefix(tablet, 'md'));
             }
             if (desktop !== undefined && desktop !== tablet) {
                 classes.push(...applyPrefix(desktop, 'lg'));
             }
             return classes;
         } else {
             // For props (non-class), we default to desktop as the "base" value for JS props
             // This is a limitation for JS-only props, but most styling should be CSS classes.
             return desktop;
         }
    }
    
    return asClasses ? [] : ''; 
};

const resolveBlockSettings = (block) => {
    const settings = { ...block.settings };
    const propsToPass = {
        nestedBlocks: block.children || []
    };

    for (const key in settings) {
        if (!/^[a-zA-Z][a-zA-Z0-9_-]*$/.test(key)) continue;
        if (key.startsWith('_')) continue;
        if (['visibility', 'animation_effect', 'animation_duration', 'animation_delay', 'animation_repeat'].includes(key)) continue;

        let val = settings[key];
        
        // Resolve responsive objects
        if (val && typeof val === 'object' && !Array.isArray(val) && ('desktop' in val || 'mobile' in val)) {
             // Unlike before, we ALWAYS try to resolve to a single value for JS props (Desktop)
             // or keep it if the component can handle objects (rare).
             // For standard props, we take the desktop value to ensure stability.
             
             // Check if it's likely class-based (string with spaces or dashes)
             const isProbablyClasses = typeof val.desktop === 'string' && (val.desktop.includes('-') || val.desktop.includes(' '));
             
             if (isProbablyClasses) {
                 // If it looks like classes, we want the full array
                 propsToPass[key] = resolveResponsiveValue(val, true).join(' ');
             } else {
                // Otherwise, it's a raw value (e.g. number for a counter).
                // We default to desktop to avoid breaking the component logic.
                propsToPass[key] = val.desktop; 
             }
        } else {
            propsToPass[key] = val;
        }
    }
    
    propsToPass.settings = settings;

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
    if (!settings) return [];
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
            const desktop = val.desktop;
            const tablet = (val.tablet !== undefined && val.tablet !== null && val.tablet !== '') ? val.tablet : desktop;
            const mobile = (val.mobile !== undefined && val.mobile !== null && val.mobile !== '') ? val.mobile : tablet;

            // Always render full responsive classes
            if (mobile !== undefined && mobile !== '') classes.push(`${p}${tailwindPrefix}-${mobile}`);
            if (tablet !== undefined && tablet !== '' && tablet !== mobile) classes.push(`${p}md:${tailwindPrefix}-${tablet}`);
            if (desktop !== undefined && desktop !== '' && desktop !== tablet) classes.push(`${p}lg:${tailwindPrefix}-${desktop}`);
        } else {
            classes.push(`${p}${tailwindPrefix}-${val}`);
        }
    });

    // Handle Box Shadow separately
    if (settings.shadow && settings.shadow !== 'none') {
        const shadow = settings.shadow;
        if (typeof shadow === 'object' && !Array.isArray(shadow)) {
            const desktop = shadow.desktop;
            const tablet = (shadow.tablet !== undefined && shadow.tablet !== null && shadow.tablet !== '') ? shadow.tablet : desktop;
            const mobile = (shadow.mobile !== undefined && shadow.mobile !== null && shadow.mobile !== '') ? shadow.mobile : tablet;

            if (mobile) classes.push(`${p}${mobile}`);
            if (tablet && tablet !== mobile) classes.push(`${p}md:${tablet}`);
            if (desktop && desktop !== tablet) classes.push(`${p}lg:${desktop}`);
        } else {
            classes.push(`${p}${shadow}`);
        }
    }

    if (hasFilter) classes.push(`${p}filter`);
    if (hasTransform) classes.push(`${p}transform`);

    return classes;
};

const getVisibilityClasses = (visibility) => {
    if (!visibility) return [];
    const v = visibility;
    const desktop = v.desktop !== false;
    const tablet = v.tablet !== undefined ? v.tablet !== false : desktop;
    const mobile = v.mobile !== undefined ? v.mobile !== false : tablet;

    // We can't use "opacity-30" trick for hidden elements in builder anymore 
    // because we are using real media queries.
    // Instead, we just let them be hidden (or block) based on the current iframe width.
    // To allow editing "hidden" elements, the user must switch to the device mode where it is visible.
    
    let classes = [];
    if (!mobile) classes.push('hidden');
    
    if (tablet !== mobile) {
        classes.push(tablet ? 'md:block' : 'md:hidden');
    }
    
    if (desktop !== tablet) {
        classes.push(desktop ? 'lg:block' : 'lg:hidden');
    }
    
    return classes;
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

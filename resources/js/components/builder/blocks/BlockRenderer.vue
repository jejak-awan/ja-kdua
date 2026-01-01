<template>
    <div class="block-renderer w-full">
        <template v-for="block in blocks" :key="block.id">
            <div v-if="block && block.settings" :class="getVisibilityClasses(block.settings.visibility)">
                <component 
                    :is="getBlockComponent(block.type)" 
                    v-bind="block.settings"
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
import { defineAsyncComponent } from 'vue';

// Import all blocks dynamically from the builder/blocks directory
const blockModules = import.meta.glob('@/components/builder/blocks/*.vue', { eager: true });

const props = defineProps({
    blocks: {
        type: Array,
        default: () => []
    }
});

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
    switch (type) {
        case 'hero':
            return defineAsyncComponent(() => import('./HeroBlock.vue'));
        case 'text':
            return defineAsyncComponent(() => import('./TextBlock.vue'));
        case 'image':
            return defineAsyncComponent(() => import('./ImageBlock.vue'));
        case 'features':
            return defineAsyncComponent(() => import('./FeatureGridBlock.vue'));
        case 'cta':
            return defineAsyncComponent(() => import('./CTABlock.vue'));
        case 'video':
            return defineAsyncComponent(() => import('./VideoBlock.vue'));
        case 'spacer':
            return defineAsyncComponent(() => import('./SpacerBlock.vue'));
        case 'gallery':
            return defineAsyncComponent(() => import('./GalleryBlock.vue'));
        case 'columns':
            return defineAsyncComponent(() => import('./ColumnsBlock.vue'));
        case 'pricing':
            return defineAsyncComponent(() => import('./PricingBlock.vue'));
        case 'accordion':
            return defineAsyncComponent(() => import('./AccordionBlock.vue'));
        case 'testimonial':
            return defineAsyncComponent(() => import('./TestimonialBlock.vue'));
        default:
            return null;
    }
};
</script>

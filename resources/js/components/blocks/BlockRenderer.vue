<template>
    <div class="block-renderer w-full">
        <template v-for="block in blocks" :key="block.id">
            <div :class="getVisibilityClasses(block.settings.visibility)">
                <component 
                    :is="getBlockComponent(block.type)" 
                    v-bind="block.settings"
                    class="block-item"
                />
            </div>
        </template>
    </div>
</template>

<script setup>
import { defineAsyncComponent } from 'vue';

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
        case 'testimonials':
            return defineAsyncComponent(() => import('./TestimonialBlock.vue'));
        default:
            return null;
    }
};
</script>

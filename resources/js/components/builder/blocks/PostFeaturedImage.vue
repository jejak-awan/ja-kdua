<template>
    <div :style="containerStyle" :class="[
        'relative overflow-hidden group',
        config.aspectRatio,
        config.rounded,
        config.shadow
    ]">
        <img 
            v-if="imageUrl" 
            :src="imageUrl" 
            :alt="imageAlt"
            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
        />
        <div v-else class="w-full h-full flex items-center justify-center bg-muted/20 text-muted-foreground italic text-sm">
            <div class="flex flex-col items-center gap-2">
                <ImageIcon class="w-8 h-8 opacity-20" />
                <span>Featured Image Placeholder</span>
            </div>
        </div>
        
        <!-- Overlay if configured -->
        <div v-if="config.overlay" class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors"></div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Image as ImageIcon } from 'lucide-vue-next';

const props = defineProps({
    config: {
        type: Object,
        default: () => ({
            aspectRatio: 'aspect-video',
            rounded: 'rounded-lg',
            shadow: 'shadow-md',
            overlay: false,
            customHeight: ''
        })
    },
    context: {
        type: Object,
        default: () => ({})
    }
});

const imageUrl = computed(() => {
    // 1. Check from context (passed via inject or prop)
    const post = props.context?.post;
    if (post?.featured_image) return post.featured_image;
    
    // 2. Fallback to demo content in builder mode
    if (props.context?.builderMode) {
        return 'https://images.unsplash.com/photo-1491841573634-28140fc7ced7?q=80&w=1200';
    }
    
    return null;
});

const imageAlt = computed(() => props.context?.post?.title || 'Featured Image');

const containerStyle = computed(() => {
    if (props.config.customHeight) {
        return { height: props.config.customHeight };
    }
    return {};
});
</script>

<script setup>
import { computed } from 'vue';
import * as Icons from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    links: {
        type: Array,
        default: () => []
    },
    alignment: {
        type: String,
        default: 'center'
    },
    icon_size: {
        type: String,
        default: '24px'
    },
    shape: {
        type: String,
        default: 'rounded'
    },
    style: {
        type: String,
        default: 'filled'
    }
});

const getIcon = (platform) => {
    const mapping = {
        facebook: 'Facebook',
        twitter: 'Twitter',
        instagram: 'Instagram',
        linkedin: 'Linkedin',
        youtube: 'Youtube',
        github: 'Github',
        tiktok: 'Music2'
    };
    return Icons[mapping[platform]] || Icons.Share2;
};

const alignmentClass = computed(() => {
    return {
        'justify-start': props.alignment === 'start',
        'justify-center': props.alignment === 'center',
        'justify-end': props.alignment === 'end'
    };
});

const getShapeClass = computed(() => {
    return {
        'rounded-none': props.shape === 'square',
        'rounded-lg': props.shape === 'rounded',
        'rounded-full': props.shape === 'circle'
    };
});

const getIconStyle = (platformColor) => {
    const size = parseInt(props.icon_size) || 24;
    let styles = {
        width: `${size * 1.8}px`,
        height: `${size * 1.8}px`
    };
    
    if (props.style === 'filled') {
        styles.backgroundColor = platformColor || '#3B82F6';
        styles.color = 'white';
    } else if (props.style === 'outline') {
        styles.border = `2px solid ${platformColor || '#3B82F6'}`;
        styles.color = platformColor || '#3B82F6';
    } else {
        styles.color = platformColor || '#3B82F6';
    }
    
    return styles;
};
</script>

<template>
    <div class="flex flex-wrap gap-4" :class="alignmentClass">
        <a 
            v-for="(link, index) in links" 
            :key="index"
            :href="link.url || '#'"
            target="_blank"
            class="flex items-center justify-center transition-transform hover:scale-110"
            :class="getShapeClass"
            :style="getIconStyle(link.color)"
        >
            <component 
                :is="getIcon(link.platform)" 
                :size="parseInt(icon_size) || 24"
            />
        </a>
    </div>
</template>

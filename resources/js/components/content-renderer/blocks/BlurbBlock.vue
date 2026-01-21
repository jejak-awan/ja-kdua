<script setup>
import { computed } from 'vue';
import * as Icons from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    mediaType: { type: String, default: 'icon' }, // 'icon', 'image', 'none'
    iconName: { type: String, default: 'Zap' },
    iconColor: { type: String, default: '' },
    iconBackgroundColor: { type: String, default: '' }, // Added for builder completeness
    iconBackgroundShape: { type: String, default: 'none' }, // Added for builder completeness
    iconSize: { type: [Number, String], default: 48 }, // Can come as number from slider
    image: { type: String, default: '' },
    iconPosition: { type: String, default: 'top' }, // 'top', 'left', 'right'
    alignment: { type: String, default: 'center' },
    title: { type: String, default: 'Your Feature Title' },
    title_tag: { type: String, default: 'h3' },
    content: { type: String, default: 'Add a short description here.' },
    titleSize: { type: String, default: 'text-xl' },
    titleColor: { type: String, default: '' },
    contentSize: { type: String, default: 'text-base' },
    contentColor: { type: String, default: '' }
});

const iconComponent = computed(() => {
    return props.iconName && Icons[props.iconName] ? Icons[props.iconName] : Icons.Zap;
});

const containerClass = computed(() => {
    const classes = {
        'flex flex-col': props.iconPosition === 'top',
        'flex flex-row gap-6': props.iconPosition === 'left', // removed items-center to allow top alignment control via separate prop if needed, but flex-row implies side-by-side
        'flex flex-row-reverse gap-6': props.iconPosition === 'right' // Added right support
    };
    
    // Text Alignment
    classes['text-left'] = props.alignment === 'left';
    classes['text-center'] = props.alignment === 'center';
    classes['text-right'] = props.alignment === 'right';

    // Flex Alignment based on text alignment when stacked
    if (props.iconPosition === 'top') {
        if (props.alignment === 'center') classes['items-center'] = true;
        else if (props.alignment === 'right') classes['items-end'] = true;
        else classes['items-start'] = true;
    } else {
        // When side-by-side, usually align top or center. Defaulting to start for now to match common blurb styles
        classes['items-start'] = true;
    }

    return classes;
});

const iconStyle = computed(() => {
    const style = {
        color: props.iconColor || 'inherit'
    };
    
    if (props.iconBackgroundColor) {
        style.backgroundColor = props.iconBackgroundColor;
    }
    
    return style;
});

const iconContainerClass = computed(() => {
    const classes = ['transition-transform hover:rotate-6'];
    
    // Shape logic
    if (props.iconBackgroundColor) {
        classes.push('p-4'); // Add padding if background exists
        if (props.iconBackgroundShape === 'circle') classes.push('rounded-full');
        else if (props.iconBackgroundShape === 'square') classes.push('rounded-none');
        else if (props.iconBackgroundShape === 'rounded') classes.push('rounded-xl');
    } else {
         // Default if no bg specific settings but maybe some preset style wanted?
         // For now keep minimal
    }
    
    return classes;
});
</script>

<template>
    <div :class="containerClass" class="blurb-item">
        <!-- Media (Icon or Image) -->
        <div class="blurb-media mb-4 flex-shrink-0" :class="{ 'mb-0': iconPosition !== 'top' }">
            <template v-if="mediaType === 'icon'">
                <div 
                    :class="iconContainerClass"
                    :style="iconStyle"
                >
                    <component 
                        :is="iconComponent" 
                        :size="Number(iconSize) || 48" 
                        stroke-width="1.5" 
                    />
                </div>
            </template>
            <template v-else-if="mediaType === 'image' && image">
                <img :src="image" :alt="title" class="w-24 h-24 object-cover rounded-xl shadow-lg" />
            </template>
        </div>

        <!-- Content -->
        <div class="blurb-content flex-1 min-w-0">
            <component 
                :is="title_tag" 
                class="font-bold mb-2 tracking-tight transition-all duration-300"
                :class="[titleSize || 'text-xl']"
                :style="{ color: titleColor || '' }"
            >
                {{ title }}
            </component>
            <div 
                class="opacity-80 leading-relaxed transition-all duration-300 prose prose-sm dark:prose-invert max-w-none"
                :class="[contentSize || 'text-base']"
                :style="{ color: contentColor || '' }"
                v-html="content"
            ></div>
        </div>
    </div>
</template>

<style scoped>
.blurb-item:hover .blurb-media div {
   /* Optional hover effect */
}
</style>

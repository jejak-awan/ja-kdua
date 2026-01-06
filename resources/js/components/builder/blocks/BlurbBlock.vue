<script setup>
import { computed } from 'vue';
import * as Icons from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    use_icon: {
        type: Boolean,
        default: true
    },
    icon_name: {
        type: String,
        default: 'Zap'
    },
    icon_color: {
        type: String,
        default: ''
    },
    image_url: {
        type: String,
        default: ''
    },
    icon_position: {
        type: String,
        default: 'top'
    },
    alignment: {
        type: String,
        default: 'center'
    },
    title: {
        type: String,
        default: 'Your Feature Title'
    },
    title_tag: {
        type: String,
        default: 'h3'
    },
    content: {
        type: String,
        default: 'Add a short description here.'
    },
    titleSize: { type: String, default: 'text-xl' },
    titleColor: { type: String, default: '' },
    contentSize: { type: String, default: 'text-base' },
    contentColor: { type: String, default: '' }
});

const iconComponent = computed(() => {
    return props.icon_name && Icons[props.icon_name] ? Icons[props.icon_name] : Icons.Zap;
});

const containerClass = computed(() => {
    return {
        'flex flex-col': props.icon_position === 'top',
        'flex flex-row items-center gap-6': props.icon_position === 'left',
        'text-left': props.alignment === 'left',
        'text-center': props.alignment === 'center',
        'text-right': props.alignment === 'right',
        'items-center': props.alignment === 'center' && props.icon_position === 'top',
        'items-start': props.alignment === 'left' && props.icon_position === 'top',
        'items-end': props.alignment === 'right' && props.icon_position === 'top'
    };
});
</script>

<template>
    <div :class="containerClass" class="blurb-item">
        <!-- Media (Icon or Image) -->
        <div class="blurb-media mb-4" :class="{ 'mb-0': icon_position === 'left' }">
            <template v-if="use_icon">
                <div 
                    class="p-4 rounded-2xl bg-primary/10 text-primary transition-transform hover:rotate-6"
                    :style="{ color: icon_color || '' }"
                >
                    <component :is="iconComponent" :size="48" stroke-width="1.5" />
                </div>
            </template>
            <template v-else-if="image_url">
                <img :src="image_url" :alt="title" class="w-24 h-24 object-cover rounded-xl shadow-lg" />
            </template>
        </div>

        <!-- Content -->
        <div class="blurb-content flex-1">
            <component 
                :is="title_tag" 
                class="font-bold mb-2 tracking-tight transition-all duration-300"
                :class="[titleSize || 'text-xl']"
                :style="{ color: titleColor || '' }"
            >
                {{ title }}
            </component>
            <p 
                class="opacity-80 leading-relaxed transition-all duration-300"
                :class="[contentSize || 'text-base']"
                :style="{ color: contentColor || '' }"
            >
                {{ content }}
            </p>
        </div>
    </div>
</template>

<style scoped>
.blurb-item:hover .blurb-media div {
    background-color: hsl(var(--primary) / 0.2);
}
</style>

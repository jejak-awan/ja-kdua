<template>
    <section 
        :class="['transition-all duration-500', padding, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto px-6', width]">
            <h3 v-if="title" class="text-2xl font-bold mb-6" :class="alignmentClass">{{ title }}</h3>
            <ul :class="['space-y-4', alignmentClass]">
                <li 
                    v-for="(item, index) in items" 
                    :key="index"
                    class="flex items-start gap-4"
                >
                    <div 
                        :class="[
                            'flex-shrink-0 flex items-center justify-center',
                            iconSizeClass
                        ]"
                        :style="{ 
                            backgroundColor: iconBgColor || 'transparent',
                            color: iconColor || 'hsl(var(--primary))',
                            width: !isNaN(iconSize) ? 'auto' : undefined,
                            height: !isNaN(iconSize) ? 'auto' : undefined,
                            padding: iconBgColor ? '8px' : '0',
                            borderRadius: '50%'
                        }"
                    >
                        <LucideIcon 
                            :name="item.icon || defaultIcon" 
                            :class="iconInnerClass"
                            :size="numericSize"
                            :style="{
                                width: !isNaN(iconSize) ? `${iconSize}px` : undefined,
                                height: !isNaN(iconSize) ? `${iconSize}px` : undefined
                            }"
                        />
                    </div>
                    <div class="flex-1 pt-0.5">
                        <h4 v-if="item.title" class="font-bold text-lg">{{ item.title }}</h4>
                        <p v-if="item.description" class="opacity-80 mt-1">{{ item.description }}</p>
                        <!-- Fallback for text only items -->
                        <span v-if="item.text && !item.title" class="text-base">{{ item.text }}</span>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

import { computed } from 'vue';
import LucideIcon from '../../ui/LucideIcon.vue';

const props = defineProps({
    title: { type: String, default: '' },
    items: { 
        type: Array, 
        default: () => [] 
    },
    defaultIcon: { type: String, default: 'check' },
    iconSize: { type: [String, Number], default: 20 },
    iconColor: { type: String, default: '' },
    iconBgColor: { type: String, default: '' },
    alignment: { type: String, default: 'left' },
    width: { type: String, default: 'max-w-2xl' },
    padding: { type: String, default: 'py-12' },
    bgColor: String,
    animation: { type: String, default: '' }
});

const alignmentClass = computed(() => ({
    'left': 'text-left',
    'center': 'text-center mx-auto',
    'right': 'text-right ml-auto'
}[props.alignment] || 'text-left'));

const iconSizeClass = computed(() => {
    if (!isNaN(props.iconSize)) return '';
    return {
        'small': 'w-8 h-8',
        'medium': 'w-10 h-10',
        'large': 'w-12 h-12'
    }[props.iconSize] || 'w-10 h-10';
});

const iconInnerClass = computed(() => {
    if (!isNaN(props.iconSize)) return '';
    return {
        'small': 'w-4 h-4',
        'medium': 'w-5 h-5',
        'large': 'w-6 h-6'
    }[props.iconSize] || 'w-5 h-5';
});

const numericSize = computed(() => {
    return !isNaN(props.iconSize) ? props.iconSize : 20;
});
</script>

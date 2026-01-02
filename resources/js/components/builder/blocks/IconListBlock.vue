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
                            'flex-shrink-0 flex items-center justify-center rounded-full',
                            iconSizeClass
                        ]"
                        :style="{ 
                            backgroundColor: iconBgColor || 'hsl(var(--primary) / 0.1)',
                            color: iconColor || 'hsl(var(--primary))'
                        }"
                    >
                        <component :is="getIcon(item.icon || defaultIcon)" :class="iconInnerClass" />
                    </div>
                    <div class="flex-1 pt-0.5">
                        <h4 v-if="item.title" class="font-bold text-lg">{{ item.title }}</h4>
                        <p v-if="item.description" class="text-muted-foreground mt-1">{{ item.description }}</p>
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
import { 
    Check, Star, ArrowRight, Zap, Shield, Heart,
    CheckCircle, Circle, Square, Diamond, Sparkles
} from 'lucide-vue-next';

const props = defineProps({
    title: { type: String, default: '' },
    items: { 
        type: Array, 
        default: () => [
            { title: 'First Item', description: 'Description for the first item.', icon: 'check' },
            { title: 'Second Item', description: 'Description for the second item.', icon: 'check' },
            { title: 'Third Item', description: 'Description for the third item.', icon: 'check' }
        ] 
    },
    defaultIcon: { type: String, default: 'check' },
    iconSize: { type: String, default: 'medium' },
    iconColor: { type: String, default: '' },
    iconBgColor: { type: String, default: '' },
    alignment: { type: String, default: 'left' },
    width: { type: String, default: 'max-w-2xl' },
    padding: { type: String, default: 'py-12' },
    bgColor: String,
    animation: { type: String, default: '' }
});

const icons = {
    check: Check,
    'check-circle': CheckCircle,
    star: Star,
    'arrow-right': ArrowRight,
    zap: Zap,
    shield: Shield,
    heart: Heart,
    circle: Circle,
    square: Square,
    diamond: Diamond,
    sparkles: Sparkles
};

const getIcon = (name) => icons[name] || Check;

const alignmentClass = computed(() => ({
    'left': 'text-left',
    'center': 'text-center mx-auto',
    'right': 'text-right ml-auto'
}[props.alignment] || 'text-left'));

const iconSizeClass = computed(() => ({
    'small': 'w-8 h-8',
    'medium': 'w-10 h-10',
    'large': 'w-12 h-12'
}[props.iconSize] || 'w-10 h-10'));

const iconInnerClass = computed(() => ({
    'small': 'w-4 h-4',
    'medium': 'w-5 h-5',
    'large': 'w-6 h-6'
}[props.iconSize] || 'w-5 h-5'));
</script>

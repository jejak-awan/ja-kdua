<template>
    <section 
        :class="['transition-all duration-300 relative h-full', padding, entranceAnimation]"
        :style="containerStyle"
        v-bind="$attrs"
        @mouseenter="isHovered = true"
        @mouseleave="isHovered = false"
    >
        <div :class="['flex w-full', alignmentClass]">
            <a 
                :href="url || '#'" 
                :target="openNewTab ? '_blank' : '_self'"
                :class="[
                    'inline-flex items-center justify-center gap-2 transition-all duration-300 relative overflow-hidden',
                    sizeClass,
                    !useCustomColors ? variantClass : '',
                    hoverEffectClass
                ]"
                :style="buttonStyle"
            >
                <!-- Background (for hover effects) -->
                <div 
                    v-if="hoverEffect === 'shine'"
                    class="absolute inset-0 -translate-x-full group-hover:animate-shine bg-gradient-to-r from-transparent via-white/20 to-transparent z-0"
                ></div>

                <!-- Icon Left -->
                <component 
                    v-if="iconPosition === 'left' && iconName" 
                    :is="iconComponent" 
                    class="z-10 transition-transform duration-300"
                    :class="['shrink-0', iconSizeClass]"
                    :style="{ transform: isHovered && hoverEffect === 'lift' ? 'translateX(-2px)' : 'none' }"
                />

                <!-- Text -->
                <span class="z-10 relative">{{ text || 'Click Here' }}</span>

                <!-- Icon Right -->
                <component 
                    v-if="iconPosition === 'right' && iconName" 
                    :is="iconComponent" 
                    class="z-10 transition-transform duration-300"
                    :class="['shrink-0', iconSizeClass]"
                    :style="{ transform: isHovered && hoverEffect === 'lift' ? 'translateX(2px)' : 'none' }"
                />
            </a>
        </div>
    </section>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

import { computed, ref } from 'vue';
import { 
    ArrowRight, ArrowUpRight, ChevronRight, ExternalLink, Download, Play, 
    PlayCircle, Mail, Phone, Send, Check, Plus, Heart, Star, ShoppingCart, 
    User, Sparkles 
} from 'lucide-vue-next';

const props = defineProps({
    // Content
    text: { type: String, default: 'Click Here' },
    url: { type: String, default: '#' },
    openNewTab: { type: Boolean, default: false },
    iconName: { type: String, default: '' },
    iconPosition: { type: String, default: 'right' },
    iconSize: { type: Number, default: 16 },
    
    // Style
    variant: { type: String, default: 'primary' },
    size: { type: String, default: 'medium' },
    fullWidth: { type: Boolean, default: false },
    useCustomColors: { type: Boolean, default: false },
    bgColor: { type: String, default: '#3b82f6' },
    textColor: { type: String, default: '#ffffff' },
    borderColor: { type: String, default: '#3b82f6' },
    fontFamily: { type: String, default: 'inherit' },
    fontWeight: { type: String, default: '600' },
    textTransform: { type: String, default: 'none' },
    radius: { type: String, default: 'rounded-lg' },
    shadow: { type: String, default: 'none' },
    alignment: { type: String, default: 'left' },
    
    // Container
    padding: { type: Object, default: () => ({ top: '16px', right: '0', bottom: '16px', left: '0' }) },
    
    // Advanced
    hoverEffect: { type: String, default: 'none' },
    hoverBgColor: String,
    entranceAnimation: { type: String, default: '' },
    animationDelay: { type: Number, default: 0 }
});

const isHovered = ref(false);

const icons = {
    'arrow-right': ArrowRight,
    'arrow-up-right': ArrowUpRight,
    'chevron-right': ChevronRight,
    'external-link': ExternalLink,
    'download': Download,
    'play': Play,
    'play-circle': PlayCircle,
    'mail': Mail,
    'phone': Phone,
    'send': Send,
    'check': Check,
    'plus': Plus,
    'heart': Heart,
    'star': Star,
    'shopping-cart': ShoppingCart,
    'user': User,
    'sparkles': Sparkles
};

const iconComponent = computed(() => icons[props.iconName] || null);

const containerStyle = computed(() => {
    const p = props.padding || {};
    return {
        paddingTop: p.top,
        paddingRight: p.right,
        paddingBottom: p.bottom,
        paddingLeft: p.left,
        animationDelay: `${props.animationDelay}ms`
    };
});

const alignmentClass = computed(() => ({
    'left': 'justify-start',
    'center': 'justify-center',
    'right': 'justify-end'
}[props.alignment] || 'justify-start'));

const sizeClass = computed(() => ({
    'xs': 'px-3 py-1.5 text-xs h-7',
    'small': 'px-4 py-2 text-sm h-9',
    'medium': 'px-6 py-3 text-base h-11',
    'large': 'px-8 py-4 text-lg h-14',
    'xl': 'px-10 py-5 text-xl h-16'
}[props.size] || 'px-6 py-3 text-base h-11'));

const iconSizeClass = computed(() => {
    const size = props.iconSize || 16;
    // Map closest Tailwind class or use inline style if needed (we'll use inline for precise control)
    return ''; 
});

const variantClass = computed(() => ({
    'primary': 'bg-primary text-primary-foreground border-2 border-primary hover:bg-primary/90',
    'secondary': 'bg-secondary text-secondary-foreground border-2 border-secondary hover:bg-secondary/80',
    'outline': 'border-2 border-primary bg-transparent text-primary hover:bg-primary hover:text-primary-foreground',
    'ghost': 'bg-transparent text-primary hover:bg-primary/10 border-2 border-transparent',
    'link': 'bg-transparent text-primary underline-offset-4 hover:underline border-none p-0 h-auto'
}[props.variant] || 'bg-primary text-primary-foreground'));

const hoverEffectClass = computed(() => {
    switch (props.hoverEffect) {
        case 'lift': return 'hover:-translate-y-1 hover:shadow-lg';
        case 'scale': return 'hover:scale-105';
        case 'glow': return 'hover:shadow-[0_0_20px_rgba(59,130,246,0.5)]';
        default: return '';
    }
});

const buttonStyle = computed(() => {
    const style = {
        fontFamily: props.fontFamily,
        fontWeight: props.fontWeight,
        textTransform: props.textTransform,
        width: props.fullWidth ? '100%' : 'auto',
        borderRadius: props.radius === 'rounded-full' ? '9999px' : 
                      props.radius === 'rounded-lg' ? '0.5rem' : 
                      props.radius === 'rounded-xl' ? '0.75rem' : 
                      props.radius === 'rounded-none' ? '0' : '0.5rem' // Fallback
    };
    
    // Handle icon size manually since classes are limited
    if (props.iconSize) {
        // We can't easily style the SVG from here without deep selector or passing prop
        // So we rely on the component taking the class
    }

    if (props.useCustomColors) {
        style.backgroundColor = isHovered.value && props.hoverBgColor ? props.hoverBgColor : props.bgColor;
        style.color = props.textColor;
        style.borderColor = props.borderColor;
        style.borderWidth = '2px';
        style.borderStyle = 'solid';
    }

    if (props.shadow && props.shadow !== 'none') {
        // Map shadow classes manually or just use class binding for presets
    }

    return style;
});
</script>

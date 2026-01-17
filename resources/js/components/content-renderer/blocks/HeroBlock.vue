<template>
    <section 
        :class="['relative overflow-hidden transition-all duration-500 h-full transition-[background-position] ease-out', radius, entranceAnimation]"
        :style="sectionStyle"
        v-bind="$attrs"
        ref="heroRef"
        @mousemove="handleMouseMove"
        @mouseleave="handleMouseLeave"
    >
        <!-- Background Layer -->
        <div class="absolute inset-0 z-0" :style="backgroundStyle">
            <!-- Legacy Parallax wrapper (only if using direct props and enabled) -->
            <div 
                v-if="bgImage && bgType === 'image' && !settings?.backgroundImage" 
                class="absolute inset-0 bg-cover transition-transform duration-100"
                :style="{ 
                    backgroundImage: `url(${bgImage})`,
                    backgroundSize: bgSize || 'cover',
                    backgroundPosition: `${bgPosition.split(/\s+/)[0] || 'center'} calc(50% + ${parallaxOffset + (mouseY * 30)}px)`,
                    transform: 'none'
                }"
            ></div>
        </div>

        <!-- Overlay -->
        <div 
            v-if="overlayEnabled" 
            class="absolute inset-0 z-[1]"
            :style="{ backgroundColor: overlayColor, opacity: overlayOpacity / 100 }"
        ></div>

        <!-- Content -->
        <div 
            class="relative z-10 w-full h-full flex flex-col"
            :class="[
                verticalAlign === 'start' ? 'justify-start' : '',
                verticalAlign === 'center' ? 'justify-center' : '',
                verticalAlign === 'end' ? 'justify-end' : ''
            ]"
            :style="contentContainerStyle"
        >
            <div :style="{ maxWidth: `${contentMaxWidth}px`, margin: '0 auto', width: '100%' }">
                <!-- Title -->
                <component 
                    :is="titleTag || 'h1'"
                    v-if="title" 
                    class="font-extrabold mb-6 tracking-tight transition-all duration-300"
                    :style="titleStyle"
                >
                    {{ title }}
                </component>
                
                <!-- Subtitle -->
                <p 
                    v-if="subtitle" 
                    class="leading-relaxed mb-8 transition-all duration-300"
                    :style="subtitleStyle"
                >
                    {{ subtitle }}
                </p>

                <!-- Nested Blocks Area -->
                <div class="relative min-h-[50px]">
                    <!-- Live Mode -->
                    <div class="space-y-4">
                         <BlockRenderer 
                            :blocks="nestedBlocks" 
                            :context="context"
                            :is-preview="isPreview"
                        />
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import BlockRenderer from '../BlockRenderer.vue';
import { getBackgroundStyles } from '../utils';

defineOptions({
  inheritAttrs: false
});

const props = defineProps({
    id: String,
    // Content
    title: String,
    titleTag: { type: String, default: 'h1' },
    subtitle: String,
    
    // Title Style
    titleFontFamily: { type: String, default: 'inherit' },
    titleSize: { type: Number, default: 56 },
    titleWeight: { type: String, default: '700' },
    titleAlign: { type: String, default: 'center' },
    titleColor: { type: String, default: '#ffffff' },
    titleShadow: { type: Boolean, default: true },
    
    // Subtitle Style
    subtitleFontFamily: { type: String, default: 'inherit' },
    subtitleSize: { type: Number, default: 20 },
    subtitleWeight: { type: String, default: '400' },
    subtitleColor: { type: String, default: 'rgba(255, 255, 255, 0.8)' },
    subtitleMaxWidth: { type: Number, default: 700 },
    
    // Background
    bgType: { type: String, default: 'color' },
    bgColor: { type: String, default: '#18181b' },
    bgImage: String,
    bgSize: { type: String, default: 'cover' },
    bgPosition: { type: String, default: 'center' },
    gradientStart: { type: String, default: '#3b82f6' },
    gradientEnd: { type: String, default: '#8b5cf6' },
    gradientDirection: { type: String, default: 'to bottom right' },
    
    // Overlay
    overlayEnabled: { type: Boolean, default: true },
    overlayColor: { type: String, default: 'rgba(0, 0, 0, 0.5)' },
    overlayOpacity: { type: Number, default: 50 },
    
    // Layout
    minHeight: { type: Number, default: 500 },
    contentMaxWidth: { type: Number, default: 1200 },
    verticalAlign: { type: String, default: 'center' },
    padding: { type: Object, default: () => ({ top: '80px', right: '24px', bottom: '80px', left: '24px' }) },
    radius: { type: String, default: 'rounded-none' },
    
    // Animation
    entranceAnimation: { type: String, default: 'animate-fade' },
    animationDuration: { type: Number, default: 700 },
    animationDelay: { type: Number, default: 0 },
    parallaxEnabled: { type: Boolean, default: false },
    parallaxSpeed: { type: Number, default: 0.5 },
    
    // Nested
    blocks: { type: Array, default: () => [] },
    settings: { type: Object, default: () => ({}) },
    context: Object,
    isPreview: { type: Boolean, default: false }
});

// Parallax effect
const parallaxOffset = ref(0);
const mouseX = ref(0);
const mouseY = ref(0);
const heroRef = ref(null);

const handleMouseMove = (e) => {
    if (!props.isPreview) return;
    const rect = heroRef.value.getBoundingClientRect();
    mouseX.value = ((e.clientX - rect.left) / rect.width) * 2 - 1;
    mouseY.value = ((e.clientY - rect.top) / rect.height) * 2 - 1;
};

const handleMouseLeave = () => {
    mouseX.value = 0;
    mouseY.value = 0;
};

const handleScroll = (e) => {
    let currentScroll = window.scrollY;
    if (e && e.target && e.target.scrollTop !== undefined) {
        currentScroll = e.target.scrollTop;
    }

    const isParallax = props.settings?.parallax === true || props.settings?.parallax === 'true' || props.parallaxEnabled;
    const isTrue = props.settings?.parallaxMethod === 'true' || props.settings?.parallaxMethod === true || !props.settings?.parallaxMethod;
    
    if (isParallax && isTrue && heroRef.value) {
        const speed = props.parallaxSpeed || 0.5;
        const itemTop = heroRef.value.offsetTop || 0;
        parallaxOffset.value = (currentScroll - itemTop) * speed;
    } else {
        parallaxOffset.value = 0;
    }
};

onMounted(() => {
    const frame = document.querySelector('.canvas-frame');
    if (frame) {
        frame.addEventListener('scroll', handleScroll);
        // Initial sync
        handleScroll({ target: frame });
    } else {
        window.addEventListener('scroll', handleScroll);
    }
});

onUnmounted(() => {
    const frame = document.querySelector('.canvas-frame');
    if (frame) frame.removeEventListener('scroll', handleScroll);
    window.removeEventListener('scroll', handleScroll);
});

// Computed styles
const sectionStyle = computed(() => ({
    minHeight: `${props.minHeight}px`,
    animationDuration: `${props.animationDuration}ms`,
    animationDelay: `${props.animationDelay}ms`
}));

const backgroundStyle = computed(() => {
    const style = {};
    
    // Support global background settings if provided
    if (props.settings) {
        const bgStyles = getBackgroundStyles(props.settings);
        Object.assign(style, bgStyles);

        // Inject parallax offset for JS method if enabled via global settings
        const isParallax = props.settings?.parallax === true || props.settings?.parallax === 'true';
        const isTrue = props.settings?.parallaxMethod === 'true' || props.settings?.parallaxMethod === true || !props.settings?.parallaxMethod;
        
        if (isParallax && isTrue) {
            const mouseNudgeX = mouseX.value * 30;
            const mouseNudgeY = mouseY.value * 30;

            const basePos = bgStyles.backgroundPosition || 'center center';
            const parts = basePos.split(',').map(pos => {
                const p = pos.trim().split(/\s+/);
                let x = p[0] || 'center';
                let y = p[1] || 'center';
                
                const xNorm = x === 'left' ? '0%' : x === 'right' ? '100%' : x === 'center' ? '50%' : x;
                const yNorm = y === 'top' ? '0%' : y === 'bottom' ? '100%' : y === 'center' ? '50%' : y;
                
                const xFinal = xNorm.includes('%') || xNorm.includes('px') ? `calc(${xNorm} + ${mouseNudgeX}px)` : xNorm;
                const yFinal = `calc(${yNorm} + ${parallaxOffset.value + mouseNudgeY}px)`;
                
                return `${xFinal} ${yFinal}`;
            });
            style.backgroundPosition = parts.join(', ');
            style.backgroundAttachment = 'scroll';
        }
    } else {
        // Fallback for direct props
        switch (props.bgType) {
            case 'color':
                style.backgroundColor = props.bgColor;
                break;
            case 'gradient':
                const direction = props.gradientDirection || 'to bottom right';
                if (direction === 'radial') {
                    style.backgroundImage = `radial-gradient(circle, ${props.gradientStart}, ${props.gradientEnd})`;
                } else {
                    style.backgroundImage = `linear-gradient(${direction}, ${props.gradientStart}, ${props.gradientEnd})`;
                }
                break;
            case 'image':
                if (!props.bgImage) {
                    style.backgroundColor = props.bgColor;
                }
                break;
        }
    }
    
    return style;
});

const contentContainerStyle = computed(() => {
    const p = props.padding || { top: '80px', right: '24px', bottom: '80px', left: '24px' };
    return {
        paddingTop: p.top,
        paddingRight: p.right,
        paddingBottom: p.bottom,
        paddingLeft: p.left
    };
});

const titleStyle = computed(() => ({
    fontFamily: props.titleFontFamily,
    fontSize: `${props.titleSize}px`,
    fontWeight: props.titleWeight,
    textAlign: props.titleAlign,
    color: props.titleColor,
    textShadow: props.titleShadow ? '0 2px 4px rgba(0,0,0,0.3)' : 'none'
}));

const subtitleStyle = computed(() => ({
    fontFamily: props.subtitleFontFamily,
    fontSize: `${props.subtitleSize}px`,
    fontWeight: props.subtitleWeight,
    textAlign: props.titleAlign, // Use same alignment as title
    color: props.subtitleColor,
    maxWidth: `${props.subtitleMaxWidth}px`,
    margin: props.titleAlign === 'center' ? '0 auto' : '0'
}));

// Nested blocks logic
const nestedBlocks = computed(() => props.blocks || []);
</script>

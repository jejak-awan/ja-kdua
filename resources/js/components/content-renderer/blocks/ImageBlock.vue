<template>
    <section 
        :class="['transition-all duration-500 h-full', entranceAnimation]"
        :style="containerStyle"
        v-bind="$attrs"
    >
        <!-- Inner Container with alignment -->
        <div :class="['w-full flex', alignmentClass]">
            
            <!-- Image Wrapper -->
            <figure 
                class="relative overflow-hidden transition-all duration-500 group"
                :class="[
                    radius,
                    shadow !== 'none' ? shadow : '',
                    hoverEffect === 'zoom' ? 'hover:scale-[1.02]' : ''
                ]"
                :style="wrapperStyle"
            >
                <!-- Image -->
                <img 
                    v-if="url" 
                    :src="url" 
                    :alt="alt || 'Image'"
                    :loading="lazyLoad ? 'lazy' : 'eager'"
                    class="transition-all duration-700 block"
                    :class="[
                        objectFitClass,
                        filtersClass
                    ]"
                    :style="imageStyle"
                />
                
                <!-- Fallback / Placeholder -->
                <div 
                    v-else 
                    class="bg-muted/30 border-2 border-dashed border-primary/10 flex flex-col items-center justify-center p-8 text-center"
                    :style="{ height: height === 'auto' ? '300px' : height, width: '100%' }"
                >
                    <div class="p-4 rounded-full bg-background shadow-sm mb-4">
                        <ImageIcon class="w-8 h-8 text-muted-foreground/50" />
                    </div>
                    <p class="text-sm font-medium text-muted-foreground">Select an image</p>
                </div>

                <!-- Hover Overlay -->
                <div 
                    v-if="hoverEffect === 'overlay' || hoverEffect === 'brighten'"
                    class="absolute inset-0 transition-opacity duration-300 opacity-0 group-hover:opacity-100 pointer-events-none"
                    :style="{ backgroundColor: hoverOverlayColor }"
                ></div>

                <!-- Caption -->
                <figcaption 
                    v-if="caption" 
                    class="absolute bottom-0 left-0 right-0 bg-black/60 backdrop-blur-sm text-white p-4 text-sm text-center transform translate-y-full group-hover:translate-y-0 transition-transform duration-300"
                >
                    {{ caption }}
                </figcaption>

                <!-- Link Overlay -->
                <a 
                    v-if="linkUrl" 
                    :href="linkUrl" 
                    :target="linkNewTab ? '_blank' : '_self'"
                    class="absolute inset-0 z-10 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-inherit"
                    :aria-label="alt || 'Image link'"
                ></a>
            </figure>
        </div>
    </section>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

import { computed } from 'vue';
import { Image as ImageIcon } from 'lucide-vue-next';

const props = defineProps({
    // Content
    url: String,
    alt: String,
    caption: String,
    linkUrl: String,
    linkNewTab: { type: Boolean, default: false },
    
    // Style
    width: { type: Number, default: 800 },
    maxWidth: { type: String, default: 'max-w-full' },
    height: { type: String, default: 'auto' },
    aspectRatio: { type: String, default: 'auto' },
    objectFit: { type: String, default: 'cover' },
    objectPosition: { type: String, default: 'center' },
    alignment: { type: String, default: 'center' },
    radius: { type: String, default: 'rounded-lg' },
    borderWidth: { type: Number, default: 0 },
    borderColor: { type: String, default: '#e5e7eb' },
    shadow: { type: String, default: 'none' },
    padding: { type: Object, default: () => ({ top: '16px', right: '0', bottom: '16px', left: '0' }) },
    bgColor: { type: String, default: 'transparent' },
    
    // Advanced
    brightness: { type: Number, default: 100 },
    contrast: { type: Number, default: 100 },
    saturate: { type: Number, default: 100 },
    blur: { type: Number, default: 0 },
    grayscale: { type: Number, default: 0 },
    hoverEffect: { type: String, default: 'none' },
    hoverOverlayColor: { type: String, default: 'rgba(0, 0, 0, 0.3)' },
    entranceAnimation: { type: String, default: 'animate-fade' },
    animationDuration: { type: Number, default: 700 },
    lazyLoad: { type: Boolean, default: true }
});

const containerStyle = computed(() => {
    const p = props.padding || {};
    return {
        paddingTop: p.top,
        paddingRight: p.right,
        paddingBottom: p.bottom,
        paddingLeft: p.left,
        backgroundColor: props.bgColor,
        animationDuration: `${props.animationDuration}ms`
    };
});

const alignmentClass = computed(() => ({
    'left': 'justify-start',
    'center': 'justify-center',
    'right': 'justify-end'
}[props.alignment] || 'justify-center'));

const wrapperStyle = computed(() => {
    const style = {
        // width=0 means fit to container (100%), otherwise use specified width with max constraint
        maxWidth: props.width > 0 ? `min(${props.width}px, 100%)` : '100%',
        width: '100%' // Always fill available space up to max-width
    };

    if (props.aspectRatio && props.aspectRatio !== 'auto') {
        style.aspectRatio = props.aspectRatio;
    } else {
        style.height = props.height;
    }

    if (props.borderWidth > 0) {
        style.borderWidth = `${props.borderWidth}px`;
        style.borderColor = props.borderColor;
        style.borderStyle = 'solid';
    }

    return style;
});

const objectFitClass = computed(() => ({
    'cover': 'object-cover',
    'contain': 'object-contain',
    'fill': 'object-fill',
    'none': 'object-none'
}[props.objectFit] || 'object-cover'));

const filtersClass = computed(() => {
    const classes = [];
    if (props.hoverEffect === 'colorize') classes.push('grayscale group-hover:grayscale-0');
    if (props.hoverEffect === 'desaturate') classes.push('group-hover:grayscale');
    if (props.hoverEffect === 'brighten') classes.push('group-hover:brightness-110');
    if (props.hoverEffect === 'zoom') classes.push('group-hover:scale-110');
    if (props.hoverEffect === 'zoom-out') classes.push('scale-110 group-hover:scale-100');
    return classes.join(' ');
});

const imageStyle = computed(() => {
    const filters = [];
    if (props.blur > 0) filters.push(`blur(${props.blur}px)`);
    if (props.brightness !== 100) filters.push(`brightness(${props.brightness}%)`);
    if (props.contrast !== 100) filters.push(`contrast(${props.contrast}%)`);
    if (props.saturate !== 100) filters.push(`saturate(${props.saturate}%)`);
    if (props.grayscale > 0) filters.push(`grayscale(${props.grayscale}%)`);
    
    return {
        filter: filters.length ? filters.join(' ') : undefined,
        objectPosition: props.objectPosition,
        width: '100%',
        height: '100%'
    };
});
</script>

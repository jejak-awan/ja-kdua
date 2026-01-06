<template>
    <section 
        :class="['transition-all duration-500', entranceAnimation]"
        :style="containerStyle"
    >
        <div :class="['w-full', containerAlignmentClass]" :style="wrapperStyle">
            <!-- Title -->
            <component 
                v-if="showTitle && title" 
                :is="titleTag"
                class="font-bold tracking-tight mb-4 transition-all"
                :style="titleStyle"
            >
                {{ title }}
            </component>

            <!-- Content -->
            <div 
                ref="contentRef"
                class="transition-all duration-300"
                :class="[
                    isProse ? `prose dark:prose-invert prose-primary ${proseSize}` : '',
                    alignmentClass,
                    dropCap ? 'first-letter:float-left first-letter:text-5xl first-letter:font-bold first-letter:mr-3 first-letter:mt-[-6px]' : ''
                ]"
                :style="contentStyle"
            >
                <div v-html="content"></div>
            </div>
        </div>
    </section>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

import { computed, ref } from 'vue';

const props = defineProps({
    // Content
    showTitle: { type: Boolean, default: true },
    title: String,
    titleTag: { type: String, default: 'h2' },
    content: String,
    
    // Title Style
    titleFontFamily: { type: String, default: 'inherit' },
    titleFontSize: { type: Number, default: 32 },
    titleFontWeight: { type: String, default: '700' },
    titleColor: { type: String, default: 'inherit' },
    titleMarginBottom: { type: Number, default: 16 },
    
    // Body Style
    bodyFontFamily: { type: String, default: 'inherit' },
    bodyFontSize: { type: Number, default: 16 },
    bodyFontWeight: { type: String, default: '400' },
    bodyColor: { type: String, default: 'inherit' },
    bodyLineHeight: { type: Number, default: 1.7 },
    
    // Alignment
    alignment: { type: String, default: 'left' },
    
    // Container
    maxWidth: { type: Number, default: 900 },
    containerAlignment: { type: String, default: 'center' },
    padding: { type: Object, default: () => ({ top: '48px', right: '24px', bottom: '48px', left: '24px' }) },
    bgColor: { type: String, default: 'transparent' },
    radius: { type: String, default: 'rounded-none' },
    
    // Advanced
    isProse: { type: Boolean, default: true },
    proseSize: { type: String, default: 'prose-base' },
    dropCap: { type: Boolean, default: false },
    dropCapColor: { type: String, default: '#3b82f6' },
    columns: { type: String, default: '1' },
    columnGap: { type: Number, default: 32 },
    entranceAnimation: { type: String, default: '' }
});

const contentRef = ref(null);

const containerStyle = computed(() => {
    const p = props.padding || {};
    return {
        paddingTop: p.top,
        paddingRight: p.right,
        paddingBottom: p.bottom,
        paddingLeft: p.left,
        backgroundColor: props.bgColor
    };
});

const wrapperStyle = computed(() => ({
    maxWidth: `${props.maxWidth}px`,
    width: '100%',
    borderRadius: props.radius === 'rounded-none' ? '0' : undefined // Basic radius handling
}));

const containerAlignmentClass = computed(() => ({
    'start': 'mx-0',
    'center': 'mx-auto',
    'end': 'ml-auto mr-0'
}[props.containerAlignment] || 'mx-auto'));

const alignmentClass = computed(() => ({
    'left': 'text-left',
    'center': 'text-center',
    'right': 'text-right',
    'justify': 'text-justify'
}[props.alignment] || 'text-left'));

const titleStyle = computed(() => ({
    fontFamily: props.titleFontFamily,
    fontSize: `${props.titleFontSize}px`,
    fontWeight: props.titleFontWeight,
    color: props.titleColor,
    marginBottom: `${props.titleMarginBottom}px`,
    textAlign: props.alignment === 'justify' ? 'left' : props.alignment
}));

const contentStyle = computed(() => {
    const style = {
        fontFamily: props.bodyFontFamily,
        fontSize: props.isProse ? undefined : `${props.bodyFontSize}px`,
        fontWeight: props.bodyFontWeight,
        color: props.bodyColor,
        lineHeight: props.bodyLineHeight,
        // Drop cap color injection if needed, though usually requires CSS pseudo-element manipulation
        // or a scoped helper. We'll simplify for now.
    };

    if (props.columns !== '1') {
        style.columnCount = props.columns;
        style.columnGap = `${props.columnGap}px`;
    }

    return style;
});
</script>

<style scoped>
/* Scoped styles for detailed drop-cap control if needed */
.first-letter\:float-left::first-letter {
    float: left;
    line-height: 1;
    padding-right: 0.5rem;
}
</style>

<script setup>
import { ref, computed, onMounted } from 'vue';
import LucideIcon from '../../ui/LucideIcon.vue';
import { getBorderStyles, getSpacingStyles, getBoxShadowStyles, getSizingStyles, getTransformStyles } from '../utils';

const props = defineProps({
    items: { type: Array, default: () => [] },
    allowMultiple: { type: Boolean, default: false },
    toggleIcon: { type: String, default: 'chevron-down' },
    iconPosition: { type: String, default: 'right' },
    iconColor: { type: String, default: '' },
    iconSize: { type: [Number, String], default: 18 },
    gap: { type: [Number, String], default: 16 },
    headerBackgroundColor: { type: String, default: '' },
    openHeaderBackgroundColor: { type: String, default: '' },
    contentBackgroundColor: { type: String, default: '' },
    // Container Styles
    padding: Object,
    margin: Object,
    backgroundColor: String,
    border: Object,
    borderRadius: [String, Object],
    boxShadow: [String, Object],
    settings: { type: Object, default: () => ({}) }
});

const openIndices = ref([]);

onMounted(() => {
    props.items.forEach((item, index) => {
        if (item.open) openIndices.value.push(index);
    });
});

const toggle = (index) => {
    if (props.allowMultiple) {
        if (openIndices.value.includes(index)) {
            openIndices.value = openIndices.value.filter(i => i !== index);
        } else {
            openIndices.value.push(index);
        }
    } else {
        openIndices.value = openIndices.value.includes(index) ? [] : [index];
    }
};

const getIconName = (index) => {
    if (props.toggleIcon === 'plus') {
        return openIndices.value.includes(index) ? 'minus' : 'plus';
    }
    return props.toggleIcon;
};

const shouldRotate = computed(() => {
    const icon = String(props.toggleIcon).toLowerCase();
    return icon.includes('chevron') || icon.includes('arrow');
});

const iconStyles = computed(() => ({
    color: props.iconColor || 'currentColor',
    width: `${props.iconSize}px`,
    height: `${props.iconSize}px`
}));


const wrapperStyles = computed(() => {
    const s = {
        gap: `${props.gap}px`
    }
    
    if (props.backgroundColor) s.backgroundColor = props.backgroundColor
    if (props.padding) Object.assign(s, getSpacingStyles(props.padding, 'padding'))
    if (props.margin) Object.assign(s, getSpacingStyles(props.margin, 'margin'))
    if (props.border) Object.assign(s, getBorderStyles(props.border))
    if (props.boxShadow) Object.assign(s, getBoxShadowStyles(props.boxShadow))
    
    if (props.settings) {
        Object.assign(s, getSizingStyles(props.settings))
        Object.assign(s, getTransformStyles(props.settings))
    }
    
    return s
});
</script>

<template>
    <div class="accordion-renderer w-full flex flex-col" :style="wrapperStyles">
        <div 
            v-for="(item, index) in items" 
            :key="index"
            class="accordion-item border rounded-xl overflow-hidden transition-all duration-300"
            :class="{ 'shadow-sm': openIndices.includes(index) }"
        >
            <!-- Header -->
            <button 
                @click="toggle(index)"
                class="w-full flex items-center gap-4 p-4 text-left font-bold transition-colors"
                :style="{ 
                    backgroundColor: openIndices.includes(index) 
                        ? (openHeaderBackgroundColor || 'hsl(var(--muted)/0.3)') 
                        : (headerBackgroundColor || 'transparent')
                }"
                type="button"
            >
                <!-- Left Icon -->
                <div 
                    v-if="toggleIcon !== 'none' && iconPosition === 'left'"
                    class="shrink-0 transition-transform duration-300"
                    :class="{ 'rotate-180': openIndices.includes(index) && shouldRotate }"
                    :style="iconStyles"
                >
                    <LucideIcon :name="getIconName(index)" class="w-full h-full" :size="iconSize" />
                </div>

                <span class="flex-1">{{ item.title || 'Accordion Item' }}</span>

                <!-- Right Icon -->
                <div 
                    v-if="toggleIcon !== 'none' && iconPosition === 'right'"
                    class="shrink-0 transition-transform duration-300"
                    :class="{ 'rotate-180': openIndices.includes(index) && shouldRotate }"
                    :style="iconStyles"
                >
                    <LucideIcon :name="getIconName(index)" class="w-full h-full" :size="iconSize" />
                </div>
            </button>

            <!-- Content -->
            <div 
                class="overflow-hidden transition-all duration-300 ease-in-out"
                :class="openIndices.includes(index) ? 'max-h-[2000px] opacity-100' : 'max-h-0 opacity-0 pointer-events-none'"
                :style="{ backgroundColor: contentBackgroundColor || 'transparent' }"
            >
                <div 
                    class="p-4 pt-2 prose prose-sm dark:prose-invert max-w-none"
                    v-html="item.content || ''"
                ></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.accordion-item:hover button {
    opacity: 0.95;
}
</style>

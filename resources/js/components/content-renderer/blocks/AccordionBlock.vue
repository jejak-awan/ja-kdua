<script setup>
import { ref, computed, onMounted } from 'vue';
import LucideIcon from '../../ui/LucideIcon.vue';

defineOptions({
    inheritAttrs: false
});

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
    contentBackgroundColor: { type: String, default: '' }
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
    // If user selected "plus" specifically (legacy naming or explicit choice), handle toggle
    if (props.toggleIcon === 'plus') {
        return openIndices.value.includes(index) ? 'minus' : 'plus';
    }
    // Otherwise use the selected icon (e.g. chevron-down)
    // We handle rotation for chevron/arrow types via CSS class in template
    return props.toggleIcon;
};

// Check if we should rotate this icon when open
const shouldRotate = computed(() => {
    const icon = props.toggleIcon.toLowerCase();
    return icon.includes('chevron') || icon.includes('arrow');
});

const iconStyles = computed(() => ({
    color: props.iconColor || 'currentColor',
    width: `${props.iconSize}px`,
    height: `${props.iconSize}px`
}));
</script>

<template>
    <div class="accordion-renderer w-full flex flex-col" :style="{ gap: `${gap}px` }">
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

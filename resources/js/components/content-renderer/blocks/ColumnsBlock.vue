<template>
    <section 
        :class="['transition-all duration-500 h-full', padding, radius, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
        v-bind="$attrs"
    >
        <div :class="['mx-auto px-6 h-full', width]">
            <div 
                :class="[
                    'flex flex-wrap gap-8 h-full',
                    reverseClasses
                ]"
                :style="flexStyles"
                ref="gridRef"
            >
                <template v-for="(column, index) in computedColumns" :key="column.id || index">
                    <!-- Modern Column (Frontend/Preview) -->
                    <div 
                        v-if="isModern"
                        class="column-wrapper relative flex items-stretch flex-shrink-0 flex-grow-0"
                        :class="getColumnClasses()"
                        :style="{ '--desktop-width': colWidths[index] }"
                    >
                         <BlockRenderer 
                            :blocks="[column]" 
                            :context="context" 
                            :is-preview="isPreview"
                            class="h-full w-full"
                        />
                    </div>

                    <!-- Legacy Column (inline render) -->
                    <div 
                        v-else
                        class="column-container relative flex items-stretch flex-shrink-0 flex-grow-0"
                        :class="getColumnClasses()"
                        :style="{ '--desktop-width': colWidths[index] }"
                    >
                        <div class="flex-1 min-h-[50px] space-y-4">
                            <!-- Live Mode -->
                            <div class="h-full">
                                <BlockRenderer 
                                    :blocks="column.blocks || []" 
                                    :context="context" 
                                    :is-preview="true"
                                />
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

import { ref, computed } from 'vue';
import BlockRenderer from './BlockRenderer.vue';

const props = defineProps({
    id: String,
    columns: { type: Array, default: () => [] }, // Legacy
    blocks: { type: Array, default: () => [] },   // Modern: Column block objects
    layout: { type: String, default: '1-1' },
    customWidths: { type: Array, default: () => [50, 50] },
    stackOn: { type: String, default: 'never' },
    direction: { type: String, default: 'row' },
    mobileDirection: { type: String, default: 'column' },
    padding: { type: String, default: 'py-16' },
    width: { type: String, default: 'max-w-7xl' },
    bgColor: String,
    radius: { type: String, default: 'rounded-none' },
    animation: { type: String, default: '' },
    context: { type: Object, default: () => ({}) },
    isPreview: { type: Boolean, default: false }
});

const gridRef = ref(null);

// Determine if using modern blocks array or legacy columns
const isModern = computed(() => props.blocks && props.blocks.length > 0);

// Computed columns - use modern blocks if available, else legacy columns
const computedColumns = computed(() => {
    if (isModern.value) return props.blocks;
    return props.columns;
});

const flexStyles = computed(() => {
    return {
        // Any container level styles if needed
    };
});

// Calculate column widths accounting for gap
const colWidths = computed(() => {
    const numCols = computedColumns.value.length;
    if (numCols === 0) return [];

    let widths = [];
    
    // Determine base percentages
    if (props.layout === 'custom' && props.customWidths.length === numCols) {
        widths = props.customWidths;
    } else {
        // Standard layouts
        switch (props.layout) {
            case '1': widths = [100]; break;
            case '1-1': widths = [50, 50]; break;
            case '1-2': widths = [33.333, 66.667]; break;
            case '2-1': widths = [66.667, 33.333]; break;
            case '1-1-1': widths = [33.333, 33.333, 33.333]; break;
            case '1-1-1-1': widths = [25, 25, 25, 25]; break;
            default: widths = Array(numCols).fill(100 / numCols);
        }
    }

    // Safety: If widths count doesn't match columns count (e.g. added a column), fallback to even distribution
    if (widths.length !== numCols) {
        widths = Array(numCols).fill(100 / numCols);
    }

    // Convert percentages to calc strings subtracting gap
    // Formula: calc(P% - (totalGap * P/100))
    // Total gap = (n-1) * 2rem (gap-8)
    const gap = 2; // rem
    const totalGap = (numCols - 1) * gap;
    
    return widths.map(w => {
        const gapSubtraction = (totalGap * (w / 100)).toFixed(3);
        return `calc(${w}% - ${gapSubtraction}rem)`;
    });
});

const breakpointPrefix = computed(() => {
    const stack = props.stackOn || 'sm';
    if (stack === 'never') return ''; // No prefix, applies always
    if (stack === 'lg') return 'xl'; // Stack until XL
    if (stack === 'md') return 'lg'; // Stack until LG
    return 'md'; // Default 'sm' -> stack until MD
});

const getColumnClasses = () => {
    const bp = breakpointPrefix.value;
    
    // If always stacked (e.g. 'lg' implies stack most of the time? Or maybe we need explicit 'always')
    // For now assuming 'lg' stacks until XL.
    
    if (bp === '') return 'w-[var(--desktop-width)]'; // Never stack
    
    return `w-full ${bp}:w-[var(--desktop-width)]`;
};

// Direction classes based on mobile and desktop direction settings
const reverseClasses = computed(() => {
    const stackBreakpoint = props.stackOn || 'sm';
    const mobileDir = props.mobileDirection || 'column';
    const desktopDir = props.direction || 'row';
    
    // Map direction values to Tailwind flex classes
    const directionMap = {
        'row': 'flex-row',
        'row-reverse': 'flex-row-reverse',
        'column': 'flex-col',
        'column-reverse': 'flex-col-reverse'
    };
    
    // If never stack, just use desktop direction
    if (stackBreakpoint === 'never') {
        return directionMap[desktopDir] || 'flex-row';
    }
    
    // Responsive breakpoint prefix
    const bp = stackBreakpoint === 'sm' ? 'md' : stackBreakpoint === 'md' ? 'lg' : 'xl';
    
    // Mobile direction + Desktop direction with breakpoint
    const mobileClass = directionMap[mobileDir] || 'flex-col';
    const desktopClass = directionMap[desktopDir] || 'flex-row';
    
    return `${mobileClass} ${bp}:${desktopClass}`;
});
</script>

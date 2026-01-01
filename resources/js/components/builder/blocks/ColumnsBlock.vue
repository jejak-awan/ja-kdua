<template>
    <section 
        :class="['transition-all duration-500', padding, radius, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto px-6', width]">
            <div :class="['grid gap-8', gridLayout]">
                <div 
                    v-for="(column, index) in columns" 
                    :key="index"
                    class="min-h-[50px] space-y-4"
                >
                    <slot :name="`column-${index}`" :blocks="column.blocks">
                        <div v-if="!column.blocks || column.blocks.length === 0" class="h-full border-2 border-dashed border-muted-foreground/10 rounded-2xl flex items-center justify-center p-8 bg-muted/5 min-h-[150px]">
                            <p class="text-[10px] text-muted-foreground/40 font-bold uppercase tracking-widest">Column {{ index + 1 }}</p>
                        </div>
                    </slot>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    columns: { type: Array, default: () => [{ blocks: [] }, { blocks: [] }] },
    layout: { type: String, default: '1-1' },
    padding: { type: String, default: 'py-16' },
    width: { type: String, default: 'max-w-7xl' },
    bgColor: String,
    radius: { type: String, default: 'rounded-none' },
    animation: { type: String, default: '' }
});

const gridLayout = computed(() => {
    switch (props.layout) {
        case '1-1': return 'md:grid-cols-2';
        case '1-2': return 'md:grid-cols-3'; // 1-2 ratio
        case '2-1': return 'md:grid-cols-3'; // 2-1 ratio
        case '1-1-1': return 'md:grid-cols-3';
        case '1-1-1-1': return 'md:grid-cols-2 lg:grid-cols-4';
        default: return 'grid-cols-1';
    }
});
</script>

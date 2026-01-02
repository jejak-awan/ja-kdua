<template>
    <div :class="classes">
        <div class="flex items-center gap-4">
            <!-- Quantity -->
            <div class="flex items-center border border-border rounded-md">
                <button class="px-3 py-2 text-muted-foreground hover:bg-muted">-</button>
                <input type="number" value="1" class="w-12 text-center border-none bg-transparent focus:outline-none text-sm" />
                <button class="px-3 py-2 text-muted-foreground hover:bg-muted">+</button>
            </div>
            
            <!-- Button -->
            <button class="bg-primary text-primary-foreground px-8 py-2.5 rounded-md font-bold hover:opacity-90 transition-opacity">
                {{ custom_label || 'Add to Cart' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    custom_label: { type: String, default: 'Add to Cart' },
    alignment: { type: String, default: 'left' },
    context: { type: Object, default: () => ({}) }
});

const classes = computed(() => {
    return [
        'w-full flex',
        props.alignment === 'center' ? 'justify-center' : props.alignment === 'right' ? 'justify-end' : 'justify-start'
    ];
});
</script>

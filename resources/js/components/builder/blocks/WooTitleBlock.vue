<template>
    <div :class="classes">
        <h1 class="text-3xl font-bold text-foreground">{{ product.name || 'Product Title' }}</h1>
    </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { productService } from '@/services/ProductService';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    text_align: { type: String, default: 'left' },
    context: { type: Object, default: () => ({}) }
});

const product = ref({});

const classes = computed(() => {
    return [
        'w-full',
        props.text_align ? `text-${props.text_align}` : 'text-left'
    ];
});

onMounted(async () => {
    product.value = await productService.getProduct(1);
});
</script>

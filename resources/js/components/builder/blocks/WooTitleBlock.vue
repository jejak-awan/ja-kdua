<template>
    <div :class="classes" :style="styles">
        <h1 class="text-3xl font-bold text-foreground">{{ product.name || 'Product Title' }}</h1>
    </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { productService } from '@/services/ProductService';

const props = defineProps({
    content: Object,
    settings: Object
});

const product = ref({});

const classes = computed(() => {
    return [
        'w-full',
        props.settings.text_align ? `text-${props.settings.text_align}` : 'text-left'
    ];
});

onMounted(async () => {
    product.value = await productService.getProduct(1);
});
</script>

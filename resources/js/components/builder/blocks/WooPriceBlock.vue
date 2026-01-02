<template>
    <div :class="classes">
        <div class="flex items-baseline gap-2">
            <span v-if="product.on_sale" class="text-lg text-muted-foreground line-through opacity-70">
                {{ product.currency }}{{ product.regular_price }}
            </span>
            <span class="text-2xl font-bold text-primary">
                {{ product.currency }}{{ product.price }}
            </span>
        </div>
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
        'w-full flex',
        props.settings.text_align === 'center' ? 'justify-center' : props.settings.text_align === 'right' ? 'justify-end' : 'justify-start'
    ];
});

onMounted(async () => {
    product.value = await productService.getProduct(1);
});
</script>

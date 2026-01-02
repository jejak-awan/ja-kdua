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

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    text_align: { type: String, default: 'left' },
    context: { type: Object, default: () => ({}) },
    productId: { type: [Number, String], default: null }
});

const product = ref({});
const loading = ref(false);

const classes = computed(() => {
    return [
        'w-full flex',
        props.text_align === 'center' ? 'justify-center' : props.text_align === 'right' ? 'justify-end' : 'justify-start'
    ];
});

onMounted(async () => {
    const id = props.productId || props.context?.id || 1;
    loading.value = true;
    try {
        product.value = await productService.getProduct(id);
    } finally {
        loading.value = false;
    }
});
</script>

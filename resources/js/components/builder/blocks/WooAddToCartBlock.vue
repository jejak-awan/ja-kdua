<template>
    <div :class="classes">
        <div class="flex items-center gap-4">
            <!-- Quantity -->
            <div class="flex items-center border border-border rounded-md">
                <button @click="decrement" class="px-3 py-2 text-muted-foreground hover:bg-muted">-</button>
                <input type="number" v-model="quantity" min="1" class="w-12 text-center border-none bg-transparent focus:outline-none text-sm" />
                <button @click="increment" class="px-3 py-2 text-muted-foreground hover:bg-muted">+</button>
            </div>
            
            <!-- Button -->
            <button 
                @click="addToCart"
                :disabled="loading"
                class="bg-primary text-primary-foreground px-8 py-2.5 rounded-md font-bold hover:opacity-90 transition-opacity disabled:opacity-50"
            >
                {{ loading ? 'Loading...' : (custom_label || 'Add to Cart') }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { useCartStore } from '@/stores/useCartStore';
import { productService } from '@/services/ProductService';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    custom_label: { type: String, default: 'Add to Cart' },
    alignment: { type: String, default: 'left' },
    context: { type: Object, default: () => ({}) },
    productId: { type: [Number, String], default: null }
});

const cartStore = useCartStore();
const quantity = ref(1);
const product = ref(null);
const loading = ref(false);

const classes = computed(() => {
    return [
        'w-full flex',
        props.alignment === 'center' ? 'justify-center' : props.alignment === 'right' ? 'justify-end' : 'justify-start'
    ];
});

onMounted(async () => {
    // Try to get product from context or props
    const id = props.productId || props.context?.id || 1; // Default to 1 for demo
    loading.value = true;
    try {
        product.value = await productService.getProduct(id);
    } finally {
        loading.value = false;
    }
});

const addToCart = () => {
    if (!product.value) return;
    
    cartStore.addToCart(product.value, quantity.value);
    
    // Optional: Visual feedback
    // toast.success('Added to cart');
};

const increment = () => quantity.value++;
const decrement = () => {
    if (quantity.value > 1) quantity.value--;
};
</script>

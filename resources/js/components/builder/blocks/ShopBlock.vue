<template>
    <div 
        class="shop-grid" 
        :class="containerClasses"
        :style="containerStyles"
    >
        <template v-if="loading">
            <div v-for="i in limit" :key="i" class="animate-pulse flex flex-col gap-2">
                <div class="bg-muted aspect-[3/4] rounded"></div>
                <div class="h-4 bg-muted w-3/4"></div>
                <div class="h-4 bg-muted w-1/4"></div>
            </div>
        </template>
        
        <template v-else>
            <div v-for="product in products" :key="product.id" class="group relative flex flex-col gap-3">
                <!-- Image -->
                <div class="relative overflow-hidden rounded-lg bg-card border border-border/50 aspect-[3/4] group-hover:shadow-lg transition-all duration-300">
                    <img 
                        :src="product.images[0]" 
                        :alt="product.name"
                        class="h-full w-full object-cover object-center group-hover:scale-105 transition-transform duration-500"
                    />
                    
                    <!-- Sale Badge -->
                    <span 
                        v-if="product.on_sale" 
                        class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded"
                    >
                        SALE
                    </span>

                    <!-- Quick Actions -->
                    <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300 flex justify-center">
                        <button class="bg-primary text-primary-foreground shadow-lg px-4 py-2 rounded-full text-xs font-bold hover:scale-105 transition-transform">
                            Add to Cart
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex flex-col gap-1 text-center">
                    <h3 class="text-sm font-medium text-foreground group-hover:text-primary transition-colors cursor-pointer">{{ product.name }}</h3>
                    <div class="flex items-center justify-center gap-2 text-sm">
                        <span v-if="product.on_sale" class="text-muted-foreground line-through text-xs">{{ product.currency }}{{ product.regular_price }}</span>
                        <span class="font-bold text-foreground">{{ product.currency }}{{ product.price }}</span>
                    </div>
                    
                    <div v-if="show_rating" class="flex justify-center text-yellow-400 text-xs gap-0.5">
                       <span v-for="i in 5" :key="i">
                           <svg class="w-3 h-3" :class="i <= Math.round(product.rating) ? 'fill-current' : 'text-muted fill-current'" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                       </span>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { productService } from '@/services/ProductService';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    columns: { type: [Number, String], default: 4 },
    limit: { type: [Number, String], default: 8 },
    show_rating: { type: Boolean, default: true },
    margin_top: { type: [Number, String], default: 0 },
    margin_bottom: { type: [Number, String], default: 0 },
    context: { type: Object, default: () => ({}) }
});

const loading = ref(true);
const products = ref([]);

const containerClasses = computed(() => {
    return [
        'grid gap-6',
        `grid-cols-1 sm:grid-cols-2 lg:grid-cols-${props.columns}`
    ];
});

const containerStyles = computed(() => {
    return {
        paddingTop: `${props.margin_top}px`,
        paddingBottom: `${props.margin_bottom}px`,
    };
});

onMounted(async () => {
    try {
        products.value = await productService.getProducts({ limit: props.limit });
    } finally {
        loading.value = false;
    }
});
</script>

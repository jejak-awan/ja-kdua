<template>
    <div class="space-y-4">
        <div class="aspect-square rounded-xl overflow-hidden bg-gray-100 border border-border/50 relative group">
             <img 
                :src="activeImage" 
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
            />
        </div>
        <div class="flex gap-2 overflow-x-auto pb-2">
            <div 
                v-for="img in product.images" 
                :key="img"
                class="w-20 h-20 rounded-lg overflow-hidden cursor-pointer border-2 transition-colors shrink-0"
                :class="activeImage === img ? 'border-primary' : 'border-transparent hover:border-border'"
                @click="activeImage = img"
            >
                <img :src="img" class="w-full h-full object-cover" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted, watch } from 'vue';
import { productService } from '@/services/ProductService';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    show_gallery: { type: Boolean, default: true },
    context: { type: Object, default: () => ({}) }
});

const product = ref({ images: [] });
const activeImage = ref('');

onMounted(async () => {
    product.value = await productService.getProduct(1);
    if (product.value.images.length > 0) {
        activeImage.value = product.value.images[0];
    }
});
</script>

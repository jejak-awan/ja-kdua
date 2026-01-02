<template>
    <div class="border border-border/60 rounded-lg overflow-hidden">
        <!-- Tabs Header -->
        <div class="flex border-b border-border/60 bg-muted/20">
            <button 
                class="px-6 py-3 text-sm font-medium transition-colors border-b-2"
                :class="activeTab === 'desc' ? 'border-primary text-primary bg-background' : 'border-transparent text-muted-foreground hover:text-foreground'"
                @click="activeTab = 'desc'"
            >
                Description
            </button>
             <button 
                class="px-6 py-3 text-sm font-medium transition-colors border-b-2"
                :class="activeTab === 'reviews' ? 'border-primary text-primary bg-background' : 'border-transparent text-muted-foreground hover:text-foreground'"
                @click="activeTab = 'reviews'"
            >
                Reviews ({{ product.review_count }})
            </button>
             <button 
                class="px-6 py-3 text-sm font-medium transition-colors border-b-2"
                :class="activeTab === 'info' ? 'border-primary text-primary bg-background' : 'border-transparent text-muted-foreground hover:text-foreground'"
                @click="activeTab = 'info'"
            >
                Additional Info
            </button>
        </div>

        <!-- Content -->
        <div class="p-6 bg-card min-h-[150px]">
            <div v-show="activeTab === 'desc'" class="prose prose-sm dark:prose-invert max-w-none">
                <p>{{ product.description }}</p>
            </div>
             <div v-show="activeTab === 'reviews'" class="space-y-4">
                <div v-for="i in 2" :key="i" class="flex gap-3 pb-3 border-b border-border/50 last:border-0">
                    <div class="w-8 h-8 rounded-full bg-muted flex items-center justify-center text-xs font-bold">U{{i}}</div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-xs">User {{i}}</span>
                            <div class="flex text-yellow-500 text-[10px]"><span v-for="s in 5" :key="s">★</span></div>
                        </div>
                        <p class="text-xs text-muted-foreground">Great product, really enjoyed it!</p>
                    </div>
                </div>
            </div>
             <div v-show="activeTab === 'info'" class="text-sm">
                <div class="grid grid-cols-2 max-w-md gap-2">
                    <span class="font-medium text-muted-foreground">Weight</span>
                    <span>1.2 kg</span>
                    <span class="font-medium text-muted-foreground">Dimensions</span>
                    <span>10 x 20 x 5 cm</span>
                </div>
            </div>
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
const activeTab = ref('desc');

onMounted(async () => {
    product.value = await productService.getProduct(1);
});
</script>

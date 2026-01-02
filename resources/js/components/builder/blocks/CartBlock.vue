<template>
    <div class="w-full">
        <div v-if="cartStore.isEmpty" class="flex flex-col items-center justify-center p-12 border border-border border-dashed rounded-lg bg-muted/30">
            <div class="w-16 h-16 mb-4 text-muted-foreground bg-muted rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
            <h3 class="text-lg font-medium text-foreground">Your cart is empty</h3>
            <p class="text-muted-foreground mt-1">Add some products to see them here.</p>
        </div>

        <div v-else class="flex flex-col gap-8">
            <!-- Cart Table -->
            <div class="overflow-hidden border border-border rounded-lg">
                <table class="w-full text-sm text-left">
                    <thead class="bg-muted/50 text-muted-foreground font-medium border-b border-border">
                        <tr>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3 text-center">Price</th>
                            <th class="px-4 py-3 text-center">Quantity</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 w-[50px]"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="item in cartStore.cartItems" :key="item.product.id" class="group bg-card hover:bg-muted/30 transition-colors">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded overflow-hidden bg-muted flex-shrink-0 border border-border">
                                        <img :src="item.product.images[0]" :alt="item.product.name" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <div class="font-medium text-foreground">{{ item.product.name }}</div>
                                        <div class="text-xs text-muted-foreground">SKU: {{ item.product.sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                {{ item.product.currency }}{{ item.product.sale_price || item.product.price }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="updateQty(item, -1)" class="w-8 h-8 rounded-full border border-border flex items-center justify-center hover:bg-muted hover:text-foreground transition-colors">-</button>
                                    <span class="w-8 text-center font-medium">{{ item.quantity }}</span>
                                    <button @click="updateQty(item, 1)" class="w-8 h-8 rounded-full border border-border flex items-center justify-center hover:bg-muted hover:text-foreground transition-colors">+</button>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-right font-medium">
                                {{ item.product.currency }}{{ (parseFloat(item.product.sale_price || item.product.price) * item.quantity).toFixed(2) }}
                            </td>
                            <td class="px-4 py-4 text-right">
                                <button @click="cartStore.removeFromCart(item.product.id)" class="text-muted-foreground hover:text-destructive transition-colors p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Cart Summary -->
            <div class="flex justify-end">
                <div class="w-full md:w-1/3 bg-card border border-border rounded-lg p-6 space-y-4">
                    <h4 class="font-bold text-lg">Cart Totals</h4>
                    <div class="flex justify-between py-2 border-b border-border">
                        <span class="text-muted-foreground">Subtotal</span>
                        <span class="font-medium">${{ cartStore.cartTotal }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-border">
                        <span class="text-muted-foreground">Total</span>
                        <span class="font-bold text-xl">${{ cartStore.cartTotal }}</span>
                    </div>
                    <button class="w-full bg-primary text-primary-foreground py-3 rounded-lg font-bold shadow hover:opacity-90 transition-opacity mt-4">
                        Proceed to Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useCartStore } from '@/stores/useCartStore';

defineOptions({
    inheritAttrs: false
});

const cartStore = useCartStore();

const updateQty = (item, change) => {
    cartStore.updateQuantity(item.product.id, item.quantity + change);
};
</script>

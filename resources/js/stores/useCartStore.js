import { defineStore } from 'pinia';

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: [], // { product: Object, quantity: Number }
        isOpen: false, // For cart drawer/modal visibility
    }),

    getters: {
        cartItems: (state) => state.items,

        cartCount: (state) => {
            return state.items.reduce((total, item) => total + item.quantity, 0);
        },

        cartTotal: (state) => {
            return state.items.reduce((total, item) => {
                const price = parseFloat(item.product.sale_price || item.product.price || 0);
                return total + (price * item.quantity);
            }, 0).toFixed(2);
        },

        isEmpty: (state) => state.items.length === 0,
    },

    actions: {
        addToCart(product, quantity = 1) {
            const existingItem = this.items.find(item => item.product.id === product.id);

            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                this.items.push({
                    product,
                    quantity
                });
            }

            // Optional: Open cart or show toast
            this.isOpen = true;
        },

        removeFromCart(productId) {
            this.items = this.items.filter(item => item.product.id !== productId);
        },

        updateQuantity(productId, quantity) {
            const item = this.items.find(item => item.product.id === productId);
            if (item) {
                item.quantity = quantity;
                if (item.quantity <= 0) {
                    this.removeFromCart(productId);
                }
            }
        },

        clearCart() {
            this.items = [];
        },

        toggleCart() {
            this.isOpen = !this.isOpen;
        }
    },

    // Persist state to localStorage (simple implementation)
    persist: {
        enabled: true,
        strategies: [
            {
                key: 'ja-cms-cart',
                storage: localStorage,
            },
        ],
    },
});

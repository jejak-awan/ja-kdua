
import { ShoppingCart } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export const WooAddToCart = {
    name: 'woo_add_to_cart',
    label: 'Add To Cart',
    icon: ShoppingCart,
    category: 'commerce',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/WooAddToCartBlock.vue')),
    settings: [
        {
            key: 'custom_label',
            type: 'text',
            label: 'Button Label',
            default: 'Add to Cart'
        },
        {
            key: 'alignment',
            type: 'select',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
            ],
            default: 'left'
        }
    ]
};

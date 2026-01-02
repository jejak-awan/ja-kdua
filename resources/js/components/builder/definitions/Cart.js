import { defineAsyncComponent } from 'vue';
import { ShoppingCart } from 'lucide-vue-next';

export const Cart = {
    name: 'Cart',
    label: 'Cart',
    category: 'Commerce',
    icon: ShoppingCart,
    component: defineAsyncComponent(() => import('../blocks/CartBlock.vue')),
    settings: {
        fields: [
            {
                name: '_css_class',
                label: 'CSS Class',
                type: 'text'
            },
            {
                name: '_css_id',
                label: 'CSS ID',
                type: 'text'
            }
        ]
    }
};

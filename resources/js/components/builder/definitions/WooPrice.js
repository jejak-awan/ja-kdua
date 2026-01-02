
import { DollarSign } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export const WooPrice = {
    name: 'woo_price',
    label: 'Product Price',
    icon: DollarSign,
    category: 'commerce',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/WooPriceBlock.vue')),
    settings: [
        {
            key: 'text_align',
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

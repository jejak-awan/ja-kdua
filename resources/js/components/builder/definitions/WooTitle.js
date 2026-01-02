
import { Heading } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export const WooTitle = {
    name: 'woo_title',
    label: 'Product Title',
    icon: Heading,
    category: 'commerce',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/WooTitleBlock.vue')),
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

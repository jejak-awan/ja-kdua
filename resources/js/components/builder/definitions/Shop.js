
import { ShoppingBag } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export const Shop = {
    name: 'shop',
    label: 'Shop Grid',
    icon: ShoppingBag,
    category: 'commerce',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/ShopBlock.vue')),
    settings: [
        {
            key: 'columns',
            type: 'select',
            label: 'Columns',
            options: [
                { label: '2 Columns', value: 2 },
                { label: '3 Columns', value: 3 },
                { label: '4 Columns', value: 4 },
                { label: '5 Columns', value: 5 },
                { label: '6 Columns', value: 6 }
            ],
            default: 4
        },
        {
            key: 'limit',
            type: 'text',
            label: 'Product Count',
            default: 8
        },
        {
            key: 'show_rating',
            type: 'toggle',
            label: 'Show Rating',
            default: true
        },
        {
            key: 'orderby',
            type: 'select',
            label: 'Order By',
            options: [
                { label: 'Default', value: 'menu_order' },
                { label: 'Latest', value: 'date' },
                { label: 'Price: Low to High', value: 'price' },
                { label: 'Price: High to Low', value: 'price-desc' },
                { label: 'Rating', value: 'rating' }
            ],
            default: 'date'
        },
        {
            key: 'margin_top',
            type: 'slider',
            label: 'Margin Top',
            min: 0,
            max: 100,
            default: 0
        },
        {
            key: 'margin_bottom',
            type: 'slider',
            label: 'Margin Bottom',
            min: 0,
            max: 100,
            default: 0
        }
    ]
};

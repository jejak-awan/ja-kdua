
import { Image as ImageIcon } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export const WooImages = {
    name: 'woo_images',
    label: 'Product Images',
    icon: ImageIcon,
    category: 'commerce',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/WooImagesBlock.vue')),
    settings: []
};


import { List } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export const WooTabs = {
    name: 'woo_tabs',
    label: 'Product Tabs',
    icon: List,
    category: 'commerce',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/WooTabsBlock.vue')),
    settings: []
};

import { defineAsyncComponent } from 'vue';
export default {
    type: 'logogrid',
    name: 'Logo Grid',
    component: defineAsyncComponent(() => import('@/shared/blocks/LogoGridBlock.vue')),
    settings: [
        { name: 'items', type: 'repeater' },
        { name: 'showTitle', type: 'boolean' },
        { name: 'title', type: 'string' },
        { name: 'columns', type: 'number' },
        { name: 'gap', type: 'number' },
        { name: 'logoSize', type: 'number' },
        { name: 'grayscale', type: 'boolean' },
        { name: 'hoverColor', type: 'boolean' },
        { name: 'logoOpacity', type: 'number' }
    ]
};

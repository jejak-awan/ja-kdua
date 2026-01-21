import { defineAsyncComponent } from 'vue';

export default {
    name: 'beforeafter', // Must match builder module name
    component: defineAsyncComponent(() => import('../blocks/BeforeAfterBlock.vue')),
    label: 'Before/After',
    icon: 'SplitSquareHorizontal'
};

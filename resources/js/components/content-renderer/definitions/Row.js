import { Columns } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'row',
    label: 'Row',
    icon: Columns,
    description: 'A row layout container.',
    component: defineAsyncComponent(() => import('../blocks/RowBlock.vue')),
    settings: [] // Settings handled by builder metadata
};

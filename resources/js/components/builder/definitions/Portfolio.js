import { Briefcase } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'portfolio',
    label: 'Portfolio',
    icon: Briefcase,
    description: 'Filterable project portfolio grid.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/PortfolioBlock.vue')),
    settings: [
        { key: 'title', type: 'text', label: 'Section Title', default: '' },
        { key: 'category', type: 'text', label: 'Category Filter', default: '' },
        { key: 'limit', type: 'number', label: 'Number of Projects', default: 9 },
        {
            key: 'columns',
            type: 'select',
            label: 'Columns',
            options: [
                { label: '2 Columns', value: '2' },
                { label: '3 Columns', value: '3' },
                { label: '4 Columns', value: '4' }
            ],
            default: '3'
        },
        { key: 'show_filter', type: 'boolean', label: 'Show Category Filter', default: true },
        { key: 'show_title', type: 'boolean', label: 'Show Project Title', default: true },
        { key: 'show_category', type: 'boolean', label: 'Show Category Label', default: true },
        {
            key: 'style',
            type: 'select',
            label: 'Style',
            options: [
                { label: 'Cards', value: 'cards' },
                { label: 'Minimal', value: 'minimal' }
            ],
            default: 'cards'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'None', value: '' },
                { label: 'Small', value: 'py-8' },
                { label: 'Medium', value: 'py-16' },
                { label: 'Large', value: 'py-24' }
            ],
            default: 'py-16'
        },
        { key: 'bgColor', type: 'color', label: 'Background Color', default: '' }
    ],
    defaultSettings: {
        title: 'Our Work',
        category: '',
        limit: 9,
        columns: '3',
        show_filter: true,
        show_title: true,
        show_category: true,
        style: 'cards',
        padding: 'py-16',
        bgColor: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

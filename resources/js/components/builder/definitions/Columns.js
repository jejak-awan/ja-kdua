import { Columns as ColumnsIcon } from 'lucide-vue-next';

export default {
    name: 'columns',
    label: 'Columns',
    icon: ColumnsIcon,
    description: 'Create multi-column layouts.',
    component: () => import('@/components/builder/blocks/ColumnsBlock.vue'),
    settings: [
        {
            key: 'layout',
            type: 'select',
            label: 'Layout',
            options: [
                { label: 'Two Columns (50/50)', value: '1-1' },
                { label: 'One-Third / Two-Thirds', value: '1-2' },
                { label: 'Two-Thirds / One-Third', value: '2-1' },
                { label: 'Three Columns', value: '1-1-1' } // Future usage
                // { label: 'Four Columns', value: '1-1-1-1' }
            ],
            default: '1-1'
        },
        {
            key: 'width',
            type: 'select',
            label: 'Container Width',
            options: [
                { label: 'Standard', value: 'max-w-7xl' },
                { label: 'Full', value: 'max-w-full' }
            ],
            default: 'max-w-7xl'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'None', value: 'py-0' },
                { label: 'Medium', value: 'py-16' },
                { label: 'Large', value: 'py-24' }
            ],
            default: 'py-16'
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: 'transparent'
        }
    ],
    defaultSettings: {
        layout: '1-1',
        columns: [{ blocks: [] }, { blocks: [] }],
        padding: 'py-16',
        width: 'max-w-7xl',
        bgColor: 'transparent',
        radius: 'rounded-none',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

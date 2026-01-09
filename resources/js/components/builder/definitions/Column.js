import { Columns as ColumnsIcon } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'column',
    label: 'Column',
    icon: ColumnsIcon,
    description: 'A single column within a row.',
    category: 'structure',
    hidden: true, // Only created by Row
    component: defineAsyncComponent(() => import('@/components/builder/blocks/ColumnBlock.vue')),

    settings: [
        { type: 'header', label: 'Appearance', tab: 'style' },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background',
            default: 'transparent',
            tab: 'style'
        },
        {
            key: 'padding',
            type: 'box_model',
            mode: 'padding',
            label: 'Padding',
            default: { top: '0', right: '0', bottom: '0', left: '0' },
            tab: 'style'
        },
        {
            key: 'borderWidth',
            type: 'slider',
            label: 'Border Width',
            min: 0, max: 10, step: 1,
            default: 0,
            tab: 'style'
        },
        {
            key: 'borderColor',
            type: 'color',
            label: 'Border Color',
            default: '#e5e7eb',
            tab: 'style'
        },
        {
            key: 'radius',
            type: 'select',
            label: 'Radius',
            options: [
                { label: 'None', value: 'rounded-none' },
                { label: 'Small', value: 'rounded-sm' },
                { label: 'Default', value: 'rounded' },
                { label: 'Medium', value: 'rounded-md' },
                { label: 'Large', value: 'rounded-lg' },
                { label: 'Full', value: 'rounded-full' },
            ],
            default: 'rounded-none',
            tab: 'style'
        },
        // Internal Flex settings for the Column content? 
        // Usually Columns stack vertically.
        { type: 'header', label: 'Layout', tab: 'style' },
        {
            key: 'direction',
            type: 'toggle_group',
            label: 'Direction',
            options: [
                { label: 'Col', value: 'flex-col' },
                { label: 'Row', value: 'flex-row' }
            ],
            default: 'flex-col',
            tab: 'style'
        },
        {
            key: 'justify',
            type: 'toggle_group',
            label: 'Justify',
            options: [
                { label: 'Start', value: 'justify-start' },
                { label: 'Center', value: 'justify-center' },
                { label: 'End', value: 'justify-end' },
                { label: 'Between', value: 'justify-between' }
            ],
            default: 'justify-start',
            tab: 'style'
        },
        {
            key: 'align',
            type: 'toggle_group',
            label: 'Align',
            options: [
                { label: 'Start', value: 'items-start' },
                { label: 'Center', value: 'items-center' },
                { label: 'End', value: 'items-end' },
                { label: 'Stretch', value: 'items-stretch' }
            ],
            default: 'items-stretch',
            tab: 'style'
        },

        // Children blocks storage
        {
            key: 'blocks',
            type: 'hidden',
            default: []
        }
    ],

    defaultSettings: {
        bgColor: 'transparent',
        padding: { top: '0', right: '0', bottom: '0', left: '0' },
        borderWidth: 0,
        borderColor: '#e5e7eb',
        radius: 'rounded-none',
        direction: 'flex-col',
        justify: 'justify-start',
        align: 'items-stretch',
        blocks: [],
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

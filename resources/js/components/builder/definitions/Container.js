import { Box, AlignHorizontalJustifyStart, AlignHorizontalJustifyCenter, AlignHorizontalJustifyEnd, AlignHorizontalSpaceBetween, AlignVerticalJustifyStart, AlignVerticalJustifyCenter, AlignVerticalJustifyEnd } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'container',
    label: 'Container',
    icon: Box,
    description: 'A flexible container for grouping and laying out other blocks.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/ContainerBlock.vue')),
    settings: [
        { type: 'header', label: 'Layout', tab: 'style' },
        {
            key: 'direction',
            type: 'toggle_group',
            label: 'Direction',
            options: [
                { label: 'Column', value: 'flex-col' },
                { label: 'Row', value: 'flex-row' }
            ],
            default: 'flex-col',
            tab: 'style'
        },
        {
            key: 'justify',
            type: 'toggle_group',
            label: 'Justify Content',
            options: [
                { label: 'Start', value: 'justify-start', icon: AlignHorizontalJustifyStart },
                { label: 'Center', value: 'justify-center', icon: AlignHorizontalJustifyCenter },
                { label: 'End', value: 'justify-end', icon: AlignHorizontalJustifyEnd },
                { label: 'Space Between', value: 'justify-between', icon: AlignHorizontalSpaceBetween }
            ],
            default: 'justify-start',
            tab: 'style'
        },
        {
            key: 'align',
            type: 'toggle_group',
            label: 'Align Items',
            options: [
                { label: 'Start', value: 'items-start', icon: AlignVerticalJustifyStart },
                { label: 'Center', value: 'items-center', icon: AlignVerticalJustifyCenter },
                { label: 'End', value: 'items-end', icon: AlignVerticalJustifyEnd },
                { label: 'Stretch', value: 'items-stretch' }
            ],
            default: 'items-start',
            tab: 'style'
        },
        {
            key: 'gap',
            type: 'toggle_group',
            label: 'Gap',
            options: [
                { label: 'None', value: 'gap-0' },
                { label: 'Sm', value: 'gap-2' },
                { label: 'Md', value: 'gap-4' },
                { label: 'Lg', value: 'gap-8' }
            ],
            default: 'gap-4',
            tab: 'style'
        },
        { type: 'header', label: 'Spacing', tab: 'style' },
        {
            key: 'padding',
            type: 'toggle_group',
            label: 'Padding',
            options: [
                { label: 'None', value: 'p-0' },
                { label: 'Sm', value: 'p-4' },
                { label: 'Md', value: 'p-8' },
                { label: 'Lg', value: 'p-12' }
            ],
            default: 'p-4',
            tab: 'style'
        },
        { type: 'header', label: 'Background', tab: 'style' },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: 'transparent',
            tab: 'style'
        },
        {
            key: 'radius',
            type: 'toggle_group',
            label: 'Corner Radius',
            options: [
                { label: 'None', value: 'rounded-none' },
                { label: 'Sm', value: 'rounded-md' },
                { label: 'Md', value: 'rounded-xl' },
                { label: 'Lg', value: 'rounded-2xl' }
            ],
            default: 'rounded-none',
            tab: 'style'
        },
        // The blocks array will hold the nested blocks
        {
            key: 'blocks',
            type: 'hidden',
            default: []
        }
    ],
    defaultSettings: {
        direction: 'flex-col',
        justify: 'justify-start',
        align: 'items-start',
        gap: 'gap-4',
        padding: 'p-4',
        bgColor: 'transparent',
        radius: 'rounded-none',
        blocks: [],
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

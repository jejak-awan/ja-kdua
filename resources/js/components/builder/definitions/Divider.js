import { Minus } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'divider',
    label: 'Divider',
    icon: Minus,
    description: 'Visual separator line with multiple styles.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/DividerBlock.vue')),
    settings: [
        {
            key: 'style',
            type: 'select',
            label: 'Style',
            options: [
                { label: 'Solid Line', value: 'line' },
                { label: 'Dashed', value: 'dashed' },
                { label: 'Dotted', value: 'dotted' },
                { label: 'Double', value: 'double' },
                { label: 'Gradient', value: 'gradient' },
                { label: 'Shadow', value: 'shadow' }
            ],
            default: 'line'
        },
        {
            key: 'color',
            type: 'color',
            label: 'Line Color',
            default: 'hsl(var(--border))'
        },
        {
            key: 'height',
            type: 'select',
            label: 'Thickness',
            options: [
                { label: 'Thin', value: 'small' },
                { label: 'Medium', value: 'medium' },
                { label: 'Thick', value: 'large' }
            ],
            default: 'medium'
        },
        {
            key: 'width',
            type: 'select',
            label: 'Width',
            options: [
                { label: 'Full', value: 'max-w-full' },
                { label: 'Large', value: 'max-w-4xl' },
                { label: 'Medium', value: 'max-w-2xl' },
                { label: 'Small', value: 'max-w-xl' }
            ],
            default: 'max-w-full'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Vertical Space',
            options: [
                { label: 'None', value: 'py-0' },
                { label: 'Small', value: 'py-4' },
                { label: 'Medium', value: 'py-6' },
                { label: 'Large', value: 'py-12' }
            ],
            default: 'py-6'
        }
    ],
    defaultSettings: {
        style: 'line',
        color: 'hsl(var(--border))',
        height: 'medium',
        width: 'max-w-full',
        padding: 'py-6',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

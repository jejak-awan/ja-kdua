import { BarChart3 } from 'lucide-vue-next';

export default {
    name: 'progress-bar',
    label: 'Progress Bar',
    icon: BarChart3,
    description: 'Visual progress indicator.',
    component: () => import('@/components/builder/blocks/ProgressBarBlock.vue'),
    settings: [
        {
            key: 'label',
            type: 'text',
            label: 'Label',
            default: 'Progress'
        },
        {
            key: 'value',
            type: 'slider',
            label: 'Value',
            min: 0,
            max: 100,
            step: 5,
            unit: '%',
            default: 75
        },
        {
            key: 'showValue',
            type: 'boolean',
            label: 'Show Percentage',
            default: true
        },
        {
            key: 'variant',
            type: 'select',
            label: 'Color',
            options: [
                { label: 'Primary', value: 'primary' },
                { label: 'Success', value: 'success' },
                { label: 'Warning', value: 'warning' },
                { label: 'Error', value: 'error' },
                { label: 'Info', value: 'info' }
            ],
            default: 'primary'
        },
        {
            key: 'barColor',
            type: 'color',
            label: 'Custom Bar Color',
            default: ''
        },
        {
            key: 'height',
            type: 'select',
            label: 'Bar Height',
            options: [
                { label: 'Small', value: 'small' },
                { label: 'Medium', value: 'medium' },
                { label: 'Large', value: 'large' }
            ],
            default: 'medium'
        },
        {
            key: 'striped',
            type: 'boolean',
            label: 'Striped Pattern',
            default: false
        },
        {
            key: 'animated',
            type: 'boolean',
            label: 'Animated',
            default: false
        },
        {
            key: 'radius',
            type: 'select',
            label: 'Border Radius',
            options: [
                { label: 'None', value: 'rounded-none' },
                { label: 'Small', value: 'rounded-md' },
                { label: 'Full', value: 'rounded-full' }
            ],
            default: 'rounded-full'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Section Padding',
            options: [
                { label: 'Small', value: 'py-4' },
                { label: 'Medium', value: 'py-6' },
                { label: 'Large', value: 'py-8' }
            ],
            default: 'py-6'
        }
    ],
    defaultSettings: {
        label: 'Progress',
        value: 75,
        showValue: true,
        variant: 'primary',
        barColor: '',
        height: 'medium',
        striped: false,
        animated: false,
        radius: 'rounded-full',
        width: 'max-w-2xl',
        padding: 'py-6',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

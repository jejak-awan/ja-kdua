import { MoveVertical } from 'lucide-vue-next';

export default {
    name: 'spacer',
    label: 'Spacer / Divider',
    icon: MoveVertical,
    description: 'Add vertical space or lines.',
    component: () => import('@/components/builder/blocks/SpacerBlock.vue'),
    settings: [
        {
            key: 'height',
            type: 'select',
            label: 'Height',
            options: [
                { label: 'Small', value: 'h-12' },
                { label: 'Medium', value: 'h-24' },
                { label: 'Large', value: 'h-48' }
            ],
            default: 'h-24'
        },
        {
            key: 'showLine',
            type: 'boolean',
            label: 'Show Line/Divider',
            default: false
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: 'transparent'
        }
    ],
    defaultSettings: {
        height: 'h-24',
        showLine: false,
        padding: 'py-0',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

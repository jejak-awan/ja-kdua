import { LayoutTemplate } from 'lucide-vue-next';

export default {
    name: 'hero',
    label: 'Hero Header',
    icon: LayoutTemplate,
    description: 'Large hero banner with title and background.',
    component: () => import('@/components/builder/blocks/HeroBlock.vue'),
    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Title',
            default: 'New Hero Header'
        },
        {
            key: 'subtitle',
            type: 'textarea',
            label: 'Subtitle',
            default: 'Experience the next generation of visual editing.'
        },
        {
            key: 'bgImage',
            type: 'image',
            label: 'Background Image',
            default: ''
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: 'transparent'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'Small', value: 'py-16' },
                { label: 'Medium', value: 'py-24' },
                { label: 'Large', value: 'py-32' }
            ],
            default: 'py-32'
        },
        {
            key: 'radius',
            type: 'select',
            label: 'Border Radius',
            options: [
                { label: 'None', value: 'rounded-none' },
                { label: 'Small', value: 'rounded-lg' },
                { label: 'Medium', value: 'rounded-2xl' },
                { label: 'Large', value: 'rounded-3xl' }
            ],
            default: 'rounded-none'
        },
        {
            key: 'animation',
            type: 'select',
            label: 'Animation',
            options: [
                { label: 'None', value: '' },
                { label: 'Fade In', value: 'animate-in fade-in duration-700' },
                { label: 'Zoom In', value: 'animate-in zoom-in-95 duration-1000' }
            ],
            default: 'animate-in fade-in duration-700'
        }
    ],
    defaultSettings: {
        // Legacy support / flat structure for easy access
        title: 'New Hero Header',
        subtitle: 'Experience the next generation of visual editing.',
        bgImage: '',
        bgColor: 'transparent',
        padding: 'py-32',
        radius: 'rounded-none',
        animation: 'animate-in fade-in duration-700',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

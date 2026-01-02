import { Image as ImageIcon } from 'lucide-vue-next';

export default {
    name: 'image',
    label: 'Smooth Image',
    icon: ImageIcon,
    description: 'Display high-quality images with custom effects.',
    component: () => import('@/components/builder/blocks/ImageBlock.vue'),
    settings: [
        {
            key: 'url',
            type: 'image',
            label: 'Image Source',
            default: ''
        },
        {
            key: 'width',
            type: 'select',
            label: 'Width',
            options: [
                { label: 'Small', value: 'max-w-md' },
                { label: 'Medium', value: 'max-w-2xl' },
                { label: 'Large', value: 'max-w-5xl' },
                { label: 'Full', value: 'max-w-full' }
            ],
            default: 'max-w-5xl'
        },
        {
            key: 'radius',
            type: 'select',
            label: 'Corner Radius',
            options: [
                { label: 'None', value: 'rounded-none' },
                { label: 'Medium', value: 'rounded-2xl' },
                { label: 'Full', value: 'rounded-full' }
            ],
            default: 'rounded-2xl'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Vertical Padding',
            options: [
                { label: 'None', value: 'py-0' },
                { label: 'Small', value: 'py-8' },
                { label: 'Medium', value: 'py-16' },
                { label: 'Large', value: 'py-24' }
            ],
            default: 'py-16'
        },
        {
            key: 'animation',
            type: 'select',
            label: 'Animation',
            options: [
                { label: 'None', value: '' },
                { label: 'Zoom In', value: 'animate-in zoom-in-95 duration-1000' },
                { label: 'Fade In', value: 'animate-in fade-in duration-1000' }
            ],
            default: 'animate-in zoom-in-95 duration-1000'
        }
    ],
    defaultSettings: {
        title: '',
        url: '',
        width: 'max-w-5xl',
        padding: 'py-16',
        bgColor: 'transparent',
        radius: 'rounded-2xl',
        animation: 'animate-in zoom-in-95 duration-1000',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

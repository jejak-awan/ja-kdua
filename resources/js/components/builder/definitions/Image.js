import { Image as ImageIcon } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'image',
    label: 'Smooth Image',
    icon: ImageIcon,
    description: 'Display high-quality images with custom effects.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/ImageBlock.vue')),
    settings: [
        {
            key: 'url',
            type: 'image',
            label: 'Image Source',
            default: '',
            tab: 'content'
        },
        {
            key: 'width',
            type: 'toggle_group',
            label: 'Width',
            options: [
                { label: 'Sm', value: 'max-w-md' },
                { label: 'Md', value: 'max-w-2xl' },
                { label: 'Lg', value: 'max-w-5xl' },
                { label: 'Full', value: 'max-w-full' }
            ],
            default: 'max-w-5xl',
            tab: 'style'
        },
        {
            key: 'radius',
            type: 'toggle_group',
            label: 'Corner Radius',
            options: [
                { label: 'None', value: 'rounded-none' },
                { label: 'Md', value: 'rounded-2xl' },
                { label: 'Full', value: 'rounded-full' }
            ],
            default: 'rounded-2xl',
            tab: 'style'
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
            default: 'py-16',
            tab: 'style'
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
            default: 'animate-in zoom-in-95 duration-1000',
            tab: 'style'
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

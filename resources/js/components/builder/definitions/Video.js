import { Clapperboard } from 'lucide-vue-next';

export default {
    name: 'video',
    label: 'Video Player',
    icon: Clapperboard,
    description: 'Embed cinematic video content.',
    component: () => import('@/components/builder/blocks/VideoBlock.vue'),
    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Title',
            default: ''
        },
        {
            key: 'videoUrl',
            type: 'text',
            label: 'Video URL',
            default: ''
        },
        {
            key: 'autoplay',
            type: 'boolean', // Needs boolean toggle support in PropertiesPanel
            label: 'Autoplay',
            default: false
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
                { label: 'None', value: 'py-0' },
                { label: 'Medium', value: 'py-16' }
            ],
            default: 'py-16'
        },
        {
            key: 'radius',
            type: 'select',
            label: 'Border Radius',
            options: [
                { label: 'Medium', value: 'rounded-xl' },
                { label: 'Large', value: 'rounded-3xl' }
            ],
            default: 'rounded-3xl'
        }
    ],
    defaultSettings: {
        title: '',
        videoUrl: '',
        autoplay: false,
        padding: 'py-16',
        bgColor: 'transparent',
        radius: 'rounded-3xl',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

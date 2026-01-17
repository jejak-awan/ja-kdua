import { Image as ImageIcon } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'post_featured_image',
    label: 'Post Featured Image',
    icon: ImageIcon,
    category: 'Dynamic',
    description: 'Displays the dynamic featured image of the post.',
    component: defineAsyncComponent(() => import('@/components/content-renderer/blocks/PostFeaturedImage.vue')),
    settings: [
        {
            key: 'aspectRatio',
            type: 'select',
            label: 'Aspect Ratio',
            options: [
                { label: 'Auto', value: 'aspect-auto' },
                { label: 'Square (1:1)', value: 'aspect-square' },
                { label: 'Video (16:9)', value: 'aspect-video' },
                { label: 'Standard (4:3)', value: 'aspect-4/3' }
            ],
            default: 'aspect-video'
        },
        {
            key: 'rounded',
            type: 'select',
            label: 'Corners',
            options: [
                { label: 'None', value: 'rounded-none' },
                { label: 'Small', value: 'rounded' },
                { label: 'Medium', value: 'rounded-lg' },
                { label: 'Large', value: 'rounded-2xl' }
            ],
            default: 'rounded-lg'
        },
        {
            key: 'shadow',
            type: 'select',
            label: 'Shadow',
            options: [
                { label: 'None', value: 'shadow-none' },
                { label: 'Small', value: 'shadow-sm' },
                { label: 'Medium', value: 'shadow-md' },
                { label: 'Large', value: 'shadow-xl' }
            ],
            default: 'shadow-md'
        },
        { key: 'overlay', type: 'boolean', label: 'Enable Overlay', default: false },
        { key: 'customHeight', type: 'text', label: 'Custom Height (e.g., 400px)', default: '' }
    ],
    defaultSettings: {
        aspectRatio: 'aspect-video',
        rounded: 'rounded-lg',
        shadow: 'shadow-md',
        overlay: false,
        customHeight: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

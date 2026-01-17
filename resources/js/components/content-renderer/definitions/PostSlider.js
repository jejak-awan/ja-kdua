import { SlidersHorizontal } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'post_slider',
    label: 'Post Slider',
    icon: SlidersHorizontal,
    description: 'Display posts in a fullwidth slider.',
    component: defineAsyncComponent(() => import('@/components/content-renderer/blocks/PostSliderBlock.vue')),
    settings: [
        { key: 'title', type: 'text', label: 'Section Title', default: '' },
        {
            key: 'post_type',
            type: 'select',
            label: 'Post Type',
            options: [
                { label: 'Posts', value: 'post' },
                { label: 'Pages', value: 'page' },
                { label: 'Projects', value: 'project' }
            ],
            default: 'post'
        },
        { key: 'category', type: 'text', label: 'Category Filter', default: '' },
        { key: 'limit', type: 'number', label: 'Number of Posts', default: 5 },
        { key: 'autoplay', type: 'boolean', label: 'Autoplay', default: true },
        { key: 'autoplay_speed', type: 'number', label: 'Autoplay Speed (ms)', default: 5000 },
        { key: 'show_arrows', type: 'boolean', label: 'Show Arrows', default: true },
        { key: 'show_dots', type: 'boolean', label: 'Show Dots', default: true },
        { key: 'show_excerpt', type: 'boolean', label: 'Show Excerpt', default: true },
        {
            key: 'height',
            type: 'select',
            label: 'Height',
            options: [
                { label: 'Small (300px)', value: 'h-[300px]' },
                { label: 'Medium (500px)', value: 'h-[500px]' },
                { label: 'Large (700px)', value: 'h-[700px]' },
                { label: 'Full Screen', value: 'h-screen' }
            ],
            default: 'h-[500px]'
        },
        { key: 'overlay_color', type: 'color', label: 'Overlay Color', default: 'rgba(0,0,0,0.5)' },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'None', value: '' },
                { label: 'Small', value: 'py-4' },
                { label: 'Medium', value: 'py-8' }
            ],
            default: ''
        }
    ],
    defaultSettings: {
        title: '',
        post_type: 'post',
        category: '',
        limit: 5,
        autoplay: true,
        autoplay_speed: 5000,
        show_arrows: true,
        show_dots: true,
        show_excerpt: true,
        height: 'h-[500px]',
        overlay_color: 'rgba(0,0,0,0.5)',
        padding: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

import { LayoutTemplate } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'hero',
    label: 'Hero Header',
    icon: LayoutTemplate,
    description: 'Large hero banner with title and background.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/HeroBlock.vue')),
    settings: [
        { type: 'header', label: 'Typography', tab: 'content' },
        {
            key: 'title',
            type: 'text',
            label: 'Title',
            default: 'New Hero Header',
            tab: 'content'
        },
        {
            key: 'titleSize',
            type: 'toggle_group',
            label: 'Title Size',
            options: [
                { label: 'Md', value: 'text-4xl md:text-5xl' },
                { label: 'Lg', value: 'text-5xl md:text-7xl' },
                { label: 'XL', value: 'text-6xl md:text-8xl' }
            ],
            default: 'text-5xl md:text-7xl',
            tab: 'style'
        },
        {
            key: 'titleColor',
            type: 'color',
            label: 'Title Color',
            default: '#ffffff',
            tab: 'style'
        },
        {
            key: 'subtitle',
            type: 'textarea',
            label: 'Subtitle',
            default: 'Experience the next generation of visual editing.',
            tab: 'content'
        },
        {
            key: 'subtitleSize',
            type: 'toggle_group',
            label: 'Subtitle Size',
            options: [
                { label: 'Sm', value: 'text-lg md:text-xl' },
                { label: 'Md', value: 'text-xl md:text-2xl' },
                { label: 'Lg', value: 'text-2xl md:text-3xl' }
            ],
            default: 'text-xl md:text-2xl',
            tab: 'style'
        },
        {
            key: 'subtitleColor',
            type: 'color',
            label: 'Subtitle Color',
            default: 'rgba(255, 255, 255, 0.8)',
            tab: 'style'
        },
        { type: 'header', label: 'Background', tab: 'style' },
        {
            key: 'bgImage',
            type: 'image',
            label: 'Background Image',
            default: '',
            tab: 'style'
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: '#18181b', // Default to dark for visibility
            tab: 'style'
        },
        { type: 'header', label: 'Layout', tab: 'style' },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'Small', value: 'py-16' },
                { label: 'Medium', value: 'py-24' },
                { label: 'Large', value: 'py-32' },
                { label: 'Extra Large', value: 'py-40' }
            ],
            default: 'py-32',
            tab: 'style'
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
            default: 'rounded-none',
            tab: 'style'
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
            default: 'animate-in fade-in duration-700',
            tab: 'style'
        }
    ],
    defaultSettings: {
        // Legacy support / flat structure for easy access
        title: 'New Hero Header',
        titleSize: 'text-5xl md:text-7xl',
        titleColor: '#ffffff',
        subtitle: 'Experience the next generation of visual editing.',
        subtitleSize: 'text-xl md:text-2xl',
        subtitleColor: 'rgba(255, 255, 255, 0.8)',
        bgImage: '',
        bgColor: '#18181b',
        padding: 'py-32',
        radius: 'rounded-none',
        animation: 'animate-in fade-in duration-700',
        blocks: [], // Nested blocks support
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

import { Grid } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'features',
    label: 'Feature Grid',
    icon: Grid,
    description: 'Display features in a responsive grid.',
    component: defineAsyncComponent(() => import('@/components/content-renderer/blocks/FeatureGridBlock.vue')),
    settings: [
        { type: 'header', label: 'Content', tab: 'content' },
        {
            key: 'title',
            type: 'text',
            label: 'Section Title',
            default: 'Core Features',
            tab: 'content'
        },
        {
            key: 'items',
            type: 'repeater',
            label: 'Features',
            itemLabel: 'Feature',
            fields: [
                { key: 'title', type: 'text', label: 'Title', default: 'New Feature' },
                {
                    key: 'icon',
                    type: 'icon',
                    label: 'Icon',
                    default: 'zap'
                },
                { key: 'description', type: 'textarea', label: 'Description', default: 'Feature description goes here.' }
            ],
            default: [
                { title: 'Visual Editor', description: 'Drag and drop elements with real-time feedback.' },
                { title: 'Responsive', description: 'Perfect look on every screen size.' },
                { title: 'Lightning Fast', description: 'Optimized for speed and SEO.' }
            ],
            tab: 'content'
        },
        { type: 'header', label: 'Grid Settings', tab: 'style' },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'Medium', value: 'py-20' },
                { label: 'Large', value: 'py-32' }
            ],
            default: 'py-20',
            tab: 'style'
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: 'transparent',
            tab: 'style'
        },
        {
            key: 'radius',
            type: 'select',
            label: 'Border Radius',
            options: [
                { label: 'None', value: 'rounded-none' },
                { label: 'Small', value: 'rounded-lg' }
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
                { label: 'Fade In', value: 'animate-in fade-in duration-700' }
            ],
            default: '',
            tab: 'style'
        }
    ],
    defaultSettings: {
        title: 'Core Features',
        items: [
            { title: 'Visual Editor', description: 'Drag and drop elements with real-time feedback.' },
            { title: 'Responsive', description: 'Perfect look on every screen size.' },
            { title: 'Lightning Fast', description: 'Optimized for speed and SEO.' }
        ],
        padding: 'py-20',
        bgColor: 'transparent',
        radius: 'rounded-none',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

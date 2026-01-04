import { MousePointerClick } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';


export default {
    name: 'cta',
    label: 'Action Bar',
    icon: MousePointerClick,
    description: 'Eye-catching section with a primary button.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/CTABlock.vue')),
    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Title',
            default: 'Start Building Today'
        },
        {
            key: 'subtitle',
            type: 'textarea',
            label: 'Subtitle',
            default: 'Join thousands of creators using JA-Builder.'
        },
        {
            key: 'buttonText',
            type: 'text',
            label: 'Button Text',
            default: 'Get Started Now'
        },
        {
            key: 'buttonUrl',
            type: 'text', // Or 'url' type if we make one
            label: 'Button Link',
            default: '#'
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: '#4f46e5'
        },
        {
            key: 'textColor',
            type: 'color',
            label: 'Text Color',
            default: '#ffffff'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
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
                { label: 'Medium', value: 'rounded-2xl' },
                { label: 'Large', value: 'rounded-3xl' }
            ],
            default: 'rounded-2xl'
        }
    ],
    defaultSettings: {
        title: 'Start Building Today',
        subtitle: 'Join thousands of creators using JA-Builder.',
        buttonText: 'Get Started Now',
        buttonUrl: '#',
        padding: 'py-32',
        bgColor: '#4f46e5',
        textColor: '#ffffff',
        radius: 'rounded-2xl',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

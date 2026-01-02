import { Navigation } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'post_nav',
    label: 'Post Navigation',
    icon: Navigation,
    description: 'Previous and next post navigation links.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/PostNavBlock.vue')),
    settings: [
        { key: 'prev_label', type: 'text', label: 'Previous Label', default: 'Previous' },
        { key: 'next_label', type: 'text', label: 'Next Label', default: 'Next' },
        { key: 'prev_title', type: 'text', label: 'Previous Title', default: 'Previous Post Title' },
        { key: 'next_title', type: 'text', label: 'Next Title', default: 'Next Post Title' },
        { key: 'prev_url', type: 'text', label: 'Previous URL', default: '#' },
        { key: 'next_url', type: 'text', label: 'Next URL', default: '#' },
        { key: 'show_titles', type: 'boolean', label: 'Show Post Titles', default: true },
        {
            key: 'style',
            type: 'select',
            label: 'Style',
            options: [
                { label: 'Simple', value: 'simple' },
                { label: 'Cards', value: 'cards' }
            ],
            default: 'simple'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'None', value: '' },
                { label: 'Small', value: 'py-4' },
                { label: 'Medium', value: 'py-8' },
                { label: 'Large', value: 'py-12' }
            ],
            default: 'py-8'
        }
    ],
    defaultSettings: {
        prev_label: 'Previous',
        next_label: 'Next',
        prev_title: 'Previous Post Title',
        next_title: 'Next Post Title',
        prev_url: '#',
        next_url: '#',
        show_titles: true,
        style: 'simple',
        padding: 'py-8',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

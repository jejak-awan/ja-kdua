import { User } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'post_meta',
    label: 'Post Meta',
    icon: User,
    category: 'Dynamic',
    description: 'Displays post author, date, and categories.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/PostMeta.vue')),
    settings: [
        { key: 'showAuthor', type: 'boolean', label: 'Show Author', default: true },
        { key: 'showDate', type: 'boolean', label: 'Show Date', default: true },
        { key: 'showCategories', type: 'boolean', label: 'Show Categories', default: true },
        { key: 'showComments', type: 'boolean', label: 'Show Comments Count', default: false },
        {
            key: 'alignment',
            type: 'select',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'justify-start' },
                { label: 'Center', value: 'justify-center' },
                { label: 'Right', value: 'justify-end' }
            ],
            default: 'justify-start'
        },
        { key: 'color', type: 'color', label: 'Custom Text Color', default: '' }
    ],
    defaultSettings: {
        showAuthor: true,
        showDate: true,
        showCategories: true,
        showComments: false,
        alignment: 'justify-start',
        color: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

import { MessageSquare } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'comments',
    label: 'Comments',
    icon: MessageSquare,
    description: 'Comments section with thread support.',
    component: defineAsyncComponent(() => import('@/components/content-renderer/blocks/CommentsBlock.vue')),
    settings: [
        { key: 'title', type: 'text', label: 'Section Title', default: 'Comments' },
        { key: 'show_avatar', type: 'boolean', label: 'Show Avatars', default: true },
        { key: 'show_reply', type: 'boolean', label: 'Show Reply Button', default: true },
        { key: 'show_date', type: 'boolean', label: 'Show Date', default: true },
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
        },
        { key: 'bgColor', type: 'color', label: 'Background Color', default: '' }
    ],
    defaultSettings: {
        title: 'Comments',
        show_avatar: true,
        show_reply: true,
        show_date: true,
        padding: 'py-8',
        bgColor: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

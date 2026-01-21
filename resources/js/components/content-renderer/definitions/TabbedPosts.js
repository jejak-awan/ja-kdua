import { defineAsyncComponent } from 'vue';
import { LayoutGrid } from 'lucide-vue-next';

export default {
    name: 'TabbedPosts',
    label: 'Tabbed Posts',
    category: 'Magazine',
    icon: LayoutGrid,
    component: defineAsyncComponent(() => import('../blocks/TabbedPostsBlock.vue')),
    settings: {
        fields: [
            { name: 'title', label: 'Section Title', type: 'text', default: 'Latest Updates' },
            {
                name: 'categories',
                label: 'Categories (Comma Separated)',
                type: 'text',
                default: 'Technology, Lifestyle, Design',
                description: 'Enter category names or IDs to create tabs.'
            },
            { name: 'limit', label: 'Posts Per Tab', type: 'number', default: 4 },
            {
                name: 'columns',
                label: 'Columns',
                type: 'select',
                options: [
                    { label: '2 Columns', value: '2' },
                    { label: '3 Columns', value: '3' },
                    { label: '4 Columns', value: '4' }
                ],
                default: '4'
            },
            {
                name: 'padding',
                label: 'Padding',
                type: 'select',
                options: [
                    { label: 'Small', value: 'py-8' },
                    { label: 'Medium', value: 'py-12' },
                    { label: 'Large', value: 'py-20' }
                ],
                default: 'py-12'
            }
        ]
    }
};

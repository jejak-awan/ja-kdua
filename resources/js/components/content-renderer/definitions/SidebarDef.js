import { LayoutDashboard } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'sidebar',
    label: 'Sidebar',
    icon: LayoutDashboard,
    description: 'Sidebar with widgets.',
    component: defineAsyncComponent(() => import('@/components/content-renderer/blocks/SidebarBlock.vue')),
    settings: [
        {
            key: 'location',
            type: 'data_select',
            label: 'Widget Area',
            source: '/admin/ja/widgets/locations',
            description: 'Select which widget area to display here.',
            default: 'sidebar-1'
        },
        { key: 'title', type: 'text', label: 'Sidebar Title Override', default: '' },
        { key: 'show_search', type: 'boolean', label: 'Show Search', default: true },
        { key: 'show_categories', type: 'boolean', label: 'Show Categories', default: true },
        { key: 'show_recent_posts', type: 'boolean', label: 'Show Recent Posts', default: true },
        { key: 'show_tags', type: 'boolean', label: 'Show Tags', default: true },
        { key: 'show_archive', type: 'boolean', label: 'Show Archive', default: false },
        {
            key: 'style',
            type: 'select',
            label: 'Widget Style',
            options: [
                { label: 'Card', value: 'card' },
                { label: 'Minimal', value: 'minimal' },
                { label: 'Filled', value: 'filled' }
            ],
            default: 'card'
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
        title: '',
        show_search: true,
        show_categories: true,
        show_recent_posts: true,
        show_tags: true,
        show_archive: false,
        style: 'card',
        padding: 'py-8',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

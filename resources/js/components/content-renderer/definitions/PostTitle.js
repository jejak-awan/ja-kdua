import { Type } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'posttitle',
    label: 'Post Title',
    icon: Type,
    description: 'Display dynamic post title with meta.',
    component: defineAsyncComponent(() => import('@/components/content-renderer/blocks/PostTitleBlock.vue')),
    settings: [
        { key: 'title', type: 'text', label: 'Title (Dynamic in frontend)', default: '' },
        { key: 'show_date', type: 'boolean', label: 'Show Date', default: true },
        { key: 'show_author', type: 'boolean', label: 'Show Author', default: true },
        { key: 'show_category', type: 'boolean', label: 'Show Category', default: true },
        { key: 'show_reading_time', type: 'boolean', label: 'Show Reading Time', default: false },
        { key: 'date', type: 'text', label: 'Date (Dynamic)', default: '' },
        { key: 'author', type: 'text', label: 'Author (Dynamic)', default: '' },
        { key: 'category', type: 'text', label: 'Category (Dynamic)', default: '' },
        { key: 'reading_time', type: 'text', label: 'Reading Time', default: '' },
        {
            key: 'title_size',
            type: 'select',
            label: 'Title Size',
            options: [
                { label: 'Medium', value: 'text-3xl md:text-4xl' },
                { label: 'Large', value: 'text-4xl md:text-5xl' },
                { label: 'Extra Large', value: 'text-5xl md:text-6xl' }
            ],
            default: 'text-4xl md:text-5xl'
        },
        {
            key: 'alignment',
            type: 'select',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'text-left' },
                { label: 'Center', value: 'text-center' }
            ],
            default: 'text-center'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'Small', value: 'py-8' },
                { label: 'Medium', value: 'py-16' },
                { label: 'Large', value: 'py-24' }
            ],
            default: 'py-16'
        },
        { key: 'bgColor', type: 'color', label: 'Background Color', default: '' }
    ],
    defaultSettings: {
        title: '',
        show_date: true,
        show_author: true,
        show_category: true,
        show_reading_time: false,
        date: '',
        author: '',
        category: '',
        reading_time: '',
        title_size: 'text-4xl md:text-5xl',
        alignment: 'text-center',
        padding: 'py-16',
        bgColor: '',
        visibility: { mobile: true, tablet: true, desktop: true },
        dynamicSettings: {
            title: 'post.title',
            date: 'post.date',
            author: 'post.author',
            category: 'post.category'
        }
    }
};

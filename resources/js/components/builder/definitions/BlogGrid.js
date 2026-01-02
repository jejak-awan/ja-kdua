import { LayoutGrid } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'blog_grid',
    label: 'Blog Grid',
    icon: LayoutGrid,
    description: 'Display a grid of blog posts with dynamic filtering.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/BlogGridBlock.vue')),
    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Section Title',
            default: ''
        },
        {
            key: 'post_type',
            type: 'select',
            label: 'Post Type',
            options: [
                { label: 'Posts', value: 'post' },
                { label: 'Pages', value: 'page' }
            ],
            default: 'post'
        },
        {
            key: 'category',
            type: 'text',
            label: 'Category (slug)',
            default: ''
        },
        {
            key: 'tag',
            type: 'text',
            label: 'Tag (slug)',
            default: ''
        },
        {
            key: 'limit',
            type: 'number',
            label: 'Number of Posts',
            default: 6
        },
        {
            key: 'layout',
            type: 'select',
            label: 'Layout',
            options: [
                { label: 'Grid', value: 'grid' },
                { label: 'List', value: 'list' }
            ],
            default: 'grid'
        },
        {
            key: 'columns',
            type: 'select',
            label: 'Columns',
            options: [
                { label: '1 Column', value: '1' },
                { label: '2 Columns', value: '2' },
                { label: '3 Columns', value: '3' },
                { label: '4 Columns', value: '4' }
            ],
            default: '3'
        },
        {
            key: 'show_image',
            type: 'boolean',
            label: 'Show Featured Image',
            default: true
        },
        {
            key: 'show_excerpt',
            type: 'boolean',
            label: 'Show Excerpt',
            default: true
        },
        {
            key: 'show_date',
            type: 'boolean',
            label: 'Show Date',
            default: true
        },
        {
            key: 'show_author',
            type: 'boolean',
            label: 'Show Author',
            default: true
        },
        {
            key: 'show_category',
            type: 'boolean',
            label: 'Show Category',
            default: true
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'None', value: '' },
                { label: 'Small', value: 'py-8' },
                { label: 'Medium', value: 'py-16' },
                { label: 'Large', value: 'py-24' }
            ],
            default: 'py-16'
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: ''
        }
    ],
    defaultSettings: {
        title: '',
        post_type: 'post',
        category: '',
        tag: '',
        limit: 6,
        layout: 'grid',
        columns: '3',
        show_image: true,
        show_excerpt: true,
        show_date: true,
        show_author: true,
        show_category: true,
        padding: 'py-16',
        bgColor: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

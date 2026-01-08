import { File } from 'lucide-vue-next';

export default {
    name: 'post',
    label: 'Post',
    category: 'content',
    icon: File,
    color: 'orange',
    description: 'Link to a blog post',
    defaultTitle: 'Post',

    dataSource: {
        endpoint: '/admin/ja/contents?type=post&status=published',
        labelField: 'title',
        valueField: 'id'
    },

    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Label',
            required: true,
            placeholder: 'Menu item label'
        },
        {
            key: 'target_id',
            type: 'data_select',
            label: 'Select Post',
            required: true,
            source: '/admin/ja/contents?type=post&status=published',
            labelField: 'title',
            valueField: 'id'
        },
        {
            key: 'description',
            type: 'textarea',
            label: 'Description',
            placeholder: 'Optional description for mega menus'
        },
        {
            key: 'open_in_new_tab',
            type: 'boolean',
            label: 'Open in new tab',
            default: false
        },
        {
            key: 'icon',
            type: 'icon_picker',
            label: 'Icon',
            default: null
        },
        {
            key: 'css_class',
            type: 'text',
            label: 'CSS Classes',
            placeholder: 'custom-class'
        },
        // Badge
        {
            key: 'badge',
            type: 'text',
            label: 'Badge Text',
            placeholder: 'New',
            group: 'badge'
        },
        {
            key: 'badge_color',
            type: 'select',
            label: 'Badge Color',
            options: [
                { label: 'Primary', value: 'primary' },
                { label: 'Secondary', value: 'secondary' },
                { label: 'Success', value: 'success' },
                { label: 'Warning', value: 'warning' },
                { label: 'Danger', value: 'danger' }
            ],
            default: 'primary',
            group: 'badge'
        },
        // Image
        {
            key: 'image',
            type: 'media',
            label: 'Image',
            group: 'appearance'
        },
        // Mega Menu
        {
            key: 'mega_menu_layout',
            type: 'select',
            label: 'Mega Menu Layout',
            options: [
                { label: 'Default (Dropdown)', value: 'default' },
                { label: 'Mega Menu', value: 'mega' }
            ],
            default: 'default',
            group: 'mega_menu'
        },
        {
            key: 'mega_menu_column',
            type: 'number',
            label: 'Column Number',
            default: 0,
            min: 0,
            max: 6,
            group: 'mega_menu'
        },
        {
            key: 'heading',
            type: 'text',
            label: 'Column Heading',
            placeholder: 'Optional heading text',
            group: 'mega_menu'
        },
        {
            key: 'hide_label',
            type: 'boolean',
            label: 'Hide Label (Show only children)',
            default: false,
            group: 'mega_menu'
        }
    ]
};

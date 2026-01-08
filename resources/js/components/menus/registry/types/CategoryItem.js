import { Tag } from 'lucide-vue-next';

export default {
    name: 'category',
    label: 'Category',
    category: 'content',
    icon: Tag,
    color: 'purple',
    description: 'Link to a category archive',
    defaultTitle: 'Category',

    dataSource: {
        endpoint: '/admin/ja/categories',
        labelField: 'name',
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
            label: 'Select Category',
            required: true,
            source: '/admin/ja/categories',
            labelField: 'name',
            valueField: 'id'
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
        {
            key: 'image_size',
            type: 'select',
            label: 'Image Size',
            options: [
                { label: 'Auto (16:9)', value: 'auto' },
                { label: 'Landscape Small', value: 'landscape_sm' },
                { label: 'Landscape Medium', value: 'landscape_md' },
                { label: 'Landscape Large', value: 'landscape_lg' },
                { label: 'Portrait Small', value: 'portrait_sm' },
                { label: 'Portrait Medium', value: 'portrait_md' },
                { label: 'Full Width (16:9)', value: 'full' }
            ],
            default: 'auto',
            group: 'appearance'
        },
        {
            key: 'description',
            type: 'textarea',
            label: 'Promotion Description (Quote)',
            placeholder: 'Add a quote or short description over the image',
            group: 'appearance'
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

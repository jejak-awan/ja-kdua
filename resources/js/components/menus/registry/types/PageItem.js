import { FileText } from 'lucide-vue-next';

export default {
    name: 'page',
    label: 'Page',
    category: 'content',
    icon: FileText,
    color: 'blue',
    description: 'Link to a page from your site',
    defaultTitle: 'Page',

    // Data source for fetching pages
    dataSource: {
        endpoint: '/admin/ja/contents?type=page&status=published',
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
            label: 'Select Page',
            required: true,
            source: '/admin/ja/contents?type=page&status=published',
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
        // Mega menu settings
        {
            key: 'mega_menu_layout',
            type: 'select',
            label: 'Mega Menu Layout',
            options: [
                { label: 'Default (Dropdown)', value: 'default' },
                { label: 'Grid (2 Columns)', value: 'grid-2' },
                { label: 'Grid (3 Columns)', value: 'grid-3' },
                { label: 'Full Width (4 Columns)', value: 'full' }
            ],
            default: 'default',
            group: 'mega_menu'
        },
        {
            key: 'mega_menu_show_dividers',
            type: 'boolean',
            label: 'Show Column Dividers',
            default: false,
            group: 'mega_menu'
        },
        {
            key: 'show_heading_line',
            type: 'boolean',
            label: 'Show Line Under Heading',
            default: false,
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
                { label: 'Auto', value: 'auto' },
                { label: 'Small (100px)', value: 'sm' },
                { label: 'Medium (150px)', value: 'md' },
                { label: 'Large (200px)', value: 'lg' },
                { label: 'Extra Large (300px)', value: 'xl' },
                { label: 'Full Width', value: 'full' }
            ],
            default: 'auto',
            group: 'appearance'
        }
    ]
};

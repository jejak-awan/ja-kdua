import { Link } from 'lucide-vue-next';

export default {
    name: 'custom',
    label: 'Custom Link',
    category: 'basic',
    icon: Link,
    color: 'emerald',
    description: 'Add a custom URL link',
    defaultTitle: 'New Link',

    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Label',
            required: true,
            placeholder: 'Menu item label'
        },
        {
            key: 'url',
            type: 'text',
            label: 'URL',
            default: '#',
            placeholder: 'https://...'
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
        }
    ]
};

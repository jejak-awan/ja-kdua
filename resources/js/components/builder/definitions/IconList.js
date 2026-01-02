import { List } from 'lucide-vue-next';

export default {
    name: 'icon-list',
    label: 'Icon List',
    icon: List,
    description: 'Bulleted list with customizable icons.',
    component: () => import('@/components/builder/blocks/IconListBlock.vue'),
    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Section Title',
            default: ''
        },
        {
            key: 'items',
            type: 'repeater',
            label: 'List Items',
            itemLabel: 'Item',
            fields: [
                { key: 'title', type: 'text', label: 'Title', default: 'List Item' },
                { key: 'description', type: 'textarea', label: 'Description', default: '' },
                {
                    key: 'icon',
                    type: 'select',
                    label: 'Icon',
                    options: [
                        { label: 'Check', value: 'check' },
                        { label: 'Check Circle', value: 'check-circle' },
                        { label: 'Star', value: 'star' },
                        { label: 'Arrow', value: 'arrow-right' },
                        { label: 'Zap', value: 'zap' },
                        { label: 'Shield', value: 'shield' },
                        { label: 'Heart', value: 'heart' },
                        { label: 'Sparkles', value: 'sparkles' }
                    ],
                    default: 'check'
                }
            ],
            default: [
                { title: 'First Item', description: 'Description for the first item.', icon: 'check' },
                { title: 'Second Item', description: 'Description for the second item.', icon: 'check' },
                { title: 'Third Item', description: 'Description for the third item.', icon: 'check' }
            ]
        },
        {
            key: 'defaultIcon',
            type: 'select',
            label: 'Default Icon',
            options: [
                { label: 'Check', value: 'check' },
                { label: 'Check Circle', value: 'check-circle' },
                { label: 'Star', value: 'star' },
                { label: 'Arrow', value: 'arrow-right' },
                { label: 'Zap', value: 'zap' },
                { label: 'Shield', value: 'shield' },
                { label: 'Heart', value: 'heart' },
                { label: 'Sparkles', value: 'sparkles' }
            ],
            default: 'check'
        },
        {
            key: 'iconSize',
            type: 'select',
            label: 'Icon Size',
            options: [
                { label: 'Small', value: 'small' },
                { label: 'Medium', value: 'medium' },
                { label: 'Large', value: 'large' }
            ],
            default: 'medium'
        },
        {
            key: 'iconColor',
            type: 'color',
            label: 'Icon Color',
            default: ''
        },
        {
            key: 'iconBgColor',
            type: 'color',
            label: 'Icon Background',
            default: ''
        },
        {
            key: 'alignment',
            type: 'select',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
            ],
            default: 'left'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Section Padding',
            options: [
                { label: 'Small', value: 'py-8' },
                { label: 'Medium', value: 'py-12' },
                { label: 'Large', value: 'py-16' }
            ],
            default: 'py-12'
        }
    ],
    defaultSettings: {
        title: '',
        items: [
            { title: 'First Feature', description: 'A brief description of this feature.', icon: 'check' },
            { title: 'Second Feature', description: 'A brief description of this feature.', icon: 'check' },
            { title: 'Third Feature', description: 'A brief description of this feature.', icon: 'check' }
        ],
        defaultIcon: 'check',
        iconSize: 'medium',
        iconColor: '',
        iconBgColor: '',
        alignment: 'left',
        width: 'max-w-2xl',
        padding: 'py-12',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

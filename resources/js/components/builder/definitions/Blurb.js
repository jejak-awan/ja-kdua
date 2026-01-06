import { Info } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'blurb',
    label: 'Blurb',
    icon: Info,
    description: 'A versatile container for an icon, title, and description.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/BlurbBlock.vue')),
    settings: [
        { type: 'header', label: 'Typography', tab: 'content' },
        { key: 'title', type: 'text', label: 'Title', tab: 'content' },
        {
            key: 'titleSize',
            type: 'toggle_group',
            label: 'Title Size',
            options: [
                { label: 'Sm', value: 'text-lg' },
                { label: 'Md', value: 'text-xl' },
                { label: 'Lg', value: 'text-2xl' }
            ],
            default: 'text-xl',
            tab: 'style'
        },
        { key: 'titleColor', type: 'color', label: 'Title Color', default: '', tab: 'style' },
        { key: 'content', type: 'textarea', label: 'Content', tab: 'content' },
        {
            key: 'contentSize',
            type: 'toggle_group',
            label: 'Content Size',
            options: [
                { label: 'Sm', value: 'text-sm' },
                { label: 'Md', value: 'text-base' },
                { label: 'Lg', value: 'text-lg' }
            ],
            default: 'text-base',
            tab: 'style'
        },
        { key: 'contentColor', type: 'color', label: 'Content Color', default: '', tab: 'style' },
        { type: 'header', label: 'Media', tab: 'content' },
        { key: 'use_icon', type: 'boolean', label: 'Use Icon', default: true, tab: 'content' },
        {
            key: 'icon_name',
            type: 'text',
            label: 'Icon Name (Lucide)',
            condition: (s) => s.use_icon,
            tab: 'content'
        },
        {
            key: 'image_url',
            type: 'image',
            label: 'Image URL',
            condition: (s) => !s.use_icon,
            tab: 'content'
        },
        { type: 'header', label: 'Style & Layout', tab: 'style' },
        {
            key: 'icon_position',
            type: 'select',
            label: 'Icon Position',
            options: [
                { label: 'Top', value: 'top' },
                { label: 'Left', value: 'left' }
            ],
            tab: 'style'
        },
        {
            key: 'alignment',
            type: 'select',
            label: 'Text Alignment',
            options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
            ],
            tab: 'style'
        },
        { key: 'icon_color', type: 'color', label: 'Icon Color', tab: 'style' }
    ],
    defaultSettings: {
        title: 'Your Feature Title',
        content: 'Add a short description here to explain your feature or service in more detail.',
        icon_name: 'Zap',
        icon_color: '',
        icon_position: 'top',
        alignment: 'center',
        use_icon: true
    }
};

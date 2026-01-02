import { Info } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'blurb',
    label: 'Blurb',
    icon: Info,
    description: 'A versatile container for an icon, title, and description.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/BlurbBlock.vue')),
    settings: [
        { key: 'title', type: 'text', label: 'Title' },
        { key: 'content', type: 'textarea', label: 'Content' },
        { key: 'use_icon', type: 'boolean', label: 'Use Icon', default: true },
        {
            key: 'icon_name',
            type: 'text',
            label: 'Icon Name (Lucide)',
            condition: (s) => s.use_icon
        },
        {
            key: 'image_url',
            type: 'text',
            label: 'Image URL',
            condition: (s) => !s.use_icon
        },
        {
            key: 'icon_position',
            type: 'select',
            label: 'Icon Position',
            options: [
                { label: 'Top', value: 'top' },
                { label: 'Left', value: 'left' }
            ]
        },
        {
            key: 'alignment',
            type: 'select',
            label: 'Text Alignment',
            options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
            ]
        },
        { key: 'icon_color', type: 'color', label: 'Icon Color' }
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

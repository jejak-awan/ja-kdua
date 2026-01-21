import { FileText } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'postcontent',
    label: 'Post Content',
    icon: FileText,
    description: 'Display dynamic post body content.',
    component: defineAsyncComponent(() => import('@/components/content-renderer/blocks/PostContentBlock.vue')),
    settings: [
        { key: 'content', type: 'textarea', label: 'Content (Dynamic in frontend)', default: '' },
        {
            key: 'max_width',
            type: 'select',
            label: 'Max Width',
            options: [
                { label: 'Small (640px)', value: 'max-w-xl' },
                { label: 'Medium (768px)', value: 'max-w-3xl' },
                { label: 'Large (1024px)', value: 'max-w-4xl' },
                { label: 'Full', value: 'max-w-full' }
            ],
            default: 'max-w-4xl'
        },
        {
            key: 'font_size',
            type: 'select',
            label: 'Font Size',
            options: [
                { label: 'Small', value: 'text-sm' },
                { label: 'Base', value: 'text-base' },
                { label: 'Large', value: 'text-lg' }
            ],
            default: 'text-base'
        },
        {
            key: 'line_height',
            type: 'select',
            label: 'Line Height',
            options: [
                { label: 'Normal', value: 'leading-normal' },
                { label: 'Relaxed', value: 'leading-relaxed' },
                { label: 'Loose', value: 'leading-loose' }
            ],
            default: 'leading-relaxed'
        },
        {
            key: 'alignment',
            type: 'select',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'text-left' },
                { label: 'Center', value: 'text-center' },
                { label: 'Justify', value: 'text-justify' }
            ],
            default: 'text-left'
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
        content: '',
        max_width: 'max-w-4xl',
        font_size: 'text-base',
        line_height: 'leading-relaxed',
        alignment: 'text-left',
        padding: 'py-8',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

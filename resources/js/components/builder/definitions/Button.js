import { MousePointer } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'button',
    label: 'Button',
    icon: MousePointer,
    description: 'Customizable call-to-action button.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/ButtonBlock.vue')),
    settings: [
        {
            key: 'text',
            type: 'text',
            label: 'Button Text',
            default: 'Click Here'
        },
        {
            key: 'url',
            type: 'text',
            label: 'Link URL',
            default: '#'
        },
        {
            key: 'openNewTab',
            type: 'boolean',
            label: 'Open in New Tab',
            default: false
        },
        {
            key: 'variant',
            type: 'select',
            label: 'Style',
            options: [
                { label: 'Primary', value: 'primary' },
                { label: 'Secondary', value: 'secondary' },
                { label: 'Outline', value: 'outline' },
                { label: 'Ghost', value: 'ghost' }
            ],
            default: 'primary'
        },
        {
            key: 'size',
            type: 'select',
            label: 'Size',
            options: [
                { label: 'Small', value: 'small' },
                { label: 'Medium', value: 'medium' },
                { label: 'Large', value: 'large' }
            ],
            default: 'medium'
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
            key: 'iconName',
            type: 'select',
            label: 'Icon',
            options: [
                { label: 'None', value: '' },
                { label: 'Arrow Right', value: 'arrow-right' },
                { label: 'External Link', value: 'external-link' },
                { label: 'Chevron Right', value: 'chevron-right' },
                { label: 'Download', value: 'download' },
                { label: 'Play', value: 'play' },
                { label: 'Mail', value: 'mail' },
                { label: 'Phone', value: 'phone' }
            ],
            default: ''
        },
        {
            key: 'iconPosition',
            type: 'select',
            label: 'Icon Position',
            options: [
                { label: 'Left', value: 'left' },
                { label: 'Right', value: 'right' }
            ],
            default: 'right'
        },
        {
            key: 'radius',
            type: 'select',
            label: 'Border Radius',
            options: [
                { label: 'None', value: 'rounded-none' },
                { label: 'Small', value: 'rounded-lg' },
                { label: 'Medium', value: 'rounded-xl' },
                { label: 'Full', value: 'rounded-full' }
            ],
            default: 'rounded-full'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Section Padding',
            options: [
                { label: 'None', value: 'py-0' },
                { label: 'Small', value: 'py-4' },
                { label: 'Medium', value: 'py-8' },
                { label: 'Large', value: 'py-12' }
            ],
            default: 'py-8'
        }
    ],
    defaultSettings: {
        text: 'Click Here',
        url: '#',
        openNewTab: false,
        variant: 'primary',
        size: 'medium',
        alignment: 'left',
        iconName: '',
        iconPosition: 'right',
        radius: 'rounded-full',
        padding: 'py-8',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

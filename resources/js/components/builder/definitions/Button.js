import { MousePointer, AlignLeft, AlignCenter, AlignRight, ArrowLeft, ArrowRight } from 'lucide-vue-next';
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
            default: 'Click Here',
            tab: 'content'
        },
        {
            key: 'url',
            type: 'text',
            label: 'Link URL',
            default: '#',
            tab: 'content'
        },
        {
            key: 'openNewTab',
            type: 'boolean',
            label: 'Open in New Tab',
            default: false,
            tab: 'content'
        },
        {
            key: 'variant',
            type: 'toggle_group',
            label: 'Style',
            options: [
                { label: 'Solid', value: 'primary' },
                { label: 'Soft', value: 'secondary' },
                { label: 'Outline', value: 'outline' },
                { label: 'Ghost', value: 'ghost' }
            ],
            default: 'primary',
            tab: 'style'
        },
        {
            key: 'size',
            type: 'toggle_group',
            label: 'Size',
            options: [
                { label: 'Sm', value: 'small' },
                { label: 'Md', value: 'medium' },
                { label: 'Lg', value: 'large' }
            ],
            default: 'medium',
            tab: 'style'
        },
        {
            key: 'alignment',
            type: 'toggle_group',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'left', icon: AlignLeft },
                { label: 'Center', value: 'center', icon: AlignCenter },
                { label: 'Right', value: 'right', icon: AlignRight }
            ],
            default: 'left',
            tab: 'style'
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
            default: '',
            tab: 'style'
        },
        {
            key: 'iconPosition',
            type: 'toggle_group',
            label: 'Icon Position',
            options: [
                { label: 'Left', value: 'left', icon: ArrowLeft },
                { label: 'Right', value: 'right', icon: ArrowRight }
            ],
            default: 'right',
            tab: 'style'
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
            default: 'rounded-full',
            tab: 'style'
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
            default: 'py-8',
            tab: 'style'
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

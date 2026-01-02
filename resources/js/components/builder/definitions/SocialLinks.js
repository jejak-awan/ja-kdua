import { Share2 } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'social-links',
    label: 'Social Links',
    icon: Share2,
    description: 'Social media icon links.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/SocialLinksBlock.vue')),
    settings: [
        {
            key: 'links',
            type: 'repeater',
            label: 'Social Links',
            itemLabel: 'Link',
            fields: [
                {
                    key: 'platform',
                    type: 'select',
                    label: 'Platform',
                    options: [
                        { label: 'Facebook', value: 'facebook' },
                        { label: 'Twitter/X', value: 'twitter' },
                        { label: 'Instagram', value: 'instagram' },
                        { label: 'LinkedIn', value: 'linkedin' },
                        { label: 'YouTube', value: 'youtube' },
                        { label: 'GitHub', value: 'github' },
                        { label: 'Email', value: 'email' },
                        { label: 'Website', value: 'website' },
                        { label: 'WhatsApp', value: 'whatsapp' },
                        { label: 'Telegram', value: 'telegram' },
                        { label: 'Phone', value: 'phone' }
                    ],
                    default: 'facebook'
                },
                { key: 'url', type: 'text', label: 'URL', default: '#' }
            ],
            default: [
                { platform: 'facebook', url: '#' },
                { platform: 'twitter', url: '#' },
                { platform: 'instagram', url: '#' },
                { platform: 'linkedin', url: '#' }
            ]
        },
        {
            key: 'size',
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
            key: 'shape',
            type: 'select',
            label: 'Icon Shape',
            options: [
                { label: 'Circle', value: 'circle' },
                { label: 'Rounded', value: 'rounded' },
                { label: 'Square', value: 'square' }
            ],
            default: 'circle'
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
            default: 'center'
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
            label: 'Background Color',
            default: ''
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Section Padding',
            options: [
                { label: 'Small', value: 'py-4' },
                { label: 'Medium', value: 'py-8' },
                { label: 'Large', value: 'py-12' }
            ],
            default: 'py-8'
        }
    ],
    defaultSettings: {
        links: [
            { platform: 'facebook', url: '#' },
            { platform: 'twitter', url: '#' },
            { platform: 'instagram', url: '#' },
            { platform: 'linkedin', url: '#' }
        ],
        size: 'medium',
        shape: 'circle',
        alignment: 'center',
        iconColor: '',
        iconBgColor: '',
        width: 'max-w-4xl',
        padding: 'py-8',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

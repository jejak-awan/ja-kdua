import { Heading } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'heading',
    label: 'Heading',
    icon: Heading,
    description: 'Customizable heading with typography controls.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/HeadingBlock.vue')),
    settings: [
        {
            key: 'text',
            type: 'text',
            label: 'Heading Text',
            default: 'Heading Text'
        },
        {
            key: 'subtitle',
            type: 'text',
            label: 'Subtitle (Optional)',
            default: ''
        },
        {
            key: 'tag',
            type: 'select',
            label: 'HTML Tag',
            options: [
                { label: 'H1', value: 'h1' },
                { label: 'H2', value: 'h2' },
                { label: 'H3', value: 'h3' },
                { label: 'H4', value: 'h4' },
                { label: 'H5', value: 'h5' },
                { label: 'H6', value: 'h6' }
            ],
            default: 'h2'
        },
        {
            key: 'size',
            type: 'select',
            label: 'Size',
            options: [
                { label: 'Small', value: 'small' },
                { label: 'Medium', value: 'medium' },
                { label: 'Large', value: 'large' },
                { label: 'Extra Large', value: 'xlarge' },
                { label: 'Display', value: 'display' }
            ],
            default: 'large'
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
            key: 'decoration',
            type: 'select',
            label: 'Decoration',
            options: [
                { label: 'None', value: 'none' },
                { label: 'Underline', value: 'underline' },
                { label: 'Highlight', value: 'highlight' },
                { label: 'Gradient', value: 'gradient' }
            ],
            default: 'none'
        },
        {
            key: 'textColor',
            type: 'color',
            label: 'Text Color',
            default: ''
        },
        {
            key: 'width',
            type: 'select',
            label: 'Max Width',
            options: [
                { label: 'Full', value: 'max-w-full' },
                { label: 'Large', value: 'max-w-4xl' },
                { label: 'Medium', value: 'max-w-2xl' },
                { label: 'Small', value: 'max-w-xl' }
            ],
            default: 'max-w-4xl'
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
        text: 'Heading Text',
        subtitle: '',
        tag: 'h2',
        size: 'large',
        alignment: 'left',
        decoration: 'none',
        textColor: '',
        width: 'max-w-4xl',
        padding: 'py-8',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};

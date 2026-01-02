import { MessageSquareQuote } from 'lucide-vue-next';

export default {
    name: 'testimonial',
    label: 'Testimonials',
    icon: MessageSquareQuote,
    description: 'Showcase social proof.',
    component: () => import('@/components/builder/blocks/TestimonialBlock.vue'),
    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Title',
            default: 'What Clients Say'
        },
        {
            key: 'items',
            type: 'repeater',
            label: 'Testimonials',
            itemLabel: 'Testimonial',
            fields: [
                { key: 'quote', type: 'textarea', label: 'Quote', default: 'This is an amazing product.' },
                { key: 'author', type: 'text', label: 'Author', default: 'Jane Doe' },
                { key: 'role', type: 'text', label: 'Role', default: 'CEO' },
                { key: 'avatar', type: 'image', label: 'Avatar', default: '' }
            ],
            default: [
                { quote: 'This tool completely changed how we work. Highly recommended!', author: 'Sarah Johnson', role: 'Product Manager', avatar: '' },
                { quote: 'The best builder experience I have ever had. Smooth and intuitive.', author: 'Mike Chen', role: 'Developer', avatar: '' },
                { quote: 'Incredible flexibility and performance. A game changer.', author: 'Emily Davis', role: 'Designer', avatar: '' }
            ]
        },
        {
            key: 'width',
            type: 'select',
            label: 'Width',
            options: [
                { label: 'Large', value: 'max-w-7xl' },
                { label: 'Full', value: 'max-w-full' }
            ],
            default: 'max-w-7xl'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'Medium', value: 'py-20' },
                { label: 'Large', value: 'py-32' }
            ],
            default: 'py-20'
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: 'transparent'
        }
    ],
    defaultSettings: {
        title: 'What Clients Say',
        items: [
            { quote: 'This tool completely changed how we work. Highly recommended!', author: 'Sarah Johnson', role: 'Product Manager', avatar: '' },
            { quote: 'The best builder experience I have ever had. Smooth and intuitive.', author: 'Mike Chen', role: 'Developer', avatar: '' },
            { quote: 'Incredible flexibility and performance. A game changer.', author: 'Emily Davis', role: 'Designer', avatar: '' }
        ],
        width: 'max-w-7xl',
        padding: 'py-20',
        bgColor: 'transparent',
        radius: 'rounded-none',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};
